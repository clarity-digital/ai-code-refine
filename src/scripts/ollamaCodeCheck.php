<?php
//setup
$config = include 'setConfig.php';
$model = strval($config['model']);
$gitDiffCommand = strval($config['git_diff_command']);
$codeLanguages = strval($config['code_languages']);
$frameworks = strval($config['frameworks']);
$extendingPrompt = strval($config['extending_prompt']);

//execute
$changes = explode('diff --git', shell_exec($gitDiffCommand));
for ($i = 0; $i < count($changes); $i++) {
    $num = $i + 1;
    $changes[$i] = "[End of previous code snippet] The following code snippet must be checked for major critical errors and nothing else,
Code Snippet: ".$changes[$i].". !end of code change snippet!";
}
$changes = implode('', $changes);
ini_set('memory_limit', '1024M');

if ($changes === null || strlen($changes) === 0 || strlen(str_replace(' ', '', $changes)) === 0) {
    echo "\033[31mNo changes recognized (Deleted files are not included to be checked)...\033[0m.";

    return;
}

$prompt = "
I will give you the output of my 'git diff -U20' command, keep in mind you only see a small portion of a code snippet. 
You should NOT check for minor/mediocre $codeLanguages coding convention mistakes ignore these, focus on giving feedback for major mistakes for/in implementations of modern framework(s): $frameworks or $codeLanguages code. 
Do NOT give feedback that says that code comments should be added.
$extendingPrompt .
";

$data = [
    'model' => $model,
    'prompt' => $prompt.$changes,
    'temperature' => '0.2',
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
    echo "\033[31mError connecting to the local Ollama, make sure 'deepseek-coder-v2' is running...\033[0m";

    return;
}

curl_close($ch);

$parsedBody = parseMultiJson($response);

$responses = array_column($parsedBody, 'response');
array_unshift($responses, "\033[33m\033[1m[Ollama feedback]\033[0m \n");
$concatenatedResponse = implode('', $responses);

echo colorizeOutput($concatenatedResponse);

return $concatenatedResponse;

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