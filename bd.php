<?php
/**
 * Conexão com Banco de Dados usando PDO para máxima segurança contra SQL Injections.
 * Stack Oficial: MySQL via PDO (com fallback transparente para SQLite caso o servidor MySQL não esteja iniciado).
 */

class BancoDeDados {
    private static ?PDO $instancia = null;
    private static string $driverUsado = 'mysql';

    // Configurações do MySQL (Padrão para XAMPP / WAMP / Docker)
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'tabela_periodica';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    /**
     * Retorna a instância única do PDO (Singleton)
     */
    public static function getConexao(): PDO {
        if (self::$instancia !== null) {
            return self::$instancia;
        }

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // 1. Tentar conectar ao MySQL
        try {
            // Tenta conectar diretamente à base de dados
            $dsn = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
            self::$instancia = new PDO($dsn, self::DB_USER, self::DB_PASS, $opcoes);
            self::$driverUsado = 'mysql';
            self::garantirTabelasMySQL(self::$instancia);
            return self::$instancia;
        } catch (PDOException $eMySQL) {
            // Se falhou porque a base ainda não existe, tenta criar
            if (str_contains($eMySQL->getMessage(), 'Unknown database') || $eMySQL->getCode() == 1049) {
                try {
                    $dsnRoot = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";charset=utf8mb4";
                    $pdoRoot = new PDO($dsnRoot, self::DB_USER, self::DB_PASS, $opcoes);
                    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `" . self::DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    
                    $dsn = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
                    self::$instancia = new PDO($dsn, self::DB_USER, self::DB_PASS, $opcoes);
                    self::$driverUsado = 'mysql';
                    self::garantirTabelasMySQL(self::$instancia);
                    return self::$instancia;
                } catch (PDOException $eCriar) {
                    // Prossegue para fallback se MySQL não responder
                }
            }

            // 2. Fallback de alta disponibilidade: SQLite via PDO
            // Garante que a aplicação funcione imediatamente mesmo sem o serviço MySQL iniciado!
            try {
                $caminhoSqlite = __DIR__ . '/database.sqlite';
                self::$instancia = new PDO("sqlite:" . $caminhoSqlite, null, null, $opcoes);
                self::$driverUsado = 'sqlite';
                self::garantirTabelasSQLite(self::$instancia);
                return self::$instancia;
            } catch (PDOException $eSqlite) {
                die("Erro crítico de conexão com o banco de dados: " . $eMySQL->getMessage());
            }
        }
    }

    /**
     * Retorna qual driver está atualmente ativo ('mysql' ou 'sqlite')
     */
    public static function getDriver(): string {
        if (self::$instancia === null) {
            self::getConexao();
        }
        return self::$driverUsado;
    }

    /**
     * Garante a criação das tabelas no MySQL
     */
    private static function garantirTabelasMySQL(PDO $pdo): void {
        $sqlUsuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nome` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `senha` VARCHAR(255) NOT NULL,
            `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlProjetos = "CREATE TABLE IF NOT EXISTS `projetos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `titulo` VARCHAR(150) NOT NULL DEFAULT 'Minha Tabela Periódica',
            `config_json` LONGTEXT NOT NULL,
            `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_projetos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sqlUsuarios);
        $pdo->exec($sqlProjetos);
    }

    /**
     * Garante a criação das tabelas no SQLite caso o MySQL esteja offline
     */
    private static function garantirTabelasSQLite(PDO $pdo): void {
        $sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );";

        $sqlProjetos = "CREATE TABLE IF NOT EXISTS projetos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            titulo TEXT NOT NULL DEFAULT 'Minha Tabela Periódica',
            config_json TEXT NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );";

        $pdo->exec($sqlUsuarios);
        $pdo->exec($sqlProjetos);
    }
}

/**
 * Função de atalho para obter a conexão PDO global
 */
function getDB(): PDO {
    return BancoDeDados::getConexao();
}
