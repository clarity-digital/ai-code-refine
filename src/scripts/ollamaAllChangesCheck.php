<?php
namespace src\scripts;

require_once 'baseFunctions.php';
require_once 'ollamaIsRunningCheck.php';
echo "\n";
echo "\033[32mRunning Ollama A.I. all changes code check, please wait...\033[0m \n";

//setup
$config = include 'setConfig.php';
$model = strval($config['model']);

$codeLanguages = strval($config['code_languages']);
$frameworks = strval($config['frameworks']);
$extendingPrompt = strval($config['extending_prompt']);
$primaryBranchName = strval($config['github_primary_branch_name']);

$changedFileNames = include 'getAllChangedFilesNames.php';

$basePrompt = include 'getBasePrompt.php';
$feedbackFilesIndex = 0;
$totalFeedbackFilesCount = count($changedFileNames);

foreach ($changedFileNames as $changedFileName) {
    $feedbackFilesIndex++;
    $changes = shell_exec("git diff origin/$primaryBranchName...HEAD -U14 ".$changedFileName);

    $prompt = $basePrompt." ".$extendingPrompt." The changes for this staged git commit file named '$changedFileName' are: $changes";

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
    array_unshift($responses, "\033[33m\033[1m[Ollama feedback: $changedFileName]\033[0m \n");
    $concatenatedResponse = implode('', $responses);

    echo colorizeOutput($concatenatedResponse)."\n";
    echo "\n\033[33m\033[1mFeedback [$feedbackFilesIndex/$totalFeedbackFilesCount]\033[0m\n\n";
}