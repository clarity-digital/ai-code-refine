<?php
namespace src\scripts;

$config = include 'setConfig.php';

$primaryBranchName = strval($config['github_primary_branch_name']);

$gitCommand = "git diff --name-only $primaryBranchName...";

$gitCommandOutput = shell_exec($gitCommand);

if (!$gitCommandOutput) {
    echo "\033[31mNo file changes found\033[0m\n";
    return [];
}
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