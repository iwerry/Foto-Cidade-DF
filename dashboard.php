<?php
/**
 * FotoCidade DF - Painel de Gestão e Dashboard Administrativo (dashboard.php)
 *
 * Módulos completos com CRUDs em PHP: Vitrine, Usuários, Mapa Cultural com Leaflet,
 * Parceiros/Talentos e Gestão de Trilhas/Cursos estilo Moodle.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/upload_helper.php';

// Exige permissão de administrador
require_admin('login.php');

$currentUser = get_logged_user();
$pdo = get_db_connection();

$abaAtiva = $_GET['aba'] ?? 'visao_geral';
$mensagemSucesso = '';
$mensagemErro = '';

// -------------------------------------------------------------
// ENDPOINTS AJAX: BIBLIOTECA DE MÍDIAS (MEDIA LIBRARY /public)
// -------------------------------------------------------------
if (isset($_GET['api_action']) && $_GET['api_action'] === 'get_media_library') {
    header('Content-Type: application/json');
    $files = scan_public_media_library();
    echo json_encode(['success' => true, 'files' => $files]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_media_library') {
    if (isset($_FILES['media_file'])) {
        $result = upload_public_anexo($_FILES['media_file']);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}

// ====================================================================
// PROCESSAMENTO DE AÇÕES POST (CRUDs)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrfToken)) {
        $mensagemErro = 'Token de segurança expirado. Tente novamente.';
    } else {
        // -------------------------------------------------------------
        // 1. CRUD USUÁRIOS COMPLETO (ADMIN & ALUNOS)
        // -------------------------------------------------------------
        if ($action === 'salvar_usuario') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nome = trim($_POST['nome'] ?? '');
            $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
            $username = trim($_POST['username'] ?? '');
            if (empty($username)) {
                $username = !empty($email) ? explode('@', $email)[0] : preg_replace('/[^A-Za-z0-9_]/', '', strtolower($nome));
            }
            $senha = $_POST['senha'] ?? '';
            $nivel = ($_POST['nivel'] ?? 'aluno') === 'admin' ? 'admin' : 'aluno';
            $cidade = trim($_POST['cidade'] ?? 'Sobradinho');
            $bairro = trim($_POST['bairro'] ?? '');
            $endereco = trim($_POST['endereco'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $biografia = trim($_POST['biografia'] ?? '');
            $removerFoto = !empty($_POST['remover_foto']) && $_POST['remover_foto'] === '1';
            $avatar = $_POST['avatar_atual'] ?? '';

            // Se solicitou remover a foto
            if ($removerFoto) {
                if (!empty($avatar) && strpos($avatar, 'public/perfil/') !== false && file_exists(ROOT_PATH . '/' . $avatar)) {
                    @unlink(ROOT_PATH . '/' . $avatar);
                }
                $avatar = null;
            }
            // Se enviou uma nova foto de perfil
            elseif (isset($_FILES['avatar_arquivo']) && $_FILES['avatar_arquivo']['error'] === UPLOAD_ERR_OK) {
                $upload = upload_user_profile_photo($_FILES['avatar_arquivo'], $nome, $id ?: time());
                if ($upload['success']) {
                    $avatar = $upload['path'];
                } else {
                    $mensagemErro = $upload['message'];
                }
            }

            if (empty($mensagemErro)) {
                if ($pdo) {
                    try {
                        $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
                        $cols = $pdo->query("SHOW COLUMNS FROM `{$tableUsers}`")->fetchAll(PDO::FETCH_COLUMN);

                        if ($id) {
                            $fields = [];
                            $params = [];

                            if (in_array('nome', $cols)) { $fields[] = "`nome` = ?"; $params[] = $nome; }
                            if (in_array('email', $cols)) { $fields[] = "`email` = ?"; $params[] = $email; }
                            if (in_array('username', $cols)) { $fields[] = "`username` = ?"; $params[] = $username; }
                            if (!empty($senha) && in_array('senha', $cols)) {
                                $fields[] = "`senha` = ?";
                                $params[] = password_hash($senha, PASSWORD_BCRYPT);
                            }
                            if (in_array('nivel', $cols)) { $fields[] = "`nivel` = ?"; $params[] = $nivel; }
                            if (in_array('cidade', $cols)) { $fields[] = "`cidade` = ?"; $params[] = $cidade; }
                            if (in_array('bairro', $cols)) { $fields[] = "`bairro` = ?"; $params[] = $bairro; }
                            if (in_array('endereco', $cols)) { $fields[] = "`endereco` = ?"; $params[] = $endereco; }
                            if (in_array('telefone', $cols)) { $fields[] = "`telefone` = ?"; $params[] = $telefone; }
                            if (in_array('biografia', $cols)) { $fields[] = "`biografia` = ?"; $params[] = $biografia; }
                            if (in_array('avatar', $cols)) { $fields[] = "`avatar` = ?"; $params[] = $avatar; }
                            if (in_array('foto', $cols)) { $fields[] = "`foto` = ?"; $params[] = $avatar; }

                            if (!empty($fields)) {
                                $sql = "UPDATE `{$tableUsers}` SET " . implode(', ', $fields) . " WHERE `id` = ?";
                                $params[] = $id;
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute($params);
                                $mensagemSucesso = 'Usuário "' . htmlspecialchars($nome) . '" atualizado com sucesso!';
                            }
                        } else {
                            $fields = [];
                            $placeholders = [];
                            $params = [];

                            if (in_array('nome', $cols)) { $fields[] = "`nome`"; $placeholders[] = "?"; $params[] = $nome; }
                            if (in_array('email', $cols)) { $fields[] = "`email`"; $placeholders[] = "?"; $params[] = $email; }
                            if (in_array('username', $cols)) { $fields[] = "`username`"; $placeholders[] = "?"; $params[] = $username; }
                            if (in_array('senha', $cols)) {
                                $fields[] = "`senha`";
                                $placeholders[] = "?";
                                $senhaHash = password_hash(!empty($senha) ? $senha : 'mudar123', PASSWORD_BCRYPT);
                                $params[] = $senhaHash;
                            }
                            if (in_array('nivel', $cols)) { $fields[] = "`nivel`"; $placeholders[] = "?"; $params[] = $nivel; }
                            if (in_array('cidade', $cols)) { $fields[] = "`cidade`"; $placeholders[] = "?"; $params[] = $cidade; }
                            if (in_array('bairro', $cols)) { $fields[] = "`bairro`"; $placeholders[] = "?"; $params[] = $bairro; }
                            if (in_array('endereco', $cols)) { $fields[] = "`endereco`"; $placeholders[] = "?"; $params[] = $endereco; }
                            if (in_array('telefone', $cols)) { $fields[] = "`telefone`"; $placeholders[] = "?"; $params[] = $telefone; }
                            if (in_array('biografia', $cols)) { $fields[] = "`biografia`"; $placeholders[] = "?"; $params[] = $biografia; }
                            if (in_array('avatar', $cols)) { $fields[] = "`avatar`"; $placeholders[] = "?"; $params[] = $avatar; }
                            if (in_array('foto', $cols)) { $fields[] = "`foto`"; $placeholders[] = "?"; $params[] = $avatar; }

                            if (!empty($fields)) {
                                $sql = "INSERT INTO `{$tableUsers}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute($params);
                                $mensagemSucesso = 'Novo usuário cadastrado com sucesso!';
                            }
                        }

                        // Atualiza os dados do usuário autenticado no cabeçalho imediatamente
                        $currentUser = get_logged_user();
                    } catch (Exception $e) {
                        $mensagemErro = 'Erro ao salvar usuário: ' . $e->getMessage();
                        error_log($mensagemErro);
                    }
                } else {
                    global $lastDbError;
                    $mensagemErro = 'Não foi possível conectar ao banco de dados: ' . ($lastDbError ?? 'Verifique as credenciais.');
                }
            }
            $abaAtiva = 'usuarios';
        }

        elseif ($action === 'excluir_usuario') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $pdo && $id !== (int)$currentUser['id']) {
                try {
                    $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
                    
                    // Busca avatar para excluir arquivo se for local
                    try {
                        $stmtFind = $pdo->prepare("SELECT foto, avatar FROM `{$tableUsers}` WHERE id = ?");
                        $stmtFind->execute([$id]);
                        $found = $stmtFind->fetch();
                        $pic = $found['avatar'] ?? ($found['foto'] ?? '');
                        if (!empty($pic) && strpos($pic, 'public/perfil/') !== false && file_exists(ROOT_PATH . '/' . $pic)) {
                            @unlink(ROOT_PATH . '/' . $pic);
                        }
                    } catch (Exception $ePic) {}

                    $stmt = $pdo->prepare("DELETE FROM `{$tableUsers}` WHERE id = ?");
                    $stmt->execute([$id]);
                    $mensagemSucesso = 'Usuário excluído com sucesso do banco de dados!';
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir usuário: ' . $e->getMessage();
                }
            } elseif ($id === (int)$currentUser['id']) {
                $mensagemErro = 'Você não pode excluir sua própria conta enquanto logado.';
            }
            $abaAtiva = 'usuarios';
        }

        // -------------------------------------------------------------
        // 2. CRUD VITRINE CULTURAL / BLOG (BASE UNIFICADA posts / vitrine)
        // -------------------------------------------------------------
        elseif ($action === 'salvar_vitrine') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $titulo = trim($_POST['titulo'] ?? '');
            $subtitulo = trim($_POST['subtitulo'] ?? '');
            $tipo = $_POST['tipo'] ?? 'Foto';
            $cidade = trim($_POST['cidade'] ?? 'Brasília/DF');
            $bairro = trim($_POST['bairro'] ?? 'Sobradinho');
            $descricao = trim($_POST['descricao'] ?? '');
            $conteudo = trim($_POST['conteudo'] ?? '');
            $video_url = trim($_POST['video_url'] ?? '');
            $audio_url = trim($_POST['audio_url'] ?? '');
            $tags = trim($_POST['tags'] ?? $tipo);
            $autor_nome = trim($_POST['autor_nome'] ?? ($currentUser['nome'] ?? 'FotoCidade DF'));
            $autor_role = trim($_POST['autor_role'] ?? 'Administrador');
            $autor_avatar = $currentUser['avatar'] ?? 'assets/images/avatar-default.jpg';
            $destaque = isset($_POST['destaque']) ? 1 : 0;
            $media_url = $_POST['media_atual'] ?? 'assets/images/oficina-olhar-fercal.jpg';
            $redirect_to = $_POST['redirect_to'] ?? '';

            if (isset($_FILES['media_arquivo']) && $_FILES['media_arquivo']['error'] === UPLOAD_ERR_OK) {
                $upload = upload_vitrine_media($_FILES['media_arquivo'], $titulo, $id ?: time());
                if ($upload['success']) {
                    $media_url = $upload['path'];
                }
            }

            if ($pdo && !empty($titulo)) {
                try {
                    ensure_vitrine_and_posts_columns($pdo);
                    $tableVitrine = get_existing_table_name($pdo, 'posts', 'vitrine');
                    $cols = $pdo->query("SHOW COLUMNS FROM `{$tableVitrine}`")->fetchAll(PDO::FETCH_COLUMN);

                    if ($id) {
                        $fields = [];
                        $params = [];
                        if (in_array('titulo', $cols)) { $fields[] = "`titulo` = ?"; $params[] = $titulo; }
                        if (in_array('subtitulo', $cols)) { $fields[] = "`subtitulo` = ?"; $params[] = $subtitulo; }
                        if (in_array('tipo', $cols)) { $fields[] = "`tipo` = ?"; $params[] = $tipo; }
                        if (in_array('cidade', $cols)) { $fields[] = "`cidade` = ?"; $params[] = $cidade; }
                        if (in_array('bairro', $cols)) { $fields[] = "`bairro` = ?"; $params[] = $bairro; }
                        if (in_array('descricao', $cols)) { $fields[] = "`descricao` = ?"; $params[] = $descricao; }
                        if (in_array('conteudo', $cols)) { $fields[] = "`conteudo` = ?"; $params[] = $conteudo; }
                        if (in_array('media_url', $cols)) { $fields[] = "`media_url` = ?"; $params[] = $media_url; }
                        if (in_array('imagem', $cols)) { $fields[] = "`imagem` = ?"; $params[] = $media_url; }
                        if (in_array('thumbnail_url', $cols)) { $fields[] = "`thumbnail_url` = ?"; $params[] = $media_url; }
                        if (in_array('video_url', $cols)) { $fields[] = "`video_url` = ?"; $params[] = $video_url; }
                        if (in_array('audio_url', $cols)) { $fields[] = "`audio_url` = ?"; $params[] = $audio_url; }
                        if (in_array('tags', $cols)) { $fields[] = "`tags` = ?"; $params[] = $tags; }
                        if (in_array('autor_nome', $cols)) { $fields[] = "`autor_nome` = ?"; $params[] = $autor_nome; }
                        if (in_array('autor_role', $cols)) { $fields[] = "`autor_role` = ?"; $params[] = $autor_role; }
                        if (in_array('destaque', $cols)) { $fields[] = "`destaque` = ?"; $params[] = $destaque; }

                        if (!empty($fields)) {
                            $sql = "UPDATE `{$tableVitrine}` SET " . implode(', ', $fields) . " WHERE `id` = ?";
                            $params[] = $id;
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $mensagemSucesso = 'Publicação atualizada com sucesso na Vitrine Cultural!';
                        }
                    } else {
                        $fields = [];
                        $placeholders = [];
                        $params = [];

                        if (in_array('titulo', $cols)) { $fields[] = "`titulo`"; $placeholders[] = "?"; $params[] = $titulo; }
                        if (in_array('subtitulo', $cols)) { $fields[] = "`subtitulo`"; $placeholders[] = "?"; $params[] = $subtitulo; }
                        if (in_array('tipo', $cols)) { $fields[] = "`tipo`"; $placeholders[] = "?"; $params[] = $tipo; }
                        if (in_array('cidade', $cols)) { $fields[] = "`cidade`"; $placeholders[] = "?"; $params[] = $cidade; }
                        if (in_array('bairro', $cols)) { $fields[] = "`bairro`"; $placeholders[] = "?"; $params[] = $bairro; }
                        if (in_array('descricao', $cols)) { $fields[] = "`descricao`"; $placeholders[] = "?"; $params[] = $descricao; }
                        if (in_array('conteudo', $cols)) { $fields[] = "`conteudo`"; $placeholders[] = "?"; $params[] = $conteudo; }
                        if (in_array('media_url', $cols)) { $fields[] = "`media_url`"; $placeholders[] = "?"; $params[] = $media_url; }
                        if (in_array('imagem', $cols)) { $fields[] = "`imagem`"; $placeholders[] = "?"; $params[] = $media_url; }
                        if (in_array('thumbnail_url', $cols)) { $fields[] = "`thumbnail_url`"; $placeholders[] = "?"; $params[] = $media_url; }
                        if (in_array('video_url', $cols)) { $fields[] = "`video_url`"; $placeholders[] = "?"; $params[] = $video_url; }
                        if (in_array('audio_url', $cols)) { $fields[] = "`audio_url`"; $placeholders[] = "?"; $params[] = $audio_url; }
                        if (in_array('tags', $cols)) { $fields[] = "`tags`"; $placeholders[] = "?"; $params[] = $tags; }
                        if (in_array('autor_id', $cols)) { $fields[] = "`autor_id`"; $placeholders[] = "?"; $params[] = (int)($currentUser['id'] ?? 1); }
                        if (in_array('autor_nome', $cols)) { $fields[] = "`autor_nome`"; $placeholders[] = "?"; $params[] = $autor_nome; }
                        if (in_array('autor_avatar', $cols)) { $fields[] = "`autor_avatar`"; $placeholders[] = "?"; $params[] = $autor_avatar; }
                        if (in_array('autor_role', $cols)) { $fields[] = "`autor_role`"; $placeholders[] = "?"; $params[] = $autor_role; }
                        if (in_array('destaque', $cols)) { $fields[] = "`destaque`"; $placeholders[] = "?"; $params[] = $destaque; }
                        if (in_array('ativo', $cols)) { $fields[] = "`ativo`"; $placeholders[] = "?"; $params[] = 1; }

                        if (!empty($fields)) {
                            $sql = "INSERT INTO `{$tableVitrine}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $mensagemSucesso = 'Nova publicação criada com sucesso na Vitrine Cultural!';
                        }
                    }

                    if (!empty($redirect_to)) {
                        header("Location: " . $redirect_to . "?msg=sucesso");
                        exit;
                    }
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar post na Vitrine: ' . $e->getMessage();
                    error_log($mensagemErro);
                }
            }
            $abaAtiva = 'vitrine';
        }

        elseif ($action === 'excluir_vitrine') {
            $id = (int)($_POST['id'] ?? 0);
            $redirect_to = $_POST['redirect_to'] ?? '';
            if ($id && $pdo) {
                try {
                    ensure_vitrine_and_posts_columns($pdo);
                    $tableVitrine = get_existing_table_name($pdo, 'posts', 'vitrine');

                    // Tenta remover arquivo de mídia se for local
                    try {
                        $stmtFind = $pdo->prepare("SELECT media_url, imagem, thumbnail_url FROM `{$tableVitrine}` WHERE id = ?");
                        $stmtFind->execute([$id]);
                        $found = $stmtFind->fetch();
                        $pic = $found['media_url'] ?? ($found['imagem'] ?? '');
                        if (!empty($pic) && strpos($pic, 'public/vitrine/') !== false && file_exists(ROOT_PATH . '/' . $pic)) {
                            @unlink(ROOT_PATH . '/' . $pic);
                        }
                    } catch (Exception $ePic) {}

                    $stmt = $pdo->prepare("DELETE FROM `{$tableVitrine}` WHERE id = ?");
                    $stmt->execute([$id]);
                    $mensagemSucesso = 'Publicação excluída com sucesso da Vitrine Cultural!';

                    if (!empty($redirect_to)) {
                        header("Location: " . $redirect_to . "?msg=excluido");
                        exit;
                    }
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir publicação: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'vitrine';
        }

        // -------------------------------------------------------------
        // 3. CRUD MAPA CULTURAL COM LEAFLET (BASE UNIFICADA map_locais)
        // -------------------------------------------------------------
        elseif ($action === 'salvar_ponto_mapa') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nome = trim($_POST['nome'] ?? '');
            $categoria = $_POST['categoria'] ?? 'Espaço Cultural';
            $tipo = $_POST['tipo'] ?? $categoria;
            $cidade = trim($_POST['cidade'] ?? 'Brasília/DF');
            $regiao = $_POST['regiao'] ?? 'Sobradinho';
            $bairro = trim($_POST['bairro'] ?? $regiao);
            $lat = (float)($_POST['lat'] ?? -15.6534);
            $lng = (float)($_POST['lng'] ?? -47.7891);
            $descricao = trim($_POST['descricao'] ?? '');
            $endereco = trim($_POST['endereco'] ?? '');
            $contato = trim($_POST['contato'] ?? '');
            $instagram = trim($_POST['instagram'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $tiktok = trim($_POST['tiktok'] ?? '');
            $whatsapp = trim($_POST['whatsapp'] ?? '');
            $foto = $_POST['foto_atual'] ?? 'assets/images/oficina-olhar-fercal.jpg';
            $destaque = isset($_POST['destaque']) ? 1 : 0;

            if (isset($_FILES['foto_arquivo']) && $_FILES['foto_arquivo']['error'] === UPLOAD_ERR_OK) {
                $upload = upload_mapa_photo($_FILES['foto_arquivo'], $nome, $id ?: time());
                if ($upload['success']) {
                    $foto = $upload['path'];
                }
            }

            if ($pdo) {
                try {
                    ensure_parceiros_and_map_columns($pdo);
                    $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');
                    $cols = $pdo->query("SHOW COLUMNS FROM `{$tableMap}`")->fetchAll(PDO::FETCH_COLUMN);

                    if ($id) {
                        $fields = [];
                        $params = [];
                        if (in_array('nome', $cols)) { $fields[] = "`nome` = ?"; $params[] = $nome; }
                        if (in_array('categoria', $cols)) { $fields[] = "`categoria` = ?"; $params[] = $categoria; }
                        if (in_array('tipo', $cols)) { $fields[] = "`tipo` = ?"; $params[] = $tipo; }
                        if (in_array('bairro', $cols)) { $fields[] = "`bairro` = ?"; $params[] = $bairro; }
                        if (in_array('regiao', $cols)) { $fields[] = "`regiao` = ?"; $params[] = $regiao; }
                        if (in_array('cidade', $cols)) { $fields[] = "`cidade` = ?"; $params[] = $cidade; }
                        if (in_array('lat', $cols)) { $fields[] = "`lat` = ?"; $params[] = $lat; }
                        if (in_array('lng', $cols)) { $fields[] = "`lng` = ?"; $params[] = $lng; }
                        if (in_array('descricao', $cols)) { $fields[] = "`descricao` = ?"; $params[] = $descricao; }
                        if (in_array('endereco', $cols)) { $fields[] = "`endereco` = ?"; $params[] = $endereco; }
                        if (in_array('contato', $cols)) { $fields[] = "`contato` = ?"; $params[] = $contato; }
                        if (in_array('instagram', $cols)) { $fields[] = "`instagram` = ?"; $params[] = $instagram; }
                        if (in_array('website', $cols)) { $fields[] = "`website` = ?"; $params[] = $website; }
                        if (in_array('tiktok', $cols)) { $fields[] = "`tiktok` = ?"; $params[] = $tiktok; }
                        if (in_array('whatsapp', $cols)) { $fields[] = "`whatsapp` = ?"; $params[] = $whatsapp; }
                        if (in_array('foto', $cols)) { $fields[] = "`foto` = ?"; $params[] = $foto; }
                        if (in_array('logo', $cols)) { $fields[] = "`logo` = ?"; $params[] = $foto; }
                        if (in_array('imagem', $cols)) { $fields[] = "`imagem` = ?"; $params[] = $foto; }
                        if (in_array('destaque', $cols)) { $fields[] = "`destaque` = ?"; $params[] = $destaque; }

                        if (!empty($fields)) {
                            $sql = "UPDATE `{$tableMap}` SET " . implode(', ', $fields) . " WHERE `id` = ?";
                            $params[] = $id;
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $mensagemSucesso = 'Ponto cultural atualizado com sucesso!';
                        }
                    } else {
                        $fields = [];
                        $placeholders = [];
                        $params = [];
                        if (in_array('nome', $cols)) { $fields[] = "`nome`"; $placeholders[] = "?"; $params[] = $nome; }
                        if (in_array('categoria', $cols)) { $fields[] = "`categoria`"; $placeholders[] = "?"; $params[] = $categoria; }
                        if (in_array('tipo', $cols)) { $fields[] = "`tipo`"; $placeholders[] = "?"; $params[] = $tipo; }
                        if (in_array('bairro', $cols)) { $fields[] = "`bairro`"; $placeholders[] = "?"; $params[] = $bairro; }
                        if (in_array('regiao', $cols)) { $fields[] = "`regiao`"; $placeholders[] = "?"; $params[] = $regiao; }
                        if (in_array('cidade', $cols)) { $fields[] = "`cidade`"; $placeholders[] = "?"; $params[] = $cidade; }
                        if (in_array('lat', $cols)) { $fields[] = "`lat`"; $placeholders[] = "?"; $params[] = $lat; }
                        if (in_array('lng', $cols)) { $fields[] = "`lng`"; $placeholders[] = "?"; $params[] = $lng; }
                        if (in_array('descricao', $cols)) { $fields[] = "`descricao`"; $placeholders[] = "?"; $params[] = $descricao; }
                        if (in_array('endereco', $cols)) { $fields[] = "`endereco`"; $placeholders[] = "?"; $params[] = $endereco; }
                        if (in_array('contato', $cols)) { $fields[] = "`contato`"; $placeholders[] = "?"; $params[] = $contato; }
                        if (in_array('instagram', $cols)) { $fields[] = "`instagram`"; $placeholders[] = "?"; $params[] = $instagram; }
                        if (in_array('website', $cols)) { $fields[] = "`website`"; $placeholders[] = "?"; $params[] = $website; }
                        if (in_array('tiktok', $cols)) { $fields[] = "`tiktok`"; $placeholders[] = "?"; $params[] = $tiktok; }
                        if (in_array('whatsapp', $cols)) { $fields[] = "`whatsapp`"; $placeholders[] = "?"; $params[] = $whatsapp; }
                        if (in_array('foto', $cols)) { $fields[] = "`foto`"; $placeholders[] = "?"; $params[] = $foto; }
                        if (in_array('logo', $cols)) { $fields[] = "`logo`"; $placeholders[] = "?"; $params[] = $foto; }
                        if (in_array('imagem', $cols)) { $fields[] = "`imagem`"; $placeholders[] = "?"; $params[] = $foto; }
                        if (in_array('autor_id', $cols)) { $fields[] = "`autor_id`"; $placeholders[] = "?"; $params[] = $currentUser['id']; }
                        if (in_array('autor_nome', $cols)) { $fields[] = "`autor_nome`"; $placeholders[] = "?"; $params[] = $currentUser['nome']; }
                        if (in_array('destaque', $cols)) { $fields[] = "`destaque`"; $placeholders[] = "?"; $params[] = $destaque; }

                        if (!empty($fields)) {
                            $sql = "INSERT INTO `{$tableMap}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $mensagemSucesso = 'Ponto cultural cadastrado no mapa com sucesso!';
                        }
                    }
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar ponto no mapa: ' . $e->getMessage();
                }
            }

            if (!empty($_POST['redirect_to'])) {
                header("Location: " . $_POST['redirect_to'] . (strpos($_POST['redirect_to'], '?') !== false ? '&' : '?') . "msg=sucesso");
                exit;
            }
            $abaAtiva = 'mapa';
        }

        elseif ($action === 'excluir_ponto_mapa') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $pdo) {
                try {
                    ensure_parceiros_and_map_columns($pdo);
                    $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');
                    $stmt = $pdo->prepare("DELETE FROM `{$tableMap}` WHERE id = ?");
                    $stmt->execute([$id]);

                    // Também remove de parceiros legado se existir com mesmo id
                    try {
                        $tableParc = get_existing_table_name($pdo, 'parceiros');
                        $stmtP = $pdo->prepare("DELETE FROM `{$tableParc}` WHERE id = ?");
                        $stmtP->execute([$id]);
                    } catch (Exception $eP) {}

                    $mensagemSucesso = 'Ponto cultural removido com sucesso!';
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir ponto: ' . $e->getMessage();
                }
            }

            if (!empty($_POST['redirect_to'])) {
                header("Location: " . $_POST['redirect_to'] . (strpos($_POST['redirect_to'], '?') !== false ? '&' : '?') . "msg=excluido");
                exit;
            }
            $abaAtiva = 'mapa';
        }

        // -------------------------------------------------------------
        // 4. CRUD PARCEIROS & ORGANIZAÇÕES (UNIFICADO EM map_locais)
        // -------------------------------------------------------------
        elseif ($action === 'salvar_parceiro') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nome = trim($_POST['nome'] ?? '');
            $setor = $_POST['setor'] ?? '1º Setor (Poder Público)';
            $tipo = trim($_POST['tipo'] ?? 'Governo');
            $categoria = $tipo;
            $bairro = trim($_POST['bairro'] ?? 'Sobradinho');
            $cidade = trim($_POST['cidade'] ?? 'Brasília/DF');
            $endereco = trim($_POST['endereco'] ?? '');
            $lat = isset($_POST['lat']) && $_POST['lat'] !== '' ? (float)$_POST['lat'] : -15.6534;
            $lng = isset($_POST['lng']) && $_POST['lng'] !== '' ? (float)$_POST['lng'] : -47.7891;
            $descricao = trim($_POST['descricao'] ?? '');
            $contribuicao = trim($_POST['contribuicao'] ?? '');
            $website = trim($_POST['website'] ?? ($_POST['site'] ?? ''));
            $instagram = trim($_POST['instagram'] ?? '');
            $tiktok = trim($_POST['tiktok'] ?? '');
            $whatsapp = trim($_POST['whatsapp'] ?? '');
            $logo = $_POST['logo_atual'] ?? '';

            if (isset($_FILES['logo_arquivo']) && $_FILES['logo_arquivo']['error'] === UPLOAD_ERR_OK) {
                $upload = upload_parceiro_logo($_FILES['logo_arquivo'], $nome, $id ?: time());
                if ($upload['success']) {
                    $logo = $upload['path'];
                }
            }

            if ($pdo) {
                try {
                    ensure_parceiros_and_map_columns($pdo);
                    $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');
                    $cols = $pdo->query("SHOW COLUMNS FROM `{$tableMap}`")->fetchAll(PDO::FETCH_COLUMN);

                    if ($id) {
                        $fields = [];
                        $params = [];
                        if (in_array('nome', $cols)) { $fields[] = "`nome` = ?"; $params[] = $nome; }
                        if (in_array('setor', $cols)) { $fields[] = "`setor` = ?"; $params[] = $setor; }
                        if (in_array('tipo', $cols)) { $fields[] = "`tipo` = ?"; $params[] = $tipo; }
                        if (in_array('categoria', $cols)) { $fields[] = "`categoria` = ?"; $params[] = $categoria; }
                        if (in_array('bairro', $cols)) { $fields[] = "`bairro` = ?"; $params[] = $bairro; }
                        if (in_array('regiao', $cols)) { $fields[] = "`regiao` = ?"; $params[] = $bairro; }
                        if (in_array('cidade', $cols)) { $fields[] = "`cidade` = ?"; $params[] = $cidade; }
                        if (in_array('endereco', $cols)) { $fields[] = "`endereco` = ?"; $params[] = $endereco; }
                        if (in_array('lat', $cols)) { $fields[] = "`lat` = ?"; $params[] = $lat; }
                        if (in_array('lng', $cols)) { $fields[] = "`lng` = ?"; $params[] = $lng; }
                        if (in_array('descricao', $cols)) { $fields[] = "`descricao` = ?"; $params[] = $descricao; }
                        if (in_array('contribuicao', $cols)) { $fields[] = "`contribuicao` = ?"; $params[] = $contribuicao; }
                        if (in_array('website', $cols)) { $fields[] = "`website` = ?"; $params[] = $website; }
                        if (in_array('instagram', $cols)) { $fields[] = "`instagram` = ?"; $params[] = $instagram; }
                        if (in_array('tiktok', $cols)) { $fields[] = "`tiktok` = ?"; $params[] = $tiktok; }
                        if (in_array('whatsapp', $cols)) { $fields[] = "`whatsapp` = ?"; $params[] = $whatsapp; }
                        if (in_array('logo', $cols)) { $fields[] = "`logo` = ?"; $params[] = $logo; }
                        if (in_array('foto', $cols)) { $fields[] = "`foto` = ?"; $params[] = $logo; }
                        if (in_array('imagem', $cols)) { $fields[] = "`imagem` = ?"; $params[] = $logo; }

                        if (!empty($fields)) {
                            $sql = "UPDATE `{$tableMap}` SET " . implode(', ', $fields) . " WHERE `id` = ?";
                            $params[] = $id;
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $mensagemSucesso = 'Organização atualizada com sucesso no banco de dados!';
                        }
                    } else {
                        $fields = [];
                        $placeholders = [];
                        $params = [];
                        if (in_array('nome', $cols)) { $fields[] = "`nome`"; $placeholders[] = "?"; $params[] = $nome; }
                        if (in_array('setor', $cols)) { $fields[] = "`setor`"; $placeholders[] = "?"; $params[] = $setor; }
                        if (in_array('tipo', $cols)) { $fields[] = "`tipo`"; $placeholders[] = "?"; $params[] = $tipo; }
                        if (in_array('categoria', $cols)) { $fields[] = "`categoria`"; $placeholders[] = "?"; $params[] = $categoria; }
                        if (in_array('bairro', $cols)) { $fields[] = "`bairro`"; $placeholders[] = "?"; $params[] = $bairro; }
                        if (in_array('regiao', $cols)) { $fields[] = "`regiao`"; $placeholders[] = "?"; $params[] = $bairro; }
                        if (in_array('cidade', $cols)) { $fields[] = "`cidade`"; $placeholders[] = "?"; $params[] = $cidade; }
                        if (in_array('endereco', $cols)) { $fields[] = "`endereco`"; $placeholders[] = "?"; $params[] = $endereco; }
                        if (in_array('lat', $cols)) { $fields[] = "`lat`"; $placeholders[] = "?"; $params[] = $lat; }
                        if (in_array('lng', $cols)) { $fields[] = "`lng`"; $placeholders[] = "?"; $params[] = $lng; }
                        if (in_array('descricao', $cols)) { $fields[] = "`descricao`"; $placeholders[] = "?"; $params[] = $descricao; }
                        if (in_array('contribuicao', $cols)) { $fields[] = "`contribuicao`"; $placeholders[] = "?"; $params[] = $contribuicao; }
                        if (in_array('website', $cols)) { $fields[] = "`website`"; $placeholders[] = "?"; $params[] = $website; }
                        if (in_array('instagram', $cols)) { $fields[] = "`instagram`"; $placeholders[] = "?"; $params[] = $instagram; }
                        if (in_array('tiktok', $cols)) { $fields[] = "`tiktok`"; $placeholders[] = "?"; $params[] = $tiktok; }
                        if (in_array('whatsapp', $cols)) { $fields[] = "`whatsapp`"; $placeholders[] = "?"; $params[] = $whatsapp; }
                        if (in_array('logo', $cols)) { $fields[] = "`logo`"; $placeholders[] = "?"; $params[] = $logo; }
                        if (in_array('foto', $cols)) { $fields[] = "`foto`"; $placeholders[] = "?"; $params[] = $logo; }
                        if (in_array('imagem', $cols)) { $fields[] = "`imagem`"; $placeholders[] = "?"; $params[] = $logo; }

                        if (!empty($fields)) {
                            $sql = "INSERT INTO `{$tableMap}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $mensagemSucesso = 'Nova organização cadastrada com sucesso!';
                        }
                    }
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar organização: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'parceiros';
        }

        elseif ($action === 'excluir_parceiro') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $pdo) {
                try {
                    ensure_parceiros_and_map_columns($pdo);
                    $tableMap = get_existing_table_name($pdo, 'map_locais', 'pontos_mapa');
                    $stmt = $pdo->prepare("DELETE FROM `{$tableMap}` WHERE id = ?");
                    $stmt->execute([$id]);

                    try {
                        $tableParc = get_existing_table_name($pdo, 'parceiros');
                        $stmtP = $pdo->prepare("DELETE FROM `{$tableParc}` WHERE id = ?");
                        $stmtP->execute([$id]);
                    } catch (Exception $eP) {}

                    $mensagemSucesso = 'Organização removida com sucesso do banco de dados!';
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir organização: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'parceiros';
        }

        // -------------------------------------------------------------
        // 5. CRUD LMS: TRILHAS, CURSOS, MÓDULOS, AULAS, ANEXOS & QUIZZES
        // -------------------------------------------------------------
        elseif ($action === 'salvar_trilha') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $eixo = trim($_POST['eixo'] ?? '');
            $cargaHoraria = trim($_POST['carga_horaria'] ?? '');
            $ordem = (int)($_POST['ordem'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['ativo', 'inativo', 'rascunho']) ? $_POST['status'] : 'ativo';

            if ($pdo && !empty($titulo)) {
                try {
                    if ($id) {
                        $stmt = $pdo->prepare("UPDATE `trilhas_cursos` SET titulo=?, descricao=?, eixo=?, carga_horaria=?, ordem=?, status=? WHERE id=?");
                        $stmt->execute([$titulo, $descricao, $eixo, $cargaHoraria, $ordem, $status, $id]);
                        $mensagemSucesso = 'Trilha de Aprendizado atualizada com sucesso!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `trilhas_cursos` (titulo, descricao, eixo, carga_horaria, ordem, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$titulo, $descricao, $eixo, $cargaHoraria, $ordem, $status]);
                        $mensagemSucesso = 'Nova Trilha criada com sucesso!';
                    }
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar trilha: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'excluir_trilha') {
            $id = (int)$_POST['id'];
            if ($pdo && $id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM `trilhas_cursos` WHERE id=?");
                    $stmt->execute([$id]);
                    $mensagemSucesso = 'Trilha excluída com sucesso!';
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir trilha: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'salvar_curso') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $trilhaId = !empty($_POST['trilha_id']) ? (int)$_POST['trilha_id'] : null;
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cargaHoraria = trim($_POST['carga_horaria'] ?? '');
            $professorNome = trim($_POST['professor_nome'] ?? 'FotoCidade DF');
            $ordem = (int)($_POST['ordem'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['ativo', 'inativo', 'rascunho']) ? $_POST['status'] : 'rascunho';
            $capaUrl = $_POST['capa_atual'] ?? '';

            if (isset($_FILES['capa_arquivo']) && $_FILES['capa_arquivo']['error'] === UPLOAD_ERR_OK) {
                $upload = upload_curso_capa($_FILES['capa_arquivo'], $titulo, $id ?: time());
                if ($upload['success']) {
                    $capaUrl = $upload['path'];
                }
            }

            if ($pdo && !empty($titulo)) {
                try {
                    if ($id) {
                        $stmt = $pdo->prepare("UPDATE `cursos` SET trilha_id=?, titulo=?, descricao=?, capa_url=?, carga_horaria=?, professor_nome=?, ordem=?, status=? WHERE id=?");
                        $stmt->execute([$trilhaId, $titulo, $descricao, $capaUrl, $cargaHoraria, $professorNome, $ordem, $status, $id]);
                        $mensagemSucesso = 'Curso atualizado com sucesso!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `cursos` (trilha_id, titulo, descricao, capa_url, carga_horaria, professor_nome, ordem, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$trilhaId, $titulo, $descricao, $capaUrl, $cargaHoraria, $professorNome, $ordem, $status]);
                        $id = $pdo->lastInsertId();
                        $mensagemSucesso = 'Novo curso criado com sucesso!';
                    }
                    header("Location: dashboard.php?aba=cursos&curso_id={$id}&msg=salvo");
                    exit;
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar curso: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'excluir_curso') {
            $id = (int)$_POST['id'];
            if ($pdo && $id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM `cursos` WHERE id=?");
                    $stmt->execute([$id]);
                    $mensagemSucesso = 'Curso excluído com sucesso!';
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir curso: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'salvar_modulo') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $cursoId = (int)$_POST['curso_id'];
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $conteudo = $_POST['conteudo'] ?? '';
            $tipoVideo = in_array($_POST['tipo_video'] ?? '', ['youtube', 'vimeo', 'upload', 'nenhum']) ? $_POST['tipo_video'] : 'nenhum';
            $urlVideo = trim($_POST['url_video'] ?? '');
            $tipoAudio = in_array($_POST['tipo_audio'] ?? '', ['link', 'upload', 'nenhum']) ? $_POST['tipo_audio'] : 'nenhum';
            $audioUrl = trim($_POST['audio_url'] ?? '');
            $duracaoMinutos = (int)($_POST['duracao_minutos'] ?? 0);
            $ordem = (int)($_POST['ordem'] ?? 0);

            // Upload de vídeo do módulo
            if ($tipoVideo === 'upload' && isset($_FILES['video_arquivo']) && $_FILES['video_arquivo']['error'] === UPLOAD_ERR_OK) {
                $uploadVid = upload_aula_video($_FILES['video_arquivo'], $titulo, $id ?: time());
                if ($uploadVid['success']) {
                    $urlVideo = $uploadVid['path'];
                }
            }

            // Upload de áudio do módulo
            if ($tipoAudio === 'upload' && isset($_FILES['audio_arquivo']) && $_FILES['audio_arquivo']['error'] === UPLOAD_ERR_OK) {
                $uploadAud = upload_aula_audio($_FILES['audio_arquivo'], $titulo, $id ?: time());
                if ($uploadAud['success']) {
                    $audioUrl = $uploadAud['path'];
                }
            }

            if ($pdo && !empty($titulo) && $cursoId) {
                try {
                    check_and_migrate_lms_schema($pdo);
                    if ($id) {
                        $stmt = $pdo->prepare("UPDATE `modulos` SET titulo=?, descricao=?, conteudo=?, tipo_video=?, url_video=?, tipo_audio=?, audio_url=?, duracao_minutos=?, ordem=? WHERE id=?");
                        $stmt->execute([$titulo, $descricao, $conteudo, $tipoVideo, $urlVideo, $tipoAudio, $audioUrl, $duracaoMinutos, $ordem, $id]);
                        $moduloId = $id;
                        $mensagemSucesso = 'Módulo atualizado com sucesso!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `modulos` (curso_id, titulo, descricao, conteudo, tipo_video, url_video, tipo_audio, audio_url, duracao_minutos, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$cursoId, $titulo, $descricao, $conteudo, $tipoVideo, $urlVideo, $tipoAudio, $audioUrl, $duracaoMinutos, $ordem]);
                        $moduloId = $pdo->lastInsertId();
                        $mensagemSucesso = 'Novo Módulo com conteúdo pedagógico criado com sucesso!';
                    }

                    header("Location: dashboard.php?aba=cursos&curso_id={$cursoId}&msg=modulo_salvo");
                    exit;
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar módulo: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'excluir_modulo') {
            $id = (int)$_POST['id'];
            $cursoId = (int)$_POST['curso_id'];
            if ($pdo && $id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM `modulos` WHERE id=?");
                    $stmt->execute([$id]);
                    $mensagemSucesso = 'Módulo excluído com sucesso!';
                    header("Location: dashboard.php?aba=cursos&curso_id={$cursoId}&msg=modulo_excluido");
                    exit;
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir módulo: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'reordenar_modulos') {
            $cursoId = (int)($_POST['curso_id'] ?? 0);
            $modulosOrdem = $_POST['modulos_ordem'] ?? [];
            if (is_string($modulosOrdem)) {
                $modulosOrdem = json_decode($modulosOrdem, true) ?: explode(',', $modulosOrdem);
            }
            if ($pdo && !empty($modulosOrdem)) {
                try {
                    $stmt = $pdo->prepare("UPDATE `modulos` SET ordem = ? WHERE id = ?");
                    foreach ($modulosOrdem as $ordemIndex => $modId) {
                        $stmt->execute([$ordemIndex + 1, (int)$modId]);
                    }
                    if (!empty($_POST['is_ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Ordem atualizada com sucesso!']);
                        exit;
                    }
                    $mensagemSucesso = 'Ordem dos módulos atualizada com sucesso!';
                } catch (Exception $e) {
                    if (!empty($_POST['is_ajax'])) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                        exit;
                    }
                    $mensagemErro = 'Erro ao reordenar módulos: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'salvar_aula') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $moduloId = (int)$_POST['modulo_id'];
            $cursoId = (int)$_POST['curso_id'];
            $titulo = trim($_POST['titulo'] ?? '');
            $conteudo = $_POST['conteudo'] ?? '';
            $tipoVideo = in_array($_POST['tipo_video'] ?? '', ['youtube', 'vimeo', 'upload', 'nenhum']) ? $_POST['tipo_video'] : 'nenhum';
            $urlVideo = trim($_POST['url_video'] ?? '');
            $duracaoMinutos = (int)($_POST['duracao_minutos'] ?? 0);
            $ordem = (int)($_POST['ordem'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['publicado', 'rascunho']) ? $_POST['status'] : 'publicado';

            // Se enviou vídeo local em upload
            if ($tipoVideo === 'upload' && isset($_FILES['video_arquivo']) && $_FILES['video_arquivo']['error'] === UPLOAD_ERR_OK) {
                $uploadVid = upload_aula_video($_FILES['video_arquivo'], $titulo, $id ?: time());
                if ($uploadVid['success']) {
                    $urlVideo = $uploadVid['path'];
                }
            }

            if ($pdo && !empty($titulo) && $moduloId) {
                try {
                    if ($id) {
                        $stmt = $pdo->prepare("UPDATE `aulas` SET modulo_id=?, titulo=?, conteudo=?, tipo_video=?, url_video=?, duracao_minutos=?, ordem=?, status=? WHERE id=?");
                        $stmt->execute([$moduloId, $titulo, $conteudo, $tipoVideo, $urlVideo, $duracaoMinutos, $ordem, $status, $id]);
                        $aulaId = $id;
                        $mensagemSucesso = 'Aula atualizada com sucesso!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `aulas` (modulo_id, titulo, conteudo, tipo_video, url_video, duracao_minutos, ordem, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$moduloId, $titulo, $conteudo, $tipoVideo, $urlVideo, $duracaoMinutos, $ordem, $status]);
                        $aulaId = $pdo->lastInsertId();
                        $mensagemSucesso = 'Nova aula publicada com sucesso!';
                    }

                    // Processa anexo/documento de apoio caso tenha sido enviado
                    if (isset($_FILES['anexo_arquivo']) && $_FILES['anexo_arquivo']['error'] === UPLOAD_ERR_OK) {
                        $uploadAnx = upload_aula_anexo($_FILES['anexo_arquivo'], $titulo, time());
                        if ($uploadAnx['success']) {
                            $stmtAnx = $pdo->prepare("INSERT INTO `anexos_aulas` (aula_id, nome_arquivo, url_arquivo, tamanho_bytes) VALUES (?, ?, ?, ?)");
                            $stmtAnx->execute([$aulaId, $uploadAnx['nomeOriginal'], $uploadAnx['path'], $uploadAnx['tamanho']]);
                        }
                    }

                    header("Location: dashboard.php?aba=cursos&curso_id={$cursoId}&msg=aula_salva");
                    exit;
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao salvar aula: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'excluir_aula') {
            $id = (int)$_POST['id'];
            $cursoId = (int)$_POST['curso_id'];
            if ($pdo && $id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM `aulas` WHERE id=?");
                    $stmt->execute([$id]);
                    $mensagemSucesso = 'Aula excluída com sucesso!';
                    header("Location: dashboard.php?aba=cursos&curso_id={$cursoId}&msg=aula_excluida");
                    exit;
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir aula: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }

        elseif ($action === 'excluir_anexo') {
            $anexoId = (int)$_POST['anexo_id'];
            $cursoId = (int)$_POST['curso_id'];
            if ($pdo && $anexoId) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM `anexos_aulas` WHERE id=?");
                    $stmt->execute([$anexoId]);
                    $mensagemSucesso = 'Anexo removido da aula!';
                    header("Location: dashboard.php?aba=cursos&curso_id={$cursoId}&msg=anexo_removido");
                    exit;
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao excluir anexo: ' . $e->getMessage();
                }
            }
            $abaAtiva = 'cursos';
        }
    }
}

// ====================================================================
// CARGA DE DADOS PARA EXIBIÇÃO NO DASHBOARD
// ====================================================================
$vitrineList = get_vitrine();
$mapaPoints = get_map_data();
$parceirosList = get_parceiros();
$talentosList = get_talentos();
$trilhasList = get_trilhas();
$trilhasLMS = get_all_trilhas();
$filtroTrilhaId = isset($_GET['trilha_id']) && $_GET['trilha_id'] !== '' ? (int)$_GET['trilha_id'] : null;
$cursosLMS = get_cursos($filtroTrilhaId);
$cursoSelecionadoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : null;
$cursoAtivoDetalhes = $cursoSelecionadoId ? get_curso_completo($cursoSelecionadoId) : (!empty($cursosLMS) ? get_curso_completo($cursosLMS[0]['id']) : null);

// Lista de Usuários diretamente do MySQL
$usuariosList = [];
if ($pdo) {
    try {
        $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
        $stmt = $pdo->query("SELECT * FROM `{$tableUsers}` ORDER BY id DESC");
        $rawUsers = $stmt->fetchAll();
        foreach ($rawUsers as $u) {
            $avatarImg = !empty($u['avatar']) ? $u['avatar'] : (!empty($u['foto']) ? $u['foto'] : 'assets/images/avatar-default.jpg');
            $usuariosList[] = [
                'id' => $u['id'],
                'nome' => $u['nome'] ?? ($u['username'] ?? 'Usuário'),
                'username' => $u['username'] ?? ($u['email'] ?? ''),
                'email' => $u['email'] ?? ($u['username'] ?? ''),
                'nivel' => $u['nivel'] ?? 'aluno',
                'cidade' => $u['cidade'] ?? 'Sobradinho',
                'bairro' => $u['bairro'] ?? '',
                'endereco' => $u['endereco'] ?? '',
                'telefone' => $u['telefone'] ?? '',
                'biografia' => $u['biografia'] ?? '',
                'avatar' => $avatarImg,
                'criado_em' => $u['criado_em'] ?? ($u['created_at'] ?? date('Y-m-d H:i:s'))
            ];
        }
    } catch (Exception $e) {
        error_log("Erro ao carregar lista de usuarios: " . $e->getMessage());
    }
}

if (!$pdo && empty($mensagemErro)) {
    global $lastDbError;
    $mensagemErro = 'Atenção: Banco de Dados desconectado (' . ($lastDbError ?? 'Verifique conexao.php') . '). Para ver e editar os dados reais, certifique-se que o banco está acessível.';
}

$pageTitle = 'Painel de Gestão e Dashboard';
require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/logo.php';
?>

<div class="min-h-screen bg-[#F1F5F9] flex flex-col">
    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Badge -->
                <div class="flex items-center gap-4">
                    <?php render_logo('sm', false, 'dashboard.php'); ?>
                    <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-1 text-[#FF8A00]"></i>
                        Painel Gestor
                    </span>
                </div>

                <!-- Right user info & actions -->
                <div class="flex items-center gap-4">
                    <a href="index.php" target="_blank" class="text-xs font-bold text-slate-600 hover:text-[#0D5BA8] flex items-center gap-1 py-2 px-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="external-link" class="w-4 h-4 text-[#00A7B5]"></i>
                        <span class="hidden sm:inline">Ver Site Público</span>
                    </a>

                    <div class="h-6 w-px bg-slate-200"></div>

                    <div class="flex items-center gap-3">
                        <a href="perfil.php" title="Ver Meu Perfil & Editar Dados" class="flex items-center gap-3 hover:bg-slate-100/80 p-1.5 rounded-xl transition-colors group">
                            <div class="text-right hidden sm:block">
                                <p class="text-xs font-bold text-slate-900 leading-tight group-hover:text-[#0D5BA8] transition-colors"><?php echo htmlspecialchars($currentUser['nome']); ?></p>
                                <p class="text-[10px] text-amber-600 font-bold uppercase tracking-wider">Administrador</p>
                            </div>
                            <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'assets/images/avatar-default.jpg'); ?>" 
                                 onerror="this.src='assets/images/avatar-default.jpg'"
                                 alt="<?php echo htmlspecialchars($currentUser['nome']); ?>" 
                                 class="w-9 h-9 rounded-full object-cover ring-2 ring-[#FF8A00] bg-white shadow-xs group-hover:scale-105 transition-transform" />
                        </a>
                        
                        <a href="logout.php" title="Encerrar Sessão" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Subheader Menu Tabs -->
    <div class="bg-white border-b border-slate-200 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-2 sm:space-x-4 overflow-x-auto py-2.5">
                <?php
                $abas = [
                    ['id' => 'visao_geral', 'label' => 'Visão Geral', 'icon' => 'layout-dashboard'],
                    ['id' => 'vitrine', 'label' => 'Vitrine Cultural', 'icon' => 'grid'],
                    ['id' => 'usuarios', 'label' => 'Usuários & Acessos', 'icon' => 'users'],
                    ['id' => 'mapa', 'label' => 'Mapa Cultural', 'icon' => 'map-pin'],
                    ['id' => 'parceiros', 'label' => 'Parceiros & Talentos', 'icon' => 'handshake'],
                    ['id' => 'cursos', 'label' => 'Trilhas & Cursos', 'icon' => 'book-open'],
                ];

                foreach ($abas as $aba):
                    $isActive = ($abaAtiva === $aba['id']);
                ?>
                    <a href="dashboard.php?aba=<?php echo $aba['id']; ?>"
                       class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?php echo $isActive ? 'bg-[#0D5BA8] text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'; ?>">
                        <i data-lucide="<?php echo $aba['icon']; ?>" class="w-4 h-4 <?php echo $isActive ? 'text-[#FFC107]' : 'text-slate-400'; ?>"></i>
                        <span><?php echo $aba['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-6">
        
        <!-- Alerts -->
        <?php if (!empty($mensagemSucesso)): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    <span><?php echo htmlspecialchars($mensagemSucesso); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600"></i>
                    <span><?php echo htmlspecialchars($mensagemErro); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- ABA 1: VISÃO GERAL -->
        <!-- ============================================================= -->
        <?php if ($abaAtiva === 'visao_geral'): ?>
            <div class="space-y-8 animate-in fade-in duration-200">
                <!-- Welcome Banner Principal -->
                <div class="bg-gradient-to-r from-[#0D5BA8] via-[#09427D] to-[#00A7B5] rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row items-center gap-5 relative z-10 text-center sm:text-left">
                        <a href="perfil.php" title="Editar Meu Perfil" class="relative group shrink-0">
                            <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'assets/images/avatar-default.jpg'); ?>"
                                 onerror="this.src='assets/images/avatar-default.jpg'"
                                 alt="<?php echo htmlspecialchars($currentUser['nome']); ?>"
                                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover ring-4 ring-white/80 shadow-md bg-white group-hover:scale-105 transition-transform" />
                            <span class="absolute bottom-1 right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Admin Ativo"></span>
                        </a>

                        <div class="space-y-1.5">
                            <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 text-xs font-semibold text-teal-200">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FFC107]"></i>
                                <span>Painel Gestor FotoCidade DF</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-black font-heading">Olá, <?php echo htmlspecialchars($currentUser['nome']); ?>!</h1>
                            <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                                Painel de controle unificado para administração dos alunos, vitrine cultural, mapa interativo e banco de talentos.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2.5 relative z-10 w-full md:w-auto">
                        <a href="perfil.php" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Meu Perfil & Talentos</span>
                        </a>

                        <button onclick="abrirModalUsuario()" class="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Novo Aluno</span>
                        </button>

                        <a href="db_init.php" class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 border border-white/20">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-[#FFC107]"></i>
                            <span>Sincronizar</span>
                        </a>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase">Usuários Cadastrados</p>
                            <h3 class="text-2xl font-black text-slate-900 font-heading"><?php echo count($usuariosList); ?></h3>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-[#00A7B5] flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase">Pontos no Mapa</p>
                            <h3 class="text-2xl font-black text-slate-900 font-heading"><?php echo count($mapaPoints); ?></h3>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#FF8A00] flex items-center justify-center">
                            <i data-lucide="grid" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase">Posts na Vitrine</p>
                            <h3 class="text-2xl font-black text-slate-900 font-heading"><?php echo count($vitrineList); ?></h3>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">
                            <i data-lucide="handshake" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase">Parceiros & Redes</p>
                            <h3 class="text-2xl font-black text-slate-900 font-heading"><?php echo count($parceirosList); ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Database Status & Shortcuts -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
                        <h3 class="font-heading font-bold text-base text-slate-900 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#FF8A00]"></i>
                            Ações Rápidas de Gerenciamento
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
                            <button onclick="abrirModalUsuario()" class="p-4 rounded-2xl bg-slate-50 hover:bg-amber-50/70 border border-slate-200 text-left transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-[#FF8A00] text-white flex items-center justify-center mb-2 shadow-2xs">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 group-hover:text-[#FF8A00]">Cadastrar Alunos / Usuários</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Adicione ou edite perfis e permissões de acesso.</p>
                            </button>

                            <button onclick="abrirModalVitrine()" class="p-4 rounded-2xl bg-slate-50 hover:bg-blue-50/70 border border-slate-200 text-left transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-[#0D5BA8] text-white flex items-center justify-center mb-2 shadow-2xs">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 group-hover:text-[#0D5BA8]">Publicar na Vitrine</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Adicione ensaios, fotos e vídeos dos alunos.</p>
                            </button>

                            <button onclick="abrirModalMapa()" class="p-4 rounded-2xl bg-slate-50 hover:bg-teal-50/70 border border-slate-200 text-left transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-[#00A7B5] text-white flex items-center justify-center mb-2 shadow-2xs">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 group-hover:text-[#00A7B5]">Mapear Ponto Cultural</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Cadastre artistas ou espaços com GPS Leaflet.</p>
                            </button>

                            <a href="dashboard.php?aba=cursos" class="p-4 rounded-2xl bg-slate-50 hover:bg-indigo-50/70 border border-slate-200 text-left transition-all group block">
                                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center mb-2 shadow-2xs">
                                    <i data-lucide="book-open" class="w-4 h-4"></i>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 group-hover:text-indigo-600">Trilhas & Cursos</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Gerencie os módulos formadores do FotoCidade.</p>
                            </a>

                            <a href="perfil.php" class="p-4 rounded-2xl bg-slate-50 hover:bg-emerald-50/70 border border-slate-200 text-left transition-all group block sm:col-span-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center mb-2 shadow-2xs">
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 group-hover:text-emerald-700">Meu Perfil & Banco de Talentos</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Atualize seus dados pessoais, redes sociais e sua presença no Banco de Talentos.</p>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-3">
                        <h3 class="font-heading font-bold text-base text-slate-900">Status da Plataforma</h3>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                                <span class="text-slate-600 font-medium">Banco de Dados:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $pdo ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?>">
                                    <?php echo $pdo ? 'Conectado' : 'Modo Demonstrativo'; ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                                <span class="text-slate-600 font-medium">Uploads Locais:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    /assets/uploads/
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                                <span class="text-slate-600 font-medium">Criptografia de Senhas:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Bcrypt (password_hash)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- ABA 2: GERENCIADOR DE VITRINE (CRUD COMPLETO) -->
        <!-- ============================================================= -->
        <?php if ($abaAtiva === 'vitrine'): ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden space-y-6 p-6 animate-in fade-in duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-heading font-bold text-xl text-slate-900">Gerenciador da Vitrine Cultural</h2>
                        <p class="text-xs text-slate-500">Publicação de fotos, minidocs e ensaios dos alunos do FotoCidade.</p>
                    </div>
                    <button onclick="abrirModalVitrine()" class="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md flex items-center gap-2 cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Novo Post na Vitrine</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Mídia</th>
                                <th class="py-3 px-4">Título / Subtítulo</th>
                                <th class="py-3 px-4">Tipo</th>
                                <th class="py-3 px-4">Autor</th>
                                <th class="py-3 px-4">Bairro</th>
                                <th class="py-3 px-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($vitrineList as $v): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4">
                                        <img src="<?php echo htmlspecialchars($v['thumbnailUrl'] ?? $v['mediaUrl']); ?>" 
                                             class="w-14 h-10 object-cover rounded-lg border border-slate-200 shadow-2xs" />
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 max-w-xs truncate">
                                        <?php echo htmlspecialchars($v['titulo']); ?>
                                        <p class="text-[10px] text-slate-400 font-normal truncate"><?php echo htmlspecialchars($v['subtitulo'] ?? ''); ?></p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo ($v['tipo'] === 'Vídeo') ? 'bg-amber-100 text-amber-800' : 'bg-cyan-100 text-cyan-800'; ?>">
                                            <?php echo htmlspecialchars($v['tipo']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($v['autorNome']); ?></td>
                                    <td class="py-3 px-4 text-slate-500"><?php echo htmlspecialchars($v['bairro']); ?></td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button onclick='editarVitrine(<?php echo json_encode($v); ?>)' class="p-1.5 bg-blue-50 text-[#0D5BA8] hover:bg-blue-100 rounded-lg" title="Editar">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja realmente excluir este item da vitrine?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="excluir_vitrine">
                                            <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                                            <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg" title="Excluir">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- ABA 3: GERENCIADOR DE USUÁRIOS (CRUD COMPLETO) -->
        <!-- ============================================================= -->
        <?php if ($abaAtiva === 'usuarios'): ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden space-y-6 p-6 animate-in fade-in duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-heading font-bold text-xl text-slate-900">Gerenciamento de Usuários e Acessos</h2>
                        <p class="text-xs text-slate-500">Controle total de Administradores (Acesso Total) e Alunos (Acesso à Trilha/Perfil).</p>
                    </div>
                    <button onclick="abrirModalUsuario()" class="px-4 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-bold text-xs rounded-xl shadow-md flex items-center gap-2 cursor-pointer transition-all hover:scale-105">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Novo Usuário</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3 px-4">Usuário</th>
                                <th class="py-3 px-4">E-mail / Login</th>
                                <th class="py-3 px-4">Nível</th>
                                <th class="py-3 px-4">Cidade & Bairro</th>
                                <th class="py-3 px-4">Telefone</th>
                                <th class="py-3 px-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($usuariosList as $u): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?php echo htmlspecialchars($u['id']); ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-3">
                                        <img src="<?php echo htmlspecialchars($u['avatar'] ?? 'assets/images/avatar-default.jpg'); ?>" 
                                             onerror="this.src='assets/images/avatar-default.jpg'"
                                             class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-xs" />
                                        <div>
                                            <p class="font-bold text-slate-900"><?php echo htmlspecialchars($u['nome']); ?></p>
                                            <?php if (!empty($u['username']) && $u['username'] !== $u['nome']): ?>
                                                <p class="text-[10px] text-slate-400 font-normal">@<?php echo htmlspecialchars($u['username']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-mono text-[11px]"><?php echo htmlspecialchars($u['email'] ?: $u['username']); ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 <?php echo ($u['nivel'] === 'admin') ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-[#0D5BA8] border border-blue-200'; ?>">
                                            <i data-lucide="<?php echo ($u['nivel'] === 'admin') ? 'shield-check' : 'graduation-cap'; ?>" class="w-3 h-3"></i>
                                            <?php echo ($u['nivel'] === 'admin') ? 'Administrador' : 'Aluno (Trilha)'; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($u['cidade'] ?? 'DF'); ?></p>
                                        <?php if (!empty($u['bairro'])): ?>
                                            <p class="text-[10px] text-slate-400"><?php echo htmlspecialchars($u['bairro']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 font-mono text-[11px]"><?php echo htmlspecialchars($u['telefone'] ?: '-'); ?></td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button onclick='editarUsuario(<?php echo json_encode($u); ?>)' class="p-1.5 bg-blue-50 text-[#0D5BA8] hover:bg-blue-100 rounded-lg transition-colors cursor-pointer" title="Editar Usuário">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                            <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja realmente excluir este usuário (#<?php echo $u['id']; ?> - <?php echo addslashes($u['nome']); ?>)?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                                <input type="hidden" name="action" value="excluir_usuario">
                                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors cursor-pointer" title="Excluir Usuário">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- ABA 4: GERENCIADOR DO MAPA CULTURAL COM LEAFLET (CRUD) -->
        <!-- ============================================================= -->
        <?php if ($abaAtiva === 'mapa'): ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden space-y-6 p-6 animate-in fade-in duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-heading font-bold text-xl text-slate-900">Gerenciador do Mapa Cultural Georreferenciado</h2>
                        <p class="text-xs text-slate-500">Adicione e edite pontos com coordenadas precisas via Leaflet (Sobradinho, Sobradinho II, Fercal, Grande Colorado).</p>
                    </div>
                    <button onclick="abrirModalMapa()" class="px-4 py-2.5 bg-[#00A7B5] hover:bg-[#008f9c] text-white font-bold text-xs rounded-xl shadow-md flex items-center gap-2 cursor-pointer">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        <span>Novo Ponto no Mapa</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Local</th>
                                <th class="py-3 px-4">Categoria</th>
                                <th class="py-3 px-4">Região</th>
                                <th class="py-3 px-4">Coordenadas</th>
                                <th class="py-3 px-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($mapaPoints as $p): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2">
                                        <?php if (!empty($p['foto'])): ?>
                                            <img src="<?php echo htmlspecialchars($p['foto']); ?>" class="w-8 h-8 rounded-lg object-cover" />
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($p['nome']); ?></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                            <?php echo htmlspecialchars($p['categoria']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($p['regiao'] ?? $p['bairro']); ?></td>
                                    <td class="py-3 px-4 text-slate-400 font-mono text-[10px]"><?php echo number_format((float)$p['lat'], 4) . ', ' . number_format((float)$p['lng'], 4); ?></td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button onclick='editarPontoMapa(<?php echo json_encode($p); ?>)' class="p-1.5 bg-teal-50 text-[#00A7B5] hover:bg-teal-100 rounded-lg" title="Editar">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja realmente excluir este ponto do mapa?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="excluir_ponto_mapa">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg" title="Excluir">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- ABA 5: PARCEIROS & TALENTOS DA REDE -->
        <!-- ============================================================= -->
        <?php if ($abaAtiva === 'parceiros'): ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden space-y-6 p-6 animate-in fade-in duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-heading font-bold text-xl text-slate-900">Poder Público, Empresas, ONGs, Turismo & Cultura</h2>
                        <p class="text-xs text-slate-500">Mantenha atualizada a rede colaborativa de parceiros institucionais, empresas, ONGs e equipamentos turísticos.</p>
                    </div>
                    <button onclick="abrirModalParceiro()" class="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md flex items-center gap-2 cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Novo Parceiro</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($parceirosList as $parc): ?>
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($parc['logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($parc['logo']); ?>" class="w-10 h-10 rounded-xl object-contain bg-white p-1 border border-slate-200" />
                                <?php endif; ?>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($parc['nome']); ?></h4>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($parc['tipo'] ?? 'Apoiador'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button onclick='editarParceiro(<?php echo json_encode($parc); ?>)' class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg" title="Editar">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="dashboard.php" onsubmit="return confirm('Excluir parceiro?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="excluir_parceiro">
                                    <input type="hidden" name="id" value="<?php echo $parc['id']; ?>">
                                    <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg" title="Excluir">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- ABA 6: GESTÃO DE CURSOS & TRILHAS DE APRENDIZADO (LMS COMPLETO) -->
        <!-- ============================================================= -->
        <?php if ($abaAtiva === 'cursos'): ?>
            <div class="space-y-8 animate-in fade-in duration-200">
                
                <!-- Header & Action Bar -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#0D5BA8] text-xs font-bold mb-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                <span>Ambiente Pedagógico & LMS</span>
                            </div>
                            <h2 class="font-heading font-extrabold text-2xl text-slate-900">Gestão de Cursos e Trilhas</h2>
                            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                                Crie cursos práticos, estruture módulos e publique aulas ricas com vídeos (YouTube ou upload local), anexos em PDF e trilhas formativas.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <button onclick="abrirModalTrilha()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl shadow-xs flex items-center gap-2 transition-all cursor-pointer">
                                <i data-lucide="layers" class="w-4 h-4 text-[#0D5BA8]"></i>
                                <span>+ Nova Trilha</span>
                            </button>
                            <button onclick="abrirModalCurso()" class="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md flex items-center gap-2 transition-all hover:scale-105 cursor-pointer">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span>+ Novo Curso</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filter by Trilha & Metrics -->
                    <div class="pt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <form method="GET" action="dashboard.php" class="flex items-center gap-2">
                            <input type="hidden" name="aba" value="cursos">
                            <label class="text-xs font-bold text-slate-600">Filtrar por Trilha:</label>
                            <select name="trilha_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8]">
                                <option value="">Todas as Trilhas (<?php echo count($cursosLMS); ?> cursos)</option>
                                <?php foreach ($trilhasLMS as $tr): ?>
                                    <option value="<?php echo $tr['id']; ?>" <?php echo ($filtroTrilhaId == $tr['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tr['titulo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <div class="flex items-center gap-4 text-xs text-slate-600 font-semibold">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#0D5BA8]"></span> <?php echo count($trilhasLMS); ?> Trilhas</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#FF8A00]"></span> <?php echo count($cursosLMS); ?> Cursos</span>
                        </div>
                    </div>
                </div>

                <!-- Lista de Cursos & Estrutura de Módulos -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Coluna Esquerda: Lista de Cursos Disponíveis -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-heading font-bold text-base text-slate-900 flex items-center gap-2">
                                <i data-lucide="book-open" class="w-4 h-4 text-[#0D5BA8]"></i>
                                <span>Cursos Disponíveis</span>
                            </h3>
                            <span class="text-xs text-slate-400"><?php echo count($cursosLMS); ?> cadastrados</span>
                        </div>

                        <?php if (empty($cursosLMS)): ?>
                            <div class="p-8 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200 space-y-3">
                                <div class="w-12 h-12 bg-orange-50 text-[#FF8A00] rounded-2xl flex items-center justify-center mx-auto">
                                    <i data-lucide="folder-plus" class="w-6 h-6"></i>
                                </div>
                                <h4 class="font-bold text-sm text-slate-800">Nenhum Curso Criado</h4>
                                <p class="text-xs text-slate-500">Clique no botão acima para cadastrar o primeiro curso da plataforma.</p>
                                <button onclick="abrirModalCurso()" class="px-4 py-2 bg-[#FF8A00] text-white font-bold text-xs rounded-xl shadow-xs">
                                    Criar Primeiro Curso
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($cursosLMS as $c): 
                                    $isCurSelected = ($cursoAtivoDetalhes && $cursoAtivoDetalhes['id'] == $c['id']);
                                ?>
                                    <div class="p-4 rounded-2xl border transition-all duration-200 bg-white flex flex-col gap-3 <?php echo $isCurSelected ? 'border-[#0D5BA8] shadow-md ring-2 ring-blue-100' : 'border-slate-200 hover:border-slate-300 shadow-2xs'; ?>">
                                        <div class="flex items-start gap-3.5">
                                            <img src="<?php echo htmlspecialchars($c['capa_url'] ?: 'assets/images/oficina-olhar-fercal.jpg'); ?>"
                                                 onerror="this.src='assets/images/oficina-olhar-fercal.jpg'"
                                                 class="w-16 h-16 rounded-xl object-cover border border-slate-100 shrink-0" />
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <?php if (!empty($c['trilha_titulo'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-[#0D5BA8] text-[10px] font-bold truncate max-w-[150px]">
                                                            <?php echo htmlspecialchars($c['trilha_titulo']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo ($c['status'] === 'ativo') ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'; ?>">
                                                        <?php echo ucfirst($c['status']); ?>
                                                    </span>
                                                </div>
                                                <h4 class="font-heading font-bold text-sm text-slate-900 truncate"><?php echo htmlspecialchars($c['titulo']); ?></h4>
                                                <p class="text-xs text-slate-500 line-clamp-1"><?php echo htmlspecialchars($c['descricao'] ?? ''); ?></p>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-slate-500">
                                            <div class="flex items-center gap-3 text-[11px]">
                                                <span>📚 <?php echo $c['total_modulos']; ?> módulos</span>
                                                <span>🎥 <?php echo $c['total_aulas']; ?> aulas</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <a href="dashboard.php?aba=cursos&curso_id=<?php echo $c['id']; ?>" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#0D5BA8] font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                                                    <span>Módulos & Aulas</span>
                                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                </a>
                                                <button onclick='editarCurso(<?php echo json_encode($c); ?>)' class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg" title="Editar Informações do Curso">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja realmente excluir este curso e todos os seus módulos/aulas?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                                    <input type="hidden" name="action" value="excluir_curso">
                                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg" title="Excluir Curso">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Seção de Trilhas de Aprendizado -->
                        <div class="pt-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-heading font-bold text-sm text-slate-900 flex items-center gap-2">
                                    <i data-lucide="layers" class="w-4 h-4 text-[#0D5BA8]"></i>
                                    <span>Trilhas Cadastradas</span>
                                </h4>
                                <button onclick="abrirModalTrilha()" class="text-xs font-bold text-[#0D5BA8] hover:underline">+ Nova Trilha</button>
                            </div>
                            <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2">
                                <?php if (empty($trilhasLMS)): ?>
                                    <p class="text-xs text-slate-400 text-center py-2">Nenhuma trilha cadastrada ainda.</p>
                                <?php else: ?>
                                    <?php foreach ($trilhasLMS as $t): ?>
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                            <div>
                                                <p class="font-bold text-slate-800"><?php echo htmlspecialchars($t['titulo']); ?></p>
                                                <p class="text-[10px] text-slate-400"><?php echo htmlspecialchars($t['eixo'] ?? 'Geral'); ?> • <?php echo htmlspecialchars($t['carga_horaria'] ?? '40h'); ?></p>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button onclick='editarTrilha(<?php echo json_encode($t); ?>)' class="p-1 text-blue-600 hover:bg-blue-100 rounded">
                                                    <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja excluir esta trilha?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                                    <input type="hidden" name="action" value="excluir_trilha">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                    <button type="submit" class="p-1 text-rose-600 hover:bg-rose-100 rounded">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna Direita: Gerenciador de Módulos e Aulas do Curso Selecionado (Estilo LMS Referência) -->
                    <div class="lg:col-span-7 space-y-6">
                        <?php if (!$cursoAtivoDetalhes): ?>
                            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-xs space-y-3">
                                <div class="w-16 h-16 bg-blue-50 text-[#0D5BA8] rounded-2xl flex items-center justify-center mx-auto">
                                    <i data-lucide="layout-list" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading font-bold text-lg text-slate-800">Selecione ou Crie um Curso</h3>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                    Escolha um curso na lista ao lado para gerenciar seus módulos, adicionar aulas, vídeos do YouTube e anexos.
                                </p>
                            </div>
                        <?php else: ?>
                            <!-- Detalhes do Curso Selecionado com Banner da Imagem de Capa -->
                            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                                <!-- Banner com a Imagem de Capa do Curso -->
                                <div class="relative min-h-[170px] bg-slate-950 flex flex-col justify-end p-6 sm:p-8 text-white overflow-hidden">
                                    <!-- Imagem de Fundo da Capa -->
                                    <img src="<?php echo htmlspecialchars($cursoAtivoDetalhes['capa_url'] ?: 'assets/images/oficina-olhar-fercal.jpg'); ?>"
                                         onerror="this.src='assets/images/oficina-olhar-fercal.jpg'"
                                         class="absolute inset-0 w-full h-full object-cover opacity-35 blur-[1px]" />
                                    
                                    <!-- Gradiente de Sobreposição Elegante -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-900/30"></div>

                                    <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-5">
                                        <div class="flex items-start gap-4">
                                            <!-- Miniatura da Capa com Acesso Rápido à Edição -->
                                            <div class="relative group cursor-pointer shrink-0" onclick='editarCurso(<?php echo json_encode($cursoAtivoDetalhes); ?>)'>
                                                <img src="<?php echo htmlspecialchars($cursoAtivoDetalhes['capa_url'] ?: 'assets/images/oficina-olhar-fercal.jpg'); ?>"
                                                     onerror="this.src='assets/images/oficina-olhar-fercal.jpg'"
                                                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-white/30 shadow-lg group-hover:opacity-80 transition-opacity" />
                                                <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-[10px] font-bold text-white transition-opacity">
                                                    <i data-lucide="camera" class="w-4 h-4 mb-0.5"></i>
                                                    <span>Alterar Foto</span>
                                                </div>
                                            </div>

                                            <div class="space-y-1.5 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#FFC107] bg-black/40 px-2.5 py-0.5 rounded-full border border-amber-400/30 backdrop-blur-xs">
                                                        <?php echo htmlspecialchars($cursoAtivoDetalhes['trilha_titulo'] ?? 'Curso Avulso'); ?>
                                                    </span>
                                                    <span class="text-[10px] font-bold text-slate-300 bg-white/10 px-2 py-0.5 rounded-full">
                                                        ⏱ <?php echo htmlspecialchars($cursoAtivoDetalhes['carga_horaria'] ?? '14h'); ?>
                                                    </span>
                                                </div>
                                                <h3 class="font-heading font-black text-xl sm:text-2xl text-white tracking-tight"><?php echo htmlspecialchars($cursoAtivoDetalhes['titulo']); ?></h3>
                                                <p class="text-xs text-slate-300 line-clamp-2 max-w-xl leading-relaxed"><?php echo htmlspecialchars($cursoAtivoDetalhes['descricao'] ?? ''); ?></p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button onclick='editarCurso(<?php echo json_encode($cursoAtivoDetalhes); ?>)' class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl backdrop-blur-xs border border-white/20 transition-all flex items-center gap-1.5" title="Editar Informações e Capa">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                <span>Editar Capa</span>
                                            </button>
                                            <button onclick="abrirModalModulo(<?php echo $cursoAtivoDetalhes['id']; ?>)" class="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-lg transition-all hover:scale-105 flex items-center gap-2 cursor-pointer">
                                                <i data-lucide="folder-plus" class="w-4 h-4"></i>
                                                <span>+ Novo Módulo</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instrução de Arrastar / Mover Módulos -->
                                <div class="px-6 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 text-blue-600"></i>
                                        <span class="font-semibold">Reorganização: Você pode arrastar pelo pegador <b class="font-mono text-slate-700 font-black">⠿</b> ou usar as setas ▲/▼ para mudar a ordem dos módulos facilmente.</span>
                                    </div>
                                    <span id="toast-ordem-status" class="hidden text-emerald-600 font-bold flex items-center gap-1 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Ordem salva!
                                    </span>
                                </div>

                                <!-- Acordeão de Módulos (Reordenável com Drag & Drop / Setas) -->
                                <div class="p-6 space-y-4" id="modulos-sortable-list" data-curso-id="<?php echo $cursoAtivoDetalhes['id']; ?>">
                                    <?php if (empty($cursoAtivoDetalhes['modulos'])): ?>
                                        <div class="p-8 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 space-y-2">
                                            <p class="text-xs font-bold text-slate-700">Este curso ainda não possui módulos cadastrados.</p>
                                            <p class="text-[11px] text-slate-500">Crie o primeiro módulo para começar a publicar as aulas.</p>
                                            <button onclick="abrirModalModulo(<?php echo $cursoAtivoDetalhes['id']; ?>)" class="mt-2 px-4 py-2 bg-[#0D5BA8] text-white font-bold text-xs rounded-xl shadow-xs">
                                                Adicionar Módulo 01
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($cursoAtivoDetalhes['modulos'] as $idx => $mod): ?>
                                            <div class="modulo-item-card border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/50 shadow-2xs transition-all duration-200" data-modulo-id="<?php echo $mod['id']; ?>" draggable="true">
                                                <!-- Cabeçalho do Módulo com Pegador e Botões de Subir/Descer -->
                                                <div class="p-4 bg-white border-b border-slate-100 flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <!-- Pegador Drag Handle -->
                                                        <div class="cursor-grab active:cursor-grabbing p-1.5 text-slate-300 hover:text-slate-700 hover:bg-slate-100 rounded-lg select-none drag-handle" title="Segure e arraste para reordenar este módulo">
                                                            <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                                        </div>

                                                        <!-- Setas Rápidas de Subir / Descer -->
                                                        <div class="flex flex-col gap-0.5 select-none">
                                                            <button type="button" onclick="moverModuloCard(this, 'up')" class="p-0.5 hover:bg-blue-50 text-slate-400 hover:text-[#0D5BA8] rounded leading-none text-[10px] font-bold" title="Mover para Cima">▲</button>
                                                            <button type="button" onclick="moverModuloCard(this, 'down')" class="p-0.5 hover:bg-blue-50 text-slate-400 hover:text-[#0D5BA8] rounded leading-none text-[10px] font-bold" title="Mover para Baixo">▼</button>
                                                        </div>

                                                        <span class="modulo-badge-num w-7 h-7 rounded-full bg-blue-100 text-[#0D5BA8] font-black text-xs flex items-center justify-center shrink-0">
                                                            <?php echo str_pad($idx + 1, 2, '0', STR_PAD_LEFT); ?>
                                                        </span>
                                                        <div class="min-w-0">
                                                            <h4 class="font-heading font-bold text-sm text-slate-900 truncate"><?php echo htmlspecialchars($mod['titulo']); ?></h4>
                                                            <p class="text-[11px] text-slate-400"><?php echo count($mod['aulas']); ?> aulas cadastradas</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <button onclick="abrirModalAula(<?php echo $cursoAtivoDetalhes['id']; ?>, <?php echo $mod['id']; ?>)" class="px-3 py-1.5 bg-[#FF8A00]/10 hover:bg-[#FF8A00] text-[#FF8A00] hover:text-white font-bold text-xs rounded-lg transition-all flex items-center gap-1">
                                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                            <span>Nova Aula</span>
                                                        </button>
                                                        <button onclick='editarModulo(<?php echo json_encode($mod); ?>)' class="p-1.5 text-slate-500 hover:bg-slate-100 rounded-lg" title="Editar Módulo">
                                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja excluir este módulo e todas as suas aulas?')">
                                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                                            <input type="hidden" name="action" value="excluir_modulo">
                                                            <input type="hidden" name="id" value="<?php echo $mod['id']; ?>">
                                                            <input type="hidden" name="curso_id" value="<?php echo $cursoAtivoDetalhes['id']; ?>">
                                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg" title="Excluir Módulo">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <!-- Lista de Aulas do Módulo -->
                                                <div class="p-4 space-y-2">
                                                    <?php if (empty($mod['aulas'])): ?>
                                                        <p class="text-[11px] text-slate-400 italic py-2 text-center">Nenhuma aula cadastrada neste módulo ainda.</p>
                                                    <?php else: ?>
                                                        <?php foreach ($mod['aulas'] as $aIdx => $aula): ?>
                                                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 hover:border-[#0D5BA8] flex items-center justify-between gap-3 shadow-2xs transition-all">
                                                                <div class="flex items-center gap-3 min-w-0">
                                                                    <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                                                        <?php if ($aula['tipo_video'] === 'youtube' || $aula['tipo_video'] === 'upload'): ?>
                                                                            <i data-lucide="play" class="w-3.5 h-3.5 text-rose-500 fill-rose-500"></i>
                                                                        <?php else: ?>
                                                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#0D5BA8]"></i>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <div class="flex items-center gap-2">
                                                                            <h5 class="font-bold text-xs text-slate-900 truncate"><?php echo htmlspecialchars($aula['titulo']); ?></h5>
                                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold <?php echo ($aula['status'] === 'publicado') ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'; ?>">
                                                                                <?php echo ucfirst($aula['status']); ?>
                                                                            </span>
                                                                        </div>
                                                                        <div class="flex items-center gap-3 text-[10px] text-slate-400 mt-0.5">
                                                                            <?php if ($aula['duracao_minutos'] > 0): ?>
                                                                                 <span>⏱ <?php echo $aula['duracao_minutos']; ?> min</span>
                                                                            <?php endif; ?>
                                                                            <?php if (!empty($aula['anexos'])): ?>
                                                                                <span class="text-blue-600 font-bold">📎 <?php echo count($aula['anexos']); ?> anexo(s)</span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="flex items-center gap-1 shrink-0">
                                                                    <button onclick='editarAula(<?php echo json_encode($aula); ?>, <?php echo $cursoAtivoDetalhes['id']; ?>)' class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                                                                        <i data-lucide="edit-3" class="w-3 h-3"></i>
                                                                        <span>Editar</span>
                                                                    </button>
                                                                    <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja excluir esta aula?')">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                                                        <input type="hidden" name="action" value="excluir_aula">
                                                                        <input type="hidden" name="id" value="<?php echo $aula['id']; ?>">
                                                                        <input type="hidden" name="curso_id" value="<?php echo $cursoAtivoDetalhes['id']; ?>">
                                                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg" title="Excluir Aula">
                                                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        <?php endif; ?>

    </main>
</div>

<!-- ==================================================================== -->
<!-- MODAL CRUD VITRINE CULTURAL / BLOG COMPLETO -->
<!-- ==================================================================== -->
<div id="modal-vitrine-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-orange-50 text-[#FF8A00] flex items-center justify-center">
                    <i data-lucide="grid" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 id="modal-vitrine-title" class="font-heading font-bold text-lg text-slate-900">Novo Post na Vitrine</h3>
                    <p class="text-[11px] text-slate-500">Publicação de fotos, ensaios, vídeos, podcasts e artigos autorais.</p>
                </div>
            </div>
            <button onclick="fecharModal('modal-vitrine-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_vitrine">
            <input type="hidden" id="vitrine_id" name="id" value="">
            <input type="hidden" id="vitrine_media_atual" name="media_atual" value="">

            <!-- Seção Mídia / Imagem com Preview -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center gap-4">
                <div class="relative shrink-0">
                    <img id="vitrine_media_preview" 
                         src="assets/images/oficina-olhar-fercal.jpg" 
                         onerror="this.src='assets/images/oficina-olhar-fercal.jpg'"
                         class="w-28 h-20 rounded-xl object-cover border-2 border-white shadow-md bg-white" />
                </div>
                <div class="space-y-1 text-center sm:text-left flex-1">
                    <label class="block text-xs font-bold text-slate-800">Foto de Capa / Imagem Principal</label>
                    <p class="text-[11px] text-slate-500">Salva no servidor em <code class="font-mono bg-white px-1 py-0.5 rounded text-blue-600">/public/vitrine/</code>.</p>
                    <input type="file" 
                           id="vitrine_media_input"
                           name="media_arquivo" 
                           accept="image/jpeg,image/png,image/webp,image/gif,audio/mp3,video/mp4" 
                           onchange="previewVitrineMedia(this)"
                           class="text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0D5BA8] file:text-white cursor-pointer">
                </div>
            </div>

            <!-- Título & Subtítulo -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Título da Publicação *</label>
                    <input type="text" id="vitrine_titulo" name="titulo" required placeholder="Ex: Mapeie Seu Bairro: Olhar sobre as Feiras Livres" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Categoria / Tipo *</label>
                    <select id="vitrine_tipo" name="tipo" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="Foto">Foto</option>
                        <option value="Vídeo">Vídeo</option>
                        <option value="Ensaio">Ensaio</option>
                        <option value="Áudio">Áudio / Podcast</option>
                        <option value="Perfil">Perfil</option>
                        <option value="Espaço">Espaço</option>
                        <option value="Notícia">Notícia / Artigo</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Subtítulo / Linha Fina</label>
                <input type="text" id="vitrine_subtitulo" name="subtitulo" placeholder="Ex: Cores e sabores matinais na Feira Central de Sobradinho" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white">
            </div>

            <!-- Cidade & Bairro Dinâmico -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cidade / Estado</label>
                    <select id="vitrine_cidade_select" name="cidade" onchange="carregarBairrosPorCidade(this.value, 'vitrine_bairro_select')" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                        <option value="Brasília/DF">Brasília / DF</option>
                        <option value="Entorno/DF">Entorno / GO-MG</option>
                        <option value="Nacional">Nacional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Região Administrativa / Bairro</label>
                    <select id="vitrine_bairro_select" name="bairro" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                    </select>
                </div>
            </div>

            <!-- Resumo (Cards) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Resumo / Descrição Curta (Aparece no Card) *</label>
                <textarea id="vitrine_descricao" name="descricao" rows="2" required placeholder="Breve introdução para atrair o leitor nos cards da vitrine..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white"></textarea>
            </div>

            <!-- Artigo Completo (Blog / Relato Extenso) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Conteúdo Completo do Artigo / Relato (Blog)</label>
                <textarea id="vitrine_conteudo" name="conteudo" rows="6" placeholder="Escreva o texto completo do artigo, relato, ensaio ou reportagem..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-normal focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white"></textarea>
            </div>

            <!-- Mídias Complementares (Vídeo & Áudio Embeds) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-amber-50/50 border border-amber-200/70">
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center gap-1">
                        <i data-lucide="video" class="w-3.5 h-3.5 text-red-600"></i>
                        <span>Link de Vídeo (YouTube, Vimeo, MP4)</span>
                    </label>
                    <input type="text" id="vitrine_video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center gap-1">
                        <i data-lucide="music" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                        <span>Link de Áudio (Spotify, SoundCloud, MP3)</span>
                    </label>
                    <input type="text" id="vitrine_audio_url" name="audio_url" placeholder="https://open.spotify.com/episode/... ou link MP3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <!-- Autor & Tags -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Autor / Criador</label>
                    <input type="text" id="vitrine_autor_nome" name="autor_nome" placeholder="Ex: Daniel Rodrigues" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cargo / Papel do Autor</label>
                    <input type="text" id="vitrine_autor_role" name="autor_role" placeholder="Ex: Administrador, Aluno - Eixo Fotografia" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tags (separadas por vírgula)</label>
                    <input type="text" id="vitrine_tags" name="tags" placeholder="feira, fotografia, cerrado" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="vitrine_destaque" name="destaque" value="1" class="rounded text-[#FF8A00] focus:ring-[#FF8A00] w-4 h-4 cursor-pointer">
                <label for="vitrine_destaque" class="text-xs font-bold text-slate-800 cursor-pointer">Destacar este post na página inicial e no topo da Vitrine</label>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-vitrine-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold cursor-pointer">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white rounded-xl text-xs font-bold shadow-md cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Salvar Publicação</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- MODAL CRUD USUÁRIO COMPLETO (1080x1080 & BANCO MYSQL) -->
<!-- ==================================================================== -->
<div id="modal-usuario-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center">
                    <i data-lucide="user-cog" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 id="modal-usuario-title" class="font-heading font-bold text-lg text-slate-900">Novo Usuário</h3>
                    <p id="modal-usuario-id-tag" class="text-[10px] text-slate-400 font-mono"></p>
                </div>
            </div>
            <button onclick="fecharModal('modal-usuario-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_usuario">
            <input type="hidden" id="usuario_id" name="id" value="">
            <input type="hidden" id="usuario_avatar_atual" name="avatar_atual" value="">

            <!-- Seção Foto de Perfil 1080x1080 -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center gap-4">
                <div class="relative shrink-0">
                    <img id="usuario_avatar_preview" 
                         src="assets/images/avatar-default.jpg" 
                         class="w-20 h-20 rounded-2xl object-cover border-2 border-white shadow-md bg-white" />
                    <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-slate-900 text-white">
                        1080x1080
                    </span>
                </div>
                <div class="space-y-1.5 text-center sm:text-left flex-1">
                    <label class="block text-xs font-bold text-slate-800">Foto de Perfil (Avatar)</label>
                    <p class="text-[11px] text-slate-500">Salva em <code class="font-mono bg-white px-1 py-0.5 rounded text-blue-600">/public/perfil/</code> com corte 1:1 automático.</p>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <input type="file" 
                               id="usuario_avatar_input"
                               name="avatar_arquivo" 
                               accept="image/jpeg,image/png,image/webp" 
                               onchange="previewAvatar(this)"
                               class="text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0D5BA8] file:text-white cursor-pointer">
                        <label id="usuario_remover_foto_box" class="hidden items-center gap-1.5 text-[11px] font-bold text-rose-600 cursor-pointer hover:underline">
                            <input type="checkbox" id="usuario_remover_foto" name="remover_foto" value="1" onchange="toggleRemoverFoto(this)">
                            <span>Remover foto atual</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Nome Completo & E-mail -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nome Completo *</label>
                    <input type="text" id="usuario_nome" name="nome" required placeholder="Ex: Daniel Rodrigues" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">E-mail de Acesso *</label>
                    <input type="email" id="usuario_email" name="email" required placeholder="daniel@fotocidade.org" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Nome de Usuário & Nível de Acesso -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nome de Usuário (Login)</label>
                    <input type="text" id="usuario_username" name="username" placeholder="Ex: daniel" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nível de Acesso *</label>
                    <select id="usuario_nivel" name="nivel" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800">
                        <option value="aluno">Aluno (Acesso à Trilha/Perfil)</option>
                        <option value="admin">Administrador (Acesso Total Dashboard)</option>
                    </select>
                </div>
            </div>

            <!-- Senha & Telefone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Senha 
                        <span id="senha_hint" class="font-normal text-slate-400 text-[10px]">(deixe em branco p/ manter)</span>
                    </label>
                    <input type="password" id="usuario_senha" name="senha" placeholder="••••••••" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Telefone / WhatsApp</label>
                    <input type="text" id="usuario_telefone" name="telefone" placeholder="(61) 99999-9999" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Cidade & Bairro -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cidade / RA</label>
                    <input type="text" id="usuario_cidade" name="cidade" placeholder="Ex: Sobradinho I, Fercal, Ceilândia" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bairro / Setor</label>
                    <input type="text" id="usuario_bairro" name="bairro" placeholder="Ex: Quadra 04, Setor de Mansões" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Endereço Completo -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Endereço Completo</label>
                <input type="text" id="usuario_endereco" name="endereco" placeholder="Ex: Conjunto A, Casa 12, Sobradinho" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all">
            </div>

            <!-- Biografia -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Biografia / Apresentação</label>
                <textarea id="usuario_biografia" name="biografia" rows="2" placeholder="Breve histórico, interesses fotográficos ou formação..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white transition-all"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-usuario-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white rounded-xl text-xs font-bold shadow-md transition-all hover:scale-105">Salvar Usuário</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- MODAL CRUD MAPA CULTURAL COM LEAFLET & GPS -->
<!-- ==================================================================== -->
<div id="modal-mapa-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#00A7B5] flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <h3 id="modal-mapa-title" class="font-heading font-bold text-lg text-slate-900">Mapear Ponto Cultural</h3>
            </div>
            <button onclick="fecharModal('modal-mapa-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_ponto_mapa">
            <input type="hidden" id="ponto_id" name="id" value="">
            <input type="hidden" id="ponto_foto_atual" name="foto_atual" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nome do Ponto / Espaço / Serviço *</label>
                <input type="text" id="ponto_nome" name="nome" required placeholder="Ex: Casa de Capoeira, C.E.M 01 de Sobradinho" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Categoria (13 Opções) *</label>
                    <select id="ponto_categoria" name="categoria" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="Patrimônio">Patrimônio</option>
                        <option value="Espaço Cultural">Espaço Cultural</option>
                        <option value="Coletivo">Coletivo</option>
                        <option value="Ponto de Memória">Ponto de Memória</option>
                        <option value="Feira Cultural">Feira Cultural</option>
                        <option value="Órgão Público">Órgão Público</option>
                        <option value="Hospital">Hospital</option>
                        <option value="Escola">Escola</option>
                        <option value="Empresa">Empresa</option>
                        <option value="ONG">ONG</option>
                        <option value="Instituto">Instituto</option>
                        <option value="Associação">Associação</option>
                        <option value="Turismo">Turismo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cidade / Estado *</label>
                    <select id="ponto_cidade_select" name="cidade" onchange="carregarBairrosPorCidade(this.value, 'ponto_bairro_select')" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="Brasília/DF">Brasília / DF</option>
                        <option value="Entorno do DF (GO)">Entorno do DF (GO)</option>
                        <option value="Goiânia/GO">Goiânia / GO</option>
                        <option value="São Paulo/SP">São Paulo / SP</option>
                        <option value="Rio de Janeiro/RJ">Rio de Janeiro / RJ</option>
                        <option value="Belo Horizonte/MG">Belo Horizonte / MG</option>
                        <option value="Outra Cidade do Brasil">Outra Cidade do Brasil</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bairro / Região Administrativa *</label>
                    <select id="ponto_bairro_select" name="regiao" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <!-- Preenchido via Javascript -->
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bairro / Complemento Manual</label>
                    <input type="text" id="ponto_bairro" name="bairro" placeholder="Quadra 04, Setor de Mansões" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Endereço Completo</label>
                <input type="text" id="ponto_endereco" name="endereco" placeholder="Quadra 02 Área Especial" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <!-- Mapa Interativo Picker Leaflet -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Selecione no Mapa (Clique ou Arraste o Pino)</label>
                <div id="leaflet-picker-map" class="w-full h-44 rounded-2xl border border-slate-200 shadow-inner overflow-hidden"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Latitude</label>
                    <input type="number" step="any" id="ponto_lat" name="lat" required class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Longitude</label>
                    <input type="number" step="any" id="ponto_lng" name="lng" required class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Instagram</label>
                    <input type="text" id="ponto_instagram" name="instagram" placeholder="@perfil" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Website</label>
                    <input type="text" id="ponto_website" name="website" placeholder="https://site.com" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">WhatsApp</label>
                    <input type="text" id="ponto_whatsapp" name="whatsapp" placeholder="(61) 98765-4321" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <!-- Foto do Ponto com Preview -->
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
                <img id="ponto_foto_preview" src="assets/images/oficina-olhar-fercal.jpg" class="w-16 h-16 rounded-xl object-cover border border-slate-300 bg-white" />
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto do Ponto (Upload Local)</label>
                    <input type="file" name="foto_arquivo" accept="image/*" onchange="previewPontoFoto(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#00A7B5] file:text-white cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Salvo em <code class="font-mono bg-white px-1 py-0.5 rounded text-teal-600">/public/mapa/</code></p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Descrição do Ponto *</label>
                <textarea id="ponto_descricao" name="descricao" rows="2" required placeholder="Histórico, relevância cultural ou serviços oferecidos..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-mapa-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#00A7B5] hover:bg-[#008f9c] text-white rounded-xl text-xs font-bold shadow-md">Salvar Ponto no Mapa</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- MODAL CRUD PARCEIRO / ORGANIZAÇÃO -->
<!-- ==================================================================== -->
<div id="modal-parceiro-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-[#FF8A00] flex items-center justify-center">
                    <i data-lucide="handshake" class="w-4 h-4"></i>
                </div>
                <h3 id="modal-parceiro-title" class="font-heading font-bold text-lg text-slate-900">Novo Parceiro / Organização</h3>
            </div>
            <button onclick="fecharModal('modal-parceiro-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_parceiro">
            <input type="hidden" id="parceiro_id" name="id" value="">
            <input type="hidden" id="parceiro_logo_atual" name="logo_atual" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nome da Organização / Parceiro *</label>
                <input type="text" id="parceiro_nome" name="nome" required placeholder="Ex: Sesc Sobradinho, Administração Regional" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Setor Institucional *</label>
                    <select id="parceiro_setor" name="setor" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="1º Setor (Poder Público)">1º Setor (Poder Público)</option>
                        <option value="2º Setor (Empresa / Privado)">2º Setor (Empresa / Privado)</option>
                        <option value="3º Setor (ONG / Associação)">3º Setor (ONG / Associação)</option>
                        <option value="Turismo">Turismo & Cultura</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Organização *</label>
                    <input type="text" id="parceiro_tipo" name="tipo" required placeholder="Ex: Governo, Escola, ONG, Empresa" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <!-- Seleção Cascata de Cidade e Bairro/Região -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cidade / Estado *</label>
                    <select id="parceiro_cidade_select" name="cidade" onchange="carregarBairrosPorCidade(this.value, 'parceiro_bairro_select')" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="Brasília/DF">Brasília / DF</option>
                        <option value="Entorno do DF (GO)">Entorno do DF (GO)</option>
                        <option value="Goiânia/GO">Goiânia / GO</option>
                        <option value="São Paulo/SP">São Paulo / SP</option>
                        <option value="Rio de Janeiro/RJ">Rio de Janeiro / RJ</option>
                        <option value="Belo Horizonte/MG">Belo Horizonte / MG</option>
                        <option value="Outra Cidade do Brasil">Outra Cidade do Brasil</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bairro / Região Administrativa *</label>
                    <select id="parceiro_bairro_select" name="bairro" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <!-- Preenchido via Javascript -->
                    </select>
                </div>
            </div>

            <!-- Endereço Completo -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Endereço Completo (Rua, Quadra, Lote)</label>
                <input type="text" id="parceiro_endereco" name="endereco" placeholder="Ex: Quadra Central, St. Administrativo Lote A - Sobradinho" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <!-- Mapa Interativo Picker Leaflet para Geolocalizar Parceiro no Mapa Cultural -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Selecione no Mapa para Exibir no Mapa Cultural (Clique ou Arraste o Pino)</label>
                <div id="leaflet-parceiro-picker-map" class="w-full h-40 rounded-2xl border border-slate-200 shadow-inner overflow-hidden"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Latitude (Opcional p/ pino no Mapa)</label>
                    <input type="number" step="any" id="parceiro_lat" name="lat" placeholder="-15.6534" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Longitude (Opcional p/ pino no Mapa)</label>
                    <input type="number" step="any" id="parceiro_lng" name="lng" placeholder="-47.7891" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Descrição Resumida *</label>
                <textarea id="parceiro_descricao" name="descricao" rows="2" required placeholder="História, atuação e foco da organização..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Atuação na Rede / Contribuição</label>
                <input type="text" id="parceiro_contribuicao" name="contribuicao" placeholder="Ex: Apoio institucional, cessão de espaço..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Instagram</label>
                    <input type="text" id="parceiro_instagram" name="instagram" placeholder="@perfil" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Website</label>
                    <input type="text" id="parceiro_website" name="website" placeholder="https://site.com" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">WhatsApp</label>
                    <input type="text" id="parceiro_whatsapp" name="whatsapp" placeholder="(61) 98765-4321" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Logo ou Marca (Upload Local)</label>
                <input type="file" name="logo_arquivo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FF8A00] file:text-white">
                <p class="text-[10px] text-slate-400 mt-1">Salvo automaticamente em <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-amber-600">/public/parceiros/{nome_do_local}.jpg</code></p>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-parceiro-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white rounded-xl text-xs font-bold shadow-md">Salvar Parceiro</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- 1. MODAL CRUD TRILHA DE APRENDIZADO -->
<!-- ==================================================================== -->
<div id="modal-trilha-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
                <h3 id="modal-trilha-title" class="font-heading font-bold text-lg text-slate-900">Nova Trilha de Aprendizado</h3>
            </div>
            <button onclick="fecharModal('modal-trilha-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_trilha">
            <input type="hidden" id="trilha_id" name="id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Título da Trilha *</label>
                <input type="text" id="trilha_titulo" name="titulo" required placeholder="Ex: Trilha de Mapeamento & Audiovisual" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Eixo / Área Temática</label>
                    <input type="text" id="trilha_eixo" name="eixo" placeholder="Ex: Comunicação Comunitária" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Carga Horária Estimada</label>
                    <input type="text" id="trilha_carga_horaria" name="carga_horaria" placeholder="Ex: 40 horas" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Descrição da Trilha</label>
                <textarea id="trilha_descricao" name="descricao" rows="3" placeholder="Objetivos formativos e competências desenvolvidas nesta trilha..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ordem de Exibição</label>
                    <input type="number" id="trilha_ordem" name="ordem" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select id="trilha_status" name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="ativo">Ativo (Público)</option>
                        <option value="rascunho">Rascunho</option>
                        <option value="inativo">Inativo / Oculto</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-trilha-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white rounded-xl text-xs font-bold shadow-md">Salvar Trilha</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- 2. MODAL CRUD CURSO -->
<!-- ==================================================================== -->
<div id="modal-curso-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-orange-50 text-[#FF8A00] flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                </div>
                <h3 id="modal-curso-title" class="font-heading font-bold text-lg text-slate-900">Novo Curso</h3>
            </div>
            <button onclick="fecharModal('modal-curso-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_curso">
            <input type="hidden" id="curso_id" name="id" value="">
            <input type="hidden" id="curso_capa_atual" name="capa_atual" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Trilha de Aprendizado Vinculada</label>
                <select id="curso_trilha_id" name="trilha_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                    <option value="">-- Sem Trilha (Curso Avulso / Independente) --</option>
                    <?php foreach ($trilhasLMS as $tr): ?>
                        <option value="<?php echo $tr['id']; ?>"><?php echo htmlspecialchars($tr['titulo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Título do Curso *</label>
                <input type="text" id="curso_titulo" name="titulo" required placeholder="Ex: Fotografia Documental e Cartografia Afetiva" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Professor / Facilitador</label>
                    <input type="text" id="curso_professor_nome" name="professor_nome" value="FotoCidade DF" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Carga Horária (ex: 14h)</label>
                    <input type="text" id="curso_carga_horaria" name="carga_horaria" placeholder="Ex: 14 horas" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Descrição / Ementa do Curso</label>
                <textarea id="curso_descricao" name="descricao" rows="3" placeholder="Apresentação do curso, metodologia e o que os alunos vão produzir..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
            </div>

            <!-- Upload da Imagem de Capa do Curso -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-4">
                <img id="curso_capa_preview" src="assets/images/oficina-olhar-fercal.jpg" class="w-16 h-16 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0" />
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Capa do Curso (Upload)</label>
                    <input type="file" name="capa_arquivo" accept="image/*" onchange="previewCursoCapa(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FF8A00] file:text-white cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Salvo em <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-amber-600">/public/cursos/</code></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ordem</label>
                    <input type="number" id="curso_ordem" name="ordem" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select id="curso_status" name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="ativo">Ativo (Publicado)</option>
                        <option value="rascunho">Rascunho</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-curso-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white rounded-xl text-xs font-bold shadow-md">Salvar Curso</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- ==================================================================== -->
<!-- 3. MODAL CRUD MÓDULO (EXPANDIDO: EDITOR RICO WYSIWYG, VÍDEO, ÁUDIO, ANEXOS) -->
<!-- ==================================================================== -->
<div id="modal-modulo-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-5xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[96vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center shadow-xs">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-modulo-title" class="font-heading font-extrabold text-xl text-slate-900">Estruturar Conteúdo do Módulo</h3>
                    <p class="text-[11px] text-slate-400">Editor visual estilo WordPress / TinyMCE com texto rico, vídeos, áudios e materiais em PDF.</p>
                </div>
            </div>
            <button onclick="fecharModal('modal-modulo-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" onsubmit="syncModuloContent()" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_modulo">
            <input type="hidden" id="modulo_id" name="id" value="">
            <input type="hidden" id="modulo_curso_id" name="curso_id" value="">
            <textarea id="modulo_conteudo_hidden" name="conteudo" class="hidden"></textarea>

            <!-- Título do Módulo -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Título do Módulo *</label>
                    <input type="text" id="modulo_titulo" name="titulo" required placeholder="Ex: Módulo 01 - Introdução ao Enquadramento e Luz" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900">
                </div>
                <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Ordem no Curso</label>
                    <input type="number" id="modulo_ordem" name="ordem" value="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                </div>
            </div>

            <!-- Descrição Curta -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Resumo Curto / Objetivos do Módulo</label>
                <textarea id="modulo_descricao" name="descricao" rows="2" placeholder="Resumo pedagógico e conceitos fundamentais desenvolvidos neste módulo..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
            </div>

            <!-- ========================================================= -->
            <!-- EDITOR DE CONTEÚDO RICO (WYSIWYG - Estilo WordPress / TinyMCE Completo) -->
            <!-- ========================================================= -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                <!-- Botão Destaque WordPress: Adicionar Mídia -->
                <div class="p-2.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <button type="button" onclick="abrirMediaLibrary('modulo', 'all')" class="px-4 py-1.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        <span>📁 Adicionar Mídia (Media Library /public)</span>
                    </button>
                    
                    <!-- Alternar Visual / HTML -->
                    <div class="flex items-center gap-1">
                        <button type="button" id="btn-mod-mode-visual" onclick="toggleModuloEditorMode('visual')" class="px-3 py-1 bg-[#0D5BA8] text-white font-bold text-xs rounded-lg">Visual</button>
                        <button type="button" id="btn-mod-mode-html" onclick="toggleModuloEditorMode('html')" class="px-3 py-1 bg-slate-200 text-slate-700 hover:bg-slate-300 text-xs rounded-lg">Código HTML</button>
                    </div>
                </div>

                <!-- Barra de Ferramentas WordPress Completa -->
                <div class="bg-slate-100 p-2.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-1.5 select-none">
                    <div class="flex flex-wrap items-center gap-1">
                        <!-- Headings Dropdown -->
                        <select onchange="formatModuloBlock(this.value); this.selectedIndex=0;" class="px-2.5 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium cursor-pointer">
                            <option value="">Formato do Texto</option>
                            <option value="p">Parágrafo Padrão</option>
                            <option value="h1">Título Principal (H1)</option>
                            <option value="h2">Subtítulo (H2)</option>
                            <option value="h3">Tópico (H3)</option>
                            <option value="blockquote">Citação (Blockquote)</option>
                            <option value="pre">Bloco de Código (Pre)</option>
                        </select>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Estilos Básicos -->
                        <button type="button" onclick="execModuloCmd('bold')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded font-black text-xs" title="Negrito (Ctrl+B)">B</button>
                        <button type="button" onclick="execModuloCmd('italic')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded italic font-serif text-xs" title="Itálico (Ctrl+I)">I</button>
                        <button type="button" onclick="execModuloCmd('underline')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded underline text-xs" title="Sublinhado">U</button>
                        <button type="button" onclick="execModuloCmd('strikeThrough')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded line-through text-xs" title="Riscado">S</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Alinhamentos -->
                        <button type="button" onclick="execModuloCmd('justifyLeft')" class="px-2 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Alinhar à Esquerda">⇤</button>
                        <button type="button" onclick="execModuloCmd('justifyCenter')" class="px-2 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs font-bold" title="Centralizar">≡</button>
                        <button type="button" onclick="execModuloCmd('justifyRight')" class="px-2 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Alinhar à Direita">⇥</button>
                        <button type="button" onclick="execModuloCmd('justifyFull')" class="px-2 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Justificado">≣</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Listas & Citações -->
                        <button type="button" onclick="execModuloCmd('insertUnorderedList')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Lista com Marcadores">• Lista</button>
                        <button type="button" onclick="execModuloCmd('insertOrderedList')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Lista Numerada">1. Lista</button>
                        <button type="button" onclick="execModuloCmd('formatBlock', '<blockquote>')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs font-serif" title="Citação">❝ Citação</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Inserções no Meio do Texto conectadas à Media Library e links -->
                        <button type="button" onclick="inserirLinkModulo()" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-blue-600 font-bold flex items-center gap-1" title="Inserir Link">🔗 Link</button>
                        <button type="button" onclick="abrirMediaLibrary('modulo', 'image')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-emerald-700 font-bold flex items-center gap-1" title="Inserir Imagem do Servidor / Upload">🖼️ Imagem</button>
                        <button type="button" onclick="abrirMediaLibrary('modulo', 'video')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-rose-600 font-bold flex items-center gap-1" title="Inserir Vídeo do Servidor / YouTube">🎬 Vídeo</button>
                        <button type="button" onclick="abrirMediaLibrary('modulo', 'audio')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-teal-600 font-bold flex items-center gap-1" title="Inserir Áudio do Servidor">🎧 Áudio</button>
                        <button type="button" onclick="abrirMediaLibrary('modulo', 'document')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-amber-700 font-bold flex items-center gap-1" title="Inserir PDF / Documento">📄 PDF</button>
                        <button type="button" onclick="inserirTabelaModulo()" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-purple-600 font-bold flex items-center gap-1" title="Inserir Tabela">📊 Tabela</button>
                        <button type="button" onclick="execModuloCmd('insertHorizontalRule')" class="px-2 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs font-bold" title="Linha Divisória">―</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Cores de Texto e Realce -->
                        <label class="flex items-center gap-1 px-2 py-1 bg-white border border-slate-300 rounded text-[11px] cursor-pointer" title="Cor do Texto">
                            <span>A</span>
                            <input type="color" onchange="execModuloCmd('foreColor', this.value)" class="w-4 h-4 border-0 p-0 cursor-pointer rounded">
                        </label>
                        <label class="flex items-center gap-1 px-2 py-1 bg-white border border-slate-300 rounded text-[11px] cursor-pointer" title="Cor de Realce (Marca-Texto)">
                            <span>🖍</span>
                            <input type="color" value="#FFF59D" onchange="execModuloCmd('hiliteColor', this.value)" class="w-4 h-4 border-0 p-0 cursor-pointer rounded">
                        </label>

                        <button type="button" onclick="execModuloCmd('removeFormat')" class="px-2.5 py-1.5 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-slate-500" title="Limpar Formatação">Tx</button>
                    </div>
                </div>

                <!-- Área Editável Visual -->
                <div id="modulo_editor_visual" contenteditable="true" class="p-5 min-h-[260px] max-h-[400px] overflow-y-auto bg-white text-slate-800 text-sm focus:outline-none leading-relaxed prose prose-slate max-w-none">
                    <p>Escreva o conteúdo textual, referências, orientações didáticas e conceitos para este módulo...</p>
                </div>

                <!-- Área Código HTML (Oculta por padrão) -->
                <textarea id="modulo_editor_html" rows="10" class="hidden w-full p-5 font-mono text-xs bg-slate-900 text-slate-100 focus:outline-none leading-relaxed"></textarea>
            </div>

            <!-- ========================================================= -->
            <!-- SEÇÃO DE VÍDEO DO MÓDULO (YouTube / Vimeo / MP4 Upload) -->
            <!-- ========================================================= -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="play-circle" class="w-4 h-4 text-rose-500"></i>
                        <span>Vídeo do Módulo (YouTube, Vimeo ou Upload MP4)</span>
                    </h4>
                    <span class="text-[10px] text-slate-400">Player de vídeo embutido</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Origem do Vídeo</label>
                        <select id="modulo_tipo_video" name="tipo_video" onchange="toggleModuloVideoInputs(this.value)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="youtube">YouTube (Link / Embed)</option>
                            <option value="vimeo">Vimeo (Link)</option>
                            <option value="upload">Upload de Vídeo Local (MP4)</option>
                            <option value="nenhum">Sem Vídeo</option>
                        </select>
                    </div>
                    
                    <div id="mod_box_url_video" class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Link do Vídeo (YouTube / Vimeo)</label>
                        <input type="text" id="modulo_url_video" name="url_video" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono">
                    </div>

                    <div id="mod_box_upload_video" class="hidden sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Upload de Arquivo de Vídeo (MP4 / WebM)</label>
                        <input type="file" name="video_arquivo" accept="video/mp4,video/webm" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-500 file:text-white cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-0.5">Salvo em <code class="font-mono bg-white px-1 py-0.5 rounded text-rose-600">/public/aulas/videos/</code></p>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- SEÇÃO DE ÁUDIO DO MÓDULO (Podcast / Narração MP3) -->
            <!-- ========================================================= -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="headphones" class="w-4 h-4 text-emerald-600"></i>
                        <span>Áudio / Podcast do Módulo (Player Específico)</span>
                    </h4>
                    <span class="text-[10px] text-slate-400">Reprodução de áudio nativo</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Origem do Áudio</label>
                        <select id="modulo_tipo_audio" name="tipo_audio" onchange="toggleModuloAudioInputs(this.value)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="nenhum">Sem Áudio</option>
                            <option value="upload">Upload de Áudio (MP3 / WAV / OGG)</option>
                            <option value="link">Link Externo de Áudio (URL)</option>
                        </select>
                    </div>

                    <div id="mod_box_upload_audio" class="hidden sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Upload de Áudio MP3 (Hospedado no Servidor)</label>
                        <input type="file" name="audio_arquivo" accept="audio/mp3,audio/wav,audio/ogg,audio/m4a" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-0.5">Salvo em <code class="font-mono bg-white px-1 py-0.5 rounded text-emerald-600">/public/aulas/audios/</code></p>
                    </div>

                    <div id="mod_box_url_audio" class="hidden sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Link / URL do Áudio</label>
                        <input type="text" id="modulo_audio_url" name="audio_url" placeholder="https://exemplo.com/audio.mp3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                </div>
            </div>

            <!-- Duração e Botões -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Duração Estimada do Módulo (Minutos)</label>
                    <input type="number" id="modulo_duracao_minutos" name="duracao_minutos" value="20" placeholder="Ex: 25" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-modulo-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-8 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white rounded-xl text-xs font-bold shadow-md">Salvar Módulo com Conteúdo</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- 4. MODAL CRUD AULA (EDITOR RICO ESTILO WORDPRESS - Referência: image_f0a333.jpg) -->
<!-- ==================================================================== -->
<div id="modal-aula-crud" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-5xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-100 max-h-[96vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-orange-50 text-[#FF8A00] flex items-center justify-center">
                    <i data-lucide="video" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 id="modal-aula-title" class="font-heading font-bold text-lg text-slate-900">Editar Aula</h3>
                    <p class="text-[11px] text-slate-400">Editor visual estilo WordPress / TinyMCE com formatação, vídeos, áudios e anexos.</p>
                </div>
            </div>
            <button onclick="fecharModal('modal-aula-crud')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
        </div>

        <form method="POST" action="dashboard.php" enctype="multipart/form-data" onsubmit="syncAulaContent()" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="salvar_aula">
            <input type="hidden" id="aula_id" name="id" value="">
            <input type="hidden" id="aula_curso_id" name="curso_id" value="">
            <input type="hidden" id="aula_modulo_id" name="modulo_id" value="">
            <textarea id="aula_conteudo_hidden" name="conteudo" class="hidden"></textarea>

            <!-- Título da Aula -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Título da Aula *</label>
                <input type="text" id="aula_titulo" name="titulo" required placeholder="Ex: Aula 01 - Luz, Sombra e Enquadramento Urbano" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900">
            </div>

            <!-- ========================================================= -->
            <!-- EDITOR DE CONTEÚDO RICO DA AULA (WYSIWYG - Estilo WordPress Completo) -->
            <!-- ========================================================= -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                <!-- Botão Destaque WordPress: Adicionar Mídia -->
                <div class="p-2.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <button type="button" onclick="abrirMediaLibrary('aula', 'all')" class="px-4 py-1.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        <span>📁 Adicionar Mídia (Media Library /public)</span>
                    </button>
                    
                    <!-- Alternar Visual / HTML -->
                    <div class="flex items-center gap-1">
                        <button type="button" id="btn-aula-mode-visual" onclick="toggleAulaEditorMode('visual')" class="px-2.5 py-1 bg-[#0D5BA8] text-white font-bold text-xs rounded">Visual</button>
                        <button type="button" id="btn-aula-mode-html" onclick="toggleAulaEditorMode('html')" class="px-2.5 py-1 bg-slate-200 text-slate-700 hover:bg-slate-300 text-xs rounded">Código HTML</button>
                    </div>
                </div>

                <!-- Barra de Ferramentas WordPress -->
                <div class="bg-slate-100 p-2.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-1.5 select-none">
                    <div class="flex flex-wrap items-center gap-1">
                        <!-- Headings Dropdown -->
                        <select onchange="formatAulaBlock(this.value); this.selectedIndex=0;" class="px-2 py-1 bg-white border border-slate-300 rounded text-xs font-medium cursor-pointer">
                            <option value="">Formato do Texto</option>
                            <option value="p">Parágrafo Padrão</option>
                            <option value="h1">Título 1 (H1)</option>
                            <option value="h2">Título 2 (H2)</option>
                            <option value="h3">Título 3 (H3)</option>
                            <option value="blockquote">Citação (Blockquote)</option>
                            <option value="pre">Bloco de Código (Pre)</option>
                        </select>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <button type="button" onclick="execAulaCmd('bold')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded font-black text-xs" title="Negrito (Ctrl+B)">B</button>
                        <button type="button" onclick="execAulaCmd('italic')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded italic font-serif text-xs" title="Itálico (Ctrl+I)">I</button>
                        <button type="button" onclick="execAulaCmd('underline')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded underline text-xs" title="Sublinhado">U</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Alinhamentos Aula -->
                        <button type="button" onclick="execAulaCmd('justifyLeft')" class="px-2 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Alinhar à Esquerda">⇤</button>
                        <button type="button" onclick="execAulaCmd('justifyCenter')" class="px-2 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs font-bold" title="Centralizar">≡</button>
                        <button type="button" onclick="execAulaCmd('justifyRight')" class="px-2 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Alinhar à Direita">⇥</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <button type="button" onclick="execAulaCmd('insertUnorderedList')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Lista com Marcadores">• Lista</button>
                        <button type="button" onclick="execAulaCmd('insertOrderedList')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs" title="Lista Numerada">1. Lista</button>
                        <button type="button" onclick="execAulaCmd('formatBlock', '<blockquote>')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs font-serif" title="Citação">❝ Citação</button>

                        <div class="h-4 w-px bg-slate-300 mx-1"></div>

                        <!-- Inserção de Link, Imagem, Vídeo, Áudio e Tabela da Media Library -->
                        <button type="button" onclick="inserirLinkAula()" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-blue-600 font-bold" title="Inserir Link">🔗 Link</button>
                        <button type="button" onclick="abrirMediaLibrary('aula', 'image')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-emerald-700 font-bold" title="Inserir Imagem">🖼️ Imagem</button>
                        <button type="button" onclick="abrirMediaLibrary('aula', 'video')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-rose-600 font-bold" title="Inserir Vídeo">🎬 Vídeo</button>
                        <button type="button" onclick="abrirMediaLibrary('aula', 'audio')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-teal-600 font-bold" title="Inserir Áudio">🎧 Áudio</button>
                        <button type="button" onclick="abrirMediaLibrary('aula', 'document')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-amber-700 font-bold" title="Inserir PDF">📄 PDF</button>
                        <button type="button" onclick="inserirTabelaAula()" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-purple-600 font-bold" title="Inserir Tabela">📊 Tabela</button>

                        <button type="button" onclick="execAulaCmd('removeFormat')" class="px-2.5 py-1 bg-white hover:bg-slate-200 border border-slate-300 rounded text-xs text-slate-500" title="Limpar Formatação">Tx</button>
                    </div>
                </div>

                <!-- Área Editável Visual -->
                <div id="aula_editor_visual" contenteditable="true" class="p-4 min-h-[220px] max-h-[350px] overflow-y-auto bg-white text-slate-800 text-sm focus:outline-none leading-relaxed prose prose-slate max-w-none">
                    <p>Digite as instruções, notas conceituais, roteiros e orientações da aula aqui...</p>
                </div>

                <!-- Área Código HTML (Oculta por padrão) -->
                <textarea id="aula_editor_html" rows="8" class="hidden w-full p-4 font-mono text-xs text-slate-800 bg-slate-900 text-slate-100 focus:outline-none leading-relaxed"></textarea>
            </div>

            <!-- ========================================================= -->
            <!-- CONFIGURAÇÃO DE VÍDEO DA AULA (Link ou Upload MP4) -->
            <!-- ========================================================= -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="play-circle" class="w-4 h-4 text-rose-500"></i>
                        <span>Vídeo da Aula</span>
                    </h4>
                    <span class="text-[10px] text-slate-400">YouTube, Vimeo ou MP4 local</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Origem do Vídeo</label>
                        <select id="aula_tipo_video" name="tipo_video" onchange="toggleVideoInputs(this.value)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="youtube">YouTube (Link / Incorporar)</option>
                            <option value="vimeo">Vimeo (Link)</option>
                            <option value="upload">Upload de Arquivo (MP4)</option>
                            <option value="nenhum">Sem Vídeo (Apenas Texto/Material)</option>
                        </select>
                    </div>
                    
                    <div id="box_url_video" class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Link do Vídeo (YouTube / Vimeo)</label>
                        <input type="text" id="aula_url_video" name="url_video" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono">
                    </div>

                    <div id="box_upload_video" class="hidden sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Upload de Vídeo MP4 (Hospedado no Servidor)</label>
                        <input type="file" name="video_arquivo" accept="video/mp4,video/webm" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-500 file:text-white cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-0.5">Salvo em <code class="font-mono bg-white px-1 py-0.5 rounded text-rose-600">/public/aulas/videos/</code></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Duração da Aula (Minutos)</label>
                        <input type="number" id="aula_duracao_minutos" name="duracao_minutos" value="10" placeholder="Ex: 15" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Ordem da Aula</label>
                        <input type="number" id="aula_ordem" name="ordem" value="0" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Status de Publicação</label>
                        <select id="aula_status" name="status" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="publicado">Publicado (Visível aos Alunos)</option>
                            <option value="rascunho">Rascunho</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- ANEXOS & ARQUIVOS PARA DOWNLOAD (PDFs, Guias, Apostilas) -->
            <!-- ========================================================= -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="paperclip" class="w-4 h-4 text-[#0D5BA8]"></i>
                        <span>Anexos & Material de Apoio para Download (PDF, DOCX, ZIP)</span>
                    </h4>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Adicionar Novo Arquivo</label>
                    <input type="file" name="anexo_arquivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.jpg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0D5BA8] file:text-white cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Salvo automaticamente em <code class="font-mono bg-white px-1 py-0.5 rounded text-blue-600">/public/aulas/anexos/</code></p>
                </div>

                <!-- Lista de Anexos Existentes nesta Aula -->
                <div id="aula_anexos_container" class="space-y-1 pt-2">
                    <!-- Preenchido via Javascript se houver anexos existentes -->
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="fecharModal('modal-aula-crud')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-8 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white rounded-xl text-xs font-bold shadow-md">Salvar & Publicar Aula</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- 5. MODAL BIBLIOTECA DE MÍDIAS (MEDIA LIBRARY /public COMPLETA) -->
<!-- ==================================================================== -->
<div id="modal-media-library" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-6 bg-slate-950/70 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-6xl w-full h-[90vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden">
        
        <!-- Header da Biblioteca -->
        <div class="px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 bg-slate-50/80">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center shadow-xs">
                    <i data-lucide="folder-open" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-lg text-slate-900">Biblioteca de Mídias</h3>
                    <p class="text-[11px] text-slate-500">Navegue em <code class="font-mono bg-white px-1.5 py-0.5 rounded text-blue-600 font-bold">/public</code> e faça upload direto para <code class="font-mono bg-white px-1.5 py-0.5 rounded text-emerald-600 font-bold">/public/anexos/</code></p>
                </div>
            </div>

            <!-- Abas da Biblioteca -->
            <div class="flex items-center gap-1.5 bg-slate-200/80 p-1 rounded-2xl">
                <button type="button" id="tab-btn-ml-browse" onclick="switchMediaLibraryTab('browse')" class="px-4 py-1.5 rounded-xl font-bold text-xs bg-white text-[#0D5BA8] shadow-xs transition-all flex items-center gap-1.5">
                    <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                    <span>Explorar Arquivos</span>
                </button>
                <button type="button" id="tab-btn-ml-upload" onclick="switchMediaLibraryTab('upload')" class="px-4 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all flex items-center gap-1.5">
                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                    <span>Enviar Arquivo</span>
                </button>
                <button type="button" id="tab-btn-ml-url" onclick="switchMediaLibraryTab('url')" class="px-4 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all flex items-center gap-1.5">
                    <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                    <span>Link / URL</span>
                </button>
            </div>

            <button type="button" onclick="fecharModal('modal-media-library')" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 rounded-xl transition-colors">✕</button>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="flex-1 overflow-hidden flex flex-col">
            
            <!-- ABA 1: EXPLORAR ARQUIVOS DA PASTA /public -->
            <div id="ml-panel-browse" class="flex-1 flex flex-col md:flex-row overflow-hidden">
                <!-- Área de Grade de Mídias (Esquerda) -->
                <div class="flex-1 flex flex-col overflow-hidden border-r border-slate-100">
                    <!-- Barra de Filtros & Busca -->
                    <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filtro Tipo -->
                            <select id="ml-filter-type" onchange="renderMediaLibraryGrid()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700">
                                <option value="all">Todos os Tipos de Mídia</option>
                                <option value="image">🖼️ Imagens (JPG, PNG, WebP)</option>
                                <option value="video">🎬 Vídeos (MP4, WebM)</option>
                                <option value="audio">🎧 Áudios (MP3, WAV)</option>
                                <option value="document">📄 Documentos (PDF, DOCX)</option>
                            </select>

                            <!-- Filtro Pasta -->
                            <select id="ml-filter-folder" onchange="renderMediaLibraryGrid()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700">
                                <option value="all">Todas as Pastas</option>
                                <option value="anexos">/public/anexos/ (Uploads)</option>
                                <option value="cursos">/public/cursos/</option>
                                <option value="aulas">/public/aulas/</option>
                                <option value="vitrine">/public/vitrine/</option>
                                <option value="parceiros">/public/parceiros/</option>
                            </select>
                        </div>

                        <!-- Busca -->
                        <div class="relative w-full sm:w-56">
                            <input type="text" id="ml-search-input" oninput="renderMediaLibraryGrid()" placeholder="Buscar arquivo..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                        </div>
                    </div>

                    <!-- Grade de Mídias -->
                    <div id="ml-grid-container" class="flex-1 p-4 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5">
                        <!-- Itens renderizados dinamicamente via JS -->
                    </div>
                </div>

                <!-- Painel de Detalhes da Mídia Selecionada (Direita) -->
                <div id="ml-sidebar-details" class="w-full md:w-80 bg-slate-50/60 p-5 overflow-y-auto flex flex-col justify-between border-t md:border-t-0 border-slate-100">
                    <div id="ml-selected-info" class="space-y-4">
                        <div class="text-center py-10 text-slate-400 space-y-2">
                            <i data-lucide="image" class="w-10 h-10 mx-auto stroke-1"></i>
                            <p class="text-xs">Selecione um arquivo ao lado para ver os detalhes e inserir.</p>
                        </div>
                    </div>

                    <div id="ml-insert-footer" class="pt-4 border-t border-slate-200 hidden space-y-3">
                        <button type="button" onclick="confirmarInsercaoMidia()" class="w-full py-3 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Inserir no Conteúdo</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ABA 2: UPLOAD DE NOVO ARQUIVO (SALVO EM /public/anexos/) -->
            <div id="ml-panel-upload" class="hidden flex-1 p-8 overflow-y-auto flex flex-col items-center justify-center">
                <div class="max-w-md w-full p-8 border-2 border-dashed border-slate-300 hover:border-[#0D5BA8] rounded-3xl bg-slate-50/50 text-center space-y-4 transition-all" id="ml-dropzone">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center mx-auto shadow-xs">
                        <i data-lucide="upload-cloud" class="w-8 h-8"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-heading font-bold text-base text-slate-900">Arraste seus arquivos para cá</h4>
                        <p class="text-xs text-slate-500">Imagens (PNG, JPG, WebP), Vídeos (MP4), Áudios (MP3) ou Documentos (PDF, DOCX)</p>
                        <p class="text-[11px] text-emerald-600 font-bold mt-1">Salvo automaticamente em <code class="font-mono bg-white px-1 py-0.5 rounded text-emerald-700">/public/anexos/</code></p>
                    </div>

                    <div>
                        <input type="file" id="ml-file-input" onchange="handleMediaLibraryUpload(this.files[0])" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                        <button type="button" onclick="document.getElementById('ml-file-input').click()" class="px-6 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all cursor-pointer">
                            Selecionar Arquivo no Computador
                        </button>
                    </div>

                    <div id="ml-upload-progress" class="hidden space-y-2 pt-2">
                        <div class="flex justify-between text-xs font-bold text-slate-600">
                            <span>Enviando para o servidor...</span>
                            <span id="ml-upload-percent">0%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div id="ml-upload-bar" class="h-full bg-[#0D5BA8] transition-all" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ABA 3: INSERIR POR LINK / URL EXTERNA -->
            <div id="ml-panel-url" class="hidden flex-1 p-8 overflow-y-auto max-w-xl mx-auto w-full space-y-4">
                <div class="space-y-1">
                    <h4 class="font-heading font-bold text-base text-slate-900">Inserir Mídia por URL / Link Externo</h4>
                    <p class="text-xs text-slate-500">Cole o link de uma imagem externa, vídeo do YouTube/Vimeo ou arquivo de áudio.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Mídia</label>
                        <select id="ml-url-type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="image">🖼️ Imagem (URL externa)</option>
                            <option value="youtube">🎬 Vídeo do YouTube / Vimeo (Player Incorporado)</option>
                            <option value="audio">🎧 Áudio Externo (Player MP3)</option>
                            <option value="link">🔗 Link / Documento para Download</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Endereço da URL *</label>
                        <input type="text" id="ml-url-input" placeholder="https://..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Legenda / Texto Alternativo (Opcional)</label>
                        <input type="text" id="ml-url-alt" placeholder="Descrição da imagem ou vídeo..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>

                    <button type="button" onclick="inserirMidiaPorUrlDireta()" class="w-full py-3 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-bold text-xs rounded-xl shadow-md transition-all cursor-pointer">
                        Inserir Mídia no Editor
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
function abrirModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
}

function fecharModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.remove('flex');
        m.classList.add('hidden');
    }
}

// -------------------------------------------------------------
// CONTROLE DO LMS: TRILHAS, CURSOS, MÓDULOS E AULAS
// -------------------------------------------------------------
function abrirModalTrilha() {
    document.getElementById('modal-trilha-title').innerText = 'Nova Trilha de Aprendizado';
    document.getElementById('trilha_id').value = '';
    document.getElementById('trilha_titulo').value = '';
    document.getElementById('trilha_eixo').value = '';
    document.getElementById('trilha_carga_horaria').value = '40 horas';
    document.getElementById('trilha_descricao').value = '';
    document.getElementById('trilha_ordem').value = '0';
    document.getElementById('trilha_status').value = 'ativo';
    abrirModal('modal-trilha-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function editarTrilha(t) {
    document.getElementById('modal-trilha-title').innerText = 'Editar Trilha de Aprendizado';
    document.getElementById('trilha_id').value = t.id || '';
    document.getElementById('trilha_titulo').value = t.titulo || '';
    document.getElementById('trilha_eixo').value = t.eixo || '';
    document.getElementById('trilha_carga_horaria').value = t.carga_horaria || '';
    document.getElementById('trilha_descricao').value = t.descricao || '';
    document.getElementById('trilha_ordem').value = t.ordem || 0;
    document.getElementById('trilha_status').value = t.status || 'ativo';
    abrirModal('modal-trilha-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function previewCursoCapa(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('curso_capa_preview');
            if (preview) preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function abrirModalCurso() {
    document.getElementById('modal-curso-title').innerText = 'Novo Curso';
    document.getElementById('curso_id').value = '';
    document.getElementById('curso_trilha_id').value = '';
    document.getElementById('curso_titulo').value = '';
    document.getElementById('curso_professor_nome').value = 'FotoCidade DF';
    document.getElementById('curso_carga_horaria').value = '14 horas';
    document.getElementById('curso_descricao').value = '';
    document.getElementById('curso_ordem').value = '0';
    document.getElementById('curso_status').value = 'ativo';
    document.getElementById('curso_capa_atual').value = '';
    if (document.getElementById('curso_capa_preview')) {
        document.getElementById('curso_capa_preview').src = 'assets/images/oficina-olhar-fercal.jpg';
    }
    abrirModal('modal-curso-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function editarCurso(c) {
    document.getElementById('modal-curso-title').innerText = 'Editar Curso';
    document.getElementById('curso_id').value = c.id || '';
    document.getElementById('curso_trilha_id').value = c.trilha_id || '';
    document.getElementById('curso_titulo').value = c.titulo || '';
    document.getElementById('curso_professor_nome').value = c.professor_nome || 'FotoCidade DF';
    document.getElementById('curso_carga_horaria').value = c.carga_horaria || '';
    document.getElementById('curso_descricao').value = c.descricao || '';
    document.getElementById('curso_ordem').value = c.ordem || 0;
    document.getElementById('curso_status').value = c.status || 'ativo';
    document.getElementById('curso_capa_atual').value = c.capa_url || '';
    if (document.getElementById('curso_capa_preview')) {
        document.getElementById('curso_capa_preview').src = c.capa_url || 'assets/images/oficina-olhar-fercal.jpg';
    }
    abrirModal('modal-curso-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function abrirModalModulo(cursoId) {
    document.getElementById('modal-modulo-title').innerText = 'Novo Módulo do Curso';
    document.getElementById('modulo_id').value = '';
    document.getElementById('modulo_curso_id').value = cursoId;
    document.getElementById('modulo_titulo').value = '';
    document.getElementById('modulo_descricao').value = '';
    document.getElementById('modulo_editor_visual').innerHTML = '<p>Escreva o conteúdo textual, referências, orientações didáticas e conceitos para este módulo...</p>';
    document.getElementById('modulo_editor_html').value = '';
    document.getElementById('modulo_tipo_video').value = 'youtube';
    toggleModuloVideoInputs('youtube');
    document.getElementById('modulo_url_video').value = '';
    document.getElementById('modulo_tipo_audio').value = 'nenhum';
    toggleModuloAudioInputs('nenhum');
    document.getElementById('modulo_audio_url').value = '';
    document.getElementById('modulo_duracao_minutos').value = '20';
    document.getElementById('modulo_ordem').value = '0';
    toggleModuloEditorMode('visual');
    abrirModal('modal-modulo-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function editarModulo(m) {
    document.getElementById('modal-modulo-title').innerText = 'Editar Módulo do Curso';
    document.getElementById('modulo_id').value = m.id || '';
    document.getElementById('modulo_curso_id').value = m.curso_id || '';
    document.getElementById('modulo_titulo').value = m.titulo || '';
    document.getElementById('modulo_descricao').value = m.descricao || '';
    
    const content = m.conteudo || '<p></p>';
    document.getElementById('modulo_editor_visual').innerHTML = content;
    document.getElementById('modulo_editor_html').value = content;
    
    const tipoVid = m.tipo_video || (m.url_video ? 'youtube' : 'nenhum');
    document.getElementById('modulo_tipo_video').value = tipoVid;
    toggleModuloVideoInputs(tipoVid);
    document.getElementById('modulo_url_video').value = m.url_video || '';

    const tipoAud = m.tipo_audio || (m.audio_url ? 'link' : 'nenhum');
    document.getElementById('modulo_tipo_audio').value = tipoAud;
    toggleModuloAudioInputs(tipoAud);
    document.getElementById('modulo_audio_url').value = m.audio_url || '';

    document.getElementById('modulo_duracao_minutos').value = m.duracao_minutos || 20;
    document.getElementById('modulo_ordem').value = m.ordem || 0;
    
    toggleModuloEditorMode('visual');
    abrirModal('modal-modulo-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function toggleModuloVideoInputs(tipo) {
    const boxUrl = document.getElementById('mod_box_url_video');
    const boxUp = document.getElementById('mod_box_upload_video');
    if (tipo === 'upload') {
        if (boxUrl) boxUrl.classList.add('hidden');
        if (boxUp) boxUp.classList.remove('hidden');
    } else if (tipo === 'nenhum') {
        if (boxUrl) boxUrl.classList.add('hidden');
        if (boxUp) boxUp.classList.add('hidden');
    } else {
        if (boxUrl) boxUrl.classList.remove('hidden');
        if (boxUp) boxUp.classList.add('hidden');
    }
}

function toggleModuloAudioInputs(tipo) {
    const boxUp = document.getElementById('mod_box_upload_audio');
    const boxUrl = document.getElementById('mod_box_url_audio');
    if (tipo === 'upload') {
        if (boxUp) boxUp.classList.remove('hidden');
        if (boxUrl) boxUrl.classList.add('hidden');
    } else if (tipo === 'link') {
        if (boxUp) boxUp.classList.add('hidden');
        if (boxUrl) boxUrl.classList.remove('hidden');
    } else {
        if (boxUp) boxUp.classList.add('hidden');
        if (boxUrl) boxUrl.classList.add('hidden');
    }
}

// Funções WYSIWYG do Módulo
function execModuloCmd(command, value = null) {
    document.execCommand(command, false, value);
    document.getElementById('modulo_editor_visual').focus();
}

function formatModuloBlock(tag) {
    if (tag) {
        document.execCommand('formatBlock', false, tag);
        document.getElementById('modulo_editor_visual').focus();
    }
}

function inserirLinkModulo() {
    const url = prompt('Digite o endereço do Link (URL):', 'https://');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

let isModuloHtmlMode = false;
function toggleModuloEditorMode(mode) {
    const visualArea = document.getElementById('modulo_editor_visual');
    const htmlArea = document.getElementById('modulo_editor_html');
    const btnVisual = document.getElementById('btn-mod-mode-visual');
    const btnHtml = document.getElementById('btn-mod-mode-html');

    if (mode === 'html' && !isModuloHtmlMode) {
        htmlArea.value = visualArea.innerHTML;
        visualArea.classList.add('hidden');
        htmlArea.classList.remove('hidden');
        btnHtml.classList.add('bg-[#0D5BA8]', 'text-white');
        btnHtml.classList.remove('bg-slate-200', 'text-slate-700');
        btnVisual.classList.remove('bg-[#0D5BA8]', 'text-white');
        btnVisual.classList.add('bg-slate-200', 'text-slate-700');
        isModuloHtmlMode = true;
    } else if (mode === 'visual' && isModuloHtmlMode) {
        visualArea.innerHTML = htmlArea.value;
        htmlArea.classList.add('hidden');
        visualArea.classList.remove('hidden');
        btnVisual.classList.add('bg-[#0D5BA8]', 'text-white');
        btnVisual.classList.remove('bg-slate-200', 'text-slate-700');
        btnHtml.classList.remove('bg-[#0D5BA8]', 'text-white');
        btnHtml.classList.add('bg-slate-200', 'text-slate-700');
        isModuloHtmlMode = false;
    }
}

function syncModuloContent() {
    if (isModuloHtmlMode) {
        document.getElementById('modulo_conteudo_hidden').value = document.getElementById('modulo_editor_html').value;
    } else {
        document.getElementById('modulo_conteudo_hidden').value = document.getElementById('modulo_editor_visual').innerHTML;
    }
}

function toggleVideoInputs(tipo) {
    const boxUrl = document.getElementById('box_url_video');
    const boxUp = document.getElementById('box_upload_video');
    if (tipo === 'upload') {
        if (boxUrl) boxUrl.classList.add('hidden');
        if (boxUp) boxUp.classList.remove('hidden');
    } else if (tipo === 'nenhum') {
        if (boxUrl) boxUrl.classList.add('hidden');
        if (boxUp) boxUp.classList.add('hidden');
    } else {
        if (boxUrl) boxUrl.classList.remove('hidden');
        if (boxUp) boxUp.classList.add('hidden');
    }
}

// -------------------------------------------------------------
// FUNÇÕES DO EDITOR RICO DE AULA ESTILO WORDPRESS
// -------------------------------------------------------------
function execAulaCmd(command, value = null) {
    document.execCommand(command, false, value);
    document.getElementById('aula_editor_visual').focus();
}

function formatAulaBlock(tag) {
    if (tag) {
        document.execCommand('formatBlock', false, tag);
        document.getElementById('aula_editor_visual').focus();
    }
}

function inserirLinkAula() {
    const url = prompt('Digite o endereço do Link (URL):', 'https://');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

// =============================================================
// BIBLIOTECA DE MÍDIAS (MEDIA LIBRARY /public CONTROLLER)
// =============================================================
let mediaLibraryState = {
    target: 'modulo', // 'modulo' or 'aula'
    filterType: 'all', // 'all', 'image', 'video', 'audio', 'document'
    filterFolder: 'all',
    files: [],
    selectedFile: null
};

function abrirMediaLibrary(target = 'modulo', filterType = 'all') {
    mediaLibraryState.target = target;
    mediaLibraryState.filterType = filterType;
    mediaLibraryState.selectedFile = null;

    const selectType = document.getElementById('ml-filter-type');
    if (selectType) selectType.value = filterType;

    switchMediaLibraryTab('browse');
    abrirModal('modal-media-library');

    carregarArquivosMediaLibrary();
}

function switchMediaLibraryTab(tab) {
    const pBrowse = document.getElementById('ml-panel-browse');
    const pUpload = document.getElementById('ml-panel-upload');
    const pUrl = document.getElementById('ml-panel-url');
    const tBrowse = document.getElementById('tab-btn-ml-browse');
    const tUpload = document.getElementById('tab-btn-ml-upload');
    const tUrl = document.getElementById('tab-btn-ml-url');

    [pBrowse, pUpload, pUrl].forEach(p => p && p.classList.add('hidden'));
    [tBrowse, tUpload, tUrl].forEach(t => {
        if (t) {
            t.classList.remove('bg-white', 'text-[#0D5BA8]', 'shadow-xs');
            t.classList.add('text-slate-600');
        }
    });

    if (tab === 'upload') {
        if (pUpload) pUpload.classList.remove('hidden');
        if (tUpload) {
            tUpload.classList.add('bg-white', 'text-[#0D5BA8]', 'shadow-xs');
            tUpload.classList.remove('text-slate-600');
        }
    } else if (tab === 'url') {
        if (pUrl) pUrl.classList.remove('hidden');
        if (tUrl) {
            tUrl.classList.add('bg-white', 'text-[#0D5BA8]', 'shadow-xs');
            tUrl.classList.remove('text-slate-600');
        }
    } else {
        if (pBrowse) pBrowse.classList.remove('hidden');
        if (tBrowse) {
            tBrowse.classList.add('bg-white', 'text-[#0D5BA8]', 'shadow-xs');
            tBrowse.classList.remove('text-slate-600');
        }
    }
}

function carregarArquivosMediaLibrary() {
    const grid = document.getElementById('ml-grid-container');
    if (grid) {
        grid.innerHTML = '<div class="col-span-full py-16 text-center text-slate-400"><p class="text-xs font-bold animate-pulse">Carregando arquivos da pasta /public...</p></div>';
    }

    fetch('dashboard.php?api_action=get_media_library')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.files) {
                mediaLibraryState.files = data.files;
                renderMediaLibraryGrid();
            }
        })
        .catch(err => {
            if (grid) grid.innerHTML = '<div class="col-span-full py-10 text-center text-rose-500 text-xs">Erro ao carregar arquivos da biblioteca.</div>';
        });
}

function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function renderMediaLibraryGrid() {
    const grid = document.getElementById('ml-grid-container');
    if (!grid) return;

    const typeFilter = document.getElementById('ml-filter-type')?.value || 'all';
    const folderFilter = document.getElementById('ml-filter-folder')?.value || 'all';
    const search = (document.getElementById('ml-search-input')?.value || '').toLowerCase().trim();

    const filtered = mediaLibraryState.files.filter(f => {
        const matchType = (typeFilter === 'all') || (f.type === typeFilter);
        const matchFolder = (folderFilter === 'all') || (f.folder === folderFilter);
        const matchSearch = !search || f.name.toLowerCase().includes(search) || f.url.toLowerCase().includes(search);
        return matchType && matchFolder && matchSearch;
    });

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full py-16 text-center text-slate-400 space-y-2">
                <p class="text-xs font-bold text-slate-600">Nenhum arquivo encontrado com estes filtros.</p>
                <p class="text-[11px]">Faça upload de novos arquivos para a pasta /public/anexos na aba Enviar Arquivo.</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = '';
    filtered.forEach(file => {
        const item = document.createElement('div');
        const isSelected = mediaLibraryState.selectedFile && mediaLibraryState.selectedFile.url === file.url;
        item.className = `group relative rounded-2xl border ${isSelected ? 'border-2 border-[#0D5BA8] ring-2 ring-blue-100 bg-blue-50/20' : 'border-slate-200 hover:border-[#0D5BA8] bg-white'} p-2 cursor-pointer transition-all flex flex-col justify-between overflow-hidden shadow-2xs`;
        
        let previewHtml = '';
        if (file.type === 'image') {
            previewHtml = `<div class="aspect-square rounded-xl overflow-hidden bg-slate-100 flex items-center justify-center"><img src="${file.url}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" /></div>`;
        } else if (file.type === 'video') {
            previewHtml = `<div class="aspect-square rounded-xl bg-slate-900 text-white flex flex-col items-center justify-center gap-1"><i data-lucide="video" class="w-7 h-7 text-rose-400"></i><span class="text-[9px] font-mono uppercase bg-rose-500/20 px-1.5 py-0.2 rounded text-rose-300">VÍDEO</span></div>`;
        } else if (file.type === 'audio') {
            previewHtml = `<div class="aspect-square rounded-xl bg-emerald-50 text-emerald-600 flex flex-col items-center justify-center gap-1"><i data-lucide="headphones" class="w-7 h-7"></i><span class="text-[9px] font-mono uppercase bg-emerald-100 px-1.5 py-0.2 rounded text-emerald-800">ÁUDIO</span></div>`;
        } else {
            previewHtml = `<div class="aspect-square rounded-xl bg-amber-50 text-amber-700 flex flex-col items-center justify-center gap-1"><i data-lucide="file-text" class="w-7 h-7"></i><span class="text-[9px] font-mono uppercase bg-amber-100 px-1.5 py-0.2 rounded text-amber-900">${file.ext || 'DOC'}</span></div>`;
        }

        item.innerHTML = `
            ${previewHtml}
            <div class="mt-2 min-w-0">
                <p class="text-[11px] font-bold text-slate-800 truncate" title="${file.name}">${file.name}</p>
                <div class="flex items-center justify-between text-[9px] text-slate-400 mt-0.5">
                    <span>${formatBytes(file.size)}</span>
                    <span class="font-mono bg-slate-100 px-1 rounded truncate max-w-[70px]">${file.folder}</span>
                </div>
            </div>
        `;

        item.onclick = () => selectMediaFile(file);
        grid.appendChild(item);
    });

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function selectMediaFile(file) {
    mediaLibraryState.selectedFile = file;
    renderMediaLibraryGrid();

    const infoBox = document.getElementById('ml-selected-info');
    const footer = document.getElementById('ml-insert-footer');
    if (!infoBox || !footer) return;

    let preview = '';
    if (file.type === 'image') {
        preview = `<img src="${file.url}" class="w-full max-h-48 object-cover rounded-2xl border border-slate-200 shadow-xs mb-3" />`;
    } else if (file.type === 'video') {
        preview = `<video src="${file.url}" controls class="w-full max-h-48 rounded-2xl border border-slate-200 shadow-xs mb-3"></video>`;
    } else if (file.type === 'audio') {
        preview = `<div class="p-3 bg-white rounded-2xl border border-slate-200 mb-3"><audio src="${file.url}" controls class="w-full"></audio></div>`;
    } else {
        preview = `<div class="p-4 bg-white rounded-2xl border border-slate-200 flex items-center gap-3 mb-3"><span class="text-3xl">📄</span><div><p class="text-xs font-bold text-slate-800">${file.name}</p><p class="text-[10px] text-slate-400">${formatBytes(file.size)}</p></div></div>`;
    }

    infoBox.innerHTML = `
        <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-slate-400">Detalhes da Mídia</h4>
        ${preview}
        <div class="space-y-2 text-xs">
            <div class="space-y-0.5">
                <label class="font-bold text-slate-700 text-[10px] uppercase">Nome do Arquivo:</label>
                <p class="font-mono text-[11px] text-slate-800 truncate" title="${file.name}">${file.name}</p>
            </div>
            <div class="grid grid-cols-2 gap-2 text-[10px] text-slate-500">
                <div><b class="text-slate-700">Tamanho:</b> ${formatBytes(file.size)}</div>
                <div><b class="text-slate-700">Pasta:</b> /public/${file.folder}/</div>
            </div>
            <div class="space-y-1 pt-1">
                <label class="block font-bold text-slate-700 text-[11px]">Legenda / Texto Alternativo</label>
                <input type="text" id="ml-selected-alt" value="${file.name.replace(/\.[^/.]+$/, '').replace(/[_|-]/g, ' ')}" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs">
            </div>
        </div>
    `;

    footer.classList.remove('hidden');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function confirmarInsercaoMidia() {
    const file = mediaLibraryState.selectedFile;
    if (!file) return;

    const alt = document.getElementById('ml-selected-alt')?.value || file.name;
    const targetEditorId = mediaLibraryState.target === 'aula' ? 'aula_editor_visual' : 'modulo_editor_visual';
    const editor = document.getElementById(targetEditorId);
    if (!editor) return;

    let htmlToInsert = '';

    if (file.type === 'image') {
        htmlToInsert = `<figure class="my-4"><img src="${file.url}" alt="${alt}" class="rounded-2xl max-w-full shadow-md mx-auto" /><figcaption class="text-center text-xs text-slate-400 mt-1 italic">${alt}</figcaption></figure><p></p>`;
    } else if (file.type === 'video') {
        htmlToInsert = `<div class="my-6 aspect-video rounded-2xl overflow-hidden shadow-lg border border-slate-200"><video src="${file.url}" controls class="w-full h-full object-cover"></video></div><p></p>`;
    } else if (file.type === 'audio') {
        htmlToInsert = `
            <div class="my-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col gap-2">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                    <span>🎧</span><span>${alt}</span>
                </div>
                <audio src="${file.url}" controls class="w-full"></audio>
            </div><p></p>
        `;
    } else {
        htmlToInsert = `
            <div class="my-4 p-3.5 bg-blue-50/60 border border-blue-200 rounded-2xl flex items-center justify-between gap-3 not-prose">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="text-2xl">📄</span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate">${file.name}</p>
                        <p class="text-[10px] text-slate-500">${formatBytes(file.size)}</p>
                    </div>
                </div>
                <a href="${file.url}" target="_blank" download class="px-3.5 py-2 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white text-xs font-bold rounded-xl transition-all shrink-0">
                    Baixar Arquivo
                </a>
            </div><p></p>
        `;
    }

    editor.focus();
    document.execCommand('insertHTML', false, htmlToInsert);
    fecharModal('modal-media-library');
}

function handleMediaLibraryUpload(file) {
    if (!file) return;

    const progressBox = document.getElementById('ml-upload-progress');
    const progressBar = document.getElementById('ml-upload-bar');
    const progressPercent = document.getElementById('ml-upload-percent');

    if (progressBox) progressBox.classList.remove('hidden');

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo get_csrf_token(); ?>');
    formData.append('action', 'upload_media_library');
    formData.append('media_file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'dashboard.php', true);

    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            if (progressBar) progressBar.style.width = percent + '%';
            if (progressPercent) progressPercent.innerText = percent + '%';
        }
    };

    xhr.onload = () => {
        if (progressBox) progressBox.classList.add('hidden');
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    // Adiciona o novo arquivo no topo da biblioteca
                    mediaLibraryState.files.unshift(res);
                    switchMediaLibraryTab('browse');
                    selectMediaFile(res);
                } else {
                    alert('Erro no upload: ' + (res.message || 'Falha ao salvar.'));
                }
            } catch (e) {
                alert('Erro ao processar resposta do servidor.');
            }
        }
    };

    xhr.onerror = () => {
        if (progressBox) progressBox.classList.add('hidden');
        alert('Erro de conexão ao enviar arquivo.');
    };

    xhr.send(formData);
}

function inserirMidiaPorUrlDireta() {
    const type = document.getElementById('ml-url-type')?.value || 'image';
    const url = document.getElementById('ml-url-input')?.value.trim();
    const alt = document.getElementById('ml-url-alt')?.value.trim() || 'Mídia externa';

    if (!url) {
        alert('Por favor, digite o endereço da URL.');
        return;
    }

    const targetEditorId = mediaLibraryState.target === 'aula' ? 'aula_editor_visual' : 'modulo_editor_visual';
    const editor = document.getElementById(targetEditorId);
    if (!editor) return;

    let htmlToInsert = '';

    if (type === 'image') {
        htmlToInsert = `<figure class="my-4"><img src="${url}" alt="${alt}" class="rounded-2xl max-w-full shadow-md mx-auto" /><figcaption class="text-center text-xs text-slate-400 mt-1 italic">${alt}</figcaption></figure><p></p>`;
    } else if (type === 'youtube') {
        let embedUrl = url;
        const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
        if (match) {
            embedUrl = 'https://www.youtube.com/embed/' + match[1];
        }
        htmlToInsert = `<div class="my-6 aspect-video rounded-2xl overflow-hidden shadow-lg border border-slate-200"><iframe src="${embedUrl}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div><p></p>`;
    } else if (type === 'audio') {
        htmlToInsert = `
            <div class="my-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col gap-2">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                    <span>🎧</span><span>${alt}</span>
                </div>
                <audio src="${url}" controls class="w-full"></audio>
            </div><p></p>
        `;
    } else {
        htmlToInsert = `<p><a href="${url}" target="_blank" class="text-blue-600 font-bold underline">🔗 ${alt}</a></p>`;
    }

    editor.focus();
    document.execCommand('insertHTML', false, htmlToInsert);
    fecharModal('modal-media-library');
}

function inserirTabelaModulo() {
    const tabelaHtml = `
        <table class="w-full my-4 border border-slate-300 rounded-xl overflow-hidden text-xs">
            <thead class="bg-slate-100 font-bold text-slate-800">
                <tr><th class="p-2 border border-slate-200">Tópico</th><th class="p-2 border border-slate-200">Descrição / Prática</th></tr>
            </thead>
            <tbody>
                <tr><td class="p-2 border border-slate-200">Item 01</td><td class="p-2 border border-slate-200">Conteúdo prático aqui...</td></tr>
                <tr><td class="p-2 border border-slate-200">Item 02</td><td class="p-2 border border-slate-200">Orientações adicionais...</td></tr>
            </tbody>
        </table><p></p>
    `;
    document.getElementById('modulo_editor_visual').focus();
    document.execCommand('insertHTML', false, tabelaHtml);
}

function inserirTabelaAula() {
    const tabelaHtml = `
        <table class="w-full my-4 border border-slate-300 rounded-xl overflow-hidden text-xs">
            <thead class="bg-slate-100 font-bold text-slate-800">
                <tr><th class="p-2 border border-slate-200">Etapa</th><th class="p-2 border border-slate-200">Instruções da Aula</th></tr>
            </thead>
            <tbody>
                <tr><td class="p-2 border border-slate-200">Passo 1</td><td class="p-2 border border-slate-200">Ajuste de luz e foco...</td></tr>
                <tr><td class="p-2 border border-slate-200">Passo 2</td><td class="p-2 border border-slate-200">Composição do enquadramento...</td></tr>
            </tbody>
        </table><p></p>
    `;
    document.getElementById('aula_editor_visual').focus();
    document.execCommand('insertHTML', false, tabelaHtml);
}


let isAulaHtmlMode = false;
function toggleAulaEditorMode(mode) {
    const visualArea = document.getElementById('aula_editor_visual');
    const htmlArea = document.getElementById('aula_editor_html');
    const btnVisual = document.getElementById('btn-aula-mode-visual');
    const btnHtml = document.getElementById('btn-aula-mode-html');

    if (mode === 'html' && !isAulaHtmlMode) {
        htmlArea.value = visualArea.innerHTML;
        visualArea.classList.add('hidden');
        htmlArea.classList.remove('hidden');
        btnHtml.classList.add('bg-[#0D5BA8]', 'text-white');
        btnHtml.classList.remove('bg-slate-200', 'text-slate-700');
        btnVisual.classList.remove('bg-[#0D5BA8]', 'text-white');
        btnVisual.classList.add('bg-slate-200', 'text-slate-700');
        isAulaHtmlMode = true;
    } else if (mode === 'visual' && isAulaHtmlMode) {
        visualArea.innerHTML = htmlArea.value;
        htmlArea.classList.add('hidden');
        visualArea.classList.remove('hidden');
        btnVisual.classList.add('bg-[#0D5BA8]', 'text-white');
        btnVisual.classList.remove('bg-slate-200', 'text-slate-700');
        btnHtml.classList.remove('bg-[#0D5BA8]', 'text-white');
        btnHtml.classList.add('bg-slate-200', 'text-slate-700');
        isAulaHtmlMode = false;
    }
}

function syncAulaContent() {
    if (isAulaHtmlMode) {
        document.getElementById('aula_conteudo_hidden').value = document.getElementById('aula_editor_html').value;
    } else {
        document.getElementById('aula_conteudo_hidden').value = document.getElementById('aula_editor_visual').innerHTML;
    }
}

function abrirModalAula(cursoId, moduloId) {
    document.getElementById('modal-aula-title').innerText = 'Nova Aula';
    document.getElementById('aula_id').value = '';
    document.getElementById('aula_curso_id').value = cursoId;
    document.getElementById('aula_modulo_id').value = moduloId;
    document.getElementById('aula_titulo').value = '';
    document.getElementById('aula_editor_visual').innerHTML = '<p>Digite o roteiro e o conteúdo pedagógico desta aula...</p>';
    document.getElementById('aula_editor_html').value = '';
    document.getElementById('aula_tipo_video').value = 'youtube';
    toggleVideoInputs('youtube');
    document.getElementById('aula_url_video').value = '';
    document.getElementById('aula_duracao_minutos').value = '10';
    document.getElementById('aula_ordem').value = '0';
    document.getElementById('aula_status').value = 'publicado';
    document.getElementById('aula_anexos_container').innerHTML = '';
    toggleAulaEditorMode('visual');
    abrirModal('modal-aula-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function editarAula(a, cursoId) {
    document.getElementById('modal-aula-title').innerText = 'Editar Aula';
    document.getElementById('aula_id').value = a.id || '';
    document.getElementById('aula_curso_id').value = cursoId;
    document.getElementById('aula_modulo_id').value = a.modulo_id || '';
    document.getElementById('aula_titulo').value = a.titulo || '';
    
    const content = a.conteudo || '<p></p>';
    document.getElementById('aula_editor_visual').innerHTML = content;
    document.getElementById('aula_editor_html').value = content;
    
    const tipoVid = a.tipo_video || (a.url_video ? 'youtube' : 'nenhum');
    document.getElementById('aula_tipo_video').value = tipoVid;
    toggleVideoInputs(tipoVid);
    document.getElementById('aula_url_video').value = a.url_video || '';

    document.getElementById('aula_duracao_minutos').value = a.duracao_minutos || 10;
    document.getElementById('aula_ordem').value = a.ordem || 0;
    document.getElementById('aula_status').value = a.status || 'publicado';

    // Lista de anexos existentes
    const anxContainer = document.getElementById('aula_anexos_container');
    anxContainer.innerHTML = '';
    if (a.anexos && a.anexos.length > 0) {
        a.anexos.forEach(anx => {
            const item = document.createElement('div');
            item.className = 'flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200 text-xs';
            item.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="text-blue-600 font-bold">📄</span>
                    <a href="${anx.url_arquivo}" target="_blank" class="font-medium text-slate-800 hover:underline truncate max-w-xs">${anx.nome_arquivo}</a>
                </div>
                <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja excluir este anexo?')">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="action" value="excluir_anexo">
                    <input type="hidden" name="anexo_id" value="${anx.id}">
                    <input type="hidden" name="curso_id" value="${cursoId}">
                    <button type="submit" class="p-1 text-rose-500 hover:bg-rose-50 rounded" title="Excluir Anexo">
                        ✕
                    </button>
                </form>
            `;
            anxContainer.appendChild(item);
        });
    }

    toggleAulaEditorMode('visual');
    abrirModal('modal-aula-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

// -------------------------------------------------------------
// REORDENAÇÃO / DRAG-AND-DROP DE MÓDULOS COM SALVAMENTO AJAX
// -------------------------------------------------------------
function moverModuloCard(btn, direcao) {
    const card = btn.closest('.modulo-item-card');
    const container = document.getElementById('modulos-sortable-list');
    if (!card || !container) return;

    if (direcao === 'up' && card.previousElementSibling && card.previousElementSibling.classList.contains('modulo-item-card')) {
        container.insertBefore(card, card.previousElementSibling);
    } else if (direcao === 'down' && card.nextElementSibling && card.nextElementSibling.classList.contains('modulo-item-card')) {
        container.insertBefore(card.nextElementSibling, card);
    }

    atualizarNumeracaoModuloBadges();
    salvarOrdemModulosServidor();
}

function atualizarNumeracaoModuloBadges() {
    const cards = document.querySelectorAll('#modulos-sortable-list .modulo-item-card');
    cards.forEach((c, index) => {
        const badge = c.querySelector('.modulo-badge-num');
        if (badge) {
            badge.innerText = String(index + 1).padStart(2, '0');
        }
    });
}

function salvarOrdemModulosServidor() {
    const container = document.getElementById('modulos-sortable-list');
    if (!container) return;
    const cursoId = container.getAttribute('data-curso-id');
    const cards = document.querySelectorAll('#modulos-sortable-list .modulo-item-card');
    const ordemIds = Array.from(cards).map(c => c.getAttribute('data-modulo-id'));

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo get_csrf_token(); ?>');
    formData.append('action', 'reordenar_modulos');
    formData.append('curso_id', cursoId);
    formData.append('modulos_ordem', JSON.stringify(ordemIds));
    formData.append('is_ajax', '1');

    fetch('dashboard.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        const toast = document.getElementById('toast-ordem-status');
        if (toast) {
            toast.classList.remove('hidden');
            setTimeout(() => { toast.classList.add('hidden'); }, 2500);
        }
    })
    .catch(err => {
        console.log('Ordem salva localmente');
    });
}

// Inicializa Drag & Drop nos Módulos
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('modulos-sortable-list');
    if (!container) return;

    let draggedItem = null;

    container.querySelectorAll('.modulo-item-card').forEach(item => {
        item.addEventListener('dragstart', (e) => {
            draggedItem = item;
            item.classList.add('opacity-50', 'border-blue-400', 'scale-[0.99]');
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', () => {
            if (draggedItem) {
                draggedItem.classList.remove('opacity-50', 'border-blue-400', 'scale-[0.99]');
                draggedItem = null;
            }
            atualizarNumeracaoModuloBadges();
            salvarOrdemModulosServidor();
        });

        item.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const afterElement = getDragAfterElement(container, e.clientY);
            if (draggedItem) {
                if (afterElement == null) {
                    container.appendChild(draggedItem);
                } else {
                    container.insertBefore(draggedItem, afterElement);
                }
            }
        });
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.modulo-item-card:not(.opacity-50)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
});



const bancoBairrosPorCidade = {
    'Brasília/DF': [
        'Sobradinho', 'Sobradinho II', 'Fercal', 'Grande Colorado', 'Planaltina',
        'Paranoá', 'Itapoã', 'São Sebastião', 'Santa Maria', 'Gama', 'Ceilândia',
        'Taguatinga', 'Samambaia', 'Recanto das Emas', 'Riacho Fundo I', 'Riacho Fundo II',
        'Guará', 'Águas Claras', 'Vicente Pires', 'Plano Piloto (Asa Norte)', 'Plano Piloto (Asa Sul)',
        'Lago Norte', 'Lago Sul', 'Cruzeiro', 'Sudoeste / Octogonal', 'Varjão',
        'Candangolândia', 'Núcleo Bandeirante', 'Park Way', 'SIA', 'SCIA / Estrutural'
    ],
    'Entorno do DF (GO)': [
        'Formosa', 'Luziânia', 'Valparaíso de Goiás', 'Novo Gama', 'Cidade Ocidental',
        'Águas Lindas de Goiás', 'Santo Antônio do Descoberto', 'Cristalina', 'Planaltina de Goiás'
    ],
    'Goiânia/GO': ['Centro', 'Setor Bueno', 'Setor Marista', 'Setor Oeste', 'Jardim Goiás', 'Pedro Ludovico', 'Outro'],
    'São Paulo/SP': ['Centro', 'Pinheiros', 'Vila Mariana', 'Jardins', 'Itaquera', 'Tatuapé', 'Moema', 'Outro'],
    'Rio de Janeiro/RJ': ['Centro', 'Copacabana', 'Ipanema', 'Botafogo', 'Tijuca', 'Barra da Tijuca', 'Outro'],
    'Belo Horizonte/MG': ['Centro', 'Savassi', 'Lourdes', 'Pampulha', 'Funcionários', 'Outro'],
    'Outra Cidade do Brasil': ['Centro', 'Bairro Principal', 'Zona Norte', 'Zona Sul', 'Zona Leste', 'Zona Oeste', 'Outro']
};

function carregarBairrosPorCidade(cidade, targetSelectId, bairroSelecionado = '') {
    const selectTarget = document.getElementById(targetSelectId);
    if (!selectTarget) return;

    const lista = bancoBairrosPorCidade[cidade] || bancoBairrosPorCidade['Brasília/DF'];
    selectTarget.innerHTML = '';

    lista.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b;
        opt.textContent = b;
        if (b === bairroSelecionado || (bairrosMatch(b, bairroSelecionado))) {
            opt.selected = true;
        }
        selectTarget.appendChild(opt);
    });

    if (!selectTarget.value && selectTarget.options.length > 0) {
        selectTarget.selectedIndex = 0;
    }
}

function bairrosMatch(b1, b2) {
    if (!b1 || !b2) return false;
    return b1.toLowerCase().trim() === b2.toLowerCase().trim();
}

// Funções auxiliares para modais e preenchimento
function fecharModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('usuario_avatar_preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleRemoverFoto(chk) {
    if (chk.checked) {
        document.getElementById('usuario_avatar_preview').src = 'assets/images/avatar-default.jpg';
    } else {
        const atual = document.getElementById('usuario_avatar_atual').value;
        document.getElementById('usuario_avatar_preview').src = atual || 'assets/images/avatar-default.jpg';
    }
}

function abrirModalVitrine() {
    document.getElementById('modal-vitrine-title').innerText = 'Novo Post na Vitrine';
    document.getElementById('vitrine_id').value = '';
    document.getElementById('vitrine_titulo').value = '';
    document.getElementById('vitrine_subtitulo').value = '';
    document.getElementById('vitrine_descricao').value = '';
    document.getElementById('vitrine_tags').value = '';
    document.getElementById('modal-vitrine-crud').classList.remove('hidden');
    document.getElementById('modal-vitrine-crud').classList.add('flex');
}

function editarVitrine(item) {
    document.getElementById('modal-vitrine-title').innerText = 'Editar Item da Vitrine';
    document.getElementById('vitrine_id').value = item.id;
    document.getElementById('vitrine_tipo').value = item.tipo;
    document.getElementById('vitrine_titulo').value = item.titulo;
    document.getElementById('vitrine_subtitulo').value = item.subtitulo || '';
    document.getElementById('vitrine_descricao').value = item.descricao;
    document.getElementById('vitrine_bairro').value = item.bairro;
    document.getElementById('vitrine_autor_nome').value = item.autorNome;
    document.getElementById('vitrine_tags').value = Array.isArray(item.tags) ? item.tags.join(', ') : item.tags;
    document.getElementById('vitrine_media_url_atual').value = item.mediaUrl;
    document.getElementById('modal-vitrine-crud').classList.remove('hidden');
    document.getElementById('modal-vitrine-crud').classList.add('flex');
}

function abrirModalUsuario() {
    document.getElementById('modal-usuario-title').innerText = 'Novo Usuário';
    document.getElementById('modal-usuario-id-tag').innerText = '';
    document.getElementById('usuario_id').value = '';
    document.getElementById('usuario_nome').value = '';
    document.getElementById('usuario_email').value = '';
    document.getElementById('usuario_username').value = '';
    document.getElementById('usuario_nivel').value = 'aluno';
    document.getElementById('usuario_senha').value = '';
    document.getElementById('usuario_telefone').value = '';
    document.getElementById('usuario_cidade').value = 'Sobradinho';
    document.getElementById('usuario_bairro').value = '';
    document.getElementById('usuario_endereco').value = '';
    document.getElementById('usuario_biografia').value = '';
    document.getElementById('usuario_avatar_atual').value = '';
    document.getElementById('usuario_avatar_preview').src = 'assets/images/avatar-default.jpg';
    document.getElementById('usuario_avatar_input').value = '';
    document.getElementById('usuario_remover_foto_box').classList.add('hidden');
    document.getElementById('usuario_remover_foto').checked = false;
    document.getElementById('senha_hint').innerText = '(obrigatória p/ novos)';
    document.getElementById('modal-usuario-crud').classList.remove('hidden');
    document.getElementById('modal-usuario-crud').classList.add('flex');
}

function editarUsuario(u) {
    document.getElementById('modal-usuario-title').innerText = 'Editar Usuário';
    document.getElementById('modal-usuario-id-tag').innerText = 'ID: #' + u.id;
    document.getElementById('usuario_id').value = u.id;
    document.getElementById('usuario_nome').value = u.nome;
    document.getElementById('usuario_email').value = u.email;
    document.getElementById('usuario_username').value = u.username || '';
    document.getElementById('usuario_nivel').value = u.nivel;
    document.getElementById('usuario_senha').value = '';
    document.getElementById('usuario_telefone').value = u.telefone || '';
    document.getElementById('usuario_cidade').value = u.cidade || '';
    document.getElementById('usuario_bairro').value = u.bairro || '';
    document.getElementById('usuario_endereco').value = u.endereco || '';
    document.getElementById('usuario_biografia').value = u.biografia || '';
    document.getElementById('usuario_avatar_atual').value = u.avatar || '';
    document.getElementById('usuario_avatar_preview').src = u.avatar || 'assets/images/avatar-default.jpg';
    document.getElementById('usuario_avatar_input').value = '';
    
    if (u.avatar && u.avatar !== 'assets/images/avatar-default.jpg') {
        document.getElementById('usuario_remover_foto_box').classList.remove('hidden');
        document.getElementById('usuario_remover_foto_box').classList.add('inline-flex');
    } else {
        document.getElementById('usuario_remover_foto_box').classList.add('hidden');
    }
    document.getElementById('usuario_remover_foto').checked = false;
    document.getElementById('senha_hint').innerText = '(deixe em branco p/ manter)';

    document.getElementById('modal-usuario-crud').classList.remove('hidden');
    document.getElementById('modal-usuario-crud').classList.add('flex');
}

function previewVitrineMedia(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('vitrine_media_preview');
            if (preview) preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function abrirModalVitrine() {
    document.getElementById('modal-vitrine-title').innerText = 'Novo Post na Vitrine';
    document.getElementById('vitrine_id').value = '';
    document.getElementById('vitrine_titulo').value = '';
    document.getElementById('vitrine_subtitulo').value = '';
    document.getElementById('vitrine_tipo').value = 'Foto';
    document.getElementById('vitrine_cidade_select').value = 'Brasília/DF';
    carregarBairrosPorCidade('Brasília/DF', 'vitrine_bairro_select', 'Sobradinho');
    document.getElementById('vitrine_descricao').value = '';
    if (document.getElementById('vitrine_conteudo')) document.getElementById('vitrine_conteudo').value = '';
    if (document.getElementById('vitrine_video_url')) document.getElementById('vitrine_video_url').value = '';
    if (document.getElementById('vitrine_audio_url')) document.getElementById('vitrine_audio_url').value = '';
    if (document.getElementById('vitrine_tags')) document.getElementById('vitrine_tags').value = '';
    if (document.getElementById('vitrine_autor_nome')) document.getElementById('vitrine_autor_nome').value = 'FotoCidade DF';
    if (document.getElementById('vitrine_autor_role')) document.getElementById('vitrine_autor_role').value = 'Administrador';
    if (document.getElementById('vitrine_destaque')) document.getElementById('vitrine_destaque').checked = false;
    document.getElementById('vitrine_media_atual').value = '';
    if (document.getElementById('vitrine_media_preview')) {
        document.getElementById('vitrine_media_preview').src = 'assets/images/oficina-olhar-fercal.jpg';
    }
    abrirModal('modal-vitrine-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function editarVitrine(v) {
    document.getElementById('modal-vitrine-title').innerText = 'Editar Post da Vitrine';
    document.getElementById('vitrine_id').value = v.id || '';
    document.getElementById('vitrine_titulo').value = v.titulo || '';
    document.getElementById('vitrine_subtitulo').value = v.subtitulo || '';
    document.getElementById('vitrine_tipo').value = v.tipo || 'Foto';
    
    const cid = v.cidade || 'Brasília/DF';
    const bai = v.bairro || 'Sobradinho';
    document.getElementById('vitrine_cidade_select').value = cid;
    carregarBairrosPorCidade(cid, 'vitrine_bairro_select', bai);
    
    document.getElementById('vitrine_descricao').value = v.descricao || '';
    if (document.getElementById('vitrine_conteudo')) document.getElementById('vitrine_conteudo').value = v.conteudo || '';
    if (document.getElementById('vitrine_video_url')) document.getElementById('vitrine_video_url').value = v.videoUrl || v.video_url || '';
    if (document.getElementById('vitrine_audio_url')) document.getElementById('vitrine_audio_url').value = v.audioUrl || v.audio_url || '';
    
    const tagsVal = Array.isArray(v.tags) ? v.tags.join(', ') : (v.tags || '');
    if (document.getElementById('vitrine_tags')) document.getElementById('vitrine_tags').value = tagsVal;
    if (document.getElementById('vitrine_autor_nome')) document.getElementById('vitrine_autor_nome').value = v.autorNome || v.autor_nome || 'FotoCidade DF';
    if (document.getElementById('vitrine_autor_role')) document.getElementById('vitrine_autor_role').value = v.autorRole || v.autor_role || 'Administrador';
    if (document.getElementById('vitrine_destaque')) document.getElementById('vitrine_destaque').checked = !!(v.destaque);
    
    const currentMedia = v.mediaUrl || v.imagem || v.thumbnailUrl || 'assets/images/oficina-olhar-fercal.jpg';
    document.getElementById('vitrine_media_atual').value = currentMedia;
    if (document.getElementById('vitrine_media_preview')) {
        document.getElementById('vitrine_media_preview').src = currentMedia;
    }
    
    abrirModal('modal-vitrine-crud');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

let pickerMap = null;
let pickerMarker = null;

function previewPontoFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('ponto_foto_preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function abrirModalMapa() {
    document.getElementById('modal-mapa-title').innerText = 'Mapear Ponto Cultural';
    document.getElementById('ponto_id').value = '';
    document.getElementById('ponto_nome').value = '';
    document.getElementById('ponto_categoria').value = 'Espaço Cultural';
    document.getElementById('ponto_cidade_select').value = 'Brasília/DF';
    carregarBairrosPorCidade('Brasília/DF', 'ponto_bairro_select', 'Sobradinho');
    if (document.getElementById('ponto_bairro')) document.getElementById('ponto_bairro').value = '';
    document.getElementById('ponto_endereco').value = '';
    document.getElementById('ponto_descricao').value = '';
    if (document.getElementById('ponto_instagram')) document.getElementById('ponto_instagram').value = '';
    if (document.getElementById('ponto_website')) document.getElementById('ponto_website').value = '';
    if (document.getElementById('ponto_whatsapp')) document.getElementById('ponto_whatsapp').value = '';
    document.getElementById('ponto_lat').value = '-15.6534';
    document.getElementById('ponto_lng').value = '-47.7891';
    document.getElementById('ponto_foto_atual').value = '';
    if (document.getElementById('ponto_foto_preview')) {
        document.getElementById('ponto_foto_preview').src = 'assets/images/oficina-olhar-fercal.jpg';
    }
    document.getElementById('modal-mapa-crud').classList.remove('hidden');
    document.getElementById('modal-mapa-crud').classList.add('flex');
    initPickerMap(-15.6534, -47.7891);
}

function editarPontoMapa(p) {
    document.getElementById('modal-mapa-title').innerText = 'Editar Ponto Cultural';
    document.getElementById('ponto_id').value = p.origem_id || p.id;
    document.getElementById('ponto_nome').value = p.nome || '';
    document.getElementById('ponto_categoria').value = p.categoria || 'Espaço Cultural';
    const cid = p.cidade || 'Brasília/DF';
    const bai = p.regiao || p.regiaoAdministrativa || p.bairro || 'Sobradinho';
    document.getElementById('ponto_cidade_select').value = cid;
    carregarBairrosPorCidade(cid, 'ponto_bairro_select', bai);
    if (document.getElementById('ponto_bairro')) document.getElementById('ponto_bairro').value = p.bairro || '';
    document.getElementById('ponto_endereco').value = p.endereco || '';
    document.getElementById('ponto_descricao').value = p.descricao || '';
    if (document.getElementById('ponto_instagram')) document.getElementById('ponto_instagram').value = p.instagram || '';
    if (document.getElementById('ponto_website')) document.getElementById('ponto_website').value = p.website || '';
    if (document.getElementById('ponto_whatsapp')) document.getElementById('ponto_whatsapp').value = p.whatsapp || '';
    const lat = p.lat || p.latitude || -15.6534;
    const lng = p.lng || p.longitude || -47.7891;
    document.getElementById('ponto_lat').value = lat;
    document.getElementById('ponto_lng').value = lng;
    const currentPhoto = p.foto || p.imagem || p.logo || 'assets/images/oficina-olhar-fercal.jpg';
    document.getElementById('ponto_foto_atual').value = currentPhoto;
    if (document.getElementById('ponto_foto_preview')) {
        document.getElementById('ponto_foto_preview').src = currentPhoto;
    }
    document.getElementById('modal-mapa-crud').classList.remove('hidden');
    document.getElementById('modal-mapa-crud').classList.add('flex');
    initPickerMap(lat, lng);
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('aba') === 'vitrine' && urlParams.get('novo') === '1') {
        abrirModalVitrine();
    } else if (urlParams.get('novo') === '1' || urlParams.get('novo') === 'ponto') {
        abrirModalMapa();
    } else if (urlParams.get('novo') === 'parceiro') {
        abrirModalParceiro();
    }
});

let parceiroPickerMap = null;
let parceiroPickerMarker = null;

function abrirModalParceiro() {
    document.getElementById('modal-parceiro-title').innerText = 'Novo Parceiro / Organização';
    document.getElementById('parceiro_id').value = '';
    document.getElementById('parceiro_nome').value = '';
    document.getElementById('parceiro_tipo').value = 'Governo';
    document.getElementById('parceiro_cidade_select').value = 'Brasília/DF';
    carregarBairrosPorCidade('Brasília/DF', 'parceiro_bairro_select', 'Sobradinho');
    if (document.getElementById('parceiro_endereco')) document.getElementById('parceiro_endereco').value = '';
    if (document.getElementById('parceiro_lat')) document.getElementById('parceiro_lat').value = '-15.6534';
    if (document.getElementById('parceiro_lng')) document.getElementById('parceiro_lng').value = '-47.7891';
    document.getElementById('parceiro_descricao').value = '';
    document.getElementById('parceiro_contribuicao').value = '';
    document.getElementById('parceiro_instagram').value = '';
    document.getElementById('parceiro_website').value = '';
    document.getElementById('parceiro_whatsapp').value = '';
    document.getElementById('parceiro_logo_atual').value = '';
    document.getElementById('modal-parceiro-crud').classList.remove('hidden');
    document.getElementById('modal-parceiro-crud').classList.add('flex');
    initParceiroPickerMap(-15.6534, -47.7891);
}

function editarParceiro(p) {
    document.getElementById('modal-parceiro-title').innerText = 'Editar Organização';
    document.getElementById('parceiro_id').value = p.id;
    document.getElementById('parceiro_nome').value = p.nome;
    if (document.getElementById('parceiro_setor')) document.getElementById('parceiro_setor').value = p.setor || '1º Setor (Poder Público)';
    document.getElementById('parceiro_tipo').value = p.tipo || 'Governo';
    const cid = p.cidade || 'Brasília/DF';
    const bai = p.bairro || 'Sobradinho';
    if (document.getElementById('parceiro_cidade_select')) document.getElementById('parceiro_cidade_select').value = cid;
    carregarBairrosPorCidade(cid, 'parceiro_bairro_select', bai);
    if (document.getElementById('parceiro_endereco')) document.getElementById('parceiro_endereco').value = p.endereco || '';
    const lat = p.lat ? parseFloat(p.lat) : -15.6534;
    const lng = p.lng ? parseFloat(p.lng) : -47.7891;
    if (document.getElementById('parceiro_lat')) document.getElementById('parceiro_lat').value = lat;
    if (document.getElementById('parceiro_lng')) document.getElementById('parceiro_lng').value = lng;
    document.getElementById('parceiro_descricao').value = p.descricao || '';
    document.getElementById('parceiro_contribuicao').value = p.contribuicao || '';
    document.getElementById('parceiro_instagram').value = p.instagram || '';
    document.getElementById('parceiro_website').value = p.website || p.site || '';
    document.getElementById('parceiro_whatsapp').value = p.whatsapp || '';
    document.getElementById('parceiro_logo_atual').value = p.logo || '';
    document.getElementById('modal-parceiro-crud').classList.remove('hidden');
    document.getElementById('modal-parceiro-crud').classList.add('flex');
    initParceiroPickerMap(lat, lng);
}

function initPickerMap(lat, lng) {
    setTimeout(() => {
        if (!pickerMap) {
            pickerMap = L.map('leaflet-picker-map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(pickerMap);

            pickerMarker = L.marker([lat, lng], { draggable: true }).addTo(pickerMap);

            pickerMarker.on('dragend', function(e) {
                const pos = pickerMarker.getLatLng();
                document.getElementById('ponto_lat').value = pos.lat.toFixed(6);
                document.getElementById('ponto_lng').value = pos.lng.toFixed(6);
            });

            pickerMap.on('click', function(e) {
                pickerMarker.setLatLng(e.latlng);
                document.getElementById('ponto_lat').value = e.latlng.lat.toFixed(6);
                document.getElementById('ponto_lng').value = e.latlng.lng.toFixed(6);
            });
        } else {
            pickerMap.invalidateSize();
            pickerMap.setView([lat, lng], 13);
            pickerMarker.setLatLng([lat, lng]);
        }
    }, 150);
}

function initParceiroPickerMap(lat, lng) {
    setTimeout(() => {
        if (!parceiroPickerMap) {
            const container = document.getElementById('leaflet-parceiro-picker-map');
            if (!container) return;
            parceiroPickerMap = L.map('leaflet-parceiro-picker-map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(parceiroPickerMap);

            parceiroPickerMarker = L.marker([lat, lng], { draggable: true }).addTo(parceiroPickerMap);

            parceiroPickerMarker.on('dragend', function(e) {
                const pos = parceiroPickerMarker.getLatLng();
                document.getElementById('parceiro_lat').value = pos.lat.toFixed(6);
                document.getElementById('parceiro_lng').value = pos.lng.toFixed(6);
            });

            parceiroPickerMap.on('click', function(e) {
                parceiroPickerMarker.setLatLng(e.latlng);
                document.getElementById('parceiro_lat').value = e.latlng.lat.toFixed(6);
                document.getElementById('parceiro_lng').value = e.latlng.lng.toFixed(6);
            });
        } else {
            parceiroPickerMap.invalidateSize();
            parceiroPickerMap.setView([lat, lng], 13);
            parceiroPickerMarker.setLatLng([lat, lng]);
        }
    }, 150);
}
</script>

<?php
require_once ROOT_PATH . '/components/common/footer.php';
?>
