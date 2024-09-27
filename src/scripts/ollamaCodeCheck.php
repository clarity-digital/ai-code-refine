<?php
namespace src\scripts;

include_once 'ollamaIsRunningCheck.php';
include_once 'baseFunctions.php';

echo "\n";
echo "\033[32mRunning Ollama A.I. code check, please wait...\033[0m";

//setup
$config = include 'setConfig.php';
$model = strval($config['model']);
$extendingPrompt = strval($config['extending_prompt']);

//execute
$changedStagedFileNames = include 'getStagedFileNames.php';
ini_set('memory_limit', '1024M');

if ($changedStagedFileNames === null || count($changedStagedFileNames) === 0) {
    echo "\033[31mNo changes recognized (Deleted files are not included to be checked)...\033[0m.\n";

    return;
}

$basePrompt = include 'getBasePrompt.php';

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

    echo "\n";
    echo "\033[32mEnd Ollama A.I. response\033[0m";
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

    echo "\n";
    echo "\033[32mEnd Ollama A.I. response\033[0m";
    return $concatenatedResponse;
}