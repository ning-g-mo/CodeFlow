<?php require __DIR__ . '/../header.php'; ?>

<div class="container">
    <h2>新建文件</h2>
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/?route=file&action=list&repo=<?= urlencode($_GET['repo']) ?>">根目录</a>
            </li>
            <?php
            $paths = array_filter(explode('/', $_GET['path'] ?? ''));
            $currentLink = '';
            foreach ($paths as $p):
                $currentLink .= '/' . $p;
            ?>
                <li class="breadcrumb-item">
                    <a href="/?route=file&action=list&repo=<?= urlencode($_GET['repo']) ?>&path=<?= urlencode(trim($currentLink, '/')) ?>">
                        <?= htmlspecialchars($p) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="breadcrumb-item active">新建文件</li>
        </ol>
    </nav>
    
    <form action="/?route=file&action=create" method="post">
        <input type="hidden" name="repo" value="<?= htmlspecialchars($_GET['repo']) ?>">
        <input type="hidden" name="path" value="<?= htmlspecialchars($_GET['path'] ?? '') ?>">
        
        <div class="mb-3">
            <label for="filename" class="form-label">文件名</label>
            <input type="text" class="form-control" id="filename" name="filename" required>
            <div class="form-text">请输入文件名，包括扩展名（如：example.txt）</div>
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">创建</button>
            <a href="/?route=file&action=list&repo=<?= urlencode($_GET['repo']) ?>&path=<?= urlencode($_GET['path'] ?? '') ?>" 
               class="btn btn-secondary">返回</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../footer.php'; ?> 