<?php
$pdo = new PDO('mysql:host=localhost;dbname=facilita;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SHOW CREATE TABLE services');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
