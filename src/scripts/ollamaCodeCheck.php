<?php
//setup
$config = include 'setConfig.php';
$model = strval($config['model']);
$codeLanguages = strval($config['code_languages']);
$frameworks = strval($config['frameworks']);
$extendingPrompt = strval($config['extending_prompt']);

//execute
$changedStagedFileNames = include 'getStagedFileNames.php';
ini_set('memory_limit', '1024M');

if ($changedStagedFileNames === null || count($changedStagedFileNames) === 0) {
    echo "\033[31mNo changes recognized (Deleted files are not included to be checked)...\033[0m.\n";

    return;
}

$basePrompt = "    
    Scan the changes for the following mistakes: syntax errors, production code vulnerabilities, major mistakes with the framework(s): $frameworks, major coding language(s) mistakes: $codeLanguages, coding mistakes that would cause 500 errors.
    Ignore 'git diff' output styling in the code snippets, do not give feedback on this part.
    !!Dont explain code that does not contain mistakes!!
    Make sure your feedback is correct
    Strictly format your feedback as follows:
    - File or function name which contains the mistake
    - first showcase a small part of the original code which contains the code mistake
    - real briefly explain in less then 50 words how it should be improved
";

if ($config['per_file']) {
    $feedbackFilesIndex = 0;
    $totalFeedbackFilesCount = count($changedStagedFileNames);
    foreach ($changedStagedFileNames as $changedFileName) {
        $feedbackFilesIndex++;
        $changes = shell_exec('git diff HEAD -U14 '.$changedFileName);
        $prompt = $basePrompt." ".$extendingPrompt." The changes for this staged git commit file $changedFileName are: $changes";
        $data = [
            'model' => $model,
            'prompt' => $prompt,
            'temperature' => '0.0',
        ];

        $jsonData = json_encode($data);

        $ch = curl_init('http://localhost:11434/api/generate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($jsonData),
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);

        $response = curl_exec($ch);
        if (curl_errno($ch) || $response === null) {
            echo "\033[31mError connecting to the local Ollama, make sure Ollama is running...\033[0m\n";

            return;
        }

        curl_close($ch);

        $parsedBody = parseMultiJson($response);

        $responses = array_column($parsedBody, 'response');
        array_unshift($responses, "\033[33m\033[1m[Ollama feedback]\033[0m \n");
        $concatenatedResponse = implode('', $responses);

        echo colorizeOutput($concatenatedResponse)."\n";
        echo "\n\033[33m\033[1mFeedback [$feedbackFilesIndex/$totalFeedbackFilesCount]\033[0m\n";
    }
} else {
    $allChanges = [];
    foreach ($changedStagedFileNames as $changedFileName) {
        $diff = shell_exec('git diff HEAD -U14 '.$changedFileName);
        $allChanges[] = "Git diff changes in file $changedFileName with changes: ".$diff;
    }

    $allChanges = implode('. Next: ', $allChanges);
    $prompt = $basePrompt.$extendingPrompt." The changes for this staged git commit: $allChanges";
    $data = [
        'model' => $model,
        'prompt' => $prompt,
        'temperature' => '0.0',
    ];

    $jsonData = json_encode($data);

    $ch = curl_init('http://localhost:11434/api/generate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: '.strlen($jsonData),
    ]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);

    $response = curl_exec($ch);
    if (curl_errno($ch) || $response === null) {
        echo "\033[31mError connecting to the local Ollama, make sure Ollama is running...\033[0m\n";

        return;
    }

    curl_close($ch);

    $parsedBody = parseMultiJson($response);

    $responses = array_column($parsedBody, 'response');
    array_unshift($responses, "\033[33m\033[1m[Ollama feedback]\033[0m \n");
    $concatenatedResponse = implode('', $responses);

    echo colorizeOutput($concatenatedResponse)."\n";

    return $concatenatedResponse;
}


function parseMultiJson($string)
{
    $lines = explode("\n", trim($string));
    $result = [];

    foreach ($lines as $line) {
        if (!empty($line)) {
            $decoded = json_decode($line, true);
            if ($decoded !== null) {
                $result[] = $decoded;
            }
        }
    }

    return $result;
}

function colorizeOutput($text)
{
    // Remove code block markers and triple backticks
    $text = preg_replace('/```(?:php|diff|sh|[a-z]+)?\s*/', '', $text);
    $text = preg_replace('/```/', '', $text);

    // Colorize word directly followed by '('
    // Blue color code for the word and the parenthesis
    $text = preg_replace('/(\w+)(\()/', "\033[94m$1\033[0m$2", $text);

    // Colorize words followed by '->'
    $text = preg_replace('/(\w+)(->)/', "\033[94m$1\033[0m$2", $text);

    // Colorize words followed by '->'
    $text = preg_replace('/(\w+)(->)/', "\033[94m$1\033[0m$2", $text);

    // This pattern matches any sequence of digits that are directly followed by a '.'
    $pattern = '/(\d+)\.(?=\s|$)/';

    // This replacement pattern wraps matched digits with green color codes, preserving the dot after the number
    $replacement = "\033[32m$1\033[0m.";

    // Replace each digit sequence followed by a dot with the colored version
    $colorizedNumbersText = preg_replace($pattern, $replacement, $text);

    // Color text wrapped in double asterisks **text** with yellow and bold
    $patternText = '/\*\*(.*?)\*\*/';
    $replacementText = "\033[33m\033[1m$1\033[0m";
    $colorizedText = preg_replace($patternText, $replacementText, $colorizedNumbersText);

    $colorizedText = preg_replace_callback('/^(###\s.*)$/m', function ($matches) {
        // Define the color code you want
        $colorCode = "\033[35m"; // Example color code (magenta)
        // Return the colored text
        return "$colorCode$matches[1]\033[0m";
    }, $colorizedText);

    return $colorizedText;
}