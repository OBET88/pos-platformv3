<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Platform Admin' }}</title>
    @php($hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @if ($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root {
                color-scheme: dark;
                --platform-bg: #020617;
                --platform-bg-soft: #0f172a;
                --platform-shell: #f8fafc;
                --platform-panel: rgba(15, 23, 42, 0.82);
                --platform-panel-soft: rgba(255, 255, 255, 0.86);
                --platform-line: rgba(148, 163, 184, 0.2);
                --platform-line-soft: #e2e8f0;
                --platform-ink: #0f172a;
                --platform-text: #e2e8f0;
                --platform-muted: #64748b;
                --platform-muted-dark: #94a3b8;
                --platform-accent: #34d399;
                --platform-accent-strong: #10b981;
                --platform-danger: #f43f5e;
                --platform-warning: #f59e0b;
                --platform-info: #0ea5e9;
            }

            * {
                box-sizing: border-box;
            }

            [x-cloak] {
                display: none !important;
            }

            html,
            body {
                margin: 0;
                min-height: 100%;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.12), transparent 24%),
                    radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.1), transparent 22%),
                    linear-gradient(180deg, #020617 0%, #0f172a 18%, #e2e8f0 18%, #f8fafc 100%);
                color: var(--platform-ink);
                line-height: 1.5;
            }

            body::before {
                content: "";
                position: fixed;
                inset: 0;
                pointer-events: none;
                opacity: 0.16;
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.28) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.22) 1px, transparent 1px);
                background-size: 72px 72px;
                mask-image: linear-gradient(180deg, rgba(255, 255, 255, 0.88), transparent 85%);
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            button,
            input,
            select,
            textarea {
                font: inherit;
            }

            button,
            a,
            input,
            select,
            textarea,
            summary {
                transition:
                    background-color 160ms ease,
                    border-color 160ms ease,
                    color 160ms ease,
                    box-shadow 160ms ease,
                    transform 160ms ease,
                    opacity 160ms ease;
            }

            button:focus-visible,
            a:focus-visible,
            input:focus-visible,
            select:focus-visible,
            textarea:focus-visible,
            summary:focus-visible {
                outline: none;
                box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.16);
            }

            .hidden {
                display: none !important;
            }

            .animate-spin {
                animation: platform-spin 0.9s linear infinite;
            }

            @keyframes platform-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .platform-shell {
                min-height: 100vh;
            }

            .platform-sidebar {
                background: linear-gradient(180deg, rgba(2, 6, 23, 0.98) 0%, rgba(15, 23, 42, 0.94) 100%);
                color: var(--platform-text);
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                padding: 20px;
            }

            .platform-panel {
                border: 1px solid rgba(255, 255, 255, 0.08);
                background: var(--platform-panel);
                box-shadow: 0 26px 90px rgba(2, 6, 23, 0.22);
                backdrop-filter: blur(18px);
            }

            .platform-nav-link {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border-radius: 16px;
                border: 1px solid rgba(255, 255, 255, 0.05);
                padding: 15px 16px;
                font-size: 14px;
                font-weight: 600;
                color: #cbd5e1;
                background: rgba(15, 23, 42, 0.42);
            }

            .platform-nav-link:hover,
            .platform-admin-button:hover,
            .public-ui-link:hover,
            .public-ui-btn:hover,
            .public-ui-pill:hover {
                transform: translateY(-1px);
            }

            .platform-nav-link:hover {
                color: #f8fafc;
                background: rgba(30, 41, 59, 0.82);
            }

            .platform-nav-link.is-active {
                color: #022c22;
                border-color: rgba(52, 211, 153, 0.18);
                background: linear-gradient(135deg, #6ee7b7, #34d399);
                box-shadow: 0 18px 40px rgba(16, 185, 129, 0.22);
            }

            .platform-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 24px;
                border-radius: 999px;
                padding: 4px 8px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .platform-admin-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                border-radius: 16px;
                padding: 14px 16px;
                font-size: 14px;
                font-weight: 700;
                line-height: 1;
                border: 0;
                cursor: pointer;
            }

            .platform-admin-button.is-primary {
                color: #022c22;
                background: linear-gradient(135deg, #6ee7b7, #34d399);
                box-shadow: 0 18px 40px rgba(16, 185, 129, 0.2);
            }

            .platform-admin-button.is-secondary {
                color: #0f172a;
                border: 1px solid rgba(148, 163, 184, 0.24);
                background: rgba(255, 255, 255, 0.86);
            }

            .platform-page-header {
                /* Sticky hanya di desktop (lihat @media min-width:1024px di bawah). */
                position: static;
                margin-bottom: 24px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                background: rgba(15, 23, 42, 0.76);
                color: #e2e8f0;
                backdrop-filter: blur(18px);
                border-radius: 24px;
                padding: 20px 24px;
                box-shadow: 0 18px 48px rgba(2, 6, 23, 0.18);
            }

            @media (min-width: 1024px) {
                .platform-page-header {
                    position: sticky;
                    top: 0;
                    z-index: 20;
                }
            }

            [data-ui-submit][disabled],
            button[disabled],
            input[disabled],
            button[aria-busy="true"],
            input[aria-busy="true"] {
                cursor: wait;
                transform: none;
                opacity: 0.72;
                box-shadow: none;
            }

            button.is-loading,
            input.is-loading {
                pointer-events: none;
            }

            main {
                padding: 16px;
            }

            .platform-admin-fallback {
                color: var(--platform-ink);
            }

            .platform-admin-fallback [class*="space-y-"] > * + * {
                margin-top: 24px;
            }

            .platform-admin-fallback [class*="space-y-2"] > * + * {
                margin-top: 8px;
            }

            .platform-admin-fallback [class*="space-y-3"] > * + * {
                margin-top: 12px;
            }

            .platform-admin-fallback [class*="space-y-4"] > * + * {
                margin-top: 16px;
            }

            .platform-admin-fallback [class*="space-y-5"] > * + * {
                margin-top: 20px;
            }

            .platform-admin-fallback [class*="space-y-6"] > * + * {
                margin-top: 24px;
            }

            .platform-admin-fallback .grid {
                display: grid;
                gap: 16px;
            }

            .platform-admin-fallback .flex {
                display: flex;
            }

            .platform-admin-fallback .inline-flex {
                display: inline-flex;
            }

            .platform-admin-fallback .items-center {
                align-items: center;
            }

            .platform-admin-fallback .items-start {
                align-items: flex-start;
            }

            .platform-admin-fallback .justify-between {
                justify-content: space-between;
            }

            .platform-admin-fallback .justify-center {
                justify-content: center;
            }

            .platform-admin-fallback .flex-col {
                flex-direction: column;
            }

            .platform-admin-fallback .flex-wrap {
                flex-wrap: wrap;
            }

            .platform-admin-fallback .gap-2 { gap: 8px; }
            .platform-admin-fallback .gap-3 { gap: 12px; }
            .platform-admin-fallback .gap-4 { gap: 16px; }
            .platform-admin-fallback .gap-5 { gap: 20px; }
            .platform-admin-fallback .gap-6 { gap: 24px; }

            .platform-admin-fallback .items-end {
                align-items: flex-end;
            }

            .platform-admin-fallback .justify-end {
                justify-content: flex-end;
            }

            .platform-admin-fallback .text-left {
                text-align: left;
            }

            .platform-admin-fallback .text-center {
                text-align: center;
            }

            .platform-admin-fallback .text-right {
                text-align: right;
            }

            .platform-admin-fallback .min-w-0 {
                min-width: 0;
            }

            .platform-admin-fallback [class*="rounded-"] {
                border-radius: 24px;
            }

            .platform-admin-fallback .rounded-full {
                border-radius: 999px;
            }

            .platform-admin-fallback .border,
            .platform-admin-fallback [class*="ring-1"] {
                border: 1px solid var(--platform-line-soft);
            }

            .platform-admin-fallback .border-b {
                border-bottom: 1px solid var(--platform-line-soft);
            }

            .platform-admin-fallback .border-t {
                border-top: 1px solid var(--platform-line-soft);
            }

            .platform-admin-fallback .border-l {
                border-left: 1px solid var(--platform-line-soft);
            }

            .platform-admin-fallback .border-r {
                border-right: 1px solid var(--platform-line-soft);
            }

            .platform-admin-fallback .border-dashed {
                border-style: dashed;
            }

            .platform-admin-fallback .bg-white,
            .platform-admin-fallback .bg-white\/80,
            .platform-admin-fallback .bg-slate-50,
            .platform-admin-fallback .bg-slate-50\/70 {
                background: rgba(255, 255, 255, 0.88);
            }

            .platform-admin-fallback .bg-slate-900,
            .platform-admin-fallback .bg-slate-950 {
                background: #0f172a;
                color: #f8fafc;
            }

            .platform-admin-fallback .bg-emerald-50,
            .platform-admin-fallback .bg-emerald-50\/70 { background: #ecfdf5; }
            .platform-admin-fallback .bg-emerald-100 { background: #d1fae5; }
            .platform-admin-fallback .bg-amber-50,
            .platform-admin-fallback .bg-amber-50\/70 { background: #fffbeb; }
            .platform-admin-fallback .bg-amber-100 { background: #fef3c7; }
            .platform-admin-fallback .bg-rose-50,
            .platform-admin-fallback .bg-rose-50\/70 { background: #fff1f2; }
            .platform-admin-fallback .bg-rose-100 { background: #ffe4e6; }
            .platform-admin-fallback .bg-sky-50,
            .platform-admin-fallback .bg-sky-50\/70 { background: #f0f9ff; }
            .platform-admin-fallback .bg-sky-100 { background: #e0f2fe; }
            .platform-admin-fallback .bg-slate-100 { background: #f1f5f9; }
            .platform-admin-fallback .bg-slate-200 { background: #e2e8f0; }
            .platform-admin-fallback .bg-blue-600 { background: #2563eb; color: #ffffff; }
            .platform-admin-fallback .bg-violet-100 { background: #ede9fe; }
            .platform-admin-fallback .bg-cyan-100 { background: #cffafe; }
            .platform-admin-fallback .bg-indigo-100 { background: #e0e7ff; }

            .platform-admin-fallback .from-sky-50,
            .platform-admin-fallback .via-white,
            .platform-admin-fallback .to-emerald-50 {
                background: linear-gradient(90deg, #f0f9ff 0%, #ffffff 48%, #ecfdf5 100%);
            }

            .platform-admin-fallback [class*="p-4"] { padding: 16px; }
            .platform-admin-fallback [class*="p-5"] { padding: 20px; }
            .platform-admin-fallback [class*="p-6"] { padding: 24px; }
            .platform-admin-fallback [class*="px-4"] { padding-left: 16px; padding-right: 16px; }
            .platform-admin-fallback [class*="px-5"] { padding-left: 20px; padding-right: 20px; }
            .platform-admin-fallback [class*="px-6"] { padding-left: 24px; padding-right: 24px; }
            .platform-admin-fallback [class*="py-3"] { padding-top: 12px; padding-bottom: 12px; }
            .platform-admin-fallback [class*="py-4"] { padding-top: 16px; padding-bottom: 16px; }
            .platform-admin-fallback [class*="py-5"] { padding-top: 20px; padding-bottom: 20px; }
            .platform-admin-fallback [class*="py-6"] { padding-top: 24px; padding-bottom: 24px; }
            .platform-admin-fallback [class*="px-3"] { padding-left: 12px; padding-right: 12px; }
            .platform-admin-fallback [class*="py-1"] { padding-top: 4px; padding-bottom: 4px; }

            .platform-admin-fallback h1,
            .platform-admin-fallback h2,
            .platform-admin-fallback h3,
            .platform-admin-fallback p {
                margin-top: 0;
            }

            .platform-admin-fallback h1 {
                margin-bottom: 8px;
                font-size: 42px;
                line-height: 1.1;
            }

            .platform-admin-fallback h2 {
                margin-bottom: 8px;
                font-size: 24px;
                line-height: 1.2;
            }

            .platform-admin-fallback h3 {
                margin-bottom: 6px;
                font-size: 16px;
                line-height: 1.3;
            }

            .platform-admin-fallback .text-white { color: #ffffff; }
            .platform-admin-fallback .text-slate-900 { color: #0f172a; }
            .platform-admin-fallback .text-slate-800 { color: #172033; }
            .platform-admin-fallback .text-slate-700 { color: #243247; }
            .platform-admin-fallback .text-slate-600 { color: #31465f; }
            .platform-admin-fallback .text-slate-500 { color: #415873; }
            .platform-admin-fallback .text-slate-400 { color: #6a819c; }
            .platform-admin-fallback .text-slate-300 { color: #cbd5e1; }
            .platform-admin-fallback .text-emerald-700 { color: #047857; }
            .platform-admin-fallback .text-emerald-600,
            .platform-admin-fallback .text-emerald-500 { color: #059669; }
            .platform-admin-fallback .text-amber-700 { color: #b45309; }
            .platform-admin-fallback .text-amber-600,
            .platform-admin-fallback .text-amber-500 { color: #d97706; }
            .platform-admin-fallback .text-rose-700 { color: #be123c; }
            .platform-admin-fallback .text-rose-600,
            .platform-admin-fallback .text-rose-500 { color: #e11d48; }
            .platform-admin-fallback .text-sky-800 { color: #075985; }
            .platform-admin-fallback .text-sky-700 { color: #0369a1; }
            .platform-admin-fallback .text-sky-600,
            .platform-admin-fallback .text-sky-500 { color: #0284c7; }
            .platform-admin-fallback .text-violet-700 { color: #6d28d9; }
            .platform-admin-fallback .text-cyan-700 { color: #0e7490; }
            .platform-admin-fallback .text-indigo-700 { color: #4338ca; }

            .platform-admin-fallback .text-xs { font-size: 12px; }
            .platform-admin-fallback .text-sm { font-size: 14px; }
            .platform-admin-fallback .text-xl { font-size: 20px; }
            .platform-admin-fallback .text-2xl { font-size: 28px; }
            .platform-admin-fallback .text-3xl { font-size: 40px; }
            .platform-admin-fallback .text-base { font-size: 16px; }
            .platform-admin-fallback [class*="text-\\[11px\\]"] { font-size: 11px; }
            .platform-admin-fallback .tracking-tight { letter-spacing: -0.02em; }
            .platform-admin-fallback .tracking-\[0\.22em\] { letter-spacing: 0.22em; }
            .platform-admin-fallback .tracking-\[0\.24em\] { letter-spacing: 0.24em; }
            .platform-admin-fallback .tracking-\[0\.26em\] { letter-spacing: 0.26em; }
            .platform-admin-fallback .tracking-\[0\.28em\] { letter-spacing: 0.28em; }
            .platform-admin-fallback .leading-tight { line-height: 1.15; }
            .platform-admin-fallback .font-semibold { font-weight: 700; }
            .platform-admin-fallback .font-medium { font-weight: 600; }
            .platform-admin-fallback .font-bold { font-weight: 700; }
            .platform-admin-fallback .uppercase { text-transform: uppercase; }
            .platform-admin-fallback .leading-5 { line-height: 1.4; }
            .platform-admin-fallback .leading-6 { line-height: 1.6; }

            .platform-admin-fallback input[type="text"],
            .platform-admin-fallback input[type="email"],
            .platform-admin-fallback input[type="password"],
            .platform-admin-fallback input[type="number"],
            .platform-admin-fallback input[type="date"],
            .platform-admin-fallback select,
            .platform-admin-fallback textarea {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 14px;
                background: #ffffff;
                color: #0f172a;
                padding: 12px 14px;
                outline: none;
            }

            .platform-admin-fallback input::placeholder,
            .platform-admin-fallback textarea::placeholder {
                color: #94a3b8;
            }

            .platform-admin-fallback input:focus,
            .platform-admin-fallback select:focus,
            .platform-admin-fallback textarea:focus {
                border-color: var(--platform-accent);
                box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
            }

            .platform-admin-fallback button:not(.platform-admin-button),
            .platform-admin-fallback a.rounded-xl,
            .platform-admin-fallback a.rounded-full {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border-radius: 14px;
                padding: 12px 14px;
                font-size: 14px;
                font-weight: 700;
                border: 1px solid transparent;
            }

            .platform-admin-fallback button:hover,
            .platform-admin-fallback a.rounded-xl:hover,
            .platform-admin-fallback a.rounded-full:hover {
                transform: translateY(-1px);
            }

            .platform-admin-fallback .overflow-x-auto {
                overflow-x: auto;
            }

            .platform-admin-fallback pre {
                margin: 0;
                white-space: pre-wrap;
                word-break: break-word;
            }

            .platform-admin-fallback .w-full { width: 100%; }
            .platform-admin-fallback .h-full { height: 100%; }
            .platform-admin-fallback .max-w-2xl { max-width: 672px; }
            .platform-admin-fallback .max-w-md { max-width: 768px; }
            .platform-admin-fallback .max-w-4xl { max-width: 1100px; }
            .platform-admin-fallback .max-w-3xl { max-width: 960px; }
            .platform-admin-fallback .mx-auto { margin-left: auto; margin-right: auto; }
            .platform-admin-fallback .mt-1 { margin-top: 4px; }
            .platform-admin-fallback .mt-2 { margin-top: 8px; }
            .platform-admin-fallback .mt-3 { margin-top: 12px; }
            .platform-admin-fallback .mt-5 { margin-top: 20px; }
            .platform-admin-fallback .mt-6 { margin-top: 24px; }
            .platform-admin-fallback .break-all { word-break: break-all; }
            .platform-admin-fallback .mb-6 { margin-bottom: 24px; }

            .platform-admin-fallback .shadow-sm,
            .platform-admin-fallback [class*="shadow-"] {
                box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            }

            .platform-admin-fallback [class*="border-white\\/10"] {
                border-color: rgba(255, 255, 255, 0.1);
            }

            .platform-admin-fallback [class*="border-emerald-400\\/15"] {
                border-color: rgba(52, 211, 153, 0.15);
            }

            .platform-admin-fallback [class*="bg-emerald-400\\/10"] {
                background: rgba(52, 211, 153, 0.1);
            }

            .platform-admin-fallback [class*="bg-white\\/10"] {
                background: rgba(255, 255, 255, 0.1);
            }

            .platform-admin-fallback [class*="bg-white\\/5"] {
                background: rgba(255, 255, 255, 0.05);
            }

            .platform-admin-fallback [class*="bg-slate-950\\/35"] {
                background: rgba(2, 6, 23, 0.35);
            }

            .platform-admin-fallback [class*="text-emerald-200"] {
                color: #d1fae5;
            }

            .platform-admin-fallback [class*="text-emerald-300"] {
                color: #86efac;
            }

            .platform-admin-fallback [class*="text-slate-950"] {
                color: #020617;
            }

            .platform-admin-fallback .bg-white .text-slate-500,
            .platform-admin-fallback .bg-white\/80 .text-slate-500,
            .platform-admin-fallback .bg-slate-50 .text-slate-500,
            .platform-admin-fallback .bg-slate-50\/70 .text-slate-500,
            .platform-admin-fallback .bg-white .text-slate-400,
            .platform-admin-fallback .bg-white\/80 .text-slate-400,
            .platform-admin-fallback .bg-slate-50 .text-slate-400,
            .platform-admin-fallback .bg-slate-50\/70 .text-slate-400 {
                color: #4b6078;
            }

            .platform-admin-fallback .bg-white .text-xs,
            .platform-admin-fallback .bg-white\/80 .text-xs,
            .platform-admin-fallback .bg-slate-50 .text-xs,
            .platform-admin-fallback .bg-slate-50\/70 .text-xs {
                color: #556b84;
            }

            .platform-admin-fallback .platform-sidebar .text-slate-400 {
                color: #a6b6ca;
            }

            .platform-admin-fallback .platform-sidebar .text-slate-300 {
                color: #d7e0eb;
            }

            .platform-admin-fallback .platform-sidebar .text-slate-200 {
                color: #eef3f8;
            }

            .platform-admin-fallback .platform-sidebar .bg-white\/5 {
                background: rgba(255, 255, 255, 0.06);
            }

            .platform-admin-fallback .platform-sidebar .bg-white\/10 {
                background: rgba(255, 255, 255, 0.1);
            }

            .platform-admin-fallback .platform-sidebar .text-slate-500 {
                color: #b5c3d4;
            }

            .platform-admin-fallback .platform-sidebar .text-slate-600,
            .platform-admin-fallback .platform-sidebar .text-slate-700 {
                color: #dfe8f3;
            }

            .platform-admin-fallback .platform-sidebar .text-sky-700,
            .platform-admin-fallback .platform-sidebar .text-amber-700,
            .platform-admin-fallback .platform-sidebar .text-rose-700,
            .platform-admin-fallback .platform-sidebar .text-emerald-700 {
                color: #f8fafc;
            }

            .platform-admin-fallback main {
                padding: 20px;
            }

            .platform-admin-fallback .platform-sidebar {
                padding: 20px 16px;
            }

            .platform-admin-fallback .platform-sidebar nav,
            .platform-admin-fallback .platform-sidebar > div:last-child {
                padding-left: 4px;
                padding-right: 4px;
            }

            .platform-admin-fallback .platform-sidebar .platform-panel {
                padding: 20px;
            }

            .platform-admin-fallback .platform-sidebar .platform-nav-link {
                margin-left: 0;
                margin-right: 0;
            }

            .platform-admin-fallback .platform-page-header > div {
                gap: 20px;
            }

            .platform-admin-fallback .platform-page-header h2 {
                margin-bottom: 10px;
                font-size: 22px;
                line-height: 1.2;
            }

            .platform-admin-fallback .platform-page-header p:first-child {
                margin-bottom: 6px;
            }

            .platform-admin-fallback .platform-page-header .platform-admin-button {
                min-height: 44px;
            }

            @media (min-width: 768px) {
                main,
                .platform-admin-fallback main {
                    padding: 24px;
                }

                .platform-admin-fallback .md\:grid {
                    display: grid;
                }

                .platform-admin-fallback .md\:grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .platform-admin-fallback .md\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .platform-admin-fallback .md\:px-6 {
                    padding-left: 24px;
                    padding-right: 24px;
                }

                .platform-admin-fallback .md\:flex-row {
                    flex-direction: row;
                }
            }

            @media (min-width: 1024px) {
                body > div:first-of-type {
                    display: grid;
                    grid-template-columns: 300px minmax(0, 1fr);
                }

                .platform-sidebar {
                    min-height: 100vh;
                    border-right: 1px solid rgba(255, 255, 255, 0.08);
                    border-bottom: 0;
                    padding: 24px;
                }

                .platform-admin-fallback .platform-sidebar {
                    padding: 24px 18px;
                }

                .platform-admin-fallback .lg\:grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .platform-admin-fallback .lg\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .platform-admin-fallback .lg\:max-w-md {
                    max-width: 768px;
                }

                .platform-admin-fallback .lg\:flex-row {
                    flex-direction: row;
                }

                .platform-admin-fallback .lg\:items-center {
                    align-items: center;
                }

                .platform-admin-fallback .lg\:items-end {
                    align-items: flex-end;
                }

                .platform-admin-fallback .lg\:justify-between {
                    justify-content: space-between;
                }

                .platform-admin-fallback .lg\:grid-cols-\[1\.4fr_0\.9fr_0\.8fr_1fr\] {
                    grid-template-columns: 1.4fr 0.9fr 0.8fr 1fr;
                }
            }

            @media (min-width: 1280px) {
                .platform-admin-fallback .xl\:grid-cols-4 {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }

                .platform-admin-fallback .xl\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .platform-admin-fallback .xl\:flex-row {
                    flex-direction: row;
                }

                .platform-admin-fallback .xl\:items-end {
                    align-items: flex-end;
                }

                .platform-admin-fallback .xl\:justify-between {
                    justify-content: space-between;
                }

                .platform-admin-fallback .xl\:grid-cols-\[320px_minmax\(0,1fr\)\] {
                    grid-template-columns: 320px minmax(0, 1fr);
                }

                .platform-admin-fallback .xl\:grid-cols-\[minmax\(0,1\.2fr\)_360px\] {
                    grid-template-columns: minmax(0, 1.2fr) 360px;
                }

                .platform-admin-fallback .xl\:grid-cols-\[minmax\(0,1\.05fr\)_minmax\(340px,0\.95fr\)\] {
                    grid-template-columns: minmax(0, 1.05fr) minmax(340px, 0.95fr);
                }
            }

            @media (min-width: 1536px) {
                .platform-admin-fallback .\32xl\:grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .platform-admin-fallback .\32xl\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .platform-admin-fallback .\32xl\:col-span-2 {
                    grid-column: span 2 / span 2;
                }
            }
        </style>
    @endif
    @livewireStyles
</head>
<body class="platform-shell min-h-screen text-slate-900">
    @php($unreadInboxCount = auth()->check()
        ? \App\Models\Platform\InboxItem::query()
            ->whereDoesntHave('reads', fn ($query) => $query->where('user_id', auth()->id()))
            ->count()
        : 0)
    @php($adminName = auth()->user()?->name)
    @php($adminEmail = auth()->user()?->email)
    @php($pendingPaymentValidationCount = auth()->check()
        ? \App\Models\Platform\PlatformPaymentTransaction::query()
            ->where('status', 'waiting_validation')
            ->count()
        : 0)
    @php($activeAnnouncementModal = auth()->check()
        ? \App\Models\Platform\Announcement::query()
            ->activeWindow()
            ->where('show_as_modal', true)
            ->whereDoesntHave('dismissals', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('starts_at')
            ->latest('id')
            ->first()
        : null)

    <div
        x-data="{ navOpen: false }"
        x-on:keydown.escape.window="navOpen = false"
        class="min-h-screen {{ $hasViteAssets ? '' : 'platform-admin-fallback' }}"
    >
        {{-- Top bar ponsel: sidebar jadi drawer agar konten tidak terdorong jauh ke bawah. --}}
        <header class="sticky top-0 z-40 flex items-center justify-between gap-3 border-b border-white/10 bg-slate-950/95 px-4 py-3 backdrop-blur lg:hidden">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_18px_rgba(110,231,183,0.9)]"></span>
                <span class="text-sm font-semibold text-white">POS Platform</span>
            </div>
            <button
                type="button"
                x-on:click="navOpen = true"
                class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-100"
                aria-label="Buka menu navigasi"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
                Menu
            </button>
        </header>

        <div class="lg:grid lg:grid-cols-[300px_minmax(0,1fr)]">
        <aside
            class="platform-sidebar border-b border-white/10 px-5 py-5 lg:border-b-0 lg:border-r lg:px-6 lg:py-6"
            x-bind:data-nav-open="navOpen ? 'true' : 'false'"
        >
            <div class="platform-panel rounded-[1.75rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-3 rounded-full border border-emerald-400/15 bg-emerald-400/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_18px_rgba(110,231,183,0.9)]"></span>
                        POS Platform
                    </div>
                    <button
                        type="button"
                        x-on:click="navOpen = false"
                        class="platform-nav-close items-center justify-center rounded-xl border border-white/15 bg-white/5 p-2 text-slate-200"
                        aria-label="Tutup menu navigasi"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 space-y-3">
                    <div>
                        <p class="text-2xl font-semibold text-white">Admin SaaS Console</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            Kelola tenant, onboarding, paket, dan event operasional dari satu panel yang lebih jelas untuk dipakai harian.
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Fokus Hari Ini</p>
                            <p class="mt-2 text-sm text-slate-200">Pantau request baru, tenant yang tersendat, dan inbox operasional tanpa lompat antar halaman.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Arah Kerja</p>
                            <p class="mt-2 text-sm text-slate-200">Gunakan panel ini untuk action admin. Form pendaftaran publik tetap tersedia terpisah untuk calon tenant.</p>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="mt-5 space-y-5">
                @php($navGroups = [
                    [
                        'label' => 'Operasional Harian',
                        'items' => [
                            ['key' => 'tenants', 'label' => 'Tenant Manager', 'hint' => 'Kelola tenant', 'route' => 'platform.admin.tenants.index', 'match' => 'platform.admin.tenants.index'],
                            ['key' => 'inbox', 'label' => 'Inbox Operasional', 'hint' => 'Event masuk', 'route' => 'platform.admin.inbox.index', 'match' => 'platform.admin.inbox.index'],
                            ['key' => 'payments', 'label' => 'Validasi Pembayaran', 'hint' => 'Invoice', 'route' => 'platform.admin.payments.index', 'match' => 'platform.admin.payments.index'],
                        ],
                    ],
                    [
                        'label' => 'Pengaturan Platform',
                        'items' => [
                            ['key' => 'plans', 'label' => 'Paket & Fitur', 'hint' => 'Langganan', 'route' => 'platform.admin.plans.index', 'match' => 'platform.admin.plans.*'],
                            ['key' => 'registration', 'label' => 'Pengaturan Pendaftaran', 'hint' => 'Form publik', 'route' => 'platform.admin.settings.registration', 'match' => 'platform.admin.settings.registration'],
                            ['key' => 'subscription_gateway', 'label' => 'Gateway Langganan', 'hint' => 'DOKU platform', 'route' => 'platform.admin.settings.subscription-gateway', 'match' => 'platform.admin.settings.subscription-gateway'],
                            ['key' => 'gateways', 'label' => 'Payment Gateway', 'hint' => 'Credential per tenant', 'route' => 'platform.admin.gateways.index', 'match' => 'platform.admin.gateways.index'],
                        ],
                    ],
                    [
                        'label' => 'Komunikasi & Publik',
                        'items' => [
                            ['key' => 'announcements', 'label' => 'Pengumuman', 'hint' => 'Broadcast', 'route' => 'platform.admin.announcements.index', 'match' => 'platform.admin.announcements.index'],
                            ['key' => 'public', 'label' => 'Form Pendaftaran', 'hint' => 'Halaman publik', 'route' => 'platform.register.create', 'match' => 'platform.register.create'],
                        ],
                    ],
                    [
                        'label' => 'Hak Akses',
                        'items' => array_values(array_filter([
                            auth()->user()?->hasPlatformPermission('view_roles')
                                ? ['key' => 'roles', 'label' => 'Role & Hak Akses', 'hint' => 'Permission staff', 'route' => 'platform.admin.roles.index', 'match' => 'platform.admin.roles.index']
                                : null,
                            auth()->user()?->hasPlatformPermission('manage_platform_users')
                                ? ['key' => 'platform-users', 'label' => 'Pengguna Platform', 'hint' => 'Akun staff', 'route' => 'platform.admin.users.index', 'match' => 'platform.admin.users.index']
                                : null,
                        ])),
                    ],
                ])

                @php($badgeMap = [
                    'inbox' => $unreadInboxCount > 0 ? ['value' => $unreadInboxCount, 'class' => 'bg-rose-500 text-white'] : null,
                    'payments' => $pendingPaymentValidationCount > 0 ? ['value' => $pendingPaymentValidationCount, 'class' => 'bg-amber-500 text-slate-950'] : null,
                ])

                @foreach ($navGroups as $group)
                    @continue (empty($group['items']))
                    <div class="space-y-1.5">
                        <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $group['label'] }}</p>
                        @foreach ($group['items'] as $item)
                            @php($active = request()->routeIs($item['match']))
                            <a href="{{ route($item['route']) }}" wire:navigate x-on:click="navOpen = false" class="platform-nav-link {{ $active ? 'is-active' : '' }}">
                                <span class="flex flex-col">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="text-[11px] font-normal {{ $active ? 'text-emerald-950/70' : 'text-slate-500' }}">{{ $item['hint'] }}</span>
                                </span>
                                @php($badge = $badgeMap[$item['key']] ?? null)
                                @if ($badge)
                                    <span class="platform-badge {{ $badge['class'] }}">{{ $badge['value'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="mt-5 space-y-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Login Sebagai</p>
                    <p class="mt-2 font-medium text-white">{{ $adminName }}</p>
                    <p class="mt-1 break-all text-xs text-slate-400">{{ $adminEmail }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/35 px-3.5 py-3 text-xs leading-5 text-slate-400">
                    Gunakan logout platform setelah selesai bekerja, terutama jika panel ini dibuka di perangkat bersama.
                </div>

                <form method="POST" action="{{ route('platform.logout') }}">
                    @csrf
                    <button type="submit" class="platform-admin-button is-secondary w-full justify-center border border-white/10 bg-white/10 text-white hover:bg-white/15" data-ui-submit data-loading-label="Mengakhiri sesi...">
                        <span data-ui-submit-label>Logout Platform</span>
                    </button>
                </form>
            </div>
        </aside>
        </div>

        <div class="platform-nav-backdrop" x-show="navOpen" x-cloak x-on:click="navOpen = false" aria-hidden="true"></div>

        <main class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-7">
            <div class="platform-page-header rounded-[1.5rem] px-5 py-4 shadow-[0_18px_48px_rgba(2,6,23,0.18)]">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-emerald-300">Platform Operations</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ str_replace('Platform Admin - ', '', $title ?? 'Platform Admin') }}</p>
                        <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-300">
                            Semua operasional platform ada di sini: pantau tenant, tindak lanjuti pembayaran, atur paket, dan kelola pendaftaran publik.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('platform.admin.tenants.index') }}" wire:navigate class="platform-admin-button is-secondary">
                            <span>Tenant</span>
                        </a>
                        <a href="{{ route('platform.admin.inbox.index') }}" wire:navigate class="platform-admin-button is-secondary">
                            <span>Inbox</span>
                            @if ($unreadInboxCount > 0)
                                <span class="platform-badge bg-rose-500 text-white">{{ $unreadInboxCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('platform.admin.payments.index') }}" wire:navigate class="platform-admin-button is-secondary">
                            <span>Pembayaran</span>
                            @if ($pendingPaymentValidationCount > 0)
                                <span class="platform-badge bg-amber-500 text-slate-950">{{ $pendingPaymentValidationCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('platform.admin.plans.index') }}" wire:navigate class="platform-admin-button is-secondary">
                            <span>Paket</span>
                        </a>
                        <a href="{{ route('platform.admin.settings.registration') }}" wire:navigate class="platform-admin-button is-secondary">
                            <span>Pendaftaran</span>
                        </a>
                        <a href="{{ route('platform.register.create') }}" wire:navigate class="platform-admin-button is-primary">
                            <span>Form Publik</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    @if ($activeAnnouncementModal)
        <div class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/70 px-4 py-6" data-announcement-modal>
            <div class="w-full max-w-2xl rounded-[2rem] border border-white/10 bg-white shadow-[0_28px_90px_rgba(15,23,42,0.34)]">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Pengumuman Platform</p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $activeAnnouncementModal->title }}</h3>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($activeAnnouncementModal->tone) {
                            'maintenance' => 'bg-amber-100 text-amber-700',
                            'security' => 'bg-rose-100 text-rose-700',
                            'feature' => 'bg-sky-100 text-sky-700',
                            default => 'bg-emerald-100 text-emerald-700',
                        } }}">
                            {{ strtoupper($activeAnnouncementModal->tone) }}
                        </span>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <p class="whitespace-pre-line text-sm leading-7 text-slate-600">{{ $activeAnnouncementModal->message }}</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('platform.admin.announcements.index') }}" wire:navigate class="platform-admin-button is-secondary">
                            Lihat Semua Pengumuman
                        </a>
                        <form method="POST" action="{{ route('platform.admin.announcements.dismiss', $activeAnnouncementModal) }}">
                            @csrf
                            <button type="submit" class="platform-admin-button is-primary" data-ui-submit data-loading-label="Menutup pengumuman...">
                                <span data-ui-submit-label>Tutup Popup</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @livewireScripts
</body>
</html>
