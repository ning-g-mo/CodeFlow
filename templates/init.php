<?php require __DIR__ . '/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">初始化设置</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="github_token" class="form-label">GitHub 访问令牌</label>
                            <input type="text" class="form-control" id="github_token" name="github_token" required>
                            <div class="form-text">
                                请输入GitHub个人访问令牌，用于访问您的仓库。
                                <a href="https://github.com/settings/tokens" target="_blank">如何获取令牌？</a>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_global_password" 
                                       name="enable_global_password" value="1">
                                <label class="form-check-label" for="enable_global_password">
                                    启用全局密码保护
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="password_section" style="display: none;">
                            <label for="global_password" class="form-label">全局访问密码</label>
                            <input type="password" class="form-control" id="global_password" name="global_password">
                            <div class="form-text">
                                设置后，访问系统需要输入此密码。
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">保存设置</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('enable_global_password').addEventListener('change', function() {
    document.getElementById('password_section').style.display = this.checked ? 'block' : 'none';
    document.getElementById('global_password').required = this.checked;
});
</script>

<?php require __DIR__ . '/footer.php'; ?> 