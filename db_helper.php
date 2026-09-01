<?php
/**
 * FotoCidade DF - DB Helper (Camada Híbrida de Acesso a Dados: MySQL PDO + JSON Fallback)
 *
 * Fornece dados dinâmicos diretamente do banco de dados MySQL via PDO,
 * com fallback inteligente para os arquivos locais /data/*.json caso o banco esteja offline.
 */

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_helper.php';

/**
 * Lê e decodifica um arquivo JSON localizado na pasta /data (Fallback)
 */
function get_json_data($filename) {
    $primaryPath = ROOT_PATH . '/data/' . $filename . '.json';
    $fallbackPath = ROOT_PATH . '/src/data/' . $filename . '.json';

    if (file_exists($primaryPath)) {
        $content = file_get_contents($primaryPath);
        return json_decode($content, true) ?? [];
    } elseif (file_exists($fallbackPath)) {
        $content = file_get_contents($fallbackPath);
        return json_decode($content, true) ?? [];
    }

    return [];
}

/**
 * Obtém os dados do perfil do aluno/usuário ativo
 */
function get_user_profile($userId = null) {
    $pdo = get_db_connection();
    
    if ($userId === null && isset($_SESSION['usuario_id'])) {
        $userId = $_SESSION['usuario_id'];
    }

    $defaultProfile = [
        'id' => $userId ?: 0,
        'nome' => $_SESSION['usuario_nome'] ?? 'Aluno FotoCidade',
        'email' => $_SESSION['usuario_email'] ?? '',
        'nivel' => $_SESSION['usuario_nivel'] ?? 'aluno',
        'avatar' => $_SESSION['usuario_avatar'] ?? 'assets/images/avatar-default.jpg',
        'biografia' => 'Agente cultural do FotoCidade DF.',
        'cidade' => $_SESSION['usuario_cidade'] ?? 'Sobradinho',
        'eixoPrincipal' => 'Mapeamento & Fotografia',
        'pontosCadastrados' => 0,
        'oficinasConcluidas' => 0,
        'totalOficinas' => 8,
        'progressoTrilha' => 0,
        'interesses' => ['Fotografia de Rua', 'Mapeamento Territorial', 'Design Gráfico', 'Comunicação Comunitária'],
        'selos' => [
            ['id' => 's1', 'titulo' => 'Olhar Territorial', 'descricao' => 'Concluiu o módulo de Cartografia Afetiva', 'icone' => 'map-pin', 'cor' => 'bg-cyan-500'],
            ['id' => 's2', 'titulo' => 'Mestre da Lente', 'descricao' => 'Publicou ensaios na Vitrine Cultural', 'icone' => 'camera', 'cor' => 'bg-amber-500'],
            ['id' => 's3', 'titulo' => 'Voz Comunitária', 'descricao' => 'Participou de pautas da cidade', 'icone' => 'award', 'cor' => 'bg-indigo-500']
        ]
    ];

    $pdo = get_db_connection();
    if ($pdo && $userId !== null) {
        try {
            $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
            $stmt = $pdo->prepare("SELECT * FROM `{$tableUsers}` WHERE id = ?");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();
            if ($u) {
                $avatarVal = !empty($u['avatar']) ? $u['avatar'] : (!empty($u['foto']) ? $u['foto'] : 'assets/images/avatar-default.jpg');
                $defaultProfile['id'] = $u['id'];
                $defaultProfile['nome'] = $u['nome'] ?? ($u['username'] ?? 'Usuário');
                $defaultProfile['email'] = $u['email'] ?? ($u['username'] ?? '');
                $defaultProfile['nivel'] = $u['nivel'] ?? 'aluno';
                $defaultProfile['avatar'] = $avatarVal;
                $defaultProfile['biografia'] = !empty($u['biografia']) ? $u['biografia'] : 'Agente cultural do FotoCidade DF.';
                $defaultProfile['cidade'] = !empty($u['cidade']) ? $u['cidade'] : 'Sobradinho';
            }
        } catch (Exception $e) {
            error_log("DB get_user_profile error: " . $e->getMessage());
        }
    }

    return $defaultProfile;
}

/**
 * Garante que a tabela `posts` / `vitrine` exista com todas as colunas necessárias para blog/mídias
 */
