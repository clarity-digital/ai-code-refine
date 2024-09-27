<?php
namespace src\scripts;

$config = include 'setConfig.php';

$gitStatusResult = shell_exec("git status --porcelain | grep '^M[ ]*'");

if ($config['modified_files_only'] === false) {
    $gitStatusResult = shell_exec("git status --porcelain | grep '^[AM] '");
}

if ($gitStatusResult == null) {
    return [];
}

$gitStatusResultArray = explode(PHP_EOL,  trim($gitStatusResult));

for ($i = 0; $i < count($gitStatusResultArray); $i++) {
    $gitStatusResultArray[$i] = preg_replace('/^\w{2}\s/', '', $gitStatusResultArray[$i]);
    $gitStatusResultArray[$i] = preg_replace('/^M\s*/', '', $gitStatusResultArray[$i]);
    if ($config['modified_files_only']) {
        $gitStatusResultArray[$i] = preg_replace('/^A{1,2}\s*/', '', $gitStatusResultArray[$i]);
    }
}

return $gitStatusResultArray;