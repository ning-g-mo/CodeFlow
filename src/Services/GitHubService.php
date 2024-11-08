<?php
namespace CodeFlow\Services;

class GitHubService {
    private string $token;
    private string $apiUrl;
    
    public function __construct() {
        $config = require __DIR__ . '/../../config/config.php';
        $this->token = file_get_contents($config['github']['token_file']);
        $this->apiUrl = $config['github']['api_url'];
    }
    
    /**
     * 验证GitHub令牌
     */
    public function verifyToken(string $token): bool {
        $ch = curl_init($this->apiUrl . '/user');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'User-Agent: CodeFlow',
                'Accept: application/vnd.github.v3+json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    /**
     * 获取用户仓库列表
     */
    public function getRepositories(): array {
        $ch = curl_init($this->apiUrl . '/user/repos');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'User-Agent: CodeFlow',
                'Accept: application/vnd.github.v3+json'
            ]
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?? [];
    }
    
    /**
     * 克隆仓库
     */
    public function cloneRepository(string $repoName): bool {
        $config = require __DIR__ . '/../../config/config.php';
        $projectPath = $config['project']['base_path'] . '/' . $repoName;
        
        if (!is_dir($projectPath)) {
            mkdir($projectPath, 0755, true);
        }
        
        $cloneUrl = "https://{$this->token}@github.com/{$repoName}.git";
        $command = "git clone {$cloneUrl} {$projectPath} 2>&1";
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }
    
    /**
     * 提交更改
     */
    public function commit(string $repoName, string $message): bool {
        $config = require __DIR__ . '/../../config/config.php';
        $projectPath = $config['project']['base_path'] . '/' . $repoName;
        
        if (!is_dir($projectPath)) {
            return false;
        }
        
        $commands = [
            "cd {$projectPath}",
            "git add .",
            "git commit -m \"{$message}\"",
        ];
        
        exec(implode(" && ", $commands), $output, $returnCode);
        return $returnCode === 0;
    }
    
    /**
     * 推送更改
     */
    public function push(string $repoName): bool {
        $config = require __DIR__ . '/../../config/config.php';
        $projectPath = $config['project']['base_path'] . '/' . $repoName;
        
        if (!is_dir($projectPath)) {
            return false;
        }
        
        $command = "cd {$projectPath} && git push 2>&1";
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }
    
    /**
     * 拉取更新
     */
    public function pull(string $repoName): bool {
        $config = require __DIR__ . '/../../config/config.php';
        $projectPath = $config['project']['base_path'] . '/' . $repoName;
        
        if (!is_dir($projectPath)) {
            return false;
        }
        
        $command = "cd {$projectPath} && git pull 2>&1";
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }
} 