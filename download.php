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
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: #0f111a;
                color: #f1f5f9;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .card {
                background: #181b2a;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 20px;
                padding: 40px 30px;
                max-width: 480px;
                width: 100%;
                text-align: center;
                box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            }
            .icon {
                width: 70px;
                height: 70px;
                background: rgba(239, 68, 68, 0.15);
                color: #ef4444;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 20px;
            }
            h1 { font-size: 24px; margin-bottom: 12px; }
            p { color: #94a3b8; font-size: 15px; line-height: 1.6; margin-bottom: 25px; }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: #fff;
                text-decoration: none;
                padding: 12px 28px;
                border-radius: 12px;
                font-weight: 600;
                transition: all 0.2s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1>APK File Not Found</h1>
            <p>No APK has been uploaded yet. Please log into the Admin portal to upload the latest application build.</p>
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
