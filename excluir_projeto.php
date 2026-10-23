<?php
require_once __DIR__ . '/auth.php';
requer_login();

$usuario = obter_usuario();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM projetos WHERE id = :id AND usuario_id = :uid");
        $stmt->execute([':id' => $id, ':uid' => $usuario['id']]);
    } catch (Exception $e) {
        // Ignora
    }
}

header('Location: meus-projetos.php');
exit;
