<?php
if (!isset($_SESSION['user_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
    header('Location: ' . BASE_URL . 'login.php' . ($redirect ? '?redirect=' . $redirect : ''));
    exit;
}
