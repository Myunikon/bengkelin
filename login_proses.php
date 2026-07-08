<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    redirect(BASE_URL . 'login.php?error=1');
}

$stmt = $conn->prepare("SELECT id_user, username, password FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id_user'];
        $_SESSION['username'] = $user['username'];

        $redirectTo = BASE_URL . 'dashboard.php';
        if (!empty($_POST['redirect'])) {
            $decoded = urldecode($_POST['redirect']);
            if (strpos($decoded, BASE_URL) === 0) {
                $redirectTo = $decoded;
            }
        }
        redirect($redirectTo);
    }
}

$stmt->close();
redirect(BASE_URL . 'login.php?error=1');
