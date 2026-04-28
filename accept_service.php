<?php
require_once "includes/db.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    die("Acesso negado.");
}

$service_id = $_GET['id'] ?? null;
if ($service_id) {
    $stmt = $pdo->prepare("UPDATE services SET provider_id = ?, status = 'Em negociação' WHERE id = ? AND status = 'Aberta'");
    $stmt->execute([$_SESSION['user_id'], $service_id]);
}

header("Location: my_services.php");
exit;
?>
