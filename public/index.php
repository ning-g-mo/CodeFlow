<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// 加载配置
$config = require_once __DIR__ . '/../config/config.php';

// 检查是否已初始化
if (!file_exists($config['github']['token_file'])) {
    require_once __DIR__ . '/../templates/init.php';
    exit;
}

// 验证访问权限
if ($config['security']['global_password_enabled']) {
    require_once __DIR__ . '/../src/Utils/AuthCheck.php';
    if (!AuthCheck::verify()) {
        require_once __DIR__ . '/../templates/login.php';
        exit;
    }
}

// 路由处理
try {
    $route = $_GET['route'] ?? 'home';
    $controller = match($route) {
        'home' => new HomeController(),
        'repository' => new RepositoryController(),
        'file' => new FileController(),
        'error' => new ErrorController(),
        default => throw new Exception('页面不存在', 404)
    };
    
    $controller->handle();
} catch (Exception $e) {
    header("Location: /?route=error&code={$e->getCode()}&message=" . urlencode($e->getMessage()));
    exit;
} 