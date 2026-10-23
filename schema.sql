-- ==============================================================
-- Schema do Banco de Dados: Personalização da Tabela Periódica
-- Stack: MySQL com PDO e PHP
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `tabela_periodica`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tabela_periodica`;

-- Tabela de Usuários (Autenticação com senhas protegidas por password_hash)
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Projetos / Tabelas Periódicas Personalizadas
CREATE TABLE IF NOT EXISTS `projetos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `titulo` VARCHAR(150) NOT NULL DEFAULT 'Minha Tabela Periódica',
    `config_json` LONGTEXT NOT NULL,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_projetos_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para otimização de busca
CREATE INDEX `idx_projetos_usuario` ON `projetos` (`usuario_id`);
