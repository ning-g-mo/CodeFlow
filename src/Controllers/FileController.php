<?php
namespace CodeFlow\Controllers;

class FileController {
    private string $projectPath;
    
    public function __construct() {
        $config = require __DIR__ . '/../../config/config.php';
        $this->projectPath = $config['project']['base_path'];
    }
    
    public function handle(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                $this->listFiles();
                break;
            case 'edit':
                $this->editFile();
                break;
            case 'save':
                $this->saveFile();
                break;
            case 'create':
                $this->createFile();
                break;
            case 'delete':
                $this->deleteFile();
                break;
            case 'mkdir':
                $this->createDirectory();
                break;
            case 'upload':
                $this->handleUpload();
                break;
            case 'download':
                $this->downloadFile();
                break;
            case 'compress':
                $this->compressFiles();
                break;
            case 'extract':
                $this->extractArchive();
                break;
            case 'batchDelete':
                $this->batchDelete();
                break;
            case 'rename':
                $this->renameItem();
                break;
            case 'gitStatus':
                $this->getGitStatus();
                break;
            case 'commit':
                $this->commitChanges();
                break;
            case 'pull':
                $this->pullRepository();
                break;
            case 'push':
                $this->pushRepository();
                break;
            case 'branches':
                $this->listBranches();
                break;
            case 'createBranch':
                $this->createBranch();
                break;
            case 'switchBranch':
                $this->switchBranch();
                break;
            case 'deleteBranch':
                $this->deleteBranch();
                break;
            case 'merge':
                $this->mergeBranch();
                break;
            case 'resolveMerge':
                $this->resolveMergeConflict();
                break;
            case 'abortMerge':
                $this->abortMerge();
                break;
            default:
                header('Location: /');
                exit;
        }
    }
    
    private function listFiles(): void {
        $repoName = $_GET['repo'] ?? '';
        $path = $_GET['path'] ?? '';
        
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path;
        if (!is_dir($fullPath)) {
            $this->showError('目录不存在');
            return;
        }
        
        $files = $this->scanDirectory($fullPath);
        $currentPath = $path;
        require __DIR__ . '/../../templates/file/list.php';
    }
    
    private function editFile(): void {
        $repoName = $_GET['repo'] ?? '';
        $path = $_GET['path'] ?? '';
        
        if (empty($repoName) || empty($path)) {
            $this->showError('参数错误');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path;
        if (!file_exists($fullPath) || is_dir($fullPath)) {
            $this->showError('文件不存在');
            return;
        }
        
        $content = file_get_contents($fullPath);
        require __DIR__ . '/../../templates/file/edit.php';
    }
    
    private function saveFile(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $content = $_POST['content'] ?? '';
        
        if (empty($repoName) || empty($path)) {
            $this->showError('参数错误');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path;
        if (file_put_contents($fullPath, $content) !== false) {
            $_SESSION['success'] = '文件保存成功';
        } else {
            $_SESSION['error'] = '文件保存失败';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path=" . dirname($path));
        exit;
    }
    
    private function createFile(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../../templates/file/create.php';
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $filename = $_POST['filename'] ?? '';
        
        if (empty($repoName) || empty($filename)) {
            $this->showError('参数错误');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path . '/' . $filename;
        if (!file_exists($fullPath)) {
            if (touch($fullPath)) {
                $_SESSION['success'] = '文件创建成功';
            } else {
                $_SESSION['error'] = '文件创建失败';
            }
        } else {
            $_SESSION['error'] = '文件已存在';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path={$path}");
        exit;
    }
    
    private function deleteFile(): void {
        $repoName = $_GET['repo'] ?? '';
        $path = $_GET['path'] ?? '';
        
        if (empty($repoName) || empty($path)) {
            $this->showError('参数错误');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path;
        if (is_dir($fullPath)) {
            if ($this->deleteDirectory($fullPath)) {
                $_SESSION['success'] = '目录删除成功';
            } else {
                $_SESSION['error'] = '目录删除失败';
            }
        } else {
            if (unlink($fullPath)) {
                $_SESSION['success'] = '文件删除成功';
            } else {
                $_SESSION['error'] = '文件删除失败';
            }
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path=" . dirname($path));
        exit;
    }
    
    private function createDirectory(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../../templates/file/mkdir.php';
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $dirname = $_POST['dirname'] ?? '';
        
        if (empty($repoName) || empty($dirname)) {
            $this->showError('参数错误');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path . '/' . $dirname;
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0755, true)) {
                $_SESSION['success'] = '目录创建成功';
            } else {
                $_SESSION['error'] = '目录创建失败';
            }
        } else {
            $_SESSION['error'] = '目录已存在';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path={$path}");
        exit;
    }
    
    private function scanDirectory(string $path): array {
        $files = [];
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $path . '/' . $item;
            $files[] = [
                'name' => $item,
                'path' => str_replace($this->projectPath . '/', '', $fullPath),
                'type' => is_dir($fullPath) ? 'dir' : 'file',
                'size' => is_dir($fullPath) ? '-' : $this->formatSize(filesize($fullPath)),
                'modified' => date('Y-m-d H:i:s', filemtime($fullPath))
            ];
        }
        
        return $files;
    }
    
    private function deleteDirectory(string $path): bool {
        if (!is_dir($path)) {
            return false;
        }
        
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $path . '/' . $item;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        
        return rmdir($path);
    }
    
    private function formatSize(int $size): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
    
    private function showError(string $message): void {
        $_SESSION['error'] = $message;
        header('Location: /');
        exit;
    }
    
    /**
     * 处理文件上传
     */
    private function handleUpload(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        $uploadPath = $this->projectPath . '/' . $repoName . '/' . $path;
        if (!is_dir($uploadPath)) {
            $this->showError('上传目录不存在');
            return;
        }
        
        $files = $_FILES['files'] ?? [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($files['name'] as $index => $filename) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                $tempFile = $files['tmp_name'][$index];
                $targetFile = $uploadPath . '/' . $filename;
                
                if (move_uploaded_file($tempFile, $targetFile)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }
        
        if ($errorCount > 0) {
            $_SESSION['error'] = "上传完成，{$successCount} 个成功，{$errorCount} 个失败";
        } else {
            $_SESSION['success'] = "成功上传 {$successCount} 个文件";
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path={$path}");
        exit;
    }
    
    /**
     * 下载文件
     */
    private function downloadFile(): void {
        $repoName = $_GET['repo'] ?? '';
        $path = $_GET['path'] ?? '';
        
        if (empty($repoName) || empty($path)) {
            $this->showError('参数错误');
            return;
        }
        
        $filePath = $this->projectPath . '/' . $repoName . '/' . $path;
        if (!file_exists($filePath)) {
            $this->showError('文件不存在');
            return;
        }
        
        $filename = basename($filePath);
        $filesize = filesize($filePath);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: no-cache');
        
        readfile($filePath);
        exit;
    }
    
    /**
     * 压缩文件/文件夹
     */
    private function compressFiles(): void {
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $items = $_POST['items'] ?? [];
        
        if (empty($repoName) || empty($items)) {
            $this->showError('参数错误');
            return;
        }
        
        $basePath = $this->projectPath . '/' . $repoName . '/' . $path;
        $zipName = date('YmdHis') . '.zip';
        $zipPath = $basePath . '/' . $zipName;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($items as $item) {
                $itemPath = $basePath . '/' . $item;
                if (is_dir($itemPath)) {
                    $this->addDirToZip($zip, $itemPath, basename($item));
                } else {
                    $zip->addFile($itemPath, basename($item));
                }
            }
            $zip->close();
            $_SESSION['success'] = '压缩完成';
        } else {
            $_SESSION['error'] = '压缩失败';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path={$path}");
        exit;
    }
    
    /**
     * 解压文件
     */
    private function extractArchive(): void {
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $file = $_POST['file'] ?? '';
        
        if (empty($repoName) || empty($file)) {
            $this->showError('参数错误');
            return;
        }
        
        $archivePath = $this->projectPath . '/' . $repoName . '/' . $path . '/' . $file;
        $extractPath = dirname($archivePath);
        
        $zip = new ZipArchive();
        if ($zip->open($archivePath) === true) {
            if ($zip->extractTo($extractPath)) {
                $_SESSION['success'] = '解压完成';
            } else {
                $_SESSION['error'] = '解压失败';
            }
            $zip->close();
        } else {
            $_SESSION['error'] = '无法打开压缩文件';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path={$path}");
        exit;
    }
    
    /**
     * 递归添加目录到ZIP
     */
    private function addDirToZip(ZipArchive $zip, string $path, string $localPath = ''): void {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $itemPath = $path . '/' . $item;
            $zipPath = $localPath ? $localPath . '/' . $item : $item;
            
            if (is_dir($itemPath)) {
                $zip->addEmptyDir($zipPath);
                $this->addDirToZip($zip, $itemPath, $zipPath);
            } else {
                $zip->addFile($itemPath, $zipPath);
            }
        }
    }
    
    /**
     * 批量删除文件/文件夹
     */
    private function batchDelete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $items = $_POST['items'] ?? [];
        
        if (empty($repoName) || empty($items)) {
            $this->showError('参数错误');
            return;
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($items as $item) {
            $fullPath = $this->projectPath . '/' . $repoName . '/' . $item;
            
            if (is_dir($fullPath)) {
                if ($this->deleteDirectory($fullPath)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                if (unlink($fullPath)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        }
        
        if ($errorCount > 0) {
            $_SESSION['error'] = "删除完成，{$successCount} 个成功，{$errorCount} 个失败";
        } else {
            $_SESSION['success'] = "成功删除 {$successCount} 个项目";
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path={$path}");
        exit;
    }
    
    /**
     * 重命名文件或文件夹
     */
    private function renameItem(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $path = $_POST['path'] ?? '';
        $newName = $_POST['new_name'] ?? '';
        
        if (empty($repoName) || empty($path) || empty($newName)) {
            $this->showError('参数错误');
            return;
        }
        
        $fullPath = $this->projectPath . '/' . $repoName . '/' . $path;
        $newPath = $this->projectPath . '/' . $repoName . '/' . dirname($path) . '/' . $newName;
        
        // 检查新名称是否已存在
        if (file_exists($newPath)) {
            $_SESSION['error'] = '该名称已存在';
            header("Location: /?route=file&action=list&repo={$repoName}&path=" . dirname($path));
            exit;
        }
        
        // 执行重命名
        if (rename($fullPath, $newPath)) {
            $_SESSION['success'] = '重命名成功';
        } else {
            $_SESSION['error'] = '重命名失败';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}&path=" . dirname($path));
        exit;
    }
    
    /**
     * 获取Git状态
     */
    private function getGitStatus(): void {
        $repoName = $_GET['repo'] ?? '';
        
        if (empty($repoName)) {
            echo '参数错误';
            exit;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        if (!is_dir($repoPath)) {
            echo '仓库不存在';
            exit;
        }
        
        $command = "cd {$repoPath} && git status 2>&1";
        exec($command, $output);
        
        header('Content-Type: text/plain; charset=utf-8');
        echo implode("\n", $output);
        exit;
    }
    
    /**
     * 提交更改
     */
    private function commitChanges(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (empty($repoName) || empty($message)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        
        // 执行git add
        $command = "cd {$repoPath} && git add . 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '添加文件失败：' . implode("\n", $output);
            header("Location: /?route=file&action=list&repo={$repoName}");
            exit;
        }
        
        // 执行git commit
        $message = escapeshellarg($message);
        $command = "cd {$repoPath} && git commit -m {$message} 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '提交失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '提交成功';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 拉取更新
     */
    private function pullRepository(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        
        if (empty($repoName)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git pull 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '拉取失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '拉取成功';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 推送更改
     */
    private function pushRepository(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        
        if (empty($repoName)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git push 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '推送失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '推送成功';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 获取分支列表
     */
    private function listBranches(): void {
        $repoName = $_GET['repo'] ?? '';
        
        if (empty($repoName)) {
            $this->showError('仓库名称不能为空');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        
        // 获取当前分支
        $command = "cd {$repoPath} && git branch --show-current 2>&1";
        exec($command, $output, $returnCode);
        $currentBranch = $returnCode === 0 ? trim($output[0]) : '';
        
        // 获取所有分支
        $command = "cd {$repoPath} && git branch -a 2>&1";
        exec($command, $output, $returnCode);
        
        $branches = [];
        if ($returnCode === 0) {
            foreach ($output as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // 移除远程分支的 remotes/origin/ 前缀
                $name = preg_replace('/^\*?\s*remotes\/origin\//', '', $line);
                $name = trim($name);
                
                if (!in_array($name, $branches)) {
                    $branches[] = $name;
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'current' => $currentBranch,
            'branches' => $branches
        ]);
        exit;
    }
    
    /**
     * 创建新分支
     */
    private function createBranch(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $branchName = $_POST['branch'] ?? '';
        
        if (empty($repoName) || empty($branchName)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git checkout -b " . escapeshellarg($branchName) . " 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '创建分支失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '分支创建成功';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 切换分支
     */
    private function switchBranch(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $branchName = $_POST['branch'] ?? '';
        
        if (empty($repoName) || empty($branchName)) {
            $this->showError('���数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git checkout " . escapeshellarg($branchName) . " 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '切换分支失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '已切换到分支：' . $branchName;
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 删除分支
     */
    private function deleteBranch(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $branchName = $_POST['branch'] ?? '';
        
        if (empty($repoName) || empty($branchName)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git branch -d " . escapeshellarg($branchName) . " 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '删除分支失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '分支删除成功';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 合并分支
     */
    private function mergeBranch(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $sourceBranch = $_POST['source_branch'] ?? '';
        
        if (empty($repoName) || empty($sourceBranch)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git merge " . escapeshellarg($sourceBranch) . " 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            // 检查是否存在冲突
            if (strpos(implode("\n", $output), 'CONFLICT') !== false) {
                $_SESSION['error'] = '合并发生冲突，请解决冲突后继续';
                $_SESSION['merge_conflicts'] = true;
            } else {
                $_SESSION['error'] = '合并失败：' . implode("\n", $output);
            }
        } else {
            $_SESSION['success'] = '分支合并成功';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 解决合并冲突
     */
    private function resolveMergeConflict(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        $files = $_POST['files'] ?? [];
        
        if (empty($repoName) || empty($files)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $success = true;
        
        foreach ($files as $file => $content) {
            $filePath = $repoPath . '/' . $file;
            if (file_put_contents($filePath, $content) === false) {
                $success = false;
                break;
            }
            
            // 标记文件为已解决
            exec("cd {$repoPath} && git add " . escapeshellarg($file));
        }
        
        if ($success) {
            // 完成合并
            $command = "cd {$repoPath} && git commit --no-edit 2>&1";
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $_SESSION['success'] = '冲突解决成功';
                unset($_SESSION['merge_conflicts']);
            } else {
                $_SESSION['error'] = '提交解决的冲突失败：' . implode("\n", $output);
            }
        } else {
            $_SESSION['error'] = '保存解决的冲突失败';
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
    
    /**
     * 中止合并
     */
    private function abortMerge(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('无效的请求方法');
            return;
        }
        
        $repoName = $_POST['repo'] ?? '';
        
        if (empty($repoName)) {
            $this->showError('参数错误');
            return;
        }
        
        $repoPath = $this->projectPath . '/' . $repoName;
        $command = "cd {$repoPath} && git merge --abort 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $_SESSION['error'] = '中止合并失败：' . implode("\n", $output);
        } else {
            $_SESSION['success'] = '已中止合并';
            unset($_SESSION['merge_conflicts']);
        }
        
        header("Location: /?route=file&action=list&repo={$repoName}");
        exit;
    }
} 