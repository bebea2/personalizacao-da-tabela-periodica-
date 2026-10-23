<?php
/**
 * Endpoint de Backend em PHP para salvar ou atualizar projetos no MySQL via PDO
 * Utiliza Prepared Statements estritos para garantir 100% de segurança contra SQL Injections.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth.php';

// Verificar se o usuário está logado
if (!usuario_logado()) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$usuario = obter_usuario();
$dadosBrutos = file_get_contents('php://input');
$dados = json_decode($dadosBrutos, true);

if (!$dados || !isset($dados['config_json'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados de projeto inválidos.']);
    exit;
}

$projetoId   = isset($dados['id']) ? (int)$dados['id'] : 0;
$titulo      = trim($dados['titulo'] ?? 'Minha Tabela Periódica');
$configJson  = $dados['config_json'];

if (empty($titulo)) {
    $titulo = 'Minha Tabela Periódica';
}

try {
    $pdo = getDB();
    $dataAtual = date('Y-m-d H:i:s');

    if ($projetoId > 0) {
        // Atualizar projeto existente (verificando se pertence ao usuário logado)
        $sql = "UPDATE projetos 
                SET titulo = :titulo, 
                    config_json = :config_json, 
                    atualizado_em = :atualizado_em 
                WHERE id = :id AND usuario_id = :usuario_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo'        => $titulo,
            ':config_json'   => $configJson,
            ':atualizado_em' => $dataAtual,
            ':id'            => $projetoId,
            ':usuario_id'    => $usuario['id']
        ]);

        echo json_encode([
            'sucesso'  => true,
            'id'       => $projetoId,
            'mensagem' => 'Projeto atualizado com sucesso!'
        ]);
    } else {
        // Inserir novo projeto
        $sql = "INSERT INTO projetos (usuario_id, titulo, config_json, criado_em, atualizado_em) 
                VALUES (:usuario_id, :titulo, :config_json, :criado_em, :atualizado_em)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id'    => $usuario['id'],
            ':titulo'        => $titulo,
            ':config_json'   => $configJson,
            ':criado_em'     => $dataAtual,
            ':atualizado_em' => $dataAtual
        ]);

        $novoId = (int)$pdo->lastInsertId();

        echo json_encode([
            'sucesso'  => true,
            'id'       => $novoId,
            'mensagem' => 'Projeto criado com sucesso!'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'erro'    => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
}
