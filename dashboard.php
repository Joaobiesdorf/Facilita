<?php
require_once "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

require_once "includes/header.php";

if ($role === 'client') {
    require_once "dashboard_client.php";
} else {
    require_once "dashboard_provider.php";
}

require_once "includes/footer.php";
?>
