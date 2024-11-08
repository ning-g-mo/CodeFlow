<?php
namespace CodeFlow\Controllers;

use CodeFlow\Services\GitHubService;

class InitController {
    private GitHubService $github;
    
    public function __construct() {
        $this->github = new GitHubService();
    }
    
    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }
        
        // 显示初始化表单
        require __DIR__ . '/../../templates/init.php';
    }
    
    private function handlePost(): void {
        $token = $_POST['github_token'] ?? '';
        $enableGlobalPassword = $_POST['enable_global_password'] ?? false;
        $globalPassword = $_POST['global_password'] ?? '';
        
        if (empty($token)) {
            $this->showError('GitHub令牌不能为空');
            return;
        }
        
        // 验证令牌
        if (!$this->github->verifyToken($token)) {
            $this->showError('GitHub令牌无效');
            return;
        }
        
        // 保存令牌
        $config = require __DIR__ . '/../../config/config.php';
        file_put_contents($config['github']['token_file'], $token);
        
        // 更新全局密码设置
        if ($enableGlobalPassword) {
            $config['security']['global_password_enabled'] = true;
            $config['security']['global_password'] = password_hash($globalPassword, PASSWORD_DEFAULT);
            
            // 保存配置
            $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents(__DIR__ . '/../../config/config.php', $configContent);
        }
        
        // 重定向到首页
        header('Location: /');
        exit;
    }
    
    private function showError(string $message): void {
        $_SESSION['error'] = $message;
        require __DIR__ . '/../../templates/init.php';
    }
} 