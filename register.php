<?php
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $role = $_POST['role'] ?? 'client';
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "E-mail já cadastrado!";
    } else {
        $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nome, email, senha, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nome, $email, $hashed_password, $role])) {
            $user_id = $pdo->lastInsertId();
            
            if ($role === 'provider') {
                $specialty = $_POST['specialty'] ?? '';
                $team_size = $_POST['team_size'] ?? 'Individual';
                $bio = $_POST['bio'] ?? '';
                $hourly_rate = $_POST['hourly_rate'] ?? 0;
                $location = $_POST['location'] ?? '';
                
                $stmt = $pdo->prepare("INSERT INTO provider_profiles (user_id, specialty, team_size, bio, hourly_rate, location_name) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $specialty, $team_size, $bio, $hourly_rate, $location]);
            }
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Erro ao criar conta.";
        }
    }
}

require_once "includes/header.php";
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Criar Conta</h2>
        
        <?php if (isset($error)): ?>
            <div style="color: var(--danger); margin-bottom: 1rem; text-align: center; font-weight: bold;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-group">
                <label>Eu quero...</label>
                <select name="role" id="roleSelect" class="form-control" onchange="toggleProviderFields()">
                    <option value="client">Contratar Serviços (Cliente)</option>
                    <option value="provider">Oferecer Serviços (Prestador)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
            </div>

            <!-- Provider Only Fields -->
            <div id="providerFields" style="display: none; background: #f1f5f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <h4 class="mb-1" style="color: var(--primary-dark)">Dados Profissionais</h4>
                <div class="form-group">
                    <label>Sua Especialidade</label>
                    <input type="text" name="specialty" class="form-control" placeholder="ex: Eletricista, Desenvolvedor Web">
                </div>
                <div class="form-group">
                    <label>Tamanho da Equipe</label>
                    <select name="team_size" class="form-control">
                        <option value="Individual">Sou autônomo (Individual)</option>
                        <option value="Equipe">Tenho uma equipe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor Médio p/ Hora (R$)</label>
                    <input type="number" step="0.01" name="hourly_rate" class="form-control">
                </div>
                <div class="form-group">
                    <label>Sua Região/Bairro</label>
                    <input type="text" name="location" class="form-control" placeholder="ex: Centro, SP">
                </div>
                <div class="form-group">
                    <label>Biografia Curta</label>
                    <textarea name="bio" class="form-control" rows="3" placeholder="Fale um pouco sobre sua experiência..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
        </form>
        
        <p class="text-center mt-1">
            Já tem uma conta? <a href="login.php" style="color: var(--primary)">Entrar</a>
        </p>
    </div>
</div>

<script>
function toggleProviderFields() {
    var role = document.getElementById('roleSelect').value;
    var providerFields = document.getElementById('providerFields');
    var inputs = providerFields.querySelectorAll('input, textarea, select');
    
    if (role === 'provider') {
        providerFields.style.display = 'block';
        inputs.forEach(i => i.required = true);
    } else {
        providerFields.style.display = 'none';
        inputs.forEach(i => i.required = false);
    }
}
</script>

<?php require_once "includes/footer.php"; ?>
