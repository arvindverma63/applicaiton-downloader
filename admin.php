<?php
/**
 * Red Taxis App - Admin APK Upload & Management Portal
 * -------------------------------------------------------------------------
 * Ultra-responsive, compact, and high-performance management panel.
 */

require_once __DIR__ . '/config.php';

$error = '';
$success = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION['admin_authenticated'] = false;
    unset($_SESSION['admin_authenticated']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = $_POST['password'] ?? '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_authenticated'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid admin passcode. Please try again.';
    }
}

// Handle Reset Stats
if (is_admin_logged_in() && isset($_POST['action']) && $_POST['action'] === 'reset_stats') {
    $meta = get_app_meta();
    $meta['download_count'] = 0;
    save_app_meta($meta);
    $success = 'Download counter reset to 0.';
}

// Handle APK Upload (Supports both AJAX and standard POST)
if (is_admin_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_apk') {
    $isAjax = !empty($_POST['ajax']);

    $respondJson = function($ok, $message, $extra = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $ok, 'message' => $message], $extra));
        exit;
    };

    if (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize limit in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the MAX_FILE_SIZE limit.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
        ];
        $errCode = $_FILES['apk_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $errMsg = $uploadErrors[$errCode] ?? 'Upload failed with error: ' . $errCode;
        
        if ($isAjax) $respondJson(false, $errMsg);
        $error = $errMsg;
    } else {
        $file = $_FILES['apk_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'apk') {
            $msg = 'Invalid file type! Only Android Application Package (.apk) files are allowed.';
            if ($isAjax) $respondJson(false, $msg);
            $error = $msg;
        } else {
            $targetPath = UPLOAD_DIR . '/' . STORAGE_FILENAME;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $fileSize = filesize($targetPath);
                $fileHash = hash_file('sha256', $targetPath);

                $versionName = trim($_POST['version_name'] ?? '') ?: '1.0.0';
                $versionCode = trim($_POST['version_code'] ?? '') ?: '1';
                $minAndroid  = trim($_POST['min_android'] ?? '') ?: 'Android 7.0+';
                $changelog   = trim($_POST['changelog'] ?? '') ?: 'Bug fixes and performance improvements.';

                $metaData = [
                    'version_name'      => $versionName,
                    'version_code'      => $versionCode,
                    'min_android'       => $minAndroid,
                    'changelog'         => $changelog,
                    'upload_time'       => time(),
                    'file_size'         => $fileSize,
                    'file_hash_sha256'  => $fileHash
                ];

                save_app_meta($metaData);

                $msg = 'APK successfully uploaded! The permanent download link is now updated.';
                if ($isAjax) {
                    $respondJson(true, $msg, [
                        'file_size'    => format_bytes($fileSize),
                        'version_name' => $versionName,
                        'hash'         => $fileHash,
                        'date'         => date('M d, Y - h:i A')
                    ]);
                }
                $success = $msg;
            } else {
                $msg = 'Failed to move uploaded file. Check uploads directory permissions.';
                if ($isAjax) $respondJson(false, $msg);
                $error = $msg;
            }
        }
    }
}

