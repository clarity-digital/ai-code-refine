<?php

//does not include deleted files, or files that are not staged
$changes = shell_exec('git diff -U14 --diff-filter=ACMRTUXB --staged');
ini_set('memory_limit', '1024M');

if ($changes === null || strlen($changes) === 0) {
    echo "\033[31mNo changes recognized (Deleted files are not included to be checked)...\033[0m.";

    return;
}

$prompt = "
I will give you the output of my 'git diff -U20' command, keep in mind you only see a small portion of a code snippet. 
You should NOT check for major php coding convention mistakes ignore these, focus on giving feedback for major mistakes for/in implementations of modern Laravel/php code. 
Do NOT give feedback that says that code comments should be added.
Emphasize what code contain mistakes and emphasize how it can be improved, try to give a concise answer.
If there is no major mistakes found in the code please just return 'no major mistakes'.
The 'git diff -U20' output:
";

$data = [
    'model' => 'deepseek-coder-v2',
    'prompt' => $prompt.$changes,
    'temperature' => '0.3',
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

    return $colorizedText;
}
