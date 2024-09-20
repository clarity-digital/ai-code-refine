<?php
echo "\n";
echo "\033[32mValidating if Ollama is running...\033[0m\n";

$url = 'http://localhost:11434';

// Initialize cURL session for a HEAD request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // Only check if the resource exists
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Connection timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Overall timeout

// Execute the request
$response = curl_exec($ch);

if ($response === false || curl_errno($ch)) {
    curl_close($ch);
    echo "\n\033[32mPlease boot Ollama or install Ollama on your device\033[0m\n";
    echo "\033[33mLink:\033[0m \033]8;;https://ollama.com/library/deepseek-coder-v2\033\\Click here to navigate to the Ollama download page\033]8;;\033\\ \n\n";
    throw new Exception("\033[31mError connecting to the local Ollama, make sure 'deepseek-coder-v2' is running...\033[0m");
}

//check if the required model exists
$config = include 'setConfig.php';
$model = strval($config['model']);

$ollamaList = shell_exec("ollama list");

if (!str_contains($ollamaList, $model)) {
    echo "\n\033[32mModel: '$model' is not installed yet.\033[0m\n";
    echo "\033[32mDownloading '$model' please wait...\033[0m\n";

    $ollamaPullResponse = shell_exec("ollama pull $model");

    $ollamaList = shell_exec("ollama list");
    if (!str_contains($ollamaList, $model)) {
        throw new Exception("\033[31mOllama doesn't recognize model: '$model'.\033[0m");
    }

    echo "\033[32mSuccessfully downloaded Ollama model: '$model'.\033[0m\n";
}

echo "\033[32mOllama is running successfully.\033[0m\n";
curl_close($ch);


echo "\n";
return 0;

