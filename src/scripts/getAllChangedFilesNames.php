<?php
$config = include 'setConfig.php';

$primaryBranchName = strval($config['github_primary_branch_name']);

$gitCommand = "git diff --name-only $primaryBranchName...";

$gitCommandOutput = shell_exec($gitCommand);

$gitCommandOutputArray = explode("\n", $gitCommandOutput);
removeEmptyArrayItems($gitCommandOutputArray);

return $gitCommandOutputArray;

function removeEmptyArrayItems(array &$gitCommandOutputArray)
{
    for ($i = 0; $i < count($gitCommandOutputArray); $i++) {
        $fileName = $gitCommandOutputArray[$i];
        if (empty($fileName)) {
            unset($gitCommandOutputArray[$i]);
        }
    }
}
//
//$gitStatusResult = shell_exec("git status --porcelain | grep '^M[ ]*'");
//
//if ($config['modified_files_only'] === false) {
//    $gitStatusResult = shell_exec("git status --porcelain | grep '^[AM] '");
//}
//
//if ($gitStatusResult == null) {
//    return [];
//}
//$gitStatusResultArray = explode(PHP_EOL,  trim($gitStatusResult));
//
//for ($i = 0; $i < count($gitStatusResultArray); $i++) {
//    $gitStatusResultArray[$i] = preg_replace('/^\w{2}\s/', '', $gitStatusResultArray[$i]);
//    $gitStatusResultArray[$i] = preg_replace('/^M\s*/', '', $gitStatusResultArray[$i]);
//    if ($config['modified_files_only']) {
//        $gitStatusResultArray[$i] = preg_replace('/^A{1,2}\s*/', '', $gitStatusResultArray[$i]);
//    }
//}
//
//return $gitStatusResultArray;