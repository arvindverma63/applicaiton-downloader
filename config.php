<?php
/**
 * Red Taxis App Downloader - System Configuration
 * -------------------------------------------------------------------------
 * No database required. Configuration & metadata are stored locally in JSON.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------------------
// Global Settings
// -------------------------------------------------------------------------
define('APP_NAME', 'Red Taxis');
define('APP_TAGLINE', 'Fast, Reliable & Affordable Rides at your Fingertips');
define('APP_PACKAGE_NAME', 'com.redtaxis.customer');

// Admin Passcode to access the Upload/Management Portal
define('ADMIN_PASSWORD', 'admin123'); // <-- Change this to your secure password!

// Uploads directory & storage target
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('STORAGE_FILENAME', 'app-latest.apk'); // Constant filename for permanent link
define('DOWNLOAD_FILENAME', 'RedTaxis-latest.apk'); // Filename shown when users download
define('META_FILE', __DIR__ . '/uploads/meta.json');

// Max file upload limit in MB
define('MAX_UPLOAD_SIZE_MB', 250);

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// -------------------------------------------------------------------------
// Helper Functions
// -------------------------------------------------------------------------

/**
 * Get full base URL of the current installation
 */
function get_base_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $scriptDir = trim($scriptDir, '/.');
    return $protocol . $host . ($scriptDir ? '/' . $scriptDir : '');
}

/**
 * Get permanent direct download URL
 */
function get_download_url(): string {
    return get_base_url() . '/download.php';
}

/**
 * Get direct APK static URL
 */
function get_direct_apk_url(): string {
    return get_base_url() . '/uploads/' . STORAGE_FILENAME;
}

/**
 * Get application metadata from JSON file
 */
function get_app_meta(): array {
    $default = [
        'app_name'      => APP_NAME,
        'version_name'  => '1.0.0',
        'version_code'  => '1',
        'min_android'   => 'Android 7.0+',
        'changelog'     => "Initial release.\n- Fast booking system\n- Real-time driver tracking\n- Secure payments",
        'upload_time'   => null,
        'file_size'     => 0,
        'file_hash_sha256' => '',
        'download_count'=> 0,
        'package_name'  => APP_PACKAGE_NAME
    ];

    if (file_exists(META_FILE)) {
        $data = json_decode(file_get_contents(META_FILE), true);
        if (is_array($data)) {
            $merged = array_merge($default, $data);
            
            // Auto check current file size if file exists on disk
            $filePath = UPLOAD_DIR . '/' . STORAGE_FILENAME;
            if (file_exists($filePath)) {
                $merged['file_size'] = filesize($filePath);
                if (empty($merged['upload_time'])) {
                    $merged['upload_time'] = filemtime($filePath);
                }
            }
            return $merged;
        }
    }

    // Check if apk already exists on disk
    $filePath = UPLOAD_DIR . '/' . STORAGE_FILENAME;
    if (file_exists($filePath)) {
        $default['file_size'] = filesize($filePath);
        $default['upload_time'] = filemtime($filePath);
    }

    return $default;
}

/**
 * Save metadata to JSON file
 */
function save_app_meta(array $meta): bool {
    $existing = get_app_meta();
    $updated = array_merge($existing, $meta);
    return (bool) file_put_contents(META_FILE, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Increment download counter atomically
 */
function increment_download_count(): int {
    $meta = get_app_meta();
    $meta['download_count'] = ($meta['download_count'] ?? 0) + 1;
    save_app_meta($meta);
    return $meta['download_count'];
}

/**
 * Format bytes into human readable format (MB, KB, etc.)
 */
function format_bytes(int $bytes, int $precision = 2): string {
    if ($bytes <= 0) return '0 MB';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Check if the active APK exists
 */
function is_apk_available(): bool {
    return file_exists(UPLOAD_DIR . '/' . STORAGE_FILENAME);
}

/**
 * Authentication check for admin
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
}
