<?php
require_once "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    die("Acesso negado. Apenas clientes podem postar serviços.");
}

$provider_id = $_GET['provider_id'] ?? null;
$provider_name = "";

if ($provider_id) {
    $stmt = $pdo->prepare("SELECT nome FROM users WHERE id = ? AND role = 'provider'");
    $stmt->execute([$provider_id]);
    $prov = $stmt->fetch();
    if ($prov) {
        $provider_name = $prov['nome'];
    } else {
        $provider_id = null; // invalid
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $budget = $_POST['budget'];
    $location = $_POST['location'];
    
    // Simple mock lat/lng based on location or default
    $lat = -23.550520;
    $lng = -46.633308;

    $status = $provider_id ? 'Em negociação' : 'Aberta';

    $stmt = $pdo->prepare("INSERT INTO services (client_id, provider_id, title, description, budget, status, location_name, lat, lng) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $provider_id, $title, $description, $budget, $status, $location, $lat, $lng]);
    
    header("Location: my_services.php");
    exit;
}

require_once "includes/header.php";
?>

<div class="auth-wrapper" style="min-height: 60vh;">
    <div class="auth-card" style="max-width: 600px;">
        <h2><?= $provider_id ? "Contratar ".htmlspecialchars($provider_name) : "Criar uma Oportunidade" ?></h2>
        <p class="text-muted mb-2">Descreva detalhadamente o serviço que você precisa.</p>

        <form method="POST">
            <div class="form-group">
                <label>Título do Serviço</label>
                <input type="text" name="title" class="form-control" placeholder="Ex: Reforma elétrica, Pintura de sala..." required>
            </div>
            <div class="form-group">
                <label>Orçamento Estimado (R$)</label>
                <input type="number" step="0.01" name="budget" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Localização (Bairro, Cidade)</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Descrição e Requisitos</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block"><?= $provider_id ? "Enviar Proposta" : "Publicar Oportunidade" ?></button>
        </form>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
