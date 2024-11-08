<!DOCTYPE html>
<html lang="zh-CN" data-bs-theme="<?= isset($_COOKIE['theme_preference']) ? htmlspecialchars($_COOKIE['theme_preference']) : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeFlow - Git仓库管理</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .breadcrumb {
            background-color: var(--bs-tertiary-bg);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
        }
        .table td {
            vertical-align: middle;
        }
        
        /* 暗色主题特定样式 */
        [data-bs-theme="dark"] .CodeMirror {
            background-color: #2d2d2d;
            color: #d4d4d4;
        }
        
        [data-bs-theme="dark"] .breadcrumb {
            background-color: #2d2d2d;
        }
        
        /* 主题切换按钮样式 */
        .theme-toggle {
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        
        .theme-toggle:hover {
            background-color: var(--bs-tertiary-bg);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="/">CodeFlow</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/?route=repository">仓库管理</a>
                    </li>
                </ul>
                <div class="theme-toggle" onclick="toggleTheme()" title="切换主题">
                    <i class="bi" id="themeIcon"></i>
                </div>
            </div>
        </div>
    </nav>

<script>
// 更新主题图标
function updateThemeIcon() {
    const theme = document.documentElement.getAttribute('data-bs-theme');
    const icon = document.getElementById('themeIcon');
    icon.className = theme === 'dark' ? 'bi-sun' : 'bi-moon';
}

// 切换主题
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-bs-theme', newTheme);
    document.cookie = 'theme_preference=' + newTheme + ';path=/;max-age=31536000';
    updateThemeIcon();
    
    // 如果页面上有CodeMirror实例，更新其主题
    if (typeof CodeMirror !== 'undefined' && window.editor) {
        window.editor.setOption('theme', newTheme === 'dark' ? 'monokai' : 'default');
    }
}

// 初始化主题图标
document.addEventListener('DOMContentLoaded', updateThemeIcon);
</script>
</body>
</html> 