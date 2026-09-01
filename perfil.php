<?php
/**
 * FotoCidade DF - Painel do Usuário / Meu Perfil (perfil.php)
 * 
 * Permite visualizar e editar os dados do próprio perfil (Alunos e Administradores),
 * gerenciar visibilidade e categorias no Banco de Talentos Locais, redes sociais (Instagram, LinkedIn, TikTok),
 * realizar upload de foto 1080x1080 e alterar senha no MySQL.
 */
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/auth_helper.php';
require_once ROOT_PATH . '/upload_helper.php';

// Exige autenticação obrigatória para acessar o perfil
$currentUser = require_login();
$userId = (int)$currentUser['id'];

$mensagemSucesso = '';
$mensagemErro = '';

$pdo = get_db_connection();
if ($pdo) {
    ensure_user_talento_columns($pdo);
}

// Categorias Pré-definidas de Profissionalização
$categoriasOpcoes = [
    'Fotografia Documental',
    'Mapeamento Afetivo',
    'Vídeo com Celular & Edição',
    'Produção Cultural',
    'Design Gráfico & Fanzine',
    'Cobertura de Eventos',
    'Iluminação & Sonorização',
    'Comunicação Comunitária',
    'Muralismo & Arte Urbana'
];

// Processamento de edição do próprio perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salvar_meu_perfil') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $mensagemErro = 'Token de segurança expirado. Por favor, tente novamente.';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $tiktok = trim($_POST['tiktok'] ?? '');
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $removerFoto = !empty($_POST['remover_foto']);

        // Se clicou no botão "Salvar & Publicar no Banco de Talentos" ou marcou o checkbox
        $noBancoTalentos = (!empty($_POST['no_banco_talentos']) || isset($_POST['ativar_talentos_btn'])) ? 1 : 0;

        // Categorias selecionadas
        $catsSelecionadas = isset($_POST['categorias']) && is_array($_POST['categorias']) ? $_POST['categorias'] : [];
        $categoriaStr = implode(', ', array_map('sanitize_input_text', $catsSelecionadas));

        if (empty($nome) || empty($email)) {
            $mensagemErro = 'Nome completo e e-mail são obrigatórios.';
        } elseif (!empty($novaSenha) && strlen($novaSenha) < 4) {
            $mensagemErro = 'A nova senha deve conter no mínimo 4 caracteres.';
        } elseif (!empty($novaSenha) && $novaSenha !== $confirmarSenha) {
            $mensagemErro = 'A confirmação de senha não confere com a nova senha.';
        } else {
            if ($pdo) {
                try {
                    $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
                    
                    // Busca dados atuais no banco
                    $stmtAtual = $pdo->prepare("SELECT * FROM `{$tableUsers}` WHERE id = ?");
                    $stmtAtual->execute([$userId]);
                    $userAtual = $stmtAtual->fetch();

                    $avatarAtual = $userAtual['avatar'] ?? ($userAtual['foto'] ?? '');
                    $novoAvatar = $avatarAtual;

                    // Remoção de foto se solicitado
                    if ($removerFoto) {
                        if (!empty($avatarAtual) && file_exists(ROOT_PATH . '/' . $avatarAtual)) {
                            @unlink(ROOT_PATH . '/' . $avatarAtual);
                        }
                        $novoAvatar = '';
                    }

                    // Upload de nova foto 1080x1080
                    if (isset($_FILES['avatar_arquivo']) && $_FILES['avatar_arquivo']['error'] === UPLOAD_ERR_OK) {
                        $upRes = upload_user_profile_photo($_FILES['avatar_arquivo'], $nome, $userId);
                        if ($upRes['success']) {
                            $novoAvatar = $upRes['path'];
                        } else {
                            $mensagemErro = 'Erro no envio da foto: ' . $upRes['message'];
                        }
                    }

                    if (empty($mensagemErro)) {
                        $stmtCols = $pdo->query("SHOW COLUMNS FROM `{$tableUsers}`");
                        $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

                        $updateFields = [];
                        $params = [];

                        if (in_array('nome', $cols)) { $updateFields[] = "`nome` = ?"; $params[] = $nome; }
                        if (in_array('email', $cols)) { $updateFields[] = "`email` = ?"; $params[] = $email; }
                        if (in_array('cidade', $cols)) { $updateFields[] = "`cidade` = ?"; $params[] = $cidade; }
                        if (in_array('bairro', $cols)) { $updateFields[] = "`bairro` = ?"; $params[] = $bairro; }
                        if (in_array('endereco', $cols)) { $updateFields[] = "`endereco` = ?"; $params[] = $endereco; }
                        if (in_array('telefone', $cols)) { $updateFields[] = "`telefone` = ?"; $params[] = $telefone; }
                        if (in_array('biografia', $cols)) { $updateFields[] = "`biografia` = ?"; $params[] = $biografia; }
                        if (in_array('no_banco_talentos', $cols)) { $updateFields[] = "`no_banco_talentos` = ?"; $params[] = $noBancoTalentos; }
                        if (in_array('categoria_talento', $cols)) { $updateFields[] = "`categoria_talento` = ?"; $params[] = $categoriaStr; }
                        if (in_array('instagram', $cols)) { $updateFields[] = "`instagram` = ?"; $params[] = $instagram; }
                        if (in_array('linkedin', $cols)) { $updateFields[] = "`linkedin` = ?"; $params[] = $linkedin; }
                        if (in_array('tiktok', $cols)) { $updateFields[] = "`tiktok` = ?"; $params[] = $tiktok; }
                        if (in_array('avatar', $cols)) { $updateFields[] = "`avatar` = ?"; $params[] = $novoAvatar; }
                        if (in_array('foto', $cols)) { $updateFields[] = "`foto` = ?"; $params[] = $novoAvatar; }

                        if (!empty($novaSenha)) {
                            $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
                            if (in_array('senha', $cols)) { $updateFields[] = "`senha` = ?"; $params[] = $senhaHash; }
                            if (in_array('password', $cols)) { $updateFields[] = "`password` = ?"; $params[] = $senhaHash; }
                        }

                        if (!empty($updateFields)) {
                            $params[] = $userId;
                            $sql = "UPDATE `{$tableUsers}` SET " . implode(', ', $updateFields) . " WHERE id = ?";
                            $stmtUp = $pdo->prepare($sql);
                            $stmtUp->execute($params);

                            // Atualiza sessão
                            $_SESSION['usuario_nome'] = $nome;
                            $_SESSION['usuario_email'] = $email;
                            $_SESSION['usuario_cidade'] = $cidade;
                            if ($novoAvatar) {
                                $_SESSION['usuario_avatar'] = $novoAvatar;
                            } elseif ($removerFoto) {
                                $_SESSION['usuario_avatar'] = 'assets/images/avatar-default.jpg';
                            }

                            if ($noBancoTalentos) {
                                $mensagemSucesso = 'Perfil atualizado e publicado no Banco de Talentos com sucesso!';
                            } else {
                                $mensagemSucesso = 'Seus dados foram atualizados com sucesso!';
                            }
                        }
                    }
                } catch (Exception $e) {
                    $mensagemErro = 'Erro ao atualizar dados: ' . $e->getMessage();
                }
            } else {
                $mensagemErro = 'Banco de Dados indisponível no momento.';
            }
        }
    }
}

