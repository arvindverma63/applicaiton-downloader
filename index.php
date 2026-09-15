<?php
/**
 * Red Taxis App - Public Download & Landing Page
 * -------------------------------------------------------------------------
 * Light, compact, responsive, and professional UI using Roboto.
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --radius-md: 10px;
            --radius-lg: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* Top Navigation */
        nav {
            padding: 12px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
            margin-bottom: 24px;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #0f172a;
        }

        .admin-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .admin-link:hover {
            color: var(--primary);
            border-color: var(--primary-border);
            background: var(--primary-light);
        }

        /* Hero Main Layout */
        .hero-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            display: grid;
            grid-template-columns: 1.4fr 0.8fr;
            gap: 24px;
            align-items: center;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .hero-box {
                grid-template-columns: 1fr;
                padding: 20px 16px;
                gap: 20px;
            }
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--green-light);
            color: var(--accent-green);
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #a7f3d0;
            margin-bottom: 10px;
        }

        .badge-verified svg {
            width: 14px;
            height: 14px;
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.25;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .app-highlight {
            color: var(--primary);
        }

        .hero-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 18px;
            line-height: 1.45;
        }

        /* Compact Metadata Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .meta-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .meta-pill {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            padding: 8px 10px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .meta-pill .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--text-sub);
            font-weight: 500;
        }

        .meta-pill .value {
            font-size: 13px;
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
            padding: 12px 20px;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
            transition: background 0.15s ease, transform 0.1s ease;
            border: none;
            cursor: pointer;
        }

        .btn-download:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-download:active {
            transform: translateY(0);
        }

        .btn-download svg {
            width: 20px;
            height: 20px;
        }

        .btn-download .btn-badge {
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
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
        }

        .copy-link-text {
            font-family: 'Roboto Mono', monospace;
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-grow: 1;
        }

        .btn-copy {
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .btn-copy:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* QR Showcase Card */
        .qr-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrapper #qrcode img,
        .qr-wrapper #qrcode canvas {
            display: block;
        }

        .qr-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .qr-desc {
            font-size: 11px;
            color: var(--text-sub);
        }

        /* Section Cards */
        .section-header {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-header svg {
            color: var(--primary);
        }

        /* Steps Layout (Compact Row) */
        .steps-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 650px) {
            .steps-container {
                grid-template-columns: 1fr;
            }
        }

        .step-item {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 14px;
            box-shadow: var(--shadow-sm);
        }

        .step-badge {
            width: 24px;
            height: 24px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            border: 1px solid #fecaca;
        }

        .step-item h4 {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .step-item p {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.45;
        }

        /* Changelog Box */
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .info-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .info-date {
            font-size: 11px;
            color: var(--text-sub);
        }

        .changelog-text {
            font-size: 13px;
            color: #334155;
            line-height: 1.55;
            white-space: pre-line;
        }

        .hash-bar {
            margin-top: 12px;
            font-size: 11px;
            font-family: 'Roboto Mono', monospace;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            padding: 6px 10px;
            border-radius: 6px;
            word-break: break-all;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            padding: 16px 0;
            color: var(--text-sub);
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Toast Alert */
        #toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            font-size: 13px;
            font-weight: 500;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.2s ease;
            pointer-events: none;
            z-index: 1000;
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
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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
                Admin Upload
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Hero Box -->
        <section class="hero-box">
            <div>
                <div class="badge-verified">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                    Official & Verified Release
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
                        <button class="btn-copy" onclick="copyPermanentLink()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                            </svg>
                            Copy Link
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
                <div class="qr-desc">Scan with mobile camera to download directly</div>
            </div>
        </section>

        <!-- Installation Steps (Compact) -->
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
                <p>Click Download or scan the QR code to save <strong><?= htmlspecialchars(DOWNLOAD_FILENAME) ?></strong> to your device.</p>
            </div>
            <div class="step-item">
                <div class="step-badge">2</div>
                <h4>Allow Installation</h4>
                <p>If prompted by Android, permit <em>"Install unknown apps"</em> in your browser or file manager settings.</p>
            </div>
            <div class="step-item">
                <div class="step-badge">3</div>
                <h4>Install & Open</h4>
                <p>Open the downloaded APK from Notifications or Downloads folder and tap <strong>Install</strong>.</p>
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
                    <strong>SHA-256: </strong><?= htmlspecialchars($meta['file_hash_sha256']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <footer>
            <div>© <?= date('Y') ?> <?= htmlspecialchars($meta['app_name']) ?>. All rights reserved.</div>
            <div>Permanent Distribution Link • No Database Required</div>
        </footer>
    </div>

    <!-- Toast Notification -->
    <div id="toast">Download link copied!</div>

    <script>
        // Generate QR code for mobile scanning
        const downloadUrl = "<?= $downloadUrl ?>";
        new QRCode(document.getElementById("qrcode"), {
            text: downloadUrl,
            width: 130,
            height: 130,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        // Copy permanent link to clipboard
        function copyPermanentLink() {
            navigator.clipboard.writeText(downloadUrl).then(() => {
                showToast("Download link copied to clipboard!");
            }).catch(() => {
                const input = document.createElement('input');
                input.value = downloadUrl;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                showToast("Download link copied to clipboard!");
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            if (msg) toast.innerText = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2200);
        }
    </script>
</body>
</html>
