<?php require __DIR__ . '/../header.php'; ?>

<div class="container">
    <h2>编辑文件</h2>
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/?route=file&action=list&repo=<?= urlencode($repoName) ?>">根目录</a>
            </li>
            <?php
            $paths = array_filter(explode('/', dirname($path)));
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
            <li class="breadcrumb-item active"><?= htmlspecialchars(basename($path)) ?></li>
        </ol>
    </nav>
    
    <form action="/?route=file&action=save" method="post" id="editForm">
        <input type="hidden" name="repo" value="<?= htmlspecialchars($repoName) ?>">
        <input type="hidden" name="path" value="<?= htmlspecialchars($path) ?>">
        
        <div class="row mb-3">
            <div class="col">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="/?route=file&action=list&repo=<?= urlencode($repoName) ?>&path=<?= urlencode(dirname($path)) ?>" 
                       class="btn btn-secondary">返回</a>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <textarea name="content" id="editor" class="form-control" rows="20"><?= htmlspecialchars($content) ?></textarea>
        </div>
    </form>
</div>

<!-- 引入 CodeMirror -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>

<style>
.CodeMirror {
    height: auto;
    min-height: 500px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化 CodeMirror
    window.editor = CodeMirror.fromTextArea(document.getElementById('editor'), {
        lineNumbers: true,
        mode: getFileMode('<?= basename($path) ?>'),
        theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'monokai' : 'default',
        indentUnit: 4,
        autoCloseBrackets: true,
        matchBrackets: true,
        lineWrapping: true
    });
    
    // 自动保存功能
    let saveTimeout;
    editor.on('change', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            document.getElementById('editForm').submit();
        }, 2000);
    });
    
    // 快捷键保存
    editor.setOption('extraKeys', {
        'Ctrl-S': function(cm) {
            document.getElementById('editForm').submit();
            return false;
        }
    });
});

// 根据文件扩展名设置编辑器模式
function getFileMode(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const modes = {
        'php': 'php',
        'js': 'javascript',
        'css': 'css',
        'html': 'htmlmixed',
        'htm': 'htmlmixed',
        'xml': 'xml',
        'json': 'javascript'
    };
    return modes[ext] || 'text/plain';
}
</script>

<?php require __DIR__ . '/../footer.php'; ?> 