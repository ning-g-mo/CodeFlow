<?php require __DIR__ . '/../header.php'; ?>

<div class="container">
    <h2>仓库管理</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col">
            <a href="/?route=repository&action=create" class="btn btn-primary">创建新仓库</a>
        </div>
    </div>
    
    <h3>远程仓库</h3>
    <div class="list-group mb-4">
        <?php foreach ($remoteRepos as $repo): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <h5><?= htmlspecialchars($repo['full_name']) ?></h5>
                    <form action="/?route=repository&action=clone" method="post" class="d-inline">
                        <input type="hidden" name="repo_name" value="<?= htmlspecialchars($repo['full_name']) ?>">
                        <button type="submit" class="btn btn-sm btn-success">克隆</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <h3>本地仓库</h3>
    <div class="list-group">
        <?php foreach ($localRepos as $repo): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <h5><?= htmlspecialchars($repo) ?></h5>
                    <div>
                        <a href="/?route=repository&action=pull&name=<?= urlencode($repo) ?>" 
                           class="btn btn-sm btn-info">拉取</a>
                        <button type="button" class="btn btn-sm btn-primary" 
                                onclick="showCommitDialog('<?= htmlspecialchars($repo) ?>')">提交</button>
                        <a href="/?route=repository&action=push&name=<?= urlencode($repo) ?>" 
                           class="btn btn-sm btn-warning">推送</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 提交对话框 -->
<div class="modal fade" id="commitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/?route=repository&action=commit" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">提交更改</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="repo_name" id="commitRepoName">
                    <div class="mb-3">
                        <label for="message" class="form-label">提交信息</label>
                        <textarea class="form-control" id="message" name="message" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCommitDialog(repoName) {
    document.getElementById('commitRepoName').value = repoName;
    new bootstrap.Modal(document.getElementById('commitModal')).show();
}
</script>

<?php require __DIR__ . '/../footer.php'; ?> 