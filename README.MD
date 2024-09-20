# A.I. code feedback (via Ollama LLM provider)

Utilize the Ollama open-source local LLM to provide insightful feedback on modified code, which can be integrated with your pre-commit script or used to analyze all your changes against your primary branch.

```bash
composer require clarity-digital/ollama-code-feedback
```

## Table of content

- [Installation - base](#installation---base)
    - [Step 1 - install Ollama](#step-1---install-ollama)
    - [Review all current file changes vs primary branch](#review-all-current-file-changes-vs-primary-branch)
- [Installation - pre commit hook](#installation---pre-commit-hook)
    - [Step 1 - setup Husky pre-commit](#step-1---setup-husky-pre-commit)
    - [Step 2 - add Ollama feedback code to the pre-commit script](#step-2---add-ollama-feedback-code-to-the-pre-commit-script)
- [Configurations](#configurations)
    - [Example custom config file](#example-custom-config-file)

## Installation - base
### Step 1 - install Ollama
Make sure you have <b>Ollama</b> installed and running (<a href="https://ollama.com/download" target="_blank">link</a>)</br>
It is up to you which AI model you want to use, but we recommend using: </br>
<b>deepseek-coder-v2</b> (<a href="https://ollama.com/library/deepseek-coder-v2" target="_blank">link</a>)

You can also take a look <a href="https://ollama.com/library?c=code" target="_blank"><b>here</b></a> to see all the coding models.

### <u>Review all current file changes vs primary branch</u>
#### Execute the following php file
```bash
php ./vendor/clarity-digital/ollama-code-feedback/src/scripts/ollamaAllChangesCheck.php
```
Make sure you have setup your custom config file to contain the right primary branch name if necessary.
The default value is ```main```.
```php
return [
    'github_primary_branch_name' => 'main',
];
```

## Installation - pre commit hook
### Step 1 - setup Husky pre-commit
<b>Install husky if not done already.</b></br></br>
Follow the installation steps here:
https://typicode.github.io/husky/get-started.html
</br></br>Or if you are using <b>npm</b> execute the following commands:<br/>
```bash
  npm install --save-dev husky
  npx husky init
```

### Step 2 - add Ollama feedback code to the pre-commit script
Add the following line of code to the ```.husky/pre-commit``` file</br>
```php
php ./vendor/clarity-digital/ollama-code-feedback/src/scripts/ollamaCodeCheck.php
```
## Configurations
By <b>default</b> the following config file is set, which instructs the A.I. model what model to use and what specific code languages and frameworks to focus its feedback on.
```php
[
    'model' => 'deepseek-coder-v2',
    'frameworks' => 'laravel',
    'github_primary_branch_name' => 'main',
    'code_languages' => 'php',
    //Will also take new created staged files into consideration when set to false:
    'modified_files_only' => true, 
    // Will send a request per staged code file (Recommended to be set false)
    'per_file' => false,
    'extending_prompt' => '',
]
```

<br>

#### You can modify this config file to suit your preferences.
1. Within your root folder create a ```/config``` directory and within that directory create a file ```php``` named ```ai_code_config```.
2. Ensure this config file returns an array. If a config key is not set in the custom config file, the default value(s) will be used.
#### Example custom config file
```php
return [
    'model' => 'llama3',
    'frameworks' => 'laravel & react',
    'github_primary_branch_name' => 'main',
    'code_languages' => 'php & javascript',
    'extending_prompt' => 'Also focus on giving me feedback for better function naming',
    'per_file' => true,
];
```
<sup>*</sup> Whenever you change the model the script will automatically download the Ollama model for you.