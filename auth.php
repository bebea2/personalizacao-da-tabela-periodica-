<?php
/**
 * Gerenciador de Autenticação e Sessões Nativas em PHP
 * Utiliza PDO para queries seguras com Prepared Statements contra SQL Injection.
 */

require_once __DIR__ . '/bd.php';

function iniciar_sessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurações de segurança para cookie de sessão (apenas se os headers ainda não foram enviados)
        if (!headers_sent()) {
            @ini_set('session.cookie_httponly', 1);
            @ini_set('session.use_only_cookies', 1);
        }
        @session_start();
    }
}

function usuario_logado(): bool {
    iniciar_sessao();
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function obter_usuario(): ?array {
    iniciar_sessao();
    if (!usuario_logado()) {
        return null;
    }
    return [
        'id'    => $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email' => $_SESSION['usuario_email'] ?? '',
    ];
}

function requer_login(string $redirecionarPara = 'login.php'): void {
    if (!usuario_logado()) {
        $urlAtual = urlencode($_SERVER['REQUEST_URI']);
        header("Location: {$redirecionarPara}?redirect={$urlAtual}");
        exit;
    }
}

function fazer_login(string $email, string $senha): array {
    iniciar_sessao();
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

    if (empty($email) || empty($senha)) {
        return ['sucesso' => false, 'erro' => 'Preencha o e-mail e a senha.'];
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Regenerar ID de sessão para prevenir fixation attacks
            if (!headers_sent()) {
                @session_regenerate_id(true);
            }
            $_SESSION['usuario_id']    = (int) $usuario['id'];
            $_SESSION['usuario_nome']  = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            return ['sucesso' => true, 'usuario' => $usuario];
        }

        return ['sucesso' => false, 'erro' => 'E-mail ou senha incorretos.'];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao processar login: ' . $e->getMessage()];
    }
}

function cadastrar_usuario(string $nome, string $email, string $senha): array {
    iniciar_sessao();
    $nome = trim(strip_tags($nome));
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

    if (empty($nome) || empty($email) || empty($senha)) {
        return ['sucesso' => false, 'erro' => 'Todos os campos são obrigatórios.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['sucesso' => false, 'erro' => 'Formato de e-mail inválido.'];
    }

    if (strlen($senha) < 4) {
        return ['sucesso' => false, 'erro' => 'A senha deve conter no mínimo 4 caracteres.'];
    }

    try {
        $pdo = getDB();

        // Verificar se e-mail já existe (Prepared Statement seguro)
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmtCheck->execute([':email' => $email]);
        if ($stmtCheck->fetch()) {
            return ['sucesso' => false, 'erro' => 'Este e-mail já está cadastrado. Faça login.'];
        }

        // Criar usuário com hash seguro
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $dataAtual = date('Y-m-d H:i:s');
        
        $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, criado_em) VALUES (:nome, :email, :senha, :criado_em)");
        $stmtInsert->execute([
            ':nome'      => $nome,
            ':email'     => $email,
            ':senha'     => $hash,
            ':criado_em' => $dataAtual
        ]);

        $novoId = (int) $pdo->lastInsertId();

        // Autenticar automaticamente
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }
        $_SESSION['usuario_id']    = $novoId;
        $_SESSION['usuario_nome']  = $nome;
        $_SESSION['usuario_email'] = $email;

        return ['sucesso' => true, 'id' => $novoId];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao registrar usuário: ' . $e->getMessage()];
    }
}

function fazer_logout(): void {
    iniciar_sessao();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