// Carrega os dados mais frescos do usuário no MySQL
$userDb = null;
if ($pdo) {
    try {
        $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
        $stmt = $pdo->prepare("SELECT * FROM `{$tableUsers}` WHERE id = ?");
        $stmt->execute([$userId]);
        $userDb = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Perfil load user error: " . $e->getMessage());
    }
}

$userCatsArray = !empty($userDb['categoria_talento']) ? array_map('trim', explode(',', $userDb['categoria_talento'])) : [];

$user = [
    'id' => $userDb['id'] ?? $currentUser['id'],
    'nome' => $userDb['nome'] ?? ($currentUser['nome'] ?? 'Usuário'),
    'email' => $userDb['email'] ?? ($currentUser['email'] ?? ''),
    'username' => $userDb['username'] ?? ($currentUser['username'] ?? ''),
    'nivel' => $userDb['nivel'] ?? ($currentUser['nivel'] ?? 'aluno'),
    'telefone' => $userDb['telefone'] ?? '',
    'cidade' => $userDb['cidade'] ?? ($currentUser['cidade'] ?? 'Sobradinho'),
    'bairro' => $userDb['bairro'] ?? '',
    'endereco' => $userDb['endereco'] ?? '',
    'biografia' => $userDb['biografia'] ?? '',
    'no_banco_talentos' => (int)($userDb['no_banco_talentos'] ?? 0),
    'categoria_talento' => $userCatsArray,
    'instagram' => $userDb['instagram'] ?? '',
    'linkedin' => $userDb['linkedin'] ?? '',
    'tiktok' => $userDb['tiktok'] ?? '',
    'avatar' => !empty($userDb['avatar']) ? $userDb['avatar'] : (!empty($userDb['foto']) ? $userDb['foto'] : ($currentUser['avatar'] ?? 'assets/images/avatar-default.jpg')),
    'created_at' => $userDb['created_at'] ?? ($userDb['criado_em'] ?? date('d/m/Y')),
    'pontosCadastrados' => 0,
    'oficinasConcluidas' => 0,
    'totalOficinas' => 8,
    'progressoTrilha' => 0,
    'selos' => [
        ['id' => 's1', 'titulo' => 'Olhar Territorial', 'descricao' => 'Concluiu o módulo de Cartografia Afetiva', 'icone' => 'map-pin', 'cor' => 'bg-cyan-500'],
        ['id' => 's2', 'titulo' => 'Mestre da Lente', 'descricao' => 'Publicou ensaios na Vitrine Cultural', 'icone' => 'camera', 'cor' => 'bg-amber-500'],
        ['id' => 's3', 'titulo' => 'Voz Comunitária', 'descricao' => 'Participou de pautas da cidade', 'icone' => 'award', 'cor' => 'bg-indigo-500']
    ]
];

