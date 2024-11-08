<?php require __DIR__ . '/header.php'; ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <div class="error-template">
                <h1>
                    <?= htmlspecialchars($errorCode) ?>
                    <small class="text-muted">错误</small>
                </h1>
                <div class="error-details mb-4">
                    <?= htmlspecialchars($message) ?>
                </div>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> 返回首页
                    </a>
                    <?php if (isset($_SERVER['HTTP_REFERER'])): ?>
                        <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER']) ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 返回上一页
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">错误详情</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">错误代码：</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($errorCode) ?></dd>
                        
                        <dt class="col-sm-3">错误信息：</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($message) ?></dd>
                        
                        <dt class="col-sm-3">请求URL：</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></dd>
                        
                        <dt class="col-sm-3">请求方法：</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($_SERVER['REQUEST_METHOD']) ?></dd>
                        
                        <?php if (isset($_SERVER['HTTP_REFERER'])): ?>
                            <dt class="col-sm-3">来源页面：</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($_SERVER['HTTP_REFERER']) ?></dd>
                        <?php endif; ?>
                        
                        <dt class="col-sm-3">发生时间：</dt>
                        <dd class="col-sm-9"><?= date('Y-m-d H:i:s') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-template {
    padding: 40px 15px;
}
.error-template h1 {
    font-size: 8em;
    line-height: 1;
    margin-bottom: 30px;
}
.error-template .error-details {
    font-size: 1.2em;
    color: #666;
}
.error-template .error-actions {
    margin-top: 15px;
    margin-bottom: 15px;
}
.error-template .error-actions .btn {
    margin-right: 10px;
}
</style>

<?php require __DIR__ . '/footer.php'; ?> 