$isAuth = is_admin_logged_in();
$meta = get_app_meta();
$isAvailable = is_apk_available();
$downloadUrl = get_download_url();
$formattedSize = format_bytes($meta['file_size']);
$releaseDate = !empty($meta['upload_time']) ? date('M d, Y - h:i A', $meta['upload_time']) : 'Never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Portal - <?= htmlspecialchars(APP_NAME) ?> APK Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #dc2626;
            --primary-hover: #b91c1c;
            --primary-light: #fef2f2;
            --primary-border: #fca5a5;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #475569;
            --text-sub: #64748b;
            --accent-green: #059669;
            --green-light: #ecfdf5;
            --green-border: #a7f3d0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 920px;
            margin-left: auto;
            margin-right: auto;
            padding-left: clamp(12px, 3.5vw, 20px);
            padding-right: clamp(12px, 3.5vw, 20px);
        }

        .main-wrapper {
            flex: 1;
            padding-bottom: clamp(24px, 4vw, 40px);
        }

        /* Top Nav */
        header {
            padding: clamp(10px, 2vw, 14px) 0;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
            margin-bottom: clamp(16px, 3vw, 24px);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: clamp(8px, 2vw, 10px);
            text-decoration: none;
            color: var(--text-main);
            min-width: 0;
        }

        .brand-icon {
            width: clamp(30px, 4.5vw, 34px);
            height: clamp(30px, 4.5vw, 34px);
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-icon svg {
            width: 18px;
            height: 18px;
        }

        .brand-title {
            font-weight: 700;
            font-size: clamp(14.5px, 3vw, 16px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .brand-badge {
            font-size: 11px;
            background: var(--primary-light);
            color: var(--primary);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 4px;
            border: 1px solid var(--primary-border);
            vertical-align: middle;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: clamp(6px, 1.5vw, 8px);
            flex-wrap: wrap;
        }

        .btn-outline {
            color: var(--text-muted);
            text-decoration: none;
            font-size: clamp(11.5px, 2.2vw, 12.5px);
            font-weight: 500;
            padding: 6px clamp(8px, 2vw, 12px);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: #ffffff;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            touch-action: manipulation;
            min-height: 32px;
        }

        .btn-outline:hover {
            color: var(--text-main);
            border-color: #cbd5e1;
            background: #f1f5f9;
        }

        .btn-logout {
            color: var(--primary);
            border-color: var(--primary-border);
            background: var(--primary-light);
        }

        .btn-logout:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Login Card */
        .login-wrap {
            width: min(100%, 380px);
            margin: clamp(24px, 6vw, 48px) auto;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: clamp(20px, 5vw, 32px) clamp(16px, 4vw, 24px);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .login-icon {
            width: clamp(44px, 8vw, 48px);
            height: clamp(44px, 8vw, 48px);
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            border: 1px solid var(--primary-border);
        }

        /* Alerts */
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: clamp(12px, 2.5vw, 13px);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            word-break: break-word;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid var(--primary-border);
        }

        .alert-success {
            background: var(--green-light);
            color: #047857;
            border: 1px solid var(--green-border);
        }

        /* Ultra-Responsive Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: clamp(8px, 1.8vw, 12px);
            margin-bottom: clamp(16px, 3vw, 20px);
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: clamp(10px, 2.5vw, 14px);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: clamp(8px, 2vw, 12px);
            min-width: 0;
        }

        .stat-icon {
            width: clamp(32px, 5vw, 36px);
            height: clamp(32px, 5vw, 36px);
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        .stat-label {
            font-size: 10.5px;
            color: var(--text-sub);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-val {
            font-size: clamp(13px, 3vw, 15.5px);
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Permanent Link Box */
        .dist-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: clamp(12px, 3vw, 18px);
            margin-bottom: clamp(16px, 3vw, 20px);
            box-shadow: var(--shadow-sm);
        }

        .dist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .dist-title {
            font-size: clamp(13px, 2.5vw, 14px);
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dist-title svg {
            flex-shrink: 0;
        }

        .dist-desc {
            font-size: clamp(11.5px, 2.2vw, 12px);
            color: var(--text-muted);
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .link-row {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 4px 6px 4px 10px;
            gap: 8px;
            min-width: 0;
        }

        .link-row .url {
            font-family: 'Roboto Mono', monospace;
            font-size: clamp(11px, 2.2vw, 12px);
            color: var(--text-muted);
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            user-select: all;
            min-width: 0;
        }

        .btn-copy-sm {
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
            touch-action: manipulation;
            min-height: 30px;
        }

        .btn-copy-sm:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-copy-sm.copied {
            background: var(--green-light);
            border-color: var(--green-border);
            color: var(--accent-green);
        }

        /* Upload Portal Card */
        .upload-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: clamp(16px, 3.5vw, 24px);
            box-shadow: var(--shadow-sm);
        }

        .upload-title {
            font-size: clamp(15.5px, 3.2vw, 17.5px);
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .upload-subtitle {
            font-size: clamp(12px, 2.5vw, 13px);
            color: var(--text-muted);
            margin-bottom: clamp(14px, 3vw, 20px);
        }

        /* Compact Drop Zone */
        .dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-md);
            padding: clamp(18px, 4vw, 26px) 16px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: all 0.15s ease;
            position: relative;
            margin-bottom: 18px;
        }

        .dropzone:hover,
        .dropzone.dragover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .drop-icon {
            width: clamp(36px, 6vw, 42px);
            height: clamp(36px, 6vw, 42px);
            background: #ffffff;
            color: var(--primary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            box-shadow: var(--shadow-sm);
        }

        .drop-title {
            font-size: clamp(13px, 2.5vw, 14px);
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .drop-sub {
            font-size: clamp(11px, 2.2vw, 12px);
            color: var(--text-sub);
        }

        .file-selected-box {
            display: none;
            background: var(--green-light);
            border: 1px solid var(--green-border);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 18px;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }

        .file-selected-info {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1;
        }

        .file-selected-info svg {
            flex-shrink: 0;
        }

        .file-selected-names {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: clamp(10px, 2.5vw, 14px);
            margin-bottom: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }

        .form-group.full {
            grid-column: 1 / -1;
            margin-bottom: 16px;
        }

        label {
            font-size: clamp(11.5px, 2.2vw, 12px);
            font-weight: 600;
            color: var(--text-muted);
        }

        input[type="text"],
        input[type="password"],
        textarea {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: clamp(8px, 2vw, 10px) 12px;
            color: var(--text-main);
            font-size: clamp(12.5px, 2.5vw, 13px);
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            width: 100%;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 75px;
            line-height: 1.5;
        }

        .btn-submit {
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: clamp(11px, 2.5vw, 13px) 20px;
            border-radius: var(--radius-md);
            font-size: clamp(13.5px, 2.8vw, 14.5px);
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            width: 100%;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            touch-action: manipulation;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Progress Bar */
        .progress-container {
            display: none;
            margin-top: 16px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            gap: 8px;
        }

        .progress-track {
            height: 8px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #dc2626, #059669);
            border-radius: 9999px;
            transition: width 0.15s ease;
        }

        /* Toast */
        #toast {
            position: fixed;
            bottom: clamp(16px, 3vw, 24px);
            right: clamp(16px, 3vw, 24px);
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            font-size: clamp(12px, 2.5vw, 13px);
            font-weight: 500;
            opacity: 0;
            transform: translateY(12px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
            z-index: 1000;
            max-width: calc(100vw - 32px);
            word-break: break-word;
        }

        #toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <header>
        <div class="container">
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <path d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                </div>
                <div>
                    <span class="brand-title"><?= htmlspecialchars(APP_NAME) ?></span>
                    <span class="brand-badge">Admin</span>
                </div>
            </a>

            <div class="header-actions">
                <a href="index.php" class="btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>View App Page</span>
                </a>
                <?php if ($isAuth): ?>
                    <a href="admin.php?action=logout" class="btn-outline btn-logout">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Logout</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="main-wrapper">
        <div class="container">
            <?php if (!$isAuth): ?>
                <!-- Login Box -->
                <div class="login-wrap">
                    <div class="login-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 4px; color: #0f172a;">Admin Authentication</h2>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 18px;">Enter administrator passcode to manage APK builds.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="admin.php">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group" style="margin-bottom: 16px; text-align: left;">
                            <label for="password">Passcode / PIN</label>
                            <input type="password" id="password" name="password" placeholder="Enter admin password..." required autofocus>
                        </div>
                        <button type="submit" class="btn-submit">
                            <span>Unlock Panel</span>
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <!-- Admin Dashboard -->

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div id="dynamicAlert" style="display:none;" class="alert"></div>

                <!-- Compact Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Active Version</span>
                            <span class="stat-val" id="displayVersion">v<?= htmlspecialchars($meta['version_name']) ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Downloads</span>
                            <span class="stat-val"><?= number_format($meta['download_count']) ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 14 14"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">APK Size</span>
                            <span class="stat-val" id="displaySize"><?= $formattedSize ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Status</span>
                            <span class="stat-val" style="color: <?= $isAvailable ? 'var(--accent-green)' : '#dc2626' ?>;">
                                <?= $isAvailable ? 'Active' : 'No APK' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Permanent Link Card -->
                <div class="dist-card">
                    <div class="dist-header">
                        <div class="dist-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            Permanent Unchanging Download Link
                        </div>
                        <a href="download.php" target="_blank" class="btn-outline" style="font-size: 11px; padding: 4px 10px;">
                            Test Download ↗
                        </a>
                    </div>
                    <div class="dist-desc">
                        Share this fixed link with users. When you upload a new APK below, it replaces the active build while keeping this exact URL.
                    </div>
                    <div class="link-row">
                        <span class="url" id="permLink"><?= htmlspecialchars($downloadUrl) ?></span>
                        <button class="btn-copy-sm" id="btnCopyDist" onclick="copyToClipboard('<?= htmlspecialchars($downloadUrl) ?>', 'Link copied!', this)">Copy URL</button>
                    </div>
                </div>

                <!-- Upload Form -->
                <div class="upload-card">
                    <h2 class="upload-title">Upload New Application Build (.apk)</h2>
                    <p class="upload-subtitle">Uploading a build automatically updates the permanent download endpoint.</p>

                    <form id="uploadForm" method="POST" action="admin.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_apk">

                        <!-- Compact Dropzone -->
                        <div class="dropzone" id="dropzone">
                            <input type="file" name="apk_file" id="apkFile" accept=".apk" required>
                            <div class="drop-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="drop-title">Drag & drop .apk file here or tap to browse</div>
                            <div class="drop-sub">Max upload size: <?= MAX_UPLOAD_SIZE_MB ?>MB</div>
                        </div>

                        <!-- Selected File State -->
                        <div class="file-selected-box" id="fileSelectedBox">
                            <div class="file-selected-info">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <div class="file-selected-names">
                                    <strong id="selectedFileName" style="color: #0f172a; font-size: 13px;">app.apk</strong>
                                    <span id="selectedFileSize" style="color: var(--text-sub); font-size: 11px; margin-left: 6px;">24.5 MB</span>
                                </div>
                            </div>
                            <span style="font-size: 11px; color: var(--accent-green); font-weight: 700; white-space: nowrap;">Ready to upload</span>
                        </div>

                        <!-- Metadata Grid -->
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="version_name">Version Name (e.g. 1.2.0)</label>
                                <input type="text" id="version_name" name="version_name" value="<?= htmlspecialchars($meta['version_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="version_code">Version Code (e.g. 2)</label>
                                <input type="text" id="version_code" name="version_code" value="<?= htmlspecialchars($meta['version_code']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="min_android">Minimum Android</label>
                                <input type="text" id="min_android" name="min_android" value="<?= htmlspecialchars($meta['min_android']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="package_name">App Package</label>
                                <input type="text" id="package_name" name="package_name" value="<?= htmlspecialchars($meta['package_name']) ?>" readonly style="background: #f1f5f9; color: var(--text-sub);">
                            </div>
                        </div>

                        <div class="form-group full">
                            <label for="changelog">What's New / Release Notes</label>
                            <textarea id="changelog" name="changelog" placeholder="Enter release highlights..."><?= htmlspecialchars($meta['changelog']) ?></textarea>
                        </div>

                        <button type="submit" id="submitBtn" class="btn-submit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span>Upload & Deploy APK</span>
                        </button>

                        <!-- Progress Bar -->
                        <div class="progress-container" id="progressContainer">
                            <div class="progress-header">
                                <span id="progressStatus">Uploading APK...</span>
                                <span id="progressPercent">0%</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                        </div>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <div id="toast"></div>

    <script>
        function copyToClipboard(text, msg, btnEl) {
            const onSuccess = () => {
                showToast(msg || "Copied to clipboard!");
                if (btnEl) {
                    btnEl.classList.add('copied');
                    const orig = btnEl.textContent;
                    btnEl.textContent = 'Copied!';
                    setTimeout(() => {
                        btnEl.classList.remove('copied');
                        btnEl.textContent = orig;
                    }, 2000);
                }
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(onSuccess).catch(() => fallbackCopy(text, onSuccess));
            } else {
                fallbackCopy(text, onSuccess);
            }
        }

        function fallbackCopy(text, callback) {
            const el = document.createElement('textarea');
            el.value = text;
            el.style.position = 'fixed';
            el.style.opacity = '0';
            document.body.appendChild(el);
            el.select();
            try {
                document.execCommand('copy');
                if (callback) callback();
            } catch(e) {
                showToast("Failed to copy.");
            }
            document.body.removeChild(el);
        }

        let toastTimer = null;
        function showToast(msg) {
            const toast = document.getElementById('toast');
            if (!toast) return;
            toast.innerText = msg;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
        }

        <?php if ($isAuth): ?>
        const fileInput = document.getElementById('apkFile');
        const dropzone = document.getElementById('dropzone');
        const fileSelectedBox = document.getElementById('fileSelectedBox');
        const selectedFileName = document.getElementById('selectedFileName');
        const selectedFileSize = document.getElementById('selectedFileSize');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressPercent = document.getElementById('progressPercent');
        const progressStatus = document.getElementById('progressStatus');
        const dynamicAlert = document.getElementById('dynamicAlert');

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (!file.name.toLowerCase().endsWith('.apk')) {
                    alert('Please select a valid .apk file!');
                    this.value = '';
                    fileSelectedBox.style.display = 'none';
                    return;
                }
                selectedFileName.textContent = file.name;
                selectedFileSize.textContent = formatBytes(file.size);
                fileSelectedBox.style.display = 'flex';
            }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const file = fileInput.files[0];
            if (!file) {
                alert('Please select an APK file to upload!');
                return;
            }

            const formData = new FormData(uploadForm);
            formData.append('ajax', '1');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin.php', true);

            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span>Uploading...</span>`;
            progressContainer.style.display = 'block';
            progressFill.style.width = '0%';
            progressPercent.textContent = '0%';
            progressStatus.textContent = 'Uploading APK build...';
            dynamicAlert.style.display = 'none';

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressFill.style.width = percent + '%';
                    progressPercent.textContent = percent + '%';
                    progressStatus.textContent = `Uploading (${formatBytes(e.loaded)} / ${formatBytes(e.total)})...`;
                }
            };

            xhr.onload = function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Upload & Deploy APK</span>
                `;

                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        dynamicAlert.className = 'alert alert-success';
                        dynamicAlert.textContent = res.message;
                        dynamicAlert.style.display = 'block';
                        showToast("APK successfully updated!");

                        if (res.version_name) {
                            document.getElementById('displayVersion').textContent = 'v' + res.version_name;
                        }
                        if (res.file_size) {
                            document.getElementById('displaySize').textContent = res.file_size;
                        }

                        progressStatus.textContent = 'Upload complete! Permanent link updated.';
                        fileInput.value = '';
                        fileSelectedBox.style.display = 'none';
                    } else {
                        dynamicAlert.className = 'alert alert-error';
                        dynamicAlert.textContent = res.message || 'Upload failed.';
                        dynamicAlert.style.display = 'block';
                    }
                } catch (err) {
                    dynamicAlert.className = 'alert alert-error';
                    dynamicAlert.textContent = 'Server response error: ' + xhr.responseText;
                    dynamicAlert.style.display = 'block';
                }
            };

            xhr.onerror = function() {
                submitBtn.disabled = false;
                dynamicAlert.className = 'alert alert-error';
                dynamicAlert.textContent = 'Network or server error during upload.';
                dynamicAlert.style.display = 'block';
            };

            xhr.send(formData);
        });
        <?php endif; ?>
    </script>
</body>
</html>
