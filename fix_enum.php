<?php
$pdo = new PDO('mysql:host=localhost;dbname=facilita;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Update any broken statuses back to open so they aren't empty
$pdo->exec("UPDATE services SET status = 'Aberta' WHERE status = ''");

// Alter the enum
$sql = "ALTER TABLE services MODIFY COLUMN status ENUM('Aberta', 'Em negociação', 'Aguardando início', 'Em realização', 'Aguardando pagamento', 'Finalizado') DEFAULT 'Aberta'";
$pdo->exec($sql);

// Fix row 2 specifically which had the corrupted "Em realização"
$pdo->exec("UPDATE services SET status = 'Em realização' WHERE id = 2");

echo "Fixed successfully.\n";
?>
