<?php
require_once "includes/db.php";
require_once "includes/functions.php";

$provider_id = $_GET['id'] ?? null;
if (!$provider_id) {
    die("Profissional não especificado.");
}

// Fetch provider details
$stmt = $pdo->prepare("
    SELECT p.*, u.nome, u.email, u.profile_picture 
    FROM provider_profiles p 
    JOIN users u ON p.user_id = u.id 
    WHERE u.id = ? AND u.role = 'provider'
");
$stmt->execute([$provider_id]);
$provider = $stmt->fetch();

if (!$provider) {
    die("Profissional não encontrado.");
}

$avg_rating = getAverageRating($pdo, $provider_id);

// Fetch reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.nome as reviewer_name 
    FROM reviews r 
    JOIN users u ON r.reviewer_id = u.id 
    WHERE r.provider_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$provider_id]);
$reviews = $stmt->fetchAll();

// Check if current user is a client and can leave review
$can_review = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'client') {
    $stmt = $pdo->prepare("SELECT id FROM services WHERE client_id = ? AND provider_id = ? AND status = 'Finalizado' LIMIT 1");
    $stmt->execute([$_SESSION['user_id'], $provider_id]);
    if ($stmt->fetch()) {
        $can_review = true;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && $can_review) {
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'];
    $service_id = 1; // In a perfect scenario we'd link to the specific service ID. Using 1 as fallback for MVP if we don't have it bound.
    
    // Better: get the latest finished service id
    $stmt = $pdo->prepare("SELECT id FROM services WHERE client_id = ? AND provider_id = ? AND status = 'Finalizado' ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id'], $provider_id]);
    $srv = $stmt->fetch();
    if ($srv) {
        $service_id = $srv['id'];
    }

    $stmt = $pdo->prepare("INSERT INTO reviews (service_id, reviewer_id, provider_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$service_id, $_SESSION['user_id'], $provider_id, $rating, $comment]);
    header("Location: profile.php?id=" . $provider_id);
    exit;
}

require_once "includes/header.php";
?>

<div class="grid grid-cols-2 gap-1 mb-2">
    <div>
        <div class="data-card">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <?php if (!empty($provider['profile_picture'])): ?>
                    <img src="uploads/profile_pictures/<?= htmlspecialchars($provider['profile_picture']) ?>" alt="Foto de Perfil" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; border: 2px solid var(--primary);">
                        <?= strtoupper(substr($provider['nome'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h2 style="margin: 0;"><?= htmlspecialchars($provider['nome']) ?></h2>
                    <p class="text-muted" style="font-size: 1.1rem; margin: 0;"><?= htmlspecialchars($provider['specialty']) ?></p>
                </div>
            </div>
            
            <p><strong>Bio:</strong><br><?= nl2br(htmlspecialchars($provider['bio'])) ?></p>
            <div class="mt-1">
                <strong>Equipe:</strong> <?= htmlspecialchars($provider['team_size']) ?><br>
                <strong>Preço Base:</strong> R$ <?= number_format($provider['hourly_rate'], 2, ',', '.') ?> / hora<br>
                <strong>Localização:</strong> <?= htmlspecialchars($provider['location_name']) ?>
            </div>
            
            <div class="mt-2" style="font-size: 1.5rem;">
                <strong>Nota:</strong> <span class="stars"><?= str_repeat('★', round($avg_rating)) ?></span> 
                <span class="text-muted" style="font-size:1rem;">(<?= $avg_rating ?> / 5)</span>
            </div>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'client'): ?>
                <a href="post_service.php?provider_id=<?= $provider_id ?>" class="btn btn-primary btn-block mt-2">Contratar Profissional</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div>
        <div class="data-card" style="height: 100%;">
            <h3>Avaliações</h3>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border-color);">
            
            <?php if ($can_review): ?>
                <div style="background: #f1f5f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <h4>Deixe sua avaliação</h4>
                    <form method="POST">
                        <div class="form-group mb-1 mt-1">
                            <label style="margin-bottom: 0.5rem; display: block;">Nota (1 a 5):</label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" required />
                                <label for="star5" title="5 estrelas">★</label>
                                <input type="radio" id="star4" name="rating" value="4" />
                                <label for="star4" title="4 estrelas">★</label>
                                <input type="radio" id="star3" name="rating" value="3" />
                                <label for="star3" title="3 estrelas">★</label>
                                <input type="radio" id="star2" name="rating" value="2" />
                                <label for="star2" title="2 estrelas">★</label>
                                <input type="radio" id="star1" name="rating" value="1" />
                                <label for="star1" title="1 estrela">★</label>
                            </div>
                        </div>
                        <div class="form-group mb-1">
                            <label>Comentário:</label>
                            <textarea name="comment" class="form-control" rows="2" required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary btn-sm">Enviar</button>
                    </form>
                </div>
            <?php endif; ?>

            <div style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($reviews)): ?>
                    <p class="text-muted">Nenhuma avaliação ainda.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                        <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between;">
                                <strong><?= htmlspecialchars($r['reviewer_name']) ?></strong>
                                <span class="stars"><?= str_repeat('★', $r['rating']) ?></span>
                            </div>
                            <p class="mt-1" style="font-size: 0.95rem;"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
