<?php
/**
 * Red Taxis App - Permanent Download Handler
 * -------------------------------------------------------------------------
 * Streams the active APK with correct Android MIME types and increments stats.
 */

require_once __DIR__ . '/config.php';

$filePath = UPLOAD_DIR . '/' . STORAGE_FILENAME;

if (!file_exists($filePath)) {
    // If APK is not yet uploaded, show a clean responsive message
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <title>APK Not Available - <?= htmlspecialchars(APP_NAME) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
            body {
                font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background: #f8fafc;
                color: #0f172a;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: clamp(16px, 4vw, 24px);
                line-height: 1.5;
            }
            .card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: clamp(24px, 5vw, 36px) clamp(16px, 4vw, 28px);
                width: min(100%, 420px);
                text-align: center;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            }
            .icon {
                width: clamp(44px, 8vw, 52px);
                height: clamp(44px, 8vw, 52px);
                background: #fef2f2;
                color: #dc2626;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
                border: 1px solid #fca5a5;
            }
            .icon svg {
                width: 26px;
                height: 26px;
            }
            h1 { font-size: clamp(18px, 4vw, 21px); font-weight: 700; margin-bottom: 8px; color: #0f172a; }
            p { color: #64748b; font-size: clamp(12.5px, 2.5vw, 13.5px); line-height: 1.55; margin-bottom: 22px; }
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .btn {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #dc2626;
                color: #fff;
                text-decoration: none;
                padding: 11px 20px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                transition: background 0.15s ease, transform 0.1s ease;
                min-height: 44px;
                touch-action: manipulation;
            }
            .btn:hover {
                background: #b91c1c;
            }
            .btn:active {
                transform: scale(0.98);
            }
            .btn-secondary {
                background: #f1f5f9;
                color: #334155;
                border: 1px solid #e2e8f0;
            }
            .btn-secondary:hover {
                background: #e2e8f0;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1>APK File Not Found</h1>
            <p>No APK has been uploaded yet. Please log into the Admin portal to upload the application package.</p>
            <div class="btn-group">
                <a href="admin.php" class="btn">Go to Admin Uploader</a>
                <a href="index.php" class="btn btn-secondary">Back to Landing Page</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Increment download count
increment_download_count();

// Get file size
$fileSize = filesize($filePath);
$filename = DOWNLOAD_FILENAME;

// Clear output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Set Headers for APK Download
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $fileSize);
header('Accept-Ranges: bytes');

// Stream file in 8KB chunks to handle large APKs cleanly without memory limits
$handle = fopen($filePath, 'rb');
if ($handle !== false) {
    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
} else {
    readfile($filePath);
}
exit;
