<?php
// Note: This file is included from dashboard.php, so DB connection and session exist.
require_once "includes/functions.php";

// Client mock location for filtering (Centro SP)
$client_lat = -23.550520;
$client_lng = -46.633308;

$filter_distance = $_GET['distance'] ?? '';
$filter_rating = $_GET['rating'] ?? '';
$filter_price = $_GET['price'] ?? '';
$filter_team = $_GET['team_size'] ?? '';

$sql = "SELECT p.*, u.nome, u.profile_picture FROM provider_profiles p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];

if ($filter_team !== '') {
    $sql .= " AND p.team_size = ?";
    $params[] = $filter_team;
}

if ($filter_price !== '') {
    $sql .= " AND p.hourly_rate <= ?";
    $params[] = $filter_price;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$providers = $stmt->fetchAll();

// Apply PHP-side filters for distance and rating (since we need custom logic)
$filtered_providers = [];
foreach ($providers as $p) {
    $distance = calculateDistance($client_lat, $client_lng, $p['lat'], $p['lng']);
    $avg_rating = getAverageRating($pdo, $p['user_id']);
    
    // Check Distance filter
    if ($filter_distance !== '' && $distance > (int)$filter_distance) {
        continue;
    }
    
    // Check Rating filter
    if ($filter_rating !== '' && $avg_rating < (int)$filter_rating) {
        continue;
    }
    
    $p['calculated_distance'] = round($distance, 1);
    $p['avg_rating'] = $avg_rating;
    $filtered_providers[] = $p;
}
?>

<div>
    <h2>Encontre o profissional ideal</h2>
    <p class="text-muted mb-2">Busque por especialidade, preço ou avaliação.</p>
    
    <form method="GET" class="filter-bar">
        <div>
            <label style="display:block; font-size:0.8rem; font-weight:bold;">Distância (km)</label>
            <select name="distance">
                <option value="">Qualquer</option>
                <option value="5" <?= $filter_distance == '5' ? 'selected' : '' ?>>Até 5km</option>
                <option value="10" <?= $filter_distance == '10' ? 'selected' : '' ?>>Até 10km</option>
                <option value="20" <?= $filter_distance == '20' ? 'selected' : '' ?>>Até 20km</option>
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.8rem; font-weight:bold;">Avaliação Mínima</label>
            <select name="rating">
                <option value="">Qualquer</option>
                <option value="4" <?= $filter_rating == '4' ? 'selected' : '' ?>>4 Estrelas+</option>
                <option value="5" <?= $filter_rating == '5' ? 'selected' : '' ?>>5 Estrelas</option>
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.8rem; font-weight:bold;">Preço Máx (Hora)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($filter_price) ?>" placeholder="Ex: 100" style="width:100px">
        </div>
        <div>
            <label style="display:block; font-size:0.8rem; font-weight:bold;">Equipe</label>
            <select name="team_size">
                <option value="">Qualquer</option>
                <option value="Individual" <?= $filter_team == 'Individual' ? 'selected' : '' ?>>Individual</option>
                <option value="Equipe" <?= $filter_team == 'Equipe' ? 'selected' : '' ?>>Equipe Técnica</option>
            </select>
        </div>
        <div style="margin-top:auto;">
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:16px;">Filtrar</button>
            <a href="dashboard.php" class="btn btn-outline btn-sm" style="margin-top:16px; text-decoration:none;">Limpar</a>
        </div>
    </form>

    <div class="grid grid-cols-4">
        <?php foreach ($filtered_providers as $p): ?>
            <div class="data-card">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <?php if (!empty($p['profile_picture'])): ?>
                        <img src="uploads/profile_pictures/<?= htmlspecialchars($p['profile_picture']) ?>" alt="" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold; border: 2px solid var(--primary);">
                            <?= strtoupper(substr($p['nome'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <div class="data-card-header" style="margin-bottom: 0;">
                            <h3 class="data-card-title" style="margin-bottom: 0;"><?= htmlspecialchars($p['nome']) ?></h3>
                            <span class="badge badge-primary"><?= htmlspecialchars($p['team_size']) ?></span>
                        </div>
                    </div>
                </div>
                <p style="color: var(--secondary); font-weight: 600;"><?= htmlspecialchars($p['specialty']) ?></p>
                <p class="data-card-subtitle mt-1" style="flex:1;"><?= htmlspecialchars($p['bio']) ?></p>
                
                <div class="mt-1" style="font-size: 0.9rem;">
                    <strong>📍Local:</strong> <?= htmlspecialchars($p['location_name']) ?> <span class="text-muted">(~<?= $p['calculated_distance'] ?>km)</span>
                </div>
                
                <div class="mt-1" style="font-size: 0.9rem;">
                    <strong>⭐Nota:</strong> <?= $p['avg_rating'] > 0 ? $p['avg_rating'] . ' / 5' : 'Novo' ?>
                </div>
                
                <div class="price-tag">
                    R$ <?= number_format($p['hourly_rate'], 2, ',', '.') ?> / hr
                </div>
                
                <a href="profile.php?id=<?= $p['user_id'] ?>" class="btn btn-primary btn-block mt-1">Ver Perfil</a>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($filtered_providers)): ?>
            <div style="grid-column: span 4; text-align:center; padding: 3rem; background: var(--card-bg); border-radius: var(--radius);">
                Nenhum profissional encontrado com esses filtros.
            </div>
        <?php endif; ?>
    </div>
</div>
