<?php
// Start output buffering to prevent "headers already sent" errors
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!Session::isLoggedIn()) {
    header('Location: /e-TU/login.php');
    exit;
}

// Check session timeout
if (!Session::checkTimeout()) {
    header('Location: /e-TU/login.php?timeout=1');
    exit;
}

// Capture flash message for display after page load
$flashMessage = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : null;
$flashType = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
if ($flashMessage) {
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - E-ADMIN TU MA AL IHSAN</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/e-TU/assets/css/custom.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Glassmorphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        dark: {
                            bg: '#111827',
                            card: '#1f2937',
                            text: '#f3f4f6',
                            muted: '#9ca3af',
                            border: '#374151',
                            input: '#374151'
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Check local storage or system preference
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>



    <?php if ($flashMessage): ?>
        <script>
            // Display flash message after page load
            document.addEventListener('DOMContentLoaded', function () {
                showToast(<?= json_encode($flashMessage) ?>, <?= json_encode($flashType) ?>);
            });
        </script>
    <?php endif; ?>
</head>

<body class="bg-gray-50 min-h-screen dark:bg-gray-900 transition-colors duration-200">