// Função auxiliar de sanitização
function sanitize_input_text($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

$activeTab = 'perfil';
$pageTitle = 'Meu Perfil • ' . htmlspecialchars($user['nome']);

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<main class="flex-1 py-8 bg-[#F8FAFC]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Notificações de Sucesso ou Erro -->
        <?php if (!empty($mensagemSucesso)): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                    <span><?php echo htmlspecialchars($mensagemSucesso); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
                    <span><?php echo htmlspecialchars($mensagemErro); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner Principal do Usuário -->
        <div class="bg-gradient-to-r from-[#0D5BA8] via-[#09427D] to-[#00A7B5] rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                
                <!-- Foto do Perfil com Borda -->
                <div class="relative group">
                    <img id="banner-avatar-preview"
                         src="<?php echo htmlspecialchars($user['avatar'] ?? 'assets/images/avatar-default.jpg'); ?>"
                         onerror="this.src='assets/images/avatar-default.jpg'"
                         alt="<?php echo htmlspecialchars($user['nome']); ?>"
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-white/80 shadow-md bg-white" />
                    <span class="absolute bottom-1 right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Conta Ativa"></span>
                </div>

                <!-- Dados Rápidos -->
                <div class="text-center sm:text-left space-y-2 flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 text-xs font-semibold text-teal-200">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FFC107]"></i>
                            <span><?php echo ($user['nivel'] === 'admin') ? 'Administrador Geral' : 'Agente Cultural FotoCidade'; ?></span>
                        </span>

                        <?php if ($user['no_banco_talentos']): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-emerald-500/30 border border-emerald-300/40 text-xs font-bold text-emerald-200">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-300"></i>
                                <span>No Banco de Talentos</span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="font-heading font-black text-2xl sm:text-3xl text-white">
                        <?php echo htmlspecialchars($user['nome']); ?>
                    </h1>

                    <?php if (!empty($user['username'])): ?>
                        <p class="text-xs text-blue-200 font-mono">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <?php endif; ?>

                    <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                        <?php echo !empty($user['biografia']) ? htmlspecialchars($user['biografia']) : 'Agente cultural atuando no fortalecimento da cultura local.'; ?>
                    </p>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 pt-2 text-xs text-slate-200">
                        <span class="flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FFC107]"></i>
                            <?php echo htmlspecialchars($user['cidade'] ?? 'Brasil'); ?>
                            <?php if (!empty($user['bairro'])): ?>
                                • <?php echo htmlspecialchars($user['bairro']); ?>
                            <?php endif; ?>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-[#00A7B5]"></i>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </span>
                    </div>
                </div>

                <!-- Ações do Cabeçalho -->
                <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                    <a href="parceiros.php"
                       class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>Ver Banco de Talentos</span>
                    </a>

                    <?php if ($user['nivel'] === 'admin'): ?>
                        <a href="dashboard.php"
                           class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Painel Gestor</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Grid de Conteúdo e Formulário de Edição -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Coluna Esquerda: Informações e Resumo de Atividades (4 Colunas) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Card de Dados Cadastrais -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-sm text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <i data-lucide="user" class="w-4 h-4 text-[#0D5BA8]"></i>
                        <span>Informações da Conta</span>
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Identificador</span>
                            <span class="font-semibold text-slate-800">#<?php echo $user['id']; ?></span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Visibilidade Publicação</span>
                            <?php if ($user['no_banco_talentos']): ?>
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-[11px]">
                                    <i data-lucide="check" class="w-3 h-3"></i> Publicado no Banco de Talentos
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full text-[11px]">
                                    Perfil Privado na Trilha
                                </span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Telefone / WhatsApp</span>
                            <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($user['telefone'] ?: 'Não informado'); ?></span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Cidade / Região</span>
                            <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($user['cidade'] ?: 'Brasil'); ?></span>
                        </div>

                        <?php if (!empty($user['instagram']) || !empty($user['linkedin']) || !empty($user['tiktok'])): ?>
                            <div class="pt-2 border-t border-slate-100 space-y-1">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Redes Sociais Conectadas</span>
                                <div class="flex items-center gap-2 pt-1">
                                    <?php if (!empty($user['instagram'])): ?>
                                        <span class="text-pink-600 font-bold bg-pink-50 px-2 py-0.5 rounded text-[10px]">Insta</span>
                                    <?php endif; ?>
                                    <?php if (!empty($user['linkedin'])): ?>
                                        <span class="text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded text-[10px]">LinkedIn</span>
                                    <?php endif; ?>
                                    <?php if (!empty($user['tiktok'])): ?>
                                        <span class="text-slate-900 font-bold bg-slate-100 px-2 py-0.5 rounded text-[10px]">TikTok</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Insígnias e Selos Conquistados -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="font-heading font-bold text-sm text-slate-900 flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-[#FF8A00]"></i>
                            <span>Insígnias Culturais</span>
                        </h3>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Oficiais</span>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($user['selos'] as $selo): ?>
                            <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-xs shrink-0 <?php echo $selo['cor']; ?>">
                                    <i data-lucide="<?php echo $selo['icone']; ?>" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-heading font-bold text-xs text-slate-900 truncate"><?php echo htmlspecialchars($selo['titulo']); ?></h4>
                                    <p class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($selo['descricao']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Coluna Direita: Formulário de Edição do Perfil (8 Colunas) -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
                    
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                        <div>
                            <h2 class="font-heading font-bold text-lg text-slate-900 flex items-center gap-2">
                                <i data-lucide="settings" class="w-5 h-5 text-[#0D5BA8]"></i>
                                <span>Editar Meus Dados</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Atualize suas informações pessoais, foto de perfil, redes sociais e visibilidade no Banco de Talentos.</p>
                        </div>
                    </div>

                    <form method="POST" action="perfil.php" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="salvar_meu_perfil">

                        <!-- Seção do Banco de Talentos e Visibilidade -->
                        <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="font-heading font-bold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-[#FF8A00]"></i>
                                        <span>Banco de Talentos Locais (Vitrine Pública)</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-600 mt-1">
                                        Ao ativar, seu perfil aparecerá publicamente na aba Parceiros & Talentos para contratantes, projetos e parceiros da rede.
                                    </p>
                                </div>

                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="no_banco_talentos" value="1" <?php echo $user['no_banco_talentos'] ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <!-- Seleção de Categorias de Profissionalização -->
                            <div class="pt-3 border-t border-amber-200/60 space-y-2">
                                <label class="block text-xs font-bold text-slate-800">
                                    Áreas de Atuação & Profissionalização:
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                    <?php foreach ($categoriasOpcoes as $catOpcao): 
                                        $isChecked = in_array($catOpcao, $user['categoria_talento']);
                                    ?>
                                        <label class="flex items-center gap-2 p-2 rounded-xl border border-amber-200/80 bg-white hover:bg-amber-50 cursor-pointer text-xs font-medium text-slate-700 transition-colors">
                                            <input type="checkbox" name="categorias[]" value="<?php echo htmlspecialchars($catOpcao); ?>" <?php echo $isChecked ? 'checked' : ''; ?> class="rounded border-slate-300 text-[#0D5BA8] focus:ring-[#0D5BA8]">
                                            <span><?php echo htmlspecialchars($catOpcao); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Seção de Foto de Perfil 1080x1080 -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Foto de Perfil (1080x1080 Alta Resolução)
                            </label>
                            
                            <div class="flex flex-col sm:flex-row items-center gap-5">
                                <img id="form-avatar-preview"
                                     src="<?php echo htmlspecialchars($user['avatar'] ?? 'assets/images/avatar-default.jpg'); ?>"
                                     onerror="this.src='assets/images/avatar-default.jpg'"
                                     class="w-20 h-20 rounded-2xl object-cover ring-2 ring-blue-500/30 bg-white shadow-xs" />
                                
                                <div class="space-y-2 flex-1 text-center sm:text-left">
                                    <input type="file" 
                                           id="avatar_arquivo" 
                                           name="avatar_arquivo" 
                                           accept="image/png, image/jpeg, image/webp" 
                                           onchange="previewPerfilFoto(this)"
                                           class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0D5BA8] file:text-white hover:file:bg-[#09427D] cursor-pointer">
                                    <p class="text-[11px] text-slate-500">Formato JPG, PNG ou WEBP. A imagem será ajustada em proporção 1:1 (1080x1080).</p>
                                    
                                    <?php if (!empty($user['avatar']) && strpos($user['avatar'], 'avatar-default') === false): ?>
                                        <div class="pt-1">
                                            <label class="inline-flex items-center gap-2 text-xs text-rose-600 font-semibold cursor-pointer">
                                                <input type="checkbox" name="remover_foto" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                                <span>Remover foto atual e usar avatar padrão</span>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Campos de Dados Pessoais & Localização no Brasil -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nome Completo *</label>
                                <input type="text" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">E-mail de Acesso *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Telefone / WhatsApp (Para contato direto)</label>
                                <input type="text" name="telefone" value="<?php echo htmlspecialchars($user['telefone']); ?>" placeholder="Ex: (61) 98765-4321"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Cidade / Estado (Cidades do Brasil)</label>
                                <input type="text" name="cidade" value="<?php echo htmlspecialchars($user['cidade']); ?>" placeholder="Ex: Sobradinho / DF, Ceilândia / DF, São Paulo / SP"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Bairro / Setor (Bairros do Brasil)</label>
                                <input type="text" name="bairro" value="<?php echo htmlspecialchars($user['bairro']); ?>" placeholder="Ex: Guará II, Fercal, Setor de Mansões, Pinheiros"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Endereço Completo</label>
                                <input type="text" name="endereco" value="<?php echo htmlspecialchars($user['endereco']); ?>" placeholder="Ex: Conjunto A, Casa 10"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none">
                            </div>
                        </div>

                        <!-- Seção de Redes Sociais -->
                        <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-3">
                            <h4 class="font-heading font-bold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="share-2" class="w-4 h-4 text-[#0D5BA8]"></i>
                                <span>Redes Sociais & Portfólio</span>
                            </h4>
                            <p class="text-[11px] text-slate-500">Insira seus links ou usuários para aparecerem no seu cartão do Banco de Talentos.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1 flex items-center gap-1">
                                        <i data-lucide="instagram" class="w-3.5 h-3.5 text-pink-600"></i> Instagram
                                    </label>
                                    <input type="text" name="instagram" value="<?php echo htmlspecialchars($user['instagram']); ?>" placeholder="@seu.usuario ou link"
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-pink-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1 flex items-center gap-1">
                                        <i data-lucide="linkedin" class="w-3.5 h-3.5 text-blue-600"></i> LinkedIn
                                    </label>
                                    <input type="text" name="linkedin" value="<?php echo htmlspecialchars($user['linkedin']); ?>" placeholder="seu-perfil ou link"
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1 flex items-center gap-1">
                                        <i data-lucide="video" class="w-3.5 h-3.5 text-slate-800"></i> TikTok
                                    </label>
                                    <input type="text" name="tiktok" value="<?php echo htmlspecialchars($user['tiktok']); ?>" placeholder="@seu.usuario"
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-slate-800">
                                </div>
                            </div>
                        </div>

                        <!-- Biografia -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Apresentação / Biografia</label>
                            <textarea name="biografia" rows="3" placeholder="Conte um pouco sobre sua atuação cultural ou relação com o território..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0D5BA8] focus:bg-white outline-none"><?php echo htmlspecialchars($user['biografia']); ?></textarea>
                        </div>

                        <!-- Alteração de Senha -->
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-500"></i>
                                <span>Alterar Minha Senha (Opcional)</span>
                            </h4>
                            <p class="text-[11px] text-slate-500">Deixe em branco se não desejar alterar sua senha atual.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nova Senha</label>
                                    <input type="password" name="nova_senha" placeholder="Mínimo 4 caracteres"
                                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-[#0D5BA8]">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Confirmar Nova Senha</label>
                                    <input type="password" name="confirmar_senha" placeholder="Repita a nova senha"
                                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-[#0D5BA8]">
                                </div>
                            </div>
                        </div>

                        <!-- Dois Botões de Ação no Rodapé: Banco de Talentos e Salvar Alterações -->
                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-3">
                            <!-- Botão 1: Banco de Talentos -->
                            <button type="submit"
                                    name="ativar_talentos_btn"
                                    value="1"
                                    class="w-full sm:w-auto px-5 py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 flex items-center justify-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Banco de Talentos</span>
                            </button>

                            <!-- Botão 2: Salvar Alterações -->
                            <button type="submit"
                                    class="w-full sm:w-auto px-6 py-3 bg-[#0D5BA8] hover:bg-[#09427D] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 flex items-center justify-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Salvar Alterações</span>
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>

    </div>
</main>

<script>
function previewPerfilFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const fPrev = document.getElementById('form-avatar-preview');
            const bPrev = document.getElementById('banner-avatar-preview');
            if (fPrev) fPrev.src = e.target.result;
            if (bPrev) bPrev.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
