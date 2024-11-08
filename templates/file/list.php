<?php require __DIR__ . '/../header.php'; ?>

<div class="container">
    <h2>文件管理</h2>
    
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
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/?route=file&action=list&repo=<?= urlencode($repoName) ?>">根目录</a>
            </li>
            <?php
            $paths = array_filter(explode('/', $currentPath));
            $currentLink = '';
            foreach ($paths as $p):
                $currentLink .= '/' . $p;
            ?>
                <li class="breadcrumb-item">
                    <a href="/?route=file&action=list&repo=<?= urlencode($repoName) ?>&path=<?= urlencode(trim($currentLink, '/')) ?>">
                        <?= htmlspecialchars($p) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    
    <div class="row mb-3">
        <div class="col">
            <div class="btn-group">
                <button type="button" class="btn btn-info" onclick="pullRepository()">
                    <i class="bi bi-cloud-download"></i> 拉取更新
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#commitModal">
                    <i class="bi bi-check2-square"></i> 提交更改
                </button>
                <button type="button" class="btn btn-warning" onclick="pushRepository()">
                    <i class="bi bi-cloud-upload"></i> 推送更改
                </button>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#branchModal">
                    <i class="bi bi-diagram-2"></i> 分支管理
                </button>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col">
            <form action="/?route=file&action=upload" method="post" enctype="multipart/form-data" class="d-inline">
                <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
                <div class="input-group">
                    <input type="file" class="form-control" name="files[]" multiple>
                    <button type="submit" class="btn btn-success">上传</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleSelectAll()">
                    </th>
                    <th>名称</th>
                    <th>大小</th>
                    <th>修改时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input file-select" 
                                   value="<?= htmlspecialchars($file['path']) ?>"
                                   data-name="<?= htmlspecialchars($file['name']) ?>"
                                   onclick="toggleBatchOperations()">
                        </td>
                        <td>
                            <?php if ($file['type'] === 'dir'): ?>
                                <i class="bi bi-folder"></i>
                                <a href="/?route=file&action=list&repo=<?= urlencode($repoName) ?>&path=<?= urlencode($file['path']) ?>">
                                    <?= htmlspecialchars($file['name']) ?>
                                </a>
                            <?php else: ?>
                                <i class="bi bi-file-text"></i>
                                <a href="/?route=file&action=edit&repo=<?= urlencode($repoName) ?>&path=<?= urlencode($file['path']) ?>">
                                    <?= htmlspecialchars($file['name']) ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($file['size']) ?></td>
                        <td><?= htmlspecialchars($file['modified']) ?></td>
                        <td>
                            <?php if ($file['type'] === 'file'): ?>
                                <a href="/?route=file&action=download&repo=<?= urlencode($repoName) ?>&path=<?= urlencode($file['path']) ?>" 
                                   class="btn btn-sm btn-info">下载</a>
                                <?php if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'zip'): ?>
                                    <form action="/?route=file&action=extract" method="post" class="d-inline">
                                        <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
                                        <input type="hidden" name="path" value="<?= htmlspecialchars(dirname($file['path'])) ?>">
                                        <input type="hidden" name="file" value="<?= htmlspecialchars(basename($file['path'])) ?>">
                                        <button type="submit" class="btn btn-sm btn-warning">解压</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="showRenameDialog('<?= htmlspecialchars($file['path']) ?>', '<?= htmlspecialchars($file['name']) ?>')">
                                重命名
                            </button>
                            <a href="/?route=file&action=delete&repo=<?= urlencode($repoName) ?>&path=<?= urlencode($file['path']) ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('确定要删除吗？')">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="compressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/?route=file&action=compress" method="post">
                <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">压缩文件</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">选择要压缩的文件/文件夹</label>
                        <?php foreach ($files as $file): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="items[]" 
                                       value="<?= htmlspecialchars($file['name']) ?>" id="file_<?= htmlspecialchars($file['name']) ?>">
                                <label class="form-check-label" for="file_<?= htmlspecialchars($file['name']) ?>">
                                    <?= htmlspecialchars($file['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">压缩</button>
                </div>
            </form>
        </div>
    </div>
</div>

<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#compressModal">
    压缩文件
</button>

<div class="modal fade" id="renameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/?route=file&action=rename" method="post">
                <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
                <input type="hidden" name="path" id="renamePath">
                
                <div class="modal-header">
                    <h5 class="modal-title">重命名</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_name" class="form-label">新名称</label>
                        <input type="text" class="form-control" id="new_name" name="new_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">确定</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="commitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/?route=file&action=commit" method="post">
                <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
                <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">提交更改</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="commit_message" class="form-label">提交信息</label>
                        <textarea class="form-control" id="commit_message" name="message" rows="3" required></textarea>
                    </div>
                    <div id="gitStatus" class="mb-3">
                        <h6>Git状态：</h6>
                        <pre class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">加载中...</pre>
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

<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">分支管理</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>当前分支：<span id="currentBranch">加载中...</span></h6>
                </div>
                <div class="mb-3">
                    <h6>分支列表：</h6>
                    <div id="branchList" class="list-group">
                        <div class="text-center">加载中...</div>
                    </div>
                </div>
                <div class="mb-3">
                    <form id="createBranchForm" onsubmit="return createBranch(event)">
                        <div class="input-group">
                            <input type="text" class="form-control" id="newBranchName" 
                                   placeholder="新分支名称" required pattern="[a-zA-Z0-9_-]+">
                            <button type="submit" class="btn btn-primary">创建分支</button>
                        </div>
                    </form>
                </div>
                <div class="mb-3">
                    <h6>合并分支：</h6>
                    <form id="mergeBranchForm" onsubmit="return mergeBranch(event)">
                        <div class="input-group">
                            <select class="form-select" id="sourceBranch" required>
                                <option value="">选择要合并的分支</option>
                            </select>
                            <button type="submit" class="btn btn-warning">合并到当前分支</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mergeConflictModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/?route=file&action=resolveMerge" method="post">
                <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">解决合并冲突</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="conflictFiles">
                        <!-- 冲突文件将通过JavaScript动态添加 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="abortMerge()">中止合并</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">解决冲突</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.getElementsByClassName('file-select');
    for (let checkbox of checkboxes) {
        checkbox.checked = selectAll.checked;
    }
    toggleBatchOperations();
}

function toggleBatchOperations() {
    const checkboxes = document.getElementsByClassName('file-select');
    const batchOps = document.getElementById('batchOperations');
    let hasChecked = false;
    
    for (let checkbox of checkboxes) {
        if (checkbox.checked) {
            hasChecked = true;
            break;
        }
    }
    
    batchOps.style.display = hasChecked ? 'block' : 'none';
}

function getSelectedItems() {
    const checkboxes = document.getElementsByClassName('file-select');
    const selected = [];
    for (let checkbox of checkboxes) {
        if (checkbox.checked) {
            selected.push({
                path: checkbox.value,
                name: checkbox.dataset.name
            });
        }
    }
    return selected;
}

function batchDelete() {
    const selected = getSelectedItems();
    if (selected.length === 0) {
        alert('请选择要删除的文件/文件夹');
        return;
    }
    
    if (!confirm(`确定要删除选中的 ${selected.length} 个项目吗？`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=batchDelete';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    const pathInput = document.createElement('input');
    pathInput.type = 'hidden';
    pathInput.name = 'path';
    pathInput.value = '<?= htmlspecialchars($currentPath) ?>';
    form.appendChild(pathInput);
    
    selected.forEach(item => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'items[]';
        input.value = item.path;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

function batchCompress() {
    const selected = getSelectedItems();
    if (selected.length === 0) {
        alert('请选择要压缩的文件/文件夹');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=compress';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    const pathInput = document.createElement('input');
    pathInput.type = 'hidden';
    pathInput.name = 'path';
    pathInput.value = '<?= htmlspecialchars($currentPath) ?>';
    form.appendChild(pathInput);
    
    selected.forEach(item => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'items[]';
        input.value = item.name;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

function cancelBatch() {
    document.getElementById('selectAll').checked = false;
    const checkboxes = document.getElementsByClassName('file-select');
    for (let checkbox of checkboxes) {
        checkbox.checked = false;
    }
    toggleBatchOperations();
}

function showRenameDialog(path, currentName) {
    document.getElementById('renamePath').value = path;
    document.getElementById('new_name').value = currentName;
    new bootstrap.Modal(document.getElementById('renameModal')).show();
}

document.querySelector('#renameModal form').addEventListener('submit', function(e) {
    const newName = document.getElementById('new_name').value.trim();
    if (newName === '') {
        e.preventDefault();
        alert('新名称不能为空');
        return;
    }
    
    if (/[<>:"/\\|?*]/.test(newName)) {
        e.preventDefault();
        alert('文件名不能包含以下字符: < > : " / \\ | ? *');
        return;
    }
});

// 获取Git状态
function getGitStatus() {
    fetch('/?route=file&action=gitStatus&repo=<?= urlencode($repoName) ?>')
        .then(response => response.text())
        .then(status => {
            document.querySelector('#gitStatus pre').textContent = status;
        })
        .catch(error => {
            document.querySelector('#gitStatus pre').textContent = '获取状态失败';
        });
}

// 在打开提交对话框时获取Git状态
document.getElementById('commitModal').addEventListener('show.bs.modal', function () {
    getGitStatus();
});

// 拉取更新
function pullRepository() {
    if (!confirm('确定要拉取远程更新吗？这可能会覆盖本地更改。')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=pull';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    document.body.appendChild(form);
    form.submit();
}

// 推送更改
function pushRepository() {
    if (!confirm('确定要推送本地更改到远程仓库吗？')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=push';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    document.body.appendChild(form);
    form.submit();
}

// 加载分支信息
function loadBranches() {
    fetch('/?route=file&action=branches&repo=<?= urlencode($repoName) ?>')
        .then(response => response.json())
        .then(data => {
            document.getElementById('currentBranch').textContent = data.current;
            
            const branchList = document.getElementById('branchList');
            branchList.innerHTML = '';
            
            data.branches.forEach(branch => {
                const item = document.createElement('div');
                item.className = 'list-group-item d-flex justify-content-between align-items-center';
                
                const name = document.createElement('span');
                name.textContent = branch;
                if (branch === data.current) {
                    name.className = 'fw-bold';
                }
                item.appendChild(name);
                
                const buttons = document.createElement('div');
                if (branch !== data.current) {
                    const switchBtn = document.createElement('button');
                    switchBtn.className = 'btn btn-sm btn-primary me-2';
                    switchBtn.textContent = '切换';
                    switchBtn.onclick = () => switchBranch(branch);
                    buttons.appendChild(switchBtn);
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'btn btn-sm btn-danger';
                    deleteBtn.textContent = '删除';
                    deleteBtn.onclick = () => deleteBranch(branch);
                    buttons.appendChild(deleteBtn);
                }
                item.appendChild(buttons);
                
                branchList.appendChild(item);
            });
            
            // 更新合并选项
            const sourceBranch = document.getElementById('sourceBranch');
            sourceBranch.innerHTML = '<option value="">选择要合并的分支</option>';
            
            data.branches.forEach(branch => {
                if (branch !== data.current) {
                    const option = document.createElement('option');
                    option.value = branch;
                    option.textContent = branch;
                    sourceBranch.appendChild(option);
                }
            });
        })
        .catch(error => {
            document.getElementById('branchList').innerHTML = '<div class="text-danger">加载失败</div>';
        });
}

// 在打开分支管理对话框时加载分支信息
document.getElementById('branchModal').addEventListener('show.bs.modal', loadBranches);

// 创建分支
function createBranch(event) {
    event.preventDefault();
    
    const branchName = document.getElementById('newBranchName').value.trim();
    if (!branchName) return false;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=createBranch';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    const branchInput = document.createElement('input');
    branchInput.type = 'hidden';
    branchInput.name = 'branch';
    branchInput.value = branchName;
    form.appendChild(branchInput);
    
    document.body.appendChild(form);
    form.submit();
    return false;
}

// 切换分支
function switchBranch(branch) {
    if (!confirm(`确定要切换到分支 "${branch}" 吗？`)) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=switchBranch';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    const branchInput = document.createElement('input');
    branchInput.type = 'hidden';
    branchInput.name = 'branch';
    branchInput.value = branch;
    form.appendChild(branchInput);
    
    document.body.appendChild(form);
    form.submit();
}

// 删除分支
function deleteBranch(branch) {
    if (!confirm(`确定要删除分支 "${branch}" 吗？此操作不可恢复！`)) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/?route=file&action=deleteBranch';
    
    const repoInput = document.createElement('input');
    repoInput.type = 'hidden';
    repoInput.name = 'repo';
    repoInput.value = '<?= htmlspecialchars($repoName) ?>';
    form.appendChild(repoInput);
    
    const branchInput = document.createElement('input');
    branchInput.type = 'hidden';
    branchInput.name = 'branch';
    branchInput.value = branch;
    form.appendChild(branchInput);
    
    document.body.appendChild(form);
    form.submit();
}

// 合并分支
function mergeBranch(event) {
    event.preventDefault();
<?php require __DIR__ . '/../footer.php'; ?> 