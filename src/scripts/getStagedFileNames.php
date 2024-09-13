<?php
$gitStatusResult = shell_exec("git status --porcelain | grep '^M[ ]*'");
if ($gitStatusResult == null) {
    return [];
}
$gitStatusResultArray = explode(PHP_EOL,  trim($gitStatusResult));

for ($i = 0; $i < count($gitStatusResultArray); $i++) {
    $gitStatusResultArray[$i] = preg_replace('/^\w{2}\s/', '', $gitStatusResultArray[$i]);
    $gitStatusResultArray[$i] = preg_replace('/^M\s*/', '', $gitStatusResultArray[$i]);
}

return $gitStatusResultArray;