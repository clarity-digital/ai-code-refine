<?php
$originalConfig = include __DIR__.'/../config/config.php';

if (is_file('./config/ai_code_config.php')) {
    $configurableConfigKeys = include __DIR__.'/../config/customizable_config_keys.php';
    $customConfig = include './config/ai_code_config.php';

    foreach ($configurableConfigKeys as $key) {
        if (
            key_exists($key, $customConfig) &&
            isset($customConfig[$key]) &&
            is_string($customConfig[$key]) &&
            strlen(str_replace('', ' ', strval($customConfig[$key]))) !== 0
        ) {
            $originalConfig[$key] = $customConfig[$key];
        }

        if (
            key_exists($key, $customConfig) &&
            isset($customConfig[$key]) &&
            is_bool($customConfig[$key])
        ) {
            $originalConfig[$key] = $customConfig[$key];
        }

    }
}

return $originalConfig;