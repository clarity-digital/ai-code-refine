# *IN DEVELOPMENT*

# A.I. code feedback (via Ollama LLM provider)

Utilize the Ollama open-source local LLM to provide insightful feedback on modified code.
Which will be binded to your pre commit script.

```bash
composer require clarity-digital/ollama-code-feedback
```
## Installation
### Step 1 - install Ollama
Make sure you have <b>Ollama</b> installed and running (<a href="https://ollama.com/download" target="_blank">link</a>)</br>
It is up to you which AI model you want to use, but we recommend using: </br>
<b>llama3</b> (<a href="https://ollama.com/library/llama3" target="_blank">link</a>)

### Step 2 - setup Husky pre-commit
<b>Install husky if not done already.</b></br></br>
Follow the installation steps here:
https://typicode.github.io/husky/get-started.html
</br></br>Or if you are using <b>npm</b> execute the following commands:<br/>
```bash
  npm install --save-dev husky
  npx husky init
```

### Step 3 - add Ollama feedback code to the pre-commit script
Add the following line of codes to the ```.husky/pre-commit``` file</br>
<b>At the start of the script to validate that the A.I. model is running:</b>
```php
echo "\n";
echo "\033[32mValidating if Ollama is running...\033[0m"
php ./vendor/clarity-digital/ollama-code-feedback/src/scripts/ollamaIsRunningCheck.php

echo '\n';
```
<b>At the end of the script:</b>
```php
echo "\n";
echo "\033[32mRunning Ollama A.I. code check, please wait...\033[0m"
php ./vendor/clarity-digital/ollama-code-feedback/src/scripts/ollamaCodeCheck.php

echo '\n';
echo '\033[32mEnd Ollama A.I. response\033[0m';
```
## Configurations
By default the following config file is set, which instructs the A.I. model what model to use and what specific code languages and frameworks to focus its feedback on.
```php
[
    'model' => 'llama3',
    'frameworks' => 'laravel',
    'code_languages' => 'php',
    'modified_files_only' => true, //Or: false, will also take new created staged files into consideration
    'extending_prompt' => '',
]
```

<br>

#### You can modify this config file to suit your preferences.
1. Within your root folder create a ```/config``` directory and within create a file named ```'ai_code_config.php'```.
2. Ensure this config file returns an array. If a config key is not set in the custom config file, the default value(s) will be used.

#### Example custom config file</b>
```php
return [
    'model' => 'deepseek-coder-v1',
    'frameworks' => 'laravel & react',
    'code_languages' => 'php & javascript',
    'extending_prompt' => 'Also focus on giving me feedback for better function naming',
];
```
