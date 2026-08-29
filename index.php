<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /alarmas/dashboard/index.php");
} else {
    header("Location: /alarmas/auth/login.php");
}
exit;
