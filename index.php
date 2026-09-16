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

        /* Directive Animation & Guide Tooltip */
        .directive-wrapper {
            position: relative;
            margin-bottom: 6px;
            animation: directiveSlideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes directiveSlideDown {
            0% {
                opacity: 0;
                transform: translateY(-10px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .directive-card {
            background: linear-gradient(135deg, #fff7ed 0%, #fef2f2 100%);
            border: 1.5px solid #fca5a5;
            border-radius: 12px;
            padding: 10px 14px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.12);
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .directive-pointer-icon {
            width: 28px;
            height: 28px;
            background: #dc2626;
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
            animation: pointerBounce 1.5s infinite ease-in-out;
        }

        @keyframes pointerBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(4px); }
        }

        .directive-content {
            flex: 1;
            min-width: 0;
        }

        .directive-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 2px;
        }

        .directive-title {
            font-size: 12px;
            font-weight: 700;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .directive-live-dot {
            width: 7px;
            height: 7px;
            background: #16a34a;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7);
            animation: livePulse 1.8s infinite;
        }

        @keyframes livePulse {
            0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.8); }
            70% { box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
            100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
        }

        .directive-text {
            font-size: 11.5px;
            color: #7f1d1d;
            line-height: 1.35;
        }

        .directive-dismiss-btn {
            background: transparent;
            border: none;
            color: #991b1b;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            padding: 0 2px;
            opacity: 0.7;
            transition: opacity 0.15s ease;
            line-height: 1;
        }

        .directive-dismiss-btn:hover {
            opacity: 1;
        }

        .directive-down-arrow {
            position: absolute;
            bottom: -7px;
            left: 36px;
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-top: 7px solid #fca5a5;
        }

        .directive-down-arrow::after {
            content: '';
            position: absolute;
            bottom: 1.5px;
            left: -6px;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #fef2f2;
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
            position: relative;
            overflow: hidden;
        }

        /* Pulsing Ring Animation */
        .btn-download.directive-pulse {
            animation: btnDownloadPulse 2.2s infinite cubic-bezier(0.25, 0, 0, 1);
        }

        @keyframes btnDownloadPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.65), 0 3px 8px rgba(220, 38, 38, 0.35);
            }
            60% {
                box-shadow: 0 0 0 14px rgba(220, 38, 38, 0), 0 4px 12px rgba(220, 38, 38, 0.45);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0), 0 2px 5px rgba(220, 38, 38, 0.28);
            }
        }

        /* Shimmering Highlight Beam */
        .btn-download.directive-pulse::before {
            content: '';
            position: absolute;
            top: 0;
            left: -120%;
            width: 80%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: skewX(-20deg);
            animation: shimmerSweep 3s infinite ease-in-out;
        }

        @keyframes shimmerSweep {
            0%, 20% { left: -120%; }
            60%, 100% { left: 200%; }
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

        /* Guide Section Component */
        .guide-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: clamp(16px, 3.5vw, 24px);
            box-shadow: var(--shadow-sm);
            margin-bottom: clamp(20px, 4vw, 28px);
            overflow: hidden;
        }

        .guide-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .guide-topbar-title {
            font-size: clamp(15px, 3vw, 17px);
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guide-topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-presentation {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            background: var(--primary-light);
            border: 1px solid var(--primary-border);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-presentation:hover {
            background: var(--primary);
            color: #ffffff;
        }

        .guide-tabs-nav {
            display: flex;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: var(--radius-md);
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            margin-bottom: 18px;
        }

        .guide-tabs-nav::-webkit-scrollbar {
            display: none;
        }

        .guide-tab-btn {
            background: transparent;
            border: none;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            touch-action: manipulation;
        }

        .guide-tab-btn:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.6);
        }

        .guide-tab-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }

        .guide-tab-btn .tab-num {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .guide-tab-btn.active .tab-num {
            background: var(--primary);
            color: #ffffff;
        }

        .guide-panel {
            display: none;
            animation: fadeIn 0.25s ease forwards;
        }

        .guide-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .guide-callout {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 16px;
        }

        .guide-badge-setup {
            background: #fee2e2;
            color: #b91c1c;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .guide-badge-daily {
            background: #dcfce7;
            color: #15803d;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .guide-badge-switch {
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .fields-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .field-card {
            padding: 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: #ffffff;
        }

        .field-card h5 {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .field-card p {
            font-size: 11.5px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .field-card code {
            font-family: 'Roboto Mono', monospace;
            background: #f1f5f9;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 11px;
            color: var(--primary);
        }

        .guide-split {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .guide-split {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        .phone-mock {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 16px;
            box-shadow: var(--shadow-sm);
        }

        .mock-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .mock-brand-badge {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mock-status-pill {
            font-size: 10px;
            font-weight: 600;
            color: #059669;
            background: #ecfdf5;
            padding: 2px 8px;
            border-radius: 12px;
            border: 1px solid #a7f3d0;
        }

        .mock-field-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mock-input {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 11.5px;
            color: #334155;
            font-family: inherit;
        }

        .mock-input-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-sub);
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .guide-footer-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-guide-nav {
            background: #ffffff;
            border: 1px solid var(--border-color);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-guide-nav:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-guide-nav.primary {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        .btn-guide-nav.primary:hover {
            background: var(--primary-hover);
        }

        .btn-guide-nav:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .guide-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .guide-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .guide-dot.active {
            background: var(--primary);
            width: 20px;
            border-radius: 4px;
        }

        /* Summary Table & FAQ */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 8px;
        }

        .summary-table th, .summary-table td {
            padding: 9px 12px;
            border: 1px solid var(--border-color);
            text-align: left;
        }

        .summary-table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #1e293b;
        }

        .summary-table td {
            background: #ffffff;
            color: #334155;
        }

        .faq-item {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 8px;
        }

        .faq-item strong {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #0f172a;
            font-size: 12.5px;
            margin-bottom: 4px;
        }

        .faq-item p {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.4;
            padding-left: 20px;
        }

        /* Top Nav Guide Link */
        .nav-links-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guide-nav-btn {
            color: var(--text-main);
            text-decoration: none;
            font-size: clamp(12px, 2.5vw, 13px);
            font-weight: 600;
            padding: 6px clamp(8px, 2vw, 12px);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: #f8fafc;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .guide-nav-btn:hover {
            color: var(--primary);
            border-color: var(--primary-border);
            background: var(--primary-light);
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
            <div class="nav-links-group">
                <a href="#guide-section" class="guide-nav-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>How to Use</span>
                </a>
                <a href="admin.php" class="admin-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                    </svg>
                    <span>Admin Portal</span>
                </a>
            </div>
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
                            <!-- Directive Animated Guide Callout -->
                            <div class="directive-wrapper" id="downloadDirective">
                                <div class="directive-card">
                                    <div class="directive-pointer-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <polyline points="19 12 12 19 5 12"></polyline>
                                        </svg>
                                    </div>
                                    <div class="directive-content">
                                        <div class="directive-header">
                                            <span class="directive-title">
                                                <span class="directive-live-dot"></span>
                                                Driver App Ready for Download
                                            </span>
                                            <button class="directive-dismiss-btn" onclick="dismissDirective(event)" title="Dismiss hint">✕</button>
                                        </div>
                                        <div class="directive-text">
                                            Click <strong>Download APK</strong> below to get the official app, then follow the <strong>Setup Guide</strong> to connect your fleet.
                                        </div>
                                    </div>
                                    <div class="directive-down-arrow"></div>
                                </div>
                            </div>

                            <a href="download.php" class="btn-download directive-pulse" id="downloadBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>Download</span>
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

            <!-- ================= HOW TO USE & FLEET SETUP GUIDE ================= -->
            <div class="section-header" id="guide-section">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
                App Usage & Fleet Setup Guide
            </div>

            <section class="guide-box">
                <div class="guide-topbar">
                    <div class="guide-topbar-title">
                        <span>Driver & Fleet Walkthrough</span>
                        <span class="guide-badge-setup">5 Easy Steps</span>
                    </div>
                    <div class="guide-topbar-actions">
                        <a href="fleet-setup-guide.php" target="_blank" class="btn-presentation" title="Open Fullscreen Slide Deck Presentation">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                            </svg>
                            <span>Slide Mode</span>
                        </a>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="guide-tabs-nav" id="guideTabsNav">
                    <button class="guide-tab-btn active" onclick="switchGuideTab(0)">
                        <span class="tab-num">1</span>
                        <span>Overview & Fields</span>
                    </button>
                    <button class="guide-tab-btn" onclick="switchGuideTab(1)">
                        <span class="tab-num">2</span>
                        <span>First-Time Setup</span>
                    </button>
                    <button class="guide-tab-btn" onclick="switchGuideTab(2)">
                        <span class="tab-num">3</span>
                        <span>Everyday Shift Login</span>
                    </button>
                    <button class="guide-tab-btn" onclick="switchGuideTab(3)">
                        <span class="tab-num">4</span>
                        <span>Change Fleet</span>
                    </button>
                    <button class="guide-tab-btn" onclick="switchGuideTab(4)">
                        <span class="tab-num">5</span>
                        <span>Summary & FAQs</span>
                    </button>
                </div>

                <!-- ================= TAB 1: OVERVIEW & FIELD COMPARISON ================= -->
                <div class="guide-panel active" id="guidePanel0">
                    <div class="guide-callout">
                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                            First-Time Setup vs. Everyday Shift Login
                        </h3>
                        <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 12px;">
                            Understanding which fields are configured once upon installation versus what is entered every morning.
                        </p>

                        <!-- Image Preview Graphic -->
                        <div style="text-align: center; margin-bottom: 14px; background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 10px; overflow: hidden;">
                            <img src="images/fields_comparison.jpg" alt="Field Comparison Graphic" style="max-height: 220px; width: auto; max-width: 100%; object-contain: contain; border-radius: 8px; display: inline-block;">
                        </div>

                        <div class="fields-grid">
                            <div class="field-card" style="border-left: 3px solid #dc2626;">
                                <span class="guide-badge-setup">Once Only</span>
                                <h5>1. Tenant / Company ID</h5>
                                <p>Identifies your taxi fleet (e.g. <code>org_first_taxis</code>) so the app loads the correct branding.</p>
                            </div>
                            <div class="field-card" style="border-left: 3px solid #dc2626;">
                                <span class="guide-badge-setup">Once Only</span>
                                <h5>2. Tenant / Fleet Key</h5>
                                <p>Office secret key (or auto-scanned via QR code) that securely unlocks your fleet system.</p>
                            </div>
                            <div class="field-card" style="border-left: 3px solid #059669;">
                                <span class="guide-badge-daily">Everyday Shift</span>
                                <h5>3. Driver Username</h5>
                                <p>Your personal driver username, badge number, or official email address.</p>
                            </div>
                            <div class="field-card" style="border-left: 3px solid #059669;">
                                <span class="guide-badge-daily">Everyday Shift</span>
                                <h5>4. Driver Password</h5>
                                <p>Your individual driver account password to authenticate and start shifts.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: FIRST-TIME SETUP ================= -->
                <div class="guide-panel" id="guidePanel1">
                    <div class="guide-split">
                        <div>
                            <span class="guide-badge-setup">Step 1 • Once Upon Installation</span>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                                Connecting to Your Taxi Fleet
                            </h3>
                            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.5;">
                                When you launch the application for the first time, you have two quick connection choices:
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px;">
                                    <strong style="color: #0f172a; font-size: 12.5px; display: block;">Option A: Manual Entry</strong>
                                    <span style="font-size: 11.5px; color: var(--text-muted);">Type your Company ID & Fleet Key provided by your office manager.</span>
                                </div>
                                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 12px;">
                                    <strong style="color: #065f46; font-size: 12.5px; display: flex; align-items: center; gap: 5px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                            <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                            <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                            <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                            <path d="M21 21v.01"></path>
                                        </svg>
                                        Option B (Fastest): 1-Tap QR Scan
                                    </strong>
                                    <span style="font-size: 11.5px; color: #047857;">Tap <strong>"Scan Office QR Code"</strong> and point your camera at the office paper. The app auto-detects and auto-fills all fields instantly!</span>
                                </div>
                            </div>
                            <p style="margin-top: 10px; font-size: 11.5px; color: var(--text-sub); display: flex; align-items: center; gap: 5px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Saved permanently in phone memory — no repeated entry required.
                            </p>
                        </div>

                        <!-- Right Showcase with Real Screenshots -->
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap;">
                            <div style="text-align: center;">
                                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 4px; box-shadow: var(--shadow-sm);">
                                    <img src="images/step1_fleet_setup.jpg" alt="Step 1 Fleet Setup" style="height: 180px; width: auto; border-radius: 8px; object-fit: contain;">
                                </div>
                                <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); display: block; margin-top: 4px;">1. Tap Scan QR</span>
                            </div>
                            <div style="text-align: center;">
                                <div style="background: #ecfdf5; border: 1.5px solid #6ee7b7; border-radius: 12px; padding: 4px; box-shadow: var(--shadow-sm);">
                                    <img src="images/step1_qr_autofill.jpg" alt="Step 1 QR Scanner" style="height: 180px; width: auto; border-radius: 8px; object-fit: contain;">
                                </div>
                                <span style="font-size: 10px; font-weight: 700; color: #065f46; display: block; margin-top: 4px;">2. Auto-Fills Instantly</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 3: EVERYDAY SHIFT LOGIN ================= -->
                <div class="guide-panel" id="guidePanel2">
                    <div class="guide-split">
                        <div>
                            <span class="guide-badge-daily">Step 2 • Daily Shift Work</span>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                                Daily Shifts & Post-Logout Screen
                            </h3>
                            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.5;">
                                Once connected, you never have to re-enter Company ID or Key. The app displays your company's full branding automatically.
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="faq-item" style="margin-bottom: 0;">
                                    <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Only 2 Input Fields</strong>
                                    <p>Enter your <strong>Driver Username</strong> and <strong>Password</strong> to start accepting rides.</p>
                                </div>
                                <div class="faq-item" style="margin-bottom: 0;">
                                    <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Always Branded</strong>
                                    <p>Custom colors and logo remain active so you know you are connected to the official fleet portal.</p>
                                </div>
                                <div class="faq-item" style="margin-bottom: 0;">
                                    <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Seamless Shifts</strong>
                                    <p>Logging out at end-of-shift safely retains your fleet configuration for the next morning.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Mockup with Real Screenshot -->
                        <div style="display: flex; justify-content: center;">
                            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 14px; padding: 6px; box-shadow: var(--shadow-sm); text-align: center;">
                                <img src="images/step2_everyday_login.jpg" alt="Everyday Login Screen" style="height: 220px; width: auto; border-radius: 10px; object-fit: contain;">
                                <span style="font-size: 10.5px; font-weight: 700; color: #059669; display: block; margin-top: 4px;">● Daily 2-Field Login</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 4: CHANGE FLEET IN SETTINGS ================= -->
                <div class="guide-panel" id="guidePanel3">
                    <div class="guide-split">
                        <div>
                            <span class="guide-badge-switch">Step 3 • Fleet Switching</span>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                                Changing Companies from Settings
                            </h3>
                            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.5;">
                                If you drive for multiple taxi fleets or switch companies, there is no need to reinstall or delete the app.
                            </p>

                            <ol style="padding-left: 18px; font-size: 12px; color: #334155; display: flex; flex-direction: column; gap: 8px;">
                                <li><strong>Open Settings Menu:</strong> Navigate to the <strong>Fleet & Company</strong> section.</li>
                                <li><strong>View Status:</strong> Confirm your active fleet with the green <em>Verified & Active</em> badge.</li>
                                <li><strong>Tap "Change Taxi Company":</strong> Enter the new Company ID & Key or scan the new fleet QR code.</li>
                            </ol>
                        </div>

                        <!-- Right Mockup with Real Screenshot -->
                        <div style="display: flex; justify-content: center;">
                            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 14px; padding: 6px; box-shadow: var(--shadow-sm); text-align: center;">
                                <img src="images/step3_settings_fleet.jpg" alt="Settings Fleet Screen" style="height: 220px; width: auto; border-radius: 10px; object-fit: contain;">
                                <span style="font-size: 10.5px; font-weight: 700; color: #2563eb; display: block; margin-top: 4px;">● Fleet Settings & Switch</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 5: SUMMARY CHEAT SHEET & FAQS ================= -->
                <div class="guide-panel" id="guidePanel4">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        <!-- Summary Table -->
                        <div>
                            <h4 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                                Quick Reference Cheat Sheet
                            </h4>
                            <table class="summary-table">
                                <thead>
                                    <tr>
                                        <th>App State</th>
                                        <th>Fields Required</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>First Launch</strong></td>
                                        <td style="color: #dc2626; font-weight: 600;">Company ID & Key (Once Only)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Daily Shifts</strong></td>
                                        <td style="color: #059669; font-weight: 600;">Driver Username & Password</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Post-Logout</strong></td>
                                        <td style="color: #059669; font-weight: 600;">Driver Username & Password</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Switch Company</strong></td>
                                        <td style="color: #2563eb; font-weight: 600;">Settings ➔ Change Company</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- FAQ -->
                        <div>
                            <h4 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                                Common Driver Questions
                            </h4>
                            <div class="faq-item">
                                <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> Do I enter Company ID every day?</strong>
                                <p>No! Only once during setup. Daily shifts only require your username & password.</p>
                            </div>
                            <div class="faq-item">
                                <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> What if I typed the wrong Company ID?</strong>
                                <p>Tap the <strong>[Change]</strong> button on the login screen to update it anytime.</p>
                            </div>
                            <div class="faq-item">
                                <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> Can our fleet manager print a QR code?</strong>
                                <p>Yes! Scanning the office QR code connects any driver in 2 seconds.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Navigation Controls -->
                <div class="guide-footer-controls">
                    <button class="btn-guide-nav" id="guidePrevBtn" onclick="prevGuideStep()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        <span>Previous</span>
                    </button>

                    <div class="guide-dots">
                        <span class="guide-dot active" onclick="switchGuideTab(0)"></span>
                        <span class="guide-dot" onclick="switchGuideTab(1)"></span>
                        <span class="guide-dot" onclick="switchGuideTab(2)"></span>
                        <span class="guide-dot" onclick="switchGuideTab(3)"></span>
                        <span class="guide-dot" onclick="switchGuideTab(4)"></span>
                    </div>

                    <button class="btn-guide-nav primary" id="guideNextBtn" onclick="nextGuideStep()">
                        <span>Next</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            </section>

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

        // Guide Tabs & Step Navigation Controller
        let currentGuideTab = 0;
        const totalGuideTabs = 5;

        function switchGuideTab(index) {
            if (index < 0 || index >= totalGuideTabs) return;
            currentGuideTab = index;

            // Panels
            for (let i = 0; i < totalGuideTabs; i++) {
                const panel = document.getElementById(`guidePanel${i}`);
                if (panel) {
                    if (i === currentGuideTab) {
                        panel.classList.add('active');
                    } else {
                        panel.classList.remove('active');
                    }
                }
            }

            // Tab Buttons
            const tabButtons = document.querySelectorAll('.guide-tab-btn');
            tabButtons.forEach((btn, idx) => {
                if (idx === currentGuideTab) {
                    btn.classList.add('active');
                    btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    btn.classList.remove('active');
                }
            });

            // Dots
            const dots = document.querySelectorAll('.guide-dot');
            dots.forEach((dot, idx) => {
                if (idx === currentGuideTab) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });

            // Prev / Next button state
            const prevBtn = document.getElementById('guidePrevBtn');
            const nextBtn = document.getElementById('guideNextBtn');
            if (prevBtn) prevBtn.disabled = (currentGuideTab === 0);
            if (nextBtn) {
                if (currentGuideTab === totalGuideTabs - 1) {
                    nextBtn.innerHTML = `<span>Restart Guide ↺</span>`;
                } else {
                    nextBtn.innerHTML = `<span>Next</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>`;
                }
            }
        }

        function nextGuideStep() {
            if (currentGuideTab < totalGuideTabs - 1) {
                switchGuideTab(currentGuideTab + 1);
            } else {
                switchGuideTab(0);
            }
        }

        function prevGuideStep() {
            if (currentGuideTab > 0) {
                switchGuideTab(currentGuideTab - 1);
            }
        }

        function dismissDirective(e) {
            if (e) e.stopPropagation();
            const el = document.getElementById('downloadDirective');
            if (el) {
                el.style.transition = 'all 0.25s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-8px) scale(0.95)';
                setTimeout(() => {
                    el.style.display = 'none';
                }, 250);
            }
        }

        // Initialize Guide Controls
        switchGuideTab(0);
    </script>
</body>
</html>
