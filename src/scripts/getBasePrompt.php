<?php
$config = include 'setConfig.php';
$model = strval($config['model']);
$codeLanguages = strval($config['code_languages']);
$frameworks = strval($config['frameworks']);

return "    
    Scan the changes for the following mistakes: syntax errors, production code vulnerabilities, major mistakes with the framework(s): $frameworks, major coding language(s) mistakes: $codeLanguages, coding mistakes that would cause 500 errors.
    Ignore 'git diff' output styling in the code snippets, do not give feedback on this part.
    !!Dont explain code that does not contain mistakes!!
    Make sure your feedback is correct
    Strictly format your feedback as follows:
    - File or function name which contains the mistake
    - first showcase a small part of the original code which contains the code mistake
    - real briefly explain in less then 50 words how it should be improved
";