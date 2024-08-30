# *IN DEVELOPMENT*

# A.I. code feedback (via Ollama LLM)

Utilize the Ollama open-source local LLM to provide insightful feedback on modified code.
Which will be binded to your pre commit script.


## Installation
### Step 1 - install Ollama
Make sure you have <b>Ollama</b> installed and running (<a href="https://ollama.com/download" target="_blank">link</a>)</br>
It is up to you which AI model you want to use, but we recommend using: </br>
<b>deepseek-coder-v2</b> (<a href="https://ollama.com/library/deepseek-coder-v2" target="_blank">link</a>)

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
<b>Add the following code to the pre-commit file</b></br>
At the start of the script:
```php
echo "\n";
echo "\033[32mValidating if Ollama is running...\033[0m"
php ./vendor/Clarity/ai-code-refine/src/scripts/ollamaIsRunningCheck.php

echo '\n';
```
At the end of the script:
```php
echo "\n";
echo "\033[32mRunning Ollama A.I. code check, please wait...\033[0m"
php ./vendor/Clarity/ai-code-refine/src/scripts/ollamaCodeCheck.php

echo '\n';
echo '\033[32mEnd Ollama A.I. response\033[0m';
```
</br><b>Not implemented yet</b></br>
Run php vendor/Clarity/ai-code-refine/src/scripts/init.php
