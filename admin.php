<?php
/**
 * Red Taxis App - Admin APK Upload & Management Portal
 * -------------------------------------------------------------------------
 * Drag-and-drop APK uploader with metadata management and permanent URL distribution.
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
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was selected for upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
        ];
        $errCode = $_FILES['apk_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $errMsg = $uploadErrors[$errCode] ?? 'Upload failed with error code: ' . $errCode;
        
        if ($isAjax) $respondJson(false, $errMsg);
        $error = $errMsg;
    } else {
        $file = $_FILES['apk_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate Extension
        if ($ext !== 'apk') {
            $msg = 'Invalid file type! Only Android Application Package (.apk) files are allowed.';
            if ($isAjax) $respondJson(false, $msg);
            $error = $msg;
        } else {
            $targetPath = UPLOAD_DIR . '/' . STORAGE_FILENAME;

            // Move uploaded file and overwrite existing active APK
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $fileSize = filesize($targetPath);
                $fileHash = hash_file('sha256', $targetPath);

                // Update metadata
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

                $msg = 'APK successfully uploaded! Permanent download URL is updated.';
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
                $msg = 'Failed to move uploaded file to destination folder. Check folder write permissions.';
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
$directApkUrl = get_direct_apk_url();
$formattedSize = format_bytes($meta['file_size']);
$releaseDate = !empty($meta['upload_time']) ? date('M d, Y - h:i A', $meta['upload_time']) : 'Never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - <?= htmlspecialchars(APP_NAME) ?> APK Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ef4444;
            --primary-hover: #dc2626;
            --primary-glow: rgba(239, 68, 68, 0.35);
            --bg-dark: #090b11;
            --bg-card: #121622;
            --bg-card-hover: #181d2c;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-focus: rgba(239, 68, 68, 0.5);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-green: #10b981;
            --accent-amber: #f59e0b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(239, 68, 68, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(15, 23, 42, 0.8) 0%, transparent 50%);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        /* Top Nav */
        header {
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 36px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #ef4444, #991b1b);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-title {
            font-weight: 800;
            font-size: 18px;
        }

        .brand-badge {
            font-size: 11px;
            background: rgba(239, 68, 68, 0.2);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
            margin-left: 6px;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-outline {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Login Card */
        .login-wrap {
            max-width: 420px;
            margin: 60px auto;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            text-align: center;
        }

        .login-icon {
            width: 56px;
            height: 56px;
            background: rgba(239, 68, 68, 0.12);
            color: var(--primary);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-val {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin-top: 2px;
        }

        /* Distribution Links Box */
        .dist-card {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(18, 22, 34, 0.8) 100%);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 36px;
        }

        .dist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .dist-title {
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .link-row {
            display: flex;
            align-items: center;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 8px 12px;
            gap: 12px;
            margin-top: 10px;
        }

        .link-row .url {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            color: #f1f5f9;
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-copy-sm {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-copy-sm:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Upload Portal Card */
        .upload-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 32px;
        }

        .upload-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .upload-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        /* Drop Zone */
        .dropzone {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 36px 20px;
            text-align: center;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
            transition: all 0.2s ease;
            position: relative;
            margin-bottom: 24px;
        }

        .dropzone:hover,
        .dropzone.dragover {
            border-color: var(--primary);
            background: rgba(239, 68, 68, 0.05);
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
            width: 52px;
            height: 52px;
            background: rgba(239, 68, 68, 0.12);
            color: var(--primary);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .drop-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .drop-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        .file-selected-box {
            display: none;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 24px;
            align-items: center;
            justify-content: space-between;
        }

        .file-selected-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full {
            grid-column: 1 / -1;
            margin-bottom: 20px;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }

        input[type="text"],
        input[type="password"],
        textarea {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 16px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
            width: 100%;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            box-shadow: 0 8px 20px var(--primary-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(239, 68, 68, 0.45);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Progress Bar */
        .progress-container {
            display: none;
            margin-top: 24px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 18px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .progress-track {
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #ef4444, #10b981);
            border-radius: 9999px;
            transition: width 0.15s ease;
        }

        /* Toast Alert */
        #toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1e293b;
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            font-weight: 600;
            font-size: 14px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s;
            pointer-events: none;
            z-index: 999;
        }

        #toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <a href="index.php" class="brand">
            <div class="brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                    <circle cx="7" cy="17" r="2"></circle>
                    <path d="M9 17h6"></path>
                    <circle cx="17" cy="17" r="2"></circle>
                </svg>
            </div>
            <div>
                <span class="brand-title"><?= htmlspecialchars(APP_NAME) ?></span>
                <span class="brand-badge">Admin Manager</span>
            </div>
        </a>

        <div class="header-actions">
            <a href="index.php" class="btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                View App Landing
            </a>
            <?php if ($isAuth): ?>
                <a href="admin.php?action=logout" class="btn-outline" style="color: #f87171; border-color: rgba(239, 68, 68, 0.3);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!$isAuth): ?>
        <!-- Login Form -->
        <div class="login-wrap">
            <div class="login-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 8px;">Admin Passcode</h2>
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px;">Enter the administrator passcode configured in <code>config.php</code> to manage APK releases.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="login">
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="password">Passcode / PIN</label>
                    <input type="password" id="password" name="password" placeholder="Enter admin password..." required autofocus>
                </div>
                <button type="submit" class="btn-submit">
                    <span>Unlock Admin Panel</span>
                </button>
            </form>
        </div>

    <?php else: ?>
        <!-- Admin Dashboard & Uploader -->

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div id="dynamicAlert" style="display:none;" class="alert"></div>

        <!-- System Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total Downloads</span>
                    <span class="stat-val"><?= number_format($meta['download_count']) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 14 14"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">APK File Size</span>
                    <span class="stat-val" id="displaySize"><?= $formattedSize ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Storage Status</span>
                    <span class="stat-val" style="color: <?= $isAvailable ? 'var(--accent-green)' : '#f87171' ?>;">
                        <?= $isAvailable ? 'Ready & Active' : 'No APK Found' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Permanent URL Distribution Box -->
        <div class="dist-card">
            <div class="dist-header">
                <div class="dist-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                    </svg>
                    Permanent Unchanging Download URL
                </div>
                <a href="download.php" target="_blank" class="btn-outline" style="background: rgba(0,0,0,0.3); font-size: 12px; padding: 4px 10px;">
                    Test Download Link ↗
                </a>
            </div>
            <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5;">
                Share this fixed link with all users, marketing channels, and QR codes. When you upload a new APK below, it will instantly serve the new version at this exact same URL!
            </p>
            <div class="link-row">
                <span class="url" id="permLink"><?= htmlspecialchars($downloadUrl) ?></span>
                <button class="btn-copy-sm" onclick="copyToClipboard('<?= htmlspecialchars($downloadUrl) ?>', 'Download link copied!')">Copy Permanent URL</button>
            </div>
        </div>

        <!-- Upload Form Card -->
        <div class="upload-card">
            <h2 class="upload-title">Upload New Application Build (.apk)</h2>
            <p class="upload-subtitle">Uploading a new build will automatically replace the active APK while keeping your download URL identical.</p>

            <form id="uploadForm" method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_apk">

                <!-- Drag & Drop Zone -->
                <div class="dropzone" id="dropzone">
                    <input type="file" name="apk_file" id="apkFile" accept=".apk" required>
                    <div class="drop-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <div class="drop-title">Drag & Drop your .apk file here</div>
                    <div class="drop-sub">or click to browse from your computer (Max: <?= MAX_UPLOAD_SIZE_MB ?>MB)</div>
                </div>

                <!-- Selected File Display -->
                <div class="file-selected-box" id="fileSelectedBox">
                    <div class="file-selected-info">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <div>
                            <strong id="selectedFileName" style="color: #fff; font-size: 14px;">app.apk</strong>
                            <div id="selectedFileSize" style="color: var(--text-muted); font-size: 12px;">24.5 MB</div>
                        </div>
                    </div>
                    <span style="font-size: 12px; color: var(--accent-green); font-weight: 700;">Ready to upload</span>
                </div>

                <!-- Metadata Inputs -->
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
                        <label for="min_android">Minimum Android OS</label>
                        <input type="text" id="min_android" name="min_android" value="<?= htmlspecialchars($meta['min_android']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="package_name">App Package Name</label>
                        <input type="text" id="package_name" name="package_name" value="<?= htmlspecialchars($meta['package_name']) ?>" readonly style="opacity: 0.6;">
                    </div>
                </div>

                <div class="form-group full">
                    <label for="changelog">What's New / Release Notes</label>
                    <textarea id="changelog" name="changelog" placeholder="Enter release highlights or bug fixes..."><?= htmlspecialchars($meta['changelog']) ?></textarea>
                </div>

                <button type="submit" id="submitBtn" class="btn-submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Upload & Deploy APK</span>
                </button>

                <!-- Live Progress Bar -->
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

<div id="toast"></div>

<script>
    // Copy helper
    function copyToClipboard(text, msg) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(msg || "Copied to clipboard!");
        }).catch(() => {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showToast(msg || "Copied to clipboard!");
        });
    }

    function showToast(msg) {
        const toast = document.getElementById('toast');
        toast.innerText = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    <?php if ($isAuth): ?>
    // Drag and Drop & AJAX Upload handling
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

    fileInput.addEventListener('change', function(e) {
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

    // Drag-over styling
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

    // AJAX Asynchronous Upload with real-time percentage
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

        // UI Updates during upload
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
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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

                    // Update UI stats on page without reload
                    if (res.version_name) {
                        document.getElementById('displayVersion').textContent = 'v' + res.version_name;
                    }
                    if (res.file_size) {
                        document.getElementById('displaySize').textContent = res.file_size;
                    }

                    progressStatus.textContent = 'Upload complete! Deployed to permanent URL.';
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
