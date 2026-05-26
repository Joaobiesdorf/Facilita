<?php
require_once "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'client') {
    $stmt = $pdo->prepare("SELECT s.*, u.nome as other_party FROM services s LEFT JOIN users u ON s.provider_id = u.id WHERE s.client_id = ? ORDER BY s.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT s.*, u.nome as other_party FROM services s JOIN users u ON s.client_id = u.id WHERE s.provider_id = ? ORDER BY s.created_at DESC");
}
$stmt->execute([$user_id]);
$services = $stmt->fetchAll();

require_once "includes/header.php";
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Meus Serviços</h2>
    <?php if ($role === 'client'): ?>
        <a href="post_service.php" class="btn btn-primary">Nova Oportunidade</a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1">
    <?php foreach ($services as $s): ?>
        <?php 
            $statusColors = [
                'Aberta' => 'badge-success',
                'Em negociação' => 'badge-warning',
                'Aguardando início' => 'badge-primary',
                'Em realização' => 'badge-warning',
                'Aguardando pagamento' => 'badge-primary',
                'Finalizado' => 'badge-success'
            ];
            $colorClass = $statusColors[$s['status']] ?? 'badge-primary';
        ?>
        <div class="data-card" style="flex-direction: row; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
            <div style="flex: 2;">
                <h3 style="margin-bottom: 0.2rem; font-size: 1.1rem;"><?= htmlspecialchars($s['title']) ?></h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">
                    <?= $role === 'client' ? 'Prestador: ' : 'Cliente: ' ?>
                    <strong><?= htmlspecialchars($s['other_party'] ?? 'Aguardando interessados') ?></strong>
                </p>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Local: <?= htmlspecialchars($s['location_name']) ?> | Valor Combinado: R$ <?= number_format($s['budget'], 2, ',', '.') ?></p>
            </div>
            
            <div style="flex: 1; text-align: center;">
                <span class="badge <?= $colorClass ?>" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;"><?= htmlspecialchars($s['status']) ?></span>
            </div>
            
            <div style="flex: 1; text-align: right; display: flex; flex-direction: column; gap: 0.5rem;">
                <?php if ($s['status'] !== 'Aberta' && $s['status'] !== 'Finalizado'): ?>
                    <a href="update_status.php?id=<?= $s['id'] ?>&action=prev" class="btn btn-outline btn-sm" style="margin-bottom: 0.5rem; border-color: var(--warning); color: var(--warning);">Retornar Status</a>
                <?php endif; ?>
                <?php if ($s['status'] !== 'Finalizado'): ?>
                    <a href="update_status.php?id=<?= $s['id'] ?>&action=next" class="btn btn-outline btn-sm">Avançar Status</a>
                <?php endif; ?>
                
                <?php if ($s['status'] === 'Finalizado' && $role === 'client' && $s['provider_id']): ?>
                    <a href="profile.php?id=<?= $s['provider_id'] ?>" class="btn btn-primary btn-sm">Avaliar Profissional</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($services)): ?>
        <div style="text-align:center; padding: 3rem; background: var(--card-bg); border-radius: var(--radius);">
            Você ainda não possui nenhum serviço cadastrado.
        </div>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
