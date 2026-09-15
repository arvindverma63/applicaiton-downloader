<?php
/**
 * Red Taxis App - Public Download & Landing Page
 * -------------------------------------------------------------------------
 * Ultra-responsive, lightweight, high-performance UI using Roboto.
 */
require_once __DIR__ . '/config.php';

$meta = get_app_meta();
$isAvailable = is_apk_available();
$downloadUrl = get_download_url();
$formattedSize = format_bytes($meta['file_size']);
$releaseDate = !empty($meta['upload_time']) ? date('M d, Y - h:i A', $meta['upload_time']) : 'Recently updated';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($meta['app_name']) ?> - Official Android App Download</title>
    <meta name="description" content="Download the latest <?= htmlspecialchars($meta['app_name']) ?> APK for Android. Fast, reliable, and secure taxi bookings at your fingertips.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- QRCode generator library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        :root {
            --primary: #dc2626;
            --primary-hover: #b91c1c;
            --primary-active: #991b1b;
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
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
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

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            flex-direction: column;
        }

        /* Fluid Container */
        .container {
            width: 100%;
            max-width: 980px;
            margin-left: auto;
            margin-right: auto;
            padding-left: clamp(12px, 3.5vw, 20px);
            padding-right: clamp(12px, 3.5vw, 20px);
        }

        .main-wrapper {
            flex: 1;
            padding-bottom: clamp(24px, 4vw, 40px);
        }

        /* Top Navigation */
        nav {
            padding: clamp(10px, 2vw, 14px) 0;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
            margin-bottom: clamp(16px, 3vw, 24px);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: clamp(8px, 2vw, 12px);
            text-decoration: none;
            color: var(--text-main);
            min-width: 0;
        }

        .brand-icon {
            width: clamp(32px, 5vw, 36px);
            height: clamp(32px, 5vw, 36px);
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
            flex-shrink: 0;
        }

        .brand-icon svg {
            width: 18px;
            height: 18px;
        }

        .brand-name {
            font-size: clamp(15px, 3.5vw, 17px);
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: clamp(12px, 2.5vw, 13px);
            font-weight: 500;
            padding: 6px clamp(8px, 2vw, 12px);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: #ffffff;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
            touch-action: manipulation;
        }

        .admin-link:hover {
            color: var(--primary);
            border-color: var(--primary-border);
            background: var(--primary-light);
        }

        .admin-link:active {
            transform: scale(0.97);
        }

        /* Hero Main Layout */
        .hero-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: clamp(16px, 3.5vw, 28px);
            box-shadow: var(--shadow-sm);
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(200px, 0.85fr);
            gap: clamp(16px, 3vw, 28px);
            align-items: center;
            margin-bottom: clamp(16px, 3vw, 24px);
        }

        @media (max-width: 768px) {
            .hero-box {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .hero-content {
            min-width: 0;
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--green-light);
            color: var(--accent-green);
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: clamp(11px, 2.5vw, 12px);
            font-weight: 600;
            border: 1px solid var(--green-border);
            margin-bottom: clamp(8px, 2vw, 12px);
        }

        .badge-verified svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        h1 {
            font-size: clamp(20px, 4.5vw, 27px);
            font-weight: 700;
            line-height: 1.25;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .app-highlight {
            color: var(--primary);
        }

        .hero-desc {
            font-size: clamp(13px, 2.8vw, 14px);
            color: var(--text-muted);
            margin-bottom: clamp(14px, 2.5vw, 20px);
            line-height: 1.5;
        }

        /* Responsive Metadata Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(6px, 1.5vw, 10px);
            margin-bottom: clamp(16px, 3vw, 22px);
        }

        @media (max-width: 580px) {
            .meta-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .meta-pill {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            padding: clamp(6px, 1.8vw, 9px) clamp(8px, 2vw, 12px);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .meta-pill .label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--text-sub);
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-pill .value {
            font-size: clamp(12px, 2.8vw, 13.5px);
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Actions */
        .actions-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            padding: clamp(11px, 2.5vw, 14px) clamp(16px, 3vw, 24px);
            border-radius: var(--radius-md);
            font-size: clamp(14px, 3vw, 15px);
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(220, 38, 38, 0.28);
            transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            min-height: 46px;
            touch-action: manipulation;
        }

        .btn-download:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.35);
        }

        .btn-download:active {
            background: var(--primary-active);
            transform: scale(0.99);
        }

        .btn-download svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .btn-download .btn-badge {
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 2px;
        }

        .btn-disabled {
            background: #cbd5e1 !important;
            color: #64748b !important;
            box-shadow: none !important;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Copy Link Box */
        .copy-link-box {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 4px 4px 4px 10px;
            gap: 8px;
            min-width: 0;
        }

        .copy-link-text {
            font-family: 'Roboto Mono', monospace;
            font-size: clamp(11px, 2.3vw, 12px);
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            min-width: 0;
            user-select: all;
        }

        .btn-copy {
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
            touch-action: manipulation;
            min-height: 32px;
        }

        .btn-copy:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-copy:active {
            transform: scale(0.96);
        }

        .btn-copy.copied {
            background: var(--green-light);
            border-color: var(--green-border);
            color: var(--accent-green);
        }

        /* QR Showcase Card */
        .qr-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: clamp(14px, 3vw, 20px);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            justify-content: center;
            width: 100%;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
        }

        .qr-wrapper #qrcode {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrapper #qrcode img,
        .qr-wrapper #qrcode canvas {
            display: block;
            max-width: 100%;
            height: auto;
        }

        .qr-title {
            font-size: clamp(12.5px, 2.5vw, 13.5px);
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .qr-desc {
            font-size: clamp(10.5px, 2.2vw, 11.5px);
            color: var(--text-sub);
            max-width: 200px;
        }

        /* Section Cards */
        .section-header {
            font-size: clamp(14px, 3vw, 15px);
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-header svg {
            color: var(--primary);
            flex-shrink: 0;
        }

        /* Steps Layout (Responsive Grid) */
        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: clamp(10px, 2vw, 14px);
            margin-bottom: clamp(16px, 3vw, 24px);
        }

        @media (max-width: 540px) {
            .steps-container {
                grid-template-columns: 1fr;
            }
        }

        .step-item {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: clamp(12px, 2.5vw, 16px);
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .step-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .step-badge {
            width: 26px;
            height: 26px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            border: 1px solid #fecaca;
        }

        .step-item h4 {
            font-size: clamp(13px, 2.5vw, 14px);
            font-weight: 700;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .step-item p {
            font-size: clamp(11.5px, 2.3vw, 12.5px);
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Changelog Box */
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: clamp(14px, 3vw, 20px);
            box-shadow: var(--shadow-sm);
            margin-bottom: clamp(20px, 4vw, 30px);
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .info-title {
            font-size: clamp(13px, 2.5vw, 14px);
            font-weight: 700;
            color: #0f172a;
        }

        .info-date {
            font-size: clamp(11px, 2.2vw, 12px);
            color: var(--text-sub);
        }

        .changelog-text {
            font-size: clamp(12.5px, 2.5vw, 13.5px);
            color: #334155;
            line-height: 1.6;
            white-space: pre-line;
            word-break: break-word;
        }

        .hash-bar {
            margin-top: 14px;
            font-size: clamp(10.5px, 2.2vw, 11.5px);
            font-family: 'Roboto Mono', monospace;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            border-radius: 6px;
            word-break: break-all;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }

        .hash-code {
            flex: 1;
            min-width: 180px;
        }

        .btn-copy-hash {
            background: #ffffff;
            border: 1px solid var(--border-color);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.15s ease;
            flex-shrink: 0;
            touch-action: manipulation;
        }

        .btn-copy-hash:hover {
            color: var(--primary);
            border-color: var(--primary-border);
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            padding: clamp(14px, 2.5vw, 20px) 0;
            color: var(--text-sub);
            font-size: clamp(11px, 2.2vw, 12px);
            background: #ffffff;
            margin-top: auto;
        }

        footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 500px) {
            footer .container {
                flex-direction: column;
                text-align: center;
                gap: 6px;
            }
        }

        /* Toast Alert */
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
    <nav>
        <div class="container">
            <a href="index.php" class="brand" aria-label="<?= htmlspecialchars($meta['app_name']) ?> Home">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <path d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                </div>
                <div class="brand-name"><?= htmlspecialchars($meta['app_name']) ?></div>
            </a>
            <a href="admin.php" class="admin-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                </svg>
                <span>Admin Portal</span>
            </a>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="container">
            <!-- Hero Box -->
            <section class="hero-box">
                <div class="hero-content">
                    <div class="badge-verified">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                        Official & Verified Build
                    </div>

                    <h1>Download <span class="app-highlight"><?= htmlspecialchars($meta['app_name']) ?></span> for Android</h1>
                    <p class="hero-desc"><?= htmlspecialchars(APP_TAGLINE) ?></p>

                    <!-- Compact Metadata Grid -->
                    <div class="meta-grid">
                        <div class="meta-pill">
                            <span class="label">Version</span>
                            <span class="value">v<?= htmlspecialchars($meta['version_name']) ?></span>
                        </div>
                        <div class="meta-pill">
                            <span class="label">Size</span>
                            <span class="value"><?= $formattedSize ?></span>
                        </div>
                        <div class="meta-pill">
                            <span class="label">Compatibility</span>
                            <span class="value"><?= htmlspecialchars($meta['min_android']) ?></span>
                        </div>
                        <div class="meta-pill">
                            <span class="label">Downloads</span>
                            <span class="value"><?= number_format($meta['download_count']) ?>+</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="actions-group">
                        <?php if ($isAvailable): ?>
                            <a href="download.php" class="btn-download" id="downloadBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>Download APK</span>
                                <span class="btn-badge"><?= $formattedSize ?></span>
                            </a>
                        <?php else: ?>
                            <button class="btn-download btn-disabled" disabled>
                                <span>APK Not Uploaded Yet</span>
                            </button>
                        <?php endif; ?>

                        <!-- Copy Permanent URL -->
                        <div class="copy-link-box">
                            <span class="copy-link-text" id="permanentUrl"><?= htmlspecialchars($downloadUrl) ?></span>
                            <button class="btn-copy" id="copyBtn" onclick="copyPermanentLink()">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                </svg>
                                <span id="copyBtnText">Copy Link</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right QR Code Scan Box -->
                <div class="qr-card">
                    <div class="qr-wrapper">
                        <div id="qrcode"></div>
                    </div>
                    <div class="qr-title">Scan to Download</div>
                    <div class="qr-desc">Scan with mobile camera to download APK directly</div>
                </div>
            </section>

            <!-- Installation Steps (Responsive Grid) -->
            <div class="section-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                </svg>
                Installation Guide
            </div>

            <div class="steps-container">
                <div class="step-item">
                    <div class="step-badge">1</div>
                    <h4>Download APK</h4>
                    <p>Click Download APK or scan the QR code to save <strong><?= htmlspecialchars(DOWNLOAD_FILENAME) ?></strong> to your device.</p>
                </div>
                <div class="step-item">
                    <div class="step-badge">2</div>
                    <h4>Allow Installation</h4>
                    <p>If prompted by Android, permit <em>"Install unknown apps"</em> in your browser or file manager settings.</p>
                </div>
                <div class="step-item">
                    <div class="step-badge">3</div>
                    <h4>Install & Open</h4>
                    <p>Open the downloaded APK from Notifications or Downloads folder and tap <strong>Install</strong> to launch.</p>
                </div>
            </div>

            <!-- Changelog Card -->
            <div class="info-card">
                <div class="info-header">
                    <span class="info-title">Release Notes (v<?= htmlspecialchars($meta['version_name']) ?>)</span>
                    <span class="info-date">Updated: <?= $releaseDate ?></span>
                </div>
                <div class="changelog-text"><?= htmlspecialchars($meta['changelog']) ?></div>
                <?php if (!empty($meta['file_hash_sha256'])): ?>
                    <div class="hash-bar">
                        <div class="hash-code"><strong>SHA-256: </strong><?= htmlspecialchars($meta['file_hash_sha256']) ?></div>
                        <button class="btn-copy-hash" onclick="copyHash('<?= htmlspecialchars($meta['file_hash_sha256']) ?>')">Copy Hash</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div>© <?= date('Y') ?> <?= htmlspecialchars($meta['app_name']) ?>. All rights reserved.</div>
            <div>Permanent Distribution Link • No Database Required</div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div id="toast">Download link copied!</div>

    <script>
        // Generate dynamic QR code for mobile scanning
        const downloadUrl = "<?= $downloadUrl ?>";
        const qrContainer = document.getElementById("qrcode");
        
        // Responsive QR sizing
        const qrSize = Math.min(130, Math.max(100, Math.floor(window.innerWidth * 0.3)));
        new QRCode(qrContainer, {
            text: downloadUrl,
            width: qrSize,
            height: qrSize,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        // Copy permanent link to clipboard with visual state
        function copyPermanentLink() {
            const btn = document.getElementById('copyBtn');
            const btnText = document.getElementById('copyBtnText');

            const setCopied = () => {
                showToast("Download link copied to clipboard!");
                if (btn && btnText) {
                    btn.classList.add('copied');
                    btnText.textContent = "Copied!";
                    setTimeout(() => {
                        btn.classList.remove('copied');
                        btnText.textContent = "Copy Link";
                    }, 2000);
                }
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(downloadUrl).then(setCopied).catch(() => fallbackCopy(downloadUrl, setCopied));
            } else {
                fallbackCopy(downloadUrl, setCopied);
            }
        }

        function copyHash(hash) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(hash).then(() => showToast("SHA-256 hash copied!"));
            } else {
                fallbackCopy(hash, () => showToast("SHA-256 hash copied!"));
            }
        }

        function fallbackCopy(text, callback) {
            const input = document.createElement('input');
            input.value = text;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            try {
                document.execCommand('copy');
                if (callback) callback();
            } catch(e) {
                showToast("Failed to copy link.");
            }
            document.body.removeChild(input);
        }

        let toastTimer = null;
        function showToast(msg) {
            const toast = document.getElementById('toast');
            if (!toast) return;
            if (msg) toast.innerText = msg;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2200);
        }
    </script>
</body>
</html>
