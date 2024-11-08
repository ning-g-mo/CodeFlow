<?php

return [
    'github' => [
        'token_file' => __DIR__ . '/../.github_token',
        'api_url' => 'https://api.github.com',
    ],
    'security' => [
        'global_password_enabled' => false,
        'global_password' => '',
        'session_lifetime' => 86400, // 24小时
        'auth_type' => 'cookie', // 'cookie' 或 'ip'
    ],
    'project' => [
        'base_path' => __DIR__ . '/../public/project',
    ],
    'theme' => [
        'default' => 'light', // 默认主题
        'cookie_name' => 'theme_preference', // 保存主题偏好的cookie名
    ],
]; 