<?php
/**
 * Red Taxis App - Public Download & Landing Page
 * -------------------------------------------------------------------------
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
    <title><?= htmlspecialchars($meta['app_name']) ?> - Download Official Android App</title>
    <meta name="description" content="Download the latest <?= htmlspecialchars($meta['app_name']) ?> APK for Android. Fast, reliable, and secure taxi bookings at your fingertips.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- QRCode generator library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        :root {
            --primary: #ef4444;
            --primary-hover: #dc2626;
            --primary-glow: rgba(239, 68, 68, 0.35);
            --bg-dark: #0a0c14;
            --bg-card: #131722;
            --bg-card-hover: #191e2e;
            --border-color: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-green: #10b981;
            --accent-amber: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 15%, rgba(239, 68, 68, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(220, 38, 38, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.5) 0%, transparent 100%);
        }

        .container {
            max-width: 1160px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Top Navigation */
        nav {
            padding: 24px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: inherit;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #ef4444, #991b1b);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px var(--primary-glow);
        }

        .brand-name {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .admin-link:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Hero Section */
        .hero {
            padding: 70px 0 50px;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 48px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
                padding: 40px 0;
                text-align: center;
            }
            .hero-left {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.12);
            color: var(--accent-green);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(16, 185, 129, 0.25);
            margin-bottom: 20px;
        }

        .badge-verified svg {
            width: 16px;
            height: 16px;
        }

        h1 {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 32px;
            max-width: 540px;
        }

        /* Meta Pills Grid */
        .meta-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 36px;
        }

        @media (max-width: 900px) {
            .meta-grid {
                justify-content: center;
            }
        }

        .meta-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            padding: 10px 16px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            backdrop-filter: blur(8px);
        }

        .meta-pill .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .meta-pill .value {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        /* Download Action Buttons */
        .download-actions {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
            max-width: 480px;
        }

        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 18px 32px;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 800;
            box-shadow: 0 10px 25px var(--primary-glow);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.5);
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
        }

        .btn-download:active {
            transform: translateY(0);
        }

        .btn-download svg {
            width: 26px;
            height: 26px;
        }

        .btn-download .btn-badge {
            background: rgba(0, 0, 0, 0.25);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 6px;
        }

        .btn-disabled {
            background: #334155 !important;
            box-shadow: none !important;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Copy Link Bar */
        .copy-link-box {
            display: flex;
            align-items: center;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 6px 6px 6px 14px;
            gap: 10px;
        }

        .copy-link-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-grow: 1;
        }

        .btn-copy {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-color);
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .btn-copy:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Hero Right Card (QR & Device Showcase) */
        .hero-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            backdrop-filter: blur(12px);
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrapper #qrcode img,
        .qr-wrapper #qrcode canvas {
            display: block;
        }

        .qr-caption {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .qr-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Section Titles */
        .section-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title svg {
            color: var(--primary);
        }

        /* Cards Grid */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 60px;
        }

        .step-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 28px 24px;
            position: relative;
            transition: transform 0.2s, border-color 0.2s;
        }

        .step-card:hover {
            transform: translateY(-4px);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .step-num {
            width: 36px;
            height: 36px;
            background: rgba(239, 68, 68, 0.15);
            color: var(--primary);
            border-radius: 10px;
            font-weight: 800;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .step-card h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .step-card p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        /* Changelog Box */
        .changelog-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 28px;
            margin-bottom: 60px;
        }

        .changelog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .changelog-text {
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.7;
            white-space: pre-line;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            padding: 30px 0;
            color: var(--text-muted);
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
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
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 14px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
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
        <!-- Navigation -->
        <nav>
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <path d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                </div>
                <div class="brand-name"><?= htmlspecialchars($meta['app_name']) ?></div>
            </a>
            <div class="nav-actions">
                <a href="admin.php" class="admin-link">
                    <svg style="vertical-align: middle; margin-right: 4px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                    </svg>
                    Admin Upload
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-left">
                <div class="badge-verified">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                    Official & Verified Release
                </div>
                
                <h1>Get the Official <br><span class="gradient-text"><?= htmlspecialchars($meta['app_name']) ?></span> App</h1>
                <p class="hero-desc"><?= htmlspecialchars(APP_TAGLINE) ?>. Instant rides, transparent fares, and 24/7 support.</p>

                <!-- Metadata pills -->
                <div class="meta-grid">
                    <div class="meta-pill">
                        <span class="label">Version</span>
                        <span class="value">v<?= htmlspecialchars($meta['version_name']) ?></span>
                    </div>
                    <div class="meta-pill">
                        <span class="label">File Size</span>
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

                <!-- Download Actions -->
                <div class="download-actions">
                    <?php if ($isAvailable): ?>
                        <a href="download.php" class="btn-download" id="downloadBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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

                    <!-- Permanent Copy Link Bar -->
                    <div class="copy-link-box">
                        <span class="copy-link-text" id="permanentUrl"><?= htmlspecialchars($downloadUrl) ?></span>
                        <button class="btn-copy" onclick="copyPermanentLink()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hero Right Card (QR Code & Quick Mobile Scan) -->
            <div class="hero-card">
                <div class="qr-wrapper">
                    <div id="qrcode"></div>
                </div>
                <div class="qr-caption">Scan with Phone Camera</div>
                <div class="qr-sub">Instantly opens the download link on your mobile device</div>
            </div>
        </section>

        <!-- Installation Guide Section -->
        <h2 class="section-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                <line x1="12" y1="18" x2="12.01" y2="18"></line>
            </svg>
            How to Install on Android
        </h2>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <h3>Download the APK</h3>
                <p>Click the <strong>Download APK</strong> button above or scan the QR code to save the file to your Android phone.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <h3>Allow Unknown Sources</h3>
                <p>If prompted by your browser or Android system, allow <em>"Install unknown apps"</em> for your browser or file manager.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <h3>Tap Install & Launch</h3>
                <p>Open your notification shade or Downloads folder, tap <strong><?= htmlspecialchars(DOWNLOAD_FILENAME) ?></strong> and press Install.</p>
            </div>
        </div>

        <!-- Changelog Section -->
        <div class="changelog-box">
            <div class="changelog-header">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700;">What's New in Version <?= htmlspecialchars($meta['version_name']) ?></h3>
                    <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Released on <?= $releaseDate ?></div>
                </div>
                <span class="badge-verified" style="margin-bottom: 0;">Verified Build</span>
            </div>
            <div class="changelog-text"><?= htmlspecialchars($meta['changelog']) ?></div>
            <?php if (!empty($meta['file_hash_sha256'])): ?>
                <div style="margin-top: 20px; font-size: 12px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 8px; word-break: break-all;">
                    <span style="color: #64748b; font-weight: 600;">SHA-256 Checksum: </span><?= htmlspecialchars($meta['file_hash_sha256']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <footer>
            <div>© <?= date('Y') ?> <?= htmlspecialchars($meta['app_name']) ?>. All rights reserved.</div>
            <div>Permanent Distribution Endpoint • No DB Required</div>
        </footer>
    </div>

    <!-- Copy Toast -->
    <div id="toast">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>Download link copied to clipboard!</span>
    </div>

    <script>
        // Generate QR code for mobile scanning
        const downloadUrl = "<?= $downloadUrl ?>";
        new QRCode(document.getElementById("qrcode"), {
            text: downloadUrl,
            width: 170,
            height: 170,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        // Copy permanent link to clipboard
        function copyPermanentLink() {
            navigator.clipboard.writeText(downloadUrl).then(() => {
                showToast("Permanent download URL copied!");
            }).catch(() => {
                // Fallback
                const input = document.createElement('input');
                input.value = downloadUrl;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                showToast("Permanent download URL copied!");
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            if (msg) toast.querySelector('span').innerText = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }
    </script>
</body>
</html>
