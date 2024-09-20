<?php
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