<?php
session_start();
session_destroy();
// Hapus cookie sidebar state juga
setcookie('sidebar_state', '', time() - 3600, '/');
header('Location: ' . 'login.php');
exit;
