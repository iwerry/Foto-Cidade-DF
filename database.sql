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

-- 2. TABELA DA VITRINE CULTURAL / BLOG (Posts de fotos, vídeos, áudios, ensaios)
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo` VARCHAR(60) NOT NULL DEFAULT 'Foto',
    `titulo` VARCHAR(255) NOT NULL,
    `subtitulo` VARCHAR(255) DEFAULT NULL,
    `descricao` TEXT NOT NULL,
    `conteudo` LONGTEXT DEFAULT NULL,
    `autor_id` INT DEFAULT NULL,
    `autor_nome` VARCHAR(150) NOT NULL DEFAULT 'FotoCidade DF',
    `autor_avatar` VARCHAR(255) DEFAULT 'assets/images/avatar-default.jpg',
    `autor_role` VARCHAR(100) DEFAULT 'Administrador',
    `bairro` VARCHAR(150) NOT NULL DEFAULT 'Sobradinho',
    `cidade` VARCHAR(150) DEFAULT 'Brasília/DF',
    `imagem` VARCHAR(255) DEFAULT NULL,
    `media_url` VARCHAR(255) DEFAULT NULL,
    `thumbnail_url` VARCHAR(255) DEFAULT NULL,
    `video_url` VARCHAR(255) DEFAULT NULL,
    `audio_url` VARCHAR(255) DEFAULT NULL,
    `tags` VARCHAR(255) DEFAULT 'Foto',
    `likes` INT DEFAULT 0,
    `compartilhamentos` INT DEFAULT 0,
    `destaque` TINYINT(1) DEFAULT 0,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_post_tipo` (`tipo`),
    INDEX `idx_post_bairro` (`bairro`)
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

-- 6. TABELA DE TRILHAS FORMATIVAS (trilhas_cursos)
CREATE TABLE IF NOT EXISTS `trilhas_cursos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `eixo` VARCHAR(100) DEFAULT NULL,
    `carga_horaria` VARCHAR(50) DEFAULT NULL,
    `ordem` INT DEFAULT 0,
    `status` ENUM('ativo', 'inativo', 'rascunho') DEFAULT 'ativo',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABELA DE CURSOS (Vinculados a uma Trilha)
CREATE TABLE IF NOT EXISTS `cursos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trilha_id` INT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `capa_url` VARCHAR(255) DEFAULT NULL,
    `carga_horaria` VARCHAR(50) DEFAULT NULL,
    `professor_nome` VARCHAR(150) DEFAULT 'FotoCidade DF',
    `ordem` INT DEFAULT 0,
    `status` ENUM('ativo', 'inativo', 'rascunho') DEFAULT 'rascunho',
    `data_criacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trilha_id`) REFERENCES `trilhas_cursos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABELA DE MÓDULOS (Dentro de cada Curso)
CREATE TABLE IF NOT EXISTS `modulos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `curso_id` INT NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT DEFAULT NULL,
    `ordem` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABELA DE AULAS (Com Conteúdo Rico, Vídeos e Status)
CREATE TABLE IF NOT EXISTS `aulas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `modulo_id` INT NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `conteudo` LONGTEXT,
    `tipo_video` ENUM('youtube', 'vimeo', 'upload', 'nenhum') DEFAULT 'nenhum',
    `url_video` VARCHAR(255) DEFAULT NULL,
    `duracao_minutos` INT DEFAULT 0,
    `ordem` INT DEFAULT 0,
    `status` ENUM('publicado', 'rascunho') DEFAULT 'publicado',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`modulo_id`) REFERENCES `modulos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABELA DE ANEXOS / DOWNLOADS DAS AULAS (PDFs, Planilhas, Documentos)
CREATE TABLE IF NOT EXISTS `anexos_aulas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aula_id` INT NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `url_arquivo` VARCHAR(255) NOT NULL,
    `tamanho_bytes` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`aula_id`) REFERENCES `aulas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. TABELAS DE QUIZZES (Opcional)
CREATE TABLE IF NOT EXISTS `quizzes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aula_id` INT NULL,
    `modulo_id` INT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_perguntas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` INT NOT NULL,
    `pergunta` TEXT NOT NULL,
    `opcoes` LONGTEXT NOT NULL,
    `resposta_correta` VARCHAR(255) NOT NULL,
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. TABELA DE PROGRESSO DO ALUNO NAS AULAS
CREATE TABLE IF NOT EXISTS `progresso_aluno_aulas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aluno_id` INT NOT NULL,
    `aula_id` INT NOT NULL,
    `concluida` TINYINT(1) DEFAULT 1,
    `data_conclusao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`aluno_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`aula_id`) REFERENCES `aulas`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_aluno_aula` (`aluno_id`, `aula_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


