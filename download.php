<?php
/**
 * Red Taxis App - Permanent Download Handler
 * -------------------------------------------------------------------------
 * Streams the active APK with correct Android MIME types and increments stats.
 */

require_once __DIR__ . '/config.php';

$filePath = UPLOAD_DIR . '/' . STORAGE_FILENAME;

if (!file_exists($filePath)) {
    // If APK is not yet uploaded, show a clean message
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>APK Not Available - <?= htmlspecialchars(APP_NAME) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
                background: #f8fafc;
                color: #0f172a;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }
            .card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 32px 24px;
                max-width: 420px;
                width: 100%;
                text-align: center;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }
            .icon {
                width: 50px;
                height: 50px;
                background: #fef2f2;
                color: #dc2626;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
                border: 1px solid #fca5a5;
            }
            h1 { font-size: 20px; font-weight: 700; margin-bottom: 8px; color: #0f172a; }
            p { color: #64748b; font-size: 13px; line-height: 1.5; margin-bottom: 20px; }
            .btn {
                display: inline-block;
                background: #dc2626;
                color: #fff;
                text-decoration: none;
                padding: 10px 22px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                transition: background 0.15s ease;
            }
            .btn:hover {
                background: #b91c1c;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1>APK File Not Found</h1>
            <p>No APK has been uploaded yet. Please log into the Admin portal to upload the application package.</p>
            <a href="admin.php" class="btn">Go to Admin Uploader</a>
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