function ensure_vitrine_and_posts_columns($pdo) {
    static $checked = false;
    if ($checked || !$pdo) return;
    $checked = true;

    try {
        $tableVitrine = get_existing_table_name($pdo, 'posts', 'vitrine');
        if (empty($tableVitrine)) {
            $tableVitrine = 'posts';
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$tableVitrine}` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Verifica e adiciona colunas caso a tabela já existisse de forma simplificada
        $stmtCols = $pdo->query("SHOW COLUMNS FROM `{$tableVitrine}`");
        $existingCols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

        $neededCols = [
            'tipo' => "VARCHAR(60) NOT NULL DEFAULT 'Foto'",
            'titulo' => "VARCHAR(255) NOT NULL",
            'subtitulo' => "VARCHAR(255) DEFAULT NULL",
            'descricao' => "TEXT NOT NULL",
            'conteudo' => "LONGTEXT DEFAULT NULL",
            'autor_id' => "INT DEFAULT NULL",
            'autor_nome' => "VARCHAR(150) NOT NULL DEFAULT 'FotoCidade DF'",
            'autor_avatar' => "VARCHAR(255) DEFAULT 'assets/images/avatar-default.jpg'",
            'autor_role' => "VARCHAR(100) DEFAULT 'Administrador'",
            'bairro' => "VARCHAR(150) NOT NULL DEFAULT 'Sobradinho'",
            'cidade' => "VARCHAR(150) DEFAULT 'Brasília/DF'",
            'imagem' => "VARCHAR(255) DEFAULT NULL",
            'media_url' => "VARCHAR(255) DEFAULT NULL",
            'thumbnail_url' => "VARCHAR(255) DEFAULT NULL",
            'video_url' => "VARCHAR(255) DEFAULT NULL",
            'audio_url' => "VARCHAR(255) DEFAULT NULL",
            'tags' => "VARCHAR(255) DEFAULT 'Foto'",
            'likes' => "INT DEFAULT 0",
            'compartilhamentos' => "INT DEFAULT 0",
            'destaque' => "TINYINT(1) DEFAULT 0",
            'ativo' => "TINYINT(1) DEFAULT 1",
            'criado_em' => "DATETIME DEFAULT CURRENT_TIMESTAMP",
            'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP",
            'updated_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];

        foreach ($neededCols as $colName => $colDef) {
            if (!in_array($colName, $existingCols)) {
                try {
                    $pdo->exec("ALTER TABLE `{$tableVitrine}` ADD COLUMN `{$colName}` {$colDef}");
                } catch (Exception $eCol) {}
            }
        }
    } catch (Exception $e) {
        error_log("Ensure Vitrine / Posts error: " . $e->getMessage());
    }
}

/**
 * Obtém todos os itens da Vitrine Cultural a partir do banco de dados MySQL
 */
function get_vitrine() {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            ensure_vitrine_and_posts_columns($pdo);
            $tableVitrine = get_existing_table_name($pdo, 'posts', 'vitrine');
            
            $stmt = $pdo->query("SELECT * FROM `{$tableVitrine}` WHERE `ativo` = 1 ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            $items = [];
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $tagsArray = !empty($r['tags']) ? array_map('trim', explode(',', $r['tags'])) : ['Foto'];
                    $mediaUrl = !empty($r['media_url']) ? $r['media_url'] : (!empty($r['imagem']) ? $r['imagem'] : (!empty($r['thumbnail_url']) ? $r['thumbnail_url'] : 'assets/images/oficina-olhar-fercal.jpg'));
                    $thumbUrl = !empty($r['thumbnail_url']) ? $r['thumbnail_url'] : $mediaUrl;
                    $autorAvt = !empty($r['autor_avatar']) ? $r['autor_avatar'] : 'assets/images/avatar-default.jpg';

                    $items[] = [
                        'id' => (int)$r['id'],
                        'tipo' => $r['tipo'] ?? 'Foto',
                        'titulo' => $r['titulo'],
                        'subtitulo' => $r['subtitulo'] ?? '',
                        'descricao' => $r['descricao'] ?? '',
                        'conteudo' => $r['conteudo'] ?? '',
                        'autorNome' => $r['autor_nome'] ?? 'FotoCidade DF',
                        'autorAvatar' => $autorAvt,
                        'autorRole' => $r['autor_role'] ?? 'Administrador',
                        'bairro' => $r['bairro'] ?? 'Sobradinho',
                        'cidade' => $r['cidade'] ?? 'Brasília/DF',
                        'mediaUrl' => $mediaUrl,
                        'imagem' => $mediaUrl,
                        'thumbnailUrl' => $thumbUrl,
                        'videoUrl' => $r['video_url'] ?? '',
                        'audioUrl' => $r['audio_url'] ?? '',
                        'dataPublicacao' => date('d/m/Y', strtotime($r['criado_em'] ?? ($r['created_at'] ?? 'now'))),
                        'likes' => (int)($r['likes'] ?? 0),
                        'compartilhamentos' => (int)($r['compartilhamentos'] ?? 0),
                        'destaque' => isset($r['destaque']) && $r['destaque'] ? true : false,
                        'tags' => $tagsArray
                    ];
                }
            }
            return $items;
        } catch (Exception $e) {
            error_log("DB get_vitrine error: " . $e->getMessage());
        }
    }

    return [];
}

/**
 * Garante que a tabela `map_locais` e `parceiros` possuam todas as colunas necessárias
 */
function ensure_parceiros_and_map_columns($pdo) {
    static $checked = false;
    if ($checked || !$pdo) return;
    $checked = true;

    try {
        // Tabela principal: map_locais (ou pontos_mapa)
        $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');
        $stmtMapCols = $pdo->query("SHOW COLUMNS FROM `{$tableMap}`");
        $mapCols = $stmtMapCols->fetchAll(PDO::FETCH_COLUMN);

        // Garante que regiao e categoria sejam VARCHAR para aceitar qualquer RA, cidade ou categoria
        try {
            $pdo->exec("ALTER TABLE `{$tableMap}` MODIFY COLUMN `regiao` VARCHAR(150) NULL");
        } catch (Exception $eReg) {}
        try {
            $pdo->exec("ALTER TABLE `{$tableMap}` MODIFY COLUMN `categoria` VARCHAR(100) NULL");
        } catch (Exception $eCat) {}

        if (!in_array('categoria', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `categoria` VARCHAR(100) NULL");
        }
        if (!in_array('tipo', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `tipo` VARCHAR(100) NULL");
        }
        if (!in_array('setor', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `setor` VARCHAR(100) NULL");
        }
        if (!in_array('bairro', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `bairro` VARCHAR(150) NULL");
        }
        if (!in_array('regiao', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `regiao` VARCHAR(150) NULL");
        }
        if (!in_array('cidade', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `cidade` VARCHAR(150) NULL DEFAULT 'Brasília/DF'");
        }
        if (!in_array('lat', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `lat` DECIMAL(10,8) NULL DEFAULT -15.65340000");
        }
        if (!in_array('lng', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `lng` DECIMAL(11,8) NULL DEFAULT -47.78910000");
        }
        if (!in_array('descricao', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `descricao` TEXT NULL");
        }
        if (!in_array('contribuicao', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `contribuicao` TEXT NULL");
        }
        if (!in_array('endereco', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `endereco` VARCHAR(255) NULL");
        }
        if (!in_array('contato', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `contato` VARCHAR(120) NULL");
        }
        if (!in_array('instagram', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `instagram` VARCHAR(150) NULL");
        }
        if (!in_array('website', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `website` VARCHAR(255) NULL");
        }
        if (!in_array('tiktok', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `tiktok` VARCHAR(150) NULL");
        }
        if (!in_array('whatsapp', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `whatsapp` VARCHAR(100) NULL");
        }
        if (!in_array('foto', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `foto` VARCHAR(255) NULL");
        }
        if (!in_array('logo', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `logo` VARCHAR(255) NULL");
        }
        if (!in_array('imagem', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `imagem` VARCHAR(255) NULL");
        }
        if (!in_array('destaque', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `destaque` TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!in_array('ativo', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
        }
        if (!in_array('autor_id', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `autor_id` INT NULL");
        }
        if (!in_array('autor_nome', $mapCols)) {
            $pdo->exec("ALTER TABLE `{$tableMap}` ADD COLUMN `autor_nome` VARCHAR(120) NULL DEFAULT 'Administrador'");
        }

        // Migração suave de dados legados da tabela parceiros para map_locais (apenas se existirem e ainda não estiverem em map_locais)
        try {
            $tableParc = get_existing_table_name($pdo, 'parceiros');
            $stmtCheckParc = $pdo->query("SELECT * FROM `{$tableParc}`");
            $parcRows = $stmtCheckParc->fetchAll();
            if (!empty($parcRows)) {
                $stmtCheckMap = $pdo->prepare("SELECT COUNT(*) FROM `{$tableMap}` WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?))");
                $stmtInsertMap = $pdo->prepare("INSERT INTO `{$tableMap}` (nome, categoria, tipo, setor, bairro, regiao, cidade, lat, lng, descricao, contribuicao, website, instagram, tiktok, whatsapp, foto, logo, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                
                foreach ($parcRows as $p) {
                    $stmtCheckMap->execute([$p['nome']]);
                    if ((int)$stmtCheckMap->fetchColumn() === 0) {
                        $pLat = !empty($p['lat']) ? (float)$p['lat'] : -15.6534;
                        $pLng = !empty($p['lng']) ? (float)$p['lng'] : -47.7891;
                        $pTipo = $p['tipo'] ?? ($p['categoria'] ?? 'Governo');
                        $pSetor = $p['setor'] ?? '1º Setor (Poder Público)';
                        $pBairro = $p['bairro'] ?? 'Sobradinho';
                        $pCidade = $p['cidade'] ?? 'Brasília/DF';
                        $pImg = !empty($p['imagem']) ? $p['imagem'] : (!empty($p['logo']) ? $p['logo'] : 'assets/images/logo.png');

                        $stmtInsertMap->execute([
                            $p['nome'],
                            $pTipo,
                            $pTipo,
                            $pSetor,
                            $pBairro,
                            $pBairro,
                            $pCidade,
                            $pLat,
                            $pLng,
                            $p['descricao'] ?? '',
                            $p['contribuicao'] ?? '',
                            $p['website'] ?? ($p['link'] ?? ($p['site'] ?? '')),
                            $p['instagram'] ?? '',
                            $p['tiktok'] ?? '',
                            $p['whatsapp'] ?? '',
                            $pImg,
                            $pImg
                        ]);
                    }
                }
            }
        } catch (Exception $eLegacy) {}

    } catch (Exception $e) {
        error_log("Parceiros & Map migration error: " . $e->getMessage());
    }
}

/**
 * Obtém pontos culturais para o Mapa Leaflet diretamente da tabela unificada MySQL `map_locais`
 */
function get_map_data() {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            ensure_parceiros_and_map_columns($pdo);
            $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');

            $points = [];

            // Pontos únicos da tabela map_locais
            $stmtMap = $pdo->query("SELECT * FROM `{$tableMap}` WHERE `ativo` = 1 ORDER BY id DESC");
            $rowsMap = $stmtMap->fetchAll();
            if (!empty($rowsMap)) {
                foreach ($rowsMap as $r) {
                    $reg = !empty($r['regiao']) ? $r['regiao'] : (!empty($r['bairro']) ? $r['bairro'] : 'Sobradinho');
                    if ($reg === 'Sobradinho I') $reg = 'Sobradinho';

                    $fotoImg = !empty($r['foto']) ? $r['foto'] : (!empty($r['imagem']) ? $r['imagem'] : (!empty($r['logo']) ? $r['logo'] : 'assets/images/oficina-olhar-fercal.jpg'));

                    $lat = isset($r['lat']) && $r['lat'] !== null && $r['lat'] !== '' ? (float)$r['lat'] : -15.6534;
                    $lng = isset($r['lng']) && $r['lng'] !== null && $r['lng'] !== '' ? (float)$r['lng'] : -47.7891;

                    $points[] = [
                        'id' => (int)$r['id'],
                        'origem_id' => (int)$r['id'],
                        'origem_tipo' => 'map_locais',
                        'nome' => $r['nome'],
                        'categoria' => $r['categoria'] ?? ($r['tipo'] ?? 'Espaço Cultural'),
                        'tipo' => $r['tipo'] ?? ($r['categoria'] ?? 'Espaço Cultural'),
                        'setor' => $r['setor'] ?? 'Comunidade & Cultura',
                        'bairro' => $r['bairro'] ?? 'Sobradinho',
                        'cidade' => $r['cidade'] ?? 'Brasília/DF',
                        'regiao' => $reg,
                        'regiaoAdministrativa' => $reg,
                        'lat' => $lat,
                        'lng' => $lng,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'descricao' => $r['descricao'] ?? '',
                        'contribuicao' => $r['contribuicao'] ?? '',
                        'endereco' => $r['endereco'] ?? '',
                        'contato' => !empty($r['contato']) ? $r['contato'] : ($r['whatsapp'] ?? ''),
                        'instagram' => $r['instagram'] ?? '',
                        'website' => !empty($r['website']) ? $r['website'] : ($r['site'] ?? ($r['link'] ?? '')),
                        'tiktok' => $r['tiktok'] ?? '',
                        'whatsapp' => $r['whatsapp'] ?? '',
                        'foto' => $fotoImg,
                        'imagem' => $fotoImg,
                        'imagemUrl' => $fotoImg,
                        'thumbnailUrl' => $fotoImg,
                        'logo' => $fotoImg,
                        'autorRegistro' => !empty($r['autor_nome']) ? $r['autor_nome'] : 'FotoCidade DF',
                        'destaque' => isset($r['destaque']) && $r['destaque'] ? true : false
                    ];
                }
            }

            return $points;
        } catch (Exception $e) {
            error_log("DB get_map_data error: " . $e->getMessage());
        }
    }

    return [];
}

/**
 * Obtém parceiros e organizações a partir da base unificada `map_locais`
 */
function get_parceiros() {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            ensure_parceiros_and_map_columns($pdo);
            $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');

            $stmt = $pdo->query("SELECT * FROM `{$tableMap}` WHERE `ativo` = 1 ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            $items = [];
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $fotoImg = !empty($r['logo']) ? $r['logo'] : (!empty($r['foto']) ? $r['foto'] : (!empty($r['imagem']) ? $r['imagem'] : 'assets/images/logo.png'));
                    $items[] = [
                        'id' => (int)$r['id'],
                        'nome' => $r['nome'],
                        'setor' => $r['setor'] ?? '1º Setor (Poder Público)',
                        'tipo' => $r['tipo'] ?? ($r['categoria'] ?? 'Parceiro'),
                        'categoria' => $r['categoria'] ?? ($r['tipo'] ?? 'Parceiro'),
                        'bairro' => $r['bairro'] ?? ($r['regiao'] ?? 'Sobradinho'),
                        'cidade' => $r['cidade'] ?? 'Brasília/DF',
                        'regiao' => $r['regiao'] ?? ($r['bairro'] ?? 'Sobradinho'),
                        'lat' => $r['lat'] ?? null,
                        'lng' => $r['lng'] ?? null,
                        'descricao' => $r['descricao'] ?? '',
                        'contribuicao' => $r['contribuicao'] ?? '',
                        'logo' => $fotoImg,
                        'foto' => $fotoImg,
                        'site' => !empty($r['website']) ? $r['website'] : ($r['site'] ?? ($r['link'] ?? '')),
                        'website' => !empty($r['website']) ? $r['website'] : ($r['site'] ?? ($r['link'] ?? '')),
                        'instagram' => $r['instagram'] ?? '',
                        'tiktok' => $r['tiktok'] ?? '',
                        'whatsapp' => $r['whatsapp'] ?? '',
                        'contatoEmail' => $r['contato'] ?? ''
                    ];
                }
            }
            return $items;
        } catch (Exception $e) {
            error_log("DB get_parceiros error: " . $e->getMessage());
        }
    }

    return [];
}

/**
 * Garante que a tabela `users` possua as colunas necessárias para o Banco de Talentos e Redes Sociais
 */
function ensure_user_talento_columns($pdo) {
    static $checked = false;
    if ($checked || !$pdo) return;
    $checked = true;

    try {
        $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
        $stmtCols = $pdo->query("SHOW COLUMNS FROM `{$tableUsers}`");
        $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('no_banco_talentos', $cols)) {
            $pdo->exec("ALTER TABLE `{$tableUsers}` ADD COLUMN `no_banco_talentos` TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!in_array('categoria_talento', $cols)) {
            $pdo->exec("ALTER TABLE `{$tableUsers}` ADD COLUMN `categoria_talento` VARCHAR(255) NULL");
        }
        if (!in_array('instagram', $cols)) {
            $pdo->exec("ALTER TABLE `{$tableUsers}` ADD COLUMN `instagram` VARCHAR(150) NULL");
        }
        if (!in_array('linkedin', $cols)) {
            $pdo->exec("ALTER TABLE `{$tableUsers}` ADD COLUMN `linkedin` VARCHAR(255) NULL");
        }
        if (!in_array('tiktok', $cols)) {
            $pdo->exec("ALTER TABLE `{$tableUsers}` ADD COLUMN `tiktok` VARCHAR(150) NULL");
        }
    } catch (Exception $e) {
        error_log("Colunas talento error: " . $e->getMessage());
    }
}

/**
 * Obtém o banco de talentos (Apenas usuários REAIS cadastrados que ativaram no_banco_talentos = 1)
 */
function get_talentos() {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            ensure_user_talento_columns($pdo);
            $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
            
            // Busca apenas usuários reais ativados para o Banco de Talentos
            $stmt = $pdo->query("SELECT * FROM `{$tableUsers}` WHERE `no_banco_talentos` = 1 ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            $items = [];
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $habArray = !empty($r['categoria_talento']) ? array_map('trim', explode(',', $r['categoria_talento'])) : ['Agente Cultural'];
                    $avatarVal = !empty($r['avatar']) ? $r['avatar'] : (!empty($r['foto']) ? $r['foto'] : 'assets/images/avatar-default.jpg');
                    
                    $items[] = [
                        'id' => $r['id'],
                        'nome' => $r['nome'] ?? ($r['username'] ?? 'Agente Cultural'),
                        'areaAtuacao' => ($r['nivel'] ?? '') === 'admin' ? 'Coordenador / Admin' : 'Talento Local',
                        'regiaoAdministrativa' => (!empty($r['bairro']) ? $r['bairro'] . ' • ' : '') . ($r['cidade'] ?? 'DF'),
                        'habilidades' => $habArray,
                        'bio' => !empty($r['biografia']) ? $r['biografia'] : 'Agente cultural cadastrado na rede FotoCidade DF.',
                        'disponibilidade' => 'Disponível para projetos',
                        'fotoUrl' => $avatarVal,
                        'telefone' => $r['telefone'] ?? '',
                        'email' => $r['email'] ?? '',
                        'instagram' => $r['instagram'] ?? '',
                        'linkedin' => $r['linkedin'] ?? '',
                        'tiktok' => $r['tiktok'] ?? ''
                    ];
                }
            }
            return $items;
        } catch (Exception $e) {
            error_log("DB get_talentos error: " . $e->getMessage());
        }
    }

    return [];
}

function get_artistas() {
    return get_talentos();
}

/**
 * Obtém os eixos formativos e capítulos/missões (Estilo Moodle)
 */
function get_trilhas() {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            $tableEixos = get_existing_table_name($pdo, 'trilhas_eixos', 'trilhas_cursos');
            $tableMissoes = get_existing_table_name($pdo, 'trilhas_missoes');

            $stmtEixos = $pdo->query("SELECT * FROM `{$tableEixos}` ORDER BY id ASC");
            $eixos = $stmtEixos->fetchAll();
            if (!empty($eixos)) {
                $result = [];

                foreach ($eixos as $e) {
                    $missoes = [];
                    try {
                        $stmtMissoes = $pdo->prepare("SELECT * FROM `{$tableMissoes}` WHERE eixo_id = ? ORDER BY id ASC");
                        $stmtMissoes->execute([$e['id']]);
                        $missoesRows = $stmtMissoes->fetchAll();

                        foreach ($missoesRows as $m) {
                            $missoes[] = [
                                'id' => $m['id'],
                                'numero' => $m['numero_missao'] ?? 1,
                                'titulo' => $m['titulo'] ?? 'Missão',
                                'descricaoCurta' => $m['descricao_curta'] ?? '',
                                'descricaoCompleta' => $m['descricao_completa'] ?? '',
                                'duracaoHoras' => (int)($m['duracao_horas'] ?? 4),
                                'prazo' => $m['prazo'] ?? '7 dias',
                                'entregasRequeridas' => $m['entregas_requeridas'] ?? 'Relatório e fotos',
                                'status' => 'em_andamento'
                            ];
                        }
                    } catch (Exception $e2) {}

                    $result[] = [
                        'id' => (int)($e['numero_eixo'] ?? $e['id']),
                        'db_id' => $e['id'],
                        'titulo' => $e['titulo'],
                        'subtitulo' => $e['subtitulo'] ?? '',
                        'cargaHoraria' => $e['carga_horaria'] ?? '20 Horas',
                        'cor' => $e['cor'] ?? '#0D5BA8',
                        'icone' => $e['icone'] ?? 'compass',
                        'descricao' => $e['descricao'] ?? '',
                        'missoes' => $missoes
                    ];
                }
                return $result;
            }
        } catch (Exception $e) {
            error_log("DB get_trilhas error: " . $e->getMessage());
        }
    }

    return get_json_data('trilhas');
}
