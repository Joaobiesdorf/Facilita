<?php
// Note: This file is included from dashboard.php
require_once "includes/functions.php";

// Fetch provider profile to get their location
$stmt = $pdo->prepare("SELECT lat, lng, specialty FROM provider_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider_profile = $stmt->fetch();

$prov_lat = $provider_profile['lat'] ?? -23.550520;
$prov_lng = $provider_profile['lng'] ?? -46.633308;

$filter_distance = $_GET['distance'] ?? '';
$filter_budget = $_GET['budget'] ?? '';

// Fetch only OPEN services without a provider assigned yet
$sql = "SELECT s.*, u.nome as client_name, u.profile_picture as client_picture FROM services s JOIN users u ON s.client_id = u.id WHERE s.status = 'Aberta' AND s.provider_id IS NULL";
$params = [];

if ($filter_budget !== '') {
    $sql .= " AND s.budget >= ?";
    $params[] = $filter_budget;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$filtered_services = [];
foreach ($services as $s) {
    if (!$s['lat'] && !$s['lng']) {
        // Fallback default
        $s['lat'] = -23.550520; 
        $s['lng'] = -46.633308;
    }
    
    $distance = calculateDistance($prov_lat, $prov_lng, $s['lat'], $s['lng']);
    
    // Check Distance filter
    if ($filter_distance !== '' && $distance > (int)$filter_distance) {
        continue;
    }
    
    $s['calculated_distance'] = round($distance, 1);
    $filtered_services[] = $s;
}
?>

<div>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Feed de Oportunidades</h2>
            <p class="text-muted mb-2">Encontre clientes procurando por seus serviços.</p>
        </div>
        <div style="text-align: right;">
            <p class="text-muted">Sua Especialidade: <strong style="color: var(--primary)"><?= htmlspecialchars($provider_profile['specialty']) ?></strong></p>
        </div>
    </div>
    
    <form method="GET" class="filter-bar">
        <div>
            <label style="display:block; font-size:0.8rem; font-weight:bold;">Distância (km)</label>
            <select name="distance">
                <option value="">Qualquer lugar</option>
                <option value="5" <?= $filter_distance == '5' ? 'selected' : '' ?>>Até 5km</option>
                <option value="10" <?= $filter_distance == '10' ? 'selected' : '' ?>>Até 10km</option>
                <option value="20" <?= $filter_distance == '20' ? 'selected' : '' ?>>Até 20km</option>
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.8rem; font-weight:bold;">Orçamento Mínimo (R$)</label>
            <input type="number" name="budget" value="<?= htmlspecialchars($filter_budget) ?>" placeholder="Ex: 500" style="width:120px">
        </div>
        <div style="margin-top:auto;">
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:16px;">Filtrar</button>
            <a href="dashboard.php" class="btn btn-outline btn-sm" style="margin-top:16px; text-decoration:none;">Limpar</a>
        </div>
    </form>

    <div class="grid grid-cols-3">
        <?php foreach ($filtered_services as $s): ?>
            <div class="data-card">
                <div class="data-card-header">
                    <h3 class="data-card-title"><?= htmlspecialchars($s['title']) ?></h3>
                    <span class="badge badge-success">Aberta</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                    <?php if (!empty($s['client_picture'])): ?>
                        <img src="uploads/profile_pictures/<?= htmlspecialchars($s['client_picture']) ?>" alt="" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: bold;">
                            <?= strtoupper(substr($s['client_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <p style="color: var(--secondary); font-size: 0.9rem; margin: 0;"><strong>Cliente:</strong> <?= htmlspecialchars($s['client_name']) ?></p>
                </div>
                <p class="data-card-subtitle mt-1" style="flex:1;"><?= htmlspecialchars($s['description']) ?></p>
                
                <div class="mt-1" style="font-size: 0.9rem;">
                    <strong>📍Local:</strong> <?= htmlspecialchars($s['location_name']) ?> <span class="text-muted">(~<?= $s['calculated_distance'] ?>km)</span>
                </div>
                
                <div class="mt-1" style="font-size: 0.9rem; color: var(--text-muted);">
                    Postado em: <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                </div>

                <div class="price-tag mb-1">
                    Orçamento: R$ <?= number_format($s['budget'], 2, ',', '.') ?>
                </div>
                
                <a href="accept_service.php?id=<?= $s['id'] ?>" class="btn btn-outline btn-block mt-1 text-center" style="text-decoration:none;">Assumir / Enviar Proposta</a>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($filtered_services)): ?>
            <div style="grid-column: span 3; text-align:center; padding: 3rem; background: var(--card-bg); border-radius: var(--radius);">
                Nenhuma oportunidade aberta com estes filtros no momento.
            </div>
        <?php endif; ?>
    </div>
</div>
