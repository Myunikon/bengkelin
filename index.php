<?php
session_start();
require 'config.php';

if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . 'dashboard.php');
}
redirect(BASE_URL . 'login.php');
