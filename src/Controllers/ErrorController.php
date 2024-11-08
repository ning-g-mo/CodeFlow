<?php
namespace CodeFlow\Controllers;

class ErrorController {
    public function handle(): void {
        $errorCode = $_GET['code'] ?? '404';
        $message = $_GET['message'] ?? $this->getDefaultMessage($errorCode);
        
        require __DIR__ . '/../../templates/error.php';
    }
    
    private function getDefaultMessage(string $code): string {
        return match($code) {
            '400' => '错误的请求',
            '401' => '未授权访问',
            '403' => '禁止访问',
            '404' => '页面不存在',
            '500' => '服务器内部错误',
            default => '未知错误'
        };
    }
} 