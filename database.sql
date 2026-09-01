-- ====================================================================
-- FotoCidade DF - Esquema Completo de Banco de Dados MySQL
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `fotocidade_df` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fotocidade_df`;

-- 1. TABELA DE USUÁRIOS (Admin e Alunos)
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `nivel` ENUM('admin', 'aluno') NOT NULL DEFAULT 'aluno',
    `cidade` VARCHAR(80) DEFAULT 'Sobradinho',
    `avatar` VARCHAR(255) DEFAULT 'assets/images/avatar-default.jpg',
    `biografia` TEXT,
    `eixo_principal` VARCHAR(100) DEFAULT 'Mapeamento & Fotografia',
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_usuario_email` (`email`),
    INDEX `idx_usuario_nivel` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABELA DA VITRINE CULTURAL (Posts de fotos, vídeos, ensaios)
CREATE TABLE IF NOT EXISTS `vitrine` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo` ENUM('Foto', 'Vídeo', 'Perfil', 'Espaço') NOT NULL DEFAULT 'Foto',
    `titulo` VARCHAR(200) NOT NULL,
    `subtitulo` VARCHAR(255) DEFAULT NULL,
    `descricao` TEXT NOT NULL,
    `autor_id` INT DEFAULT NULL,
    `autor_nome` VARCHAR(120) NOT NULL,
    `autor_avatar` VARCHAR(255) DEFAULT NULL,
    `autor_role` VARCHAR(100) DEFAULT 'Aluno - FotoCidade',
    `bairro` VARCHAR(80) NOT NULL DEFAULT 'Sobradinho',
    `media_url` VARCHAR(255) NOT NULL,
    `thumbnail_url` VARCHAR(255) DEFAULT NULL,
    `likes` INT DEFAULT 0,
    `compartilhamentos` INT DEFAULT 0,
    `tags` VARCHAR(255) DEFAULT NULL,
    `destaque` TINYINT(1) DEFAULT 0,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`autor_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_vitrine_tipo` (`tipo`),
    INDEX `idx_vitrine_bairro` (`bairro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA DE PONTOS CULTURAIS & ORGANIZAÇÕES DO MAPA (map_locais)
CREATE TABLE IF NOT EXISTS `map_locais` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(180) NOT NULL,
    `categoria` VARCHAR(100) NOT NULL DEFAULT 'Espaço Cultural',
    `tipo` VARCHAR(100) DEFAULT NULL,
    `setor` VARCHAR(100) DEFAULT NULL,
    `bairro` VARCHAR(150) NOT NULL DEFAULT 'Sobradinho',
    `regiao` VARCHAR(150) DEFAULT 'Sobradinho',
    `cidade` VARCHAR(150) DEFAULT 'Brasília/DF',
    `lat` DECIMAL(10, 8) NOT NULL DEFAULT -15.65340000,
    `lng` DECIMAL(11, 8) NOT NULL DEFAULT -47.78910000,
    `descricao` TEXT NOT NULL,
    `contribuicao` TEXT DEFAULT NULL,
    `endereco` VARCHAR(255) DEFAULT NULL,
    `contato` VARCHAR(120) DEFAULT NULL,
    `instagram` VARCHAR(150) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `tiktok` VARCHAR(150) DEFAULT NULL,
    `whatsapp` VARCHAR(100) DEFAULT NULL,
    `foto` VARCHAR(255) DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `imagem` VARCHAR(255) DEFAULT NULL,
    `autor_id` INT DEFAULT NULL,
    `autor_nome` VARCHAR(120) DEFAULT 'Administrador',
    `destaque` TINYINT(1) DEFAULT 0,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`autor_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_map_regiao` (`regiao`),
    INDEX `idx_map_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABELA DE BANCO DE TALENTOS LOCAIS
CREATE TABLE IF NOT EXISTS `talentos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(120) NOT NULL,
    `funcao` VARCHAR(100) NOT NULL,
    `linguagem` VARCHAR(80) DEFAULT NULL,
    `habilidades` VARCHAR(255) DEFAULT NULL,
    `bairro` VARCHAR(80) NOT NULL,
    `bio` TEXT,
    `disponibilidade` ENUM('Disponível para projetos', 'Em formação', 'Oficineiro') DEFAULT 'Disponível para projetos',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `whatsapp` VARCHAR(30) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `portfolio` VARCHAR(255) DEFAULT NULL,
    `verificado` TINYINT(1) DEFAULT 0,
    `destaque` TINYINT(1) DEFAULT 0,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABELA DE EIXOS DA TRILHA / CURSOS (Estilo Moodle)
CREATE TABLE IF NOT EXISTS `trilhas_eixos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_eixo` INT NOT NULL UNIQUE,
    `titulo` VARCHAR(150) NOT NULL,
    `subtitulo` VARCHAR(255) DEFAULT NULL,
    `carga_horaria` VARCHAR(30) NOT NULL DEFAULT '20 Horas',
    `cor` VARCHAR(30) DEFAULT '#0D5BA8',
    `icone` VARCHAR(40) DEFAULT 'compass',
    `descricao` TEXT,
    `ordem` INT DEFAULT 1,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABELA DE MISSÕES / CAPÍTULOS DA TRILHA
CREATE TABLE IF NOT EXISTS `trilhas_missoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `eixo_id` INT NOT NULL,
    `numero_missao` INT NOT NULL,
    `titulo` VARCHAR(180) NOT NULL,
    `descricao_curta` VARCHAR(255) NOT NULL,
    `descricao_completa` TEXT NOT NULL,
    `duracao_horas` INT DEFAULT 4,
    `prazo` VARCHAR(60) DEFAULT NULL,
    `entregas_requeridas` TEXT,
    `ordem` INT DEFAULT 1,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`eixo_id`) REFERENCES `trilhas_eixos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABELA DE PROGRESSO DO ALUNO (Inscrições e Entregas)
CREATE TABLE IF NOT EXISTS `progresso_aluno` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aluno_id` INT NOT NULL,
    `missao_id` INT NOT NULL,
    `status` ENUM('bloqueado', 'em_andamento', 'concluido') NOT NULL DEFAULT 'em_andamento',
    `trabalho_titulo` VARCHAR(200) DEFAULT NULL,
    `trabalho_descricao` TEXT,
    `trabalho_arquivo` VARCHAR(255) DEFAULT NULL,
    `feedback` TEXT,
    `data_submissao` DATETIME DEFAULT NULL,
    `data_conclusao` DATETIME DEFAULT NULL,
    FOREIGN KEY (`aluno_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`missao_id`) REFERENCES `trilhas_missoes`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_aluno_missao` (`aluno_id`, `missao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABELA DE INSÍGNIAS E SELOS DE RECONHECIMENTO
CREATE TABLE IF NOT EXISTS `selos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(100) NOT NULL,
    `descricao` VARCHAR(255) NOT NULL,
    `icone` VARCHAR(40) DEFAULT 'award',
    `cor` VARCHAR(40) DEFAULT 'bg-cyan-500'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aluno_selos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aluno_id` INT NOT NULL,
    `selo_id` INT NOT NULL,
    `data_conquista` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`aluno_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`selo_id`) REFERENCES `selos`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_aluno_selo` (`aluno_id`, `selo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- SEED INICIAL: Administrador 'Daniel' e Aluno 'Ana Silva'
-- ====================================================================
-- Senha do Admin Daniel: admin123
-- Senha da Aluna Ana Silva: aluno123
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel`, `cidade`, `avatar`, `biografia`) VALUES
('Daniel', 'daniel@fotocidade.org', '$2y$10$OqXG3tF5g1n1q0m8e/d5eO1q5F8A2m4C6E8G0I2K4M6O8Q0S2U4W.', 'admin', 'Sobradinho I', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=300&q=80', 'Coordenador Geral e Administrador da Plataforma FotoCidade DF.'),
('Ana Silva', 'ana.silva@fotocidade.org', '$2y$10$T8Z6M3c8x9Y1w0v7u6t5rO1q5F8A2m4C6E8G0I2K4M6O8Q0S2U4W.', 'aluno', 'Ceilândia', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80', 'Fotógrafa comunitária e estudante de comunicação em Ceilândia. Apaixonada por registrar a arquitetura popular e histórias vivas da periferia.');
