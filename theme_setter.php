<?php
// theme_setter.php – Processor cookie sidebar_state
// Dipanggil via AJAX POST dari sidebar toggle button di script.js

session_start();
require 'config.php';
require 'includes/auth_check.php';

// Always return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $state   = $_POST['state'] ?? 'open';
    $allowed = ['open', 'closed'];
    if (!in_array($state, $allowed, true)) {
        $state = 'open';
    }

    // Tentukan cookie path dari BASE_URL agar cocok dengan subfolder XAMPP
    // Misal: http://localhost/Bengkel/ → path = /Bengkel/
    $parsedUrl  = parse_url(BASE_URL);
    $cookiePath = $parsedUrl['path'] ?? '/';

    // Set cookie selama 30 hari, httponly=false agar bisa dibaca JS juga
    setcookie('sidebar_state', $state, time() + (86400 * 30), $cookiePath, '', false, false);

    echo json_encode(['success' => true, 'state' => $state]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
exit;
