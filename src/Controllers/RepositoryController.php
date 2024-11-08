<?php
namespace CodeFlow\Controllers;

use CodeFlow\Services\GitHubService;

class RepositoryController {
    private GitHubService $github;
    private string $projectPath;
    
    public function __construct() {
        $this->github = new GitHubService();
        $config = require __DIR__ . '/../../config/config.php';
        $this->projectPath = $config['project']['base_path'];
    }
    
    public function handle(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                $this->listRepositories();
                break;
            case 'clone':
                $this->cloneRepository();
                break;
            case 'create':
                $this->createRepository();
                break;
            case 'pull':
                $this->pullRepository();
                break;
            case 'push':
                $this->pushRepository();
                break;
            case 'commit':
                $this->commitChanges();
                break;
            default:
                header('Location: /');
                exit;
        }
    }
    
    private function listRepositories(): void {
        $remoteRepos = $this->github->getRepositories();
        $localRepos = $this->getLocalRepositories();
        
        require __DIR__ . '/../../templates/repository/list.php';
    }
    
    private function cloneRepository(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo_name'] ?? '';
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        if ($this->github->cloneRepository($repoName)) {
            $_SESSION['success'] = '仓库克隆成功';
        } else {
            $_SESSION['error'] = '仓库克隆失败';
        }
        
        header('Location: /?route=repository');
        exit;
    }
    
    private function createRepository(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../../templates/repository/create.php';
            return;
        }
        
        $repoName = $_POST['repo_name'] ?? '';
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        $path = $this->projectPath . '/' . $repoName;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            exec("cd {$path} && git init");
            $_SESSION['success'] = '仓库创建成功';
        } else {
            $_SESSION['error'] = '仓库已存在';
        }
        
        header('Location: /?route=repository');
        exit;
    }
    
    private function pullRepository(): void {
        $repoName = $_GET['name'] ?? '';
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        if ($this->github->pull($repoName)) {
            $_SESSION['success'] = '仓库更新成功';
        } else {
            $_SESSION['error'] = '仓库更新失败';
        }
        
        header('Location: /?route=repository');
        exit;
    }
    
    private function pushRepository(): void {
        $repoName = $_GET['name'] ?? '';
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        if ($this->github->push($repoName)) {
            $_SESSION['success'] = '推送成功';
        } else {
            $_SESSION['error'] = '推送失败';
        }
        
        header('Location: /?route=repository');
        exit;
    }
    
    private function commitChanges(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo_name'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (empty($repoName) || empty($message)) {
            $this->showError('仓库名称和提交信息不能为空');
            return;
        }
        
        if ($this->github->commit($repoName, $message)) {
            $_SESSION['success'] = '提交成功';
        } else {
            $_SESSION['error'] = '提交失败';
        }
        
        header('Location: /?route=repository');
        exit;
    }
    
    private function getLocalRepositories(): array {
        $repos = [];
        if (is_dir($this->projectPath)) {
            $dirs = scandir($this->projectPath);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($this->projectPath . '/' . $dir)) {
                    $repos[] = $dir;
                }
            }
        }
        return $repos;
    }
    
    private function showError(string $message): void {
        $_SESSION['error'] = $message;
        header('Location: /?route=repository');
        exit;
    }
} 