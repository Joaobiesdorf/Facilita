<?php
require_once "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    die("Acesso negado.");
}

$service_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'next';

if (!$service_id) {
    header("Location: my_services.php");
    exit;
}

// Fetch current status
$stmt = $pdo->prepare("SELECT status FROM services WHERE id = ? AND (client_id = ? OR provider_id = ?)");
$stmt->execute([$service_id, $_SESSION['user_id'], $_SESSION['user_id']]);
$service = $stmt->fetch();

if ($service) {
    $pipeline = ['Aberta', 'Em negociação', 'Aguardando início', 'Em realização', 'Aguardando pagamento', 'Finalizado'];
    $currentIndex = array_search($service['status'], $pipeline);
    
    if ($currentIndex !== false) {
        if ($action === 'next' && $currentIndex < count($pipeline) - 1) {
            $newStatus = $pipeline[$currentIndex + 1];
            $stmt = $pdo->prepare("UPDATE services SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $service_id]);
        } elseif ($action === 'prev' && $currentIndex > 0) {
            $newStatus = $pipeline[$currentIndex - 1];
            $stmt = $pdo->prepare("UPDATE services SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $service_id]);
        }
    }
}

header("Location: my_services.php");
exit;
?>
