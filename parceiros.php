<?php
/**
 * FotoCidade DF - Rede Intersetorial & Parceiros (parceiros.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'parceiros';
$pageTitle = 'Rede Intersetorial & Parceiros • Juntos pelo Território';

$parceiros = get_parceiros();
$talentos = get_talentos();
$pontosMapa = get_map_data();

// Consulta número real de usuários cadastrados no MySQL
$totalUsuariosRegistrados = count($talentos);
$pdo = get_db_connection();
if ($pdo) {
    try {
        $tableUsers = get_existing_table_name($pdo, 'usuarios', 'users');
        $stmtU = $pdo->query("SELECT COUNT(*) FROM `{$tableUsers}`");
        $totalUsuariosRegistrados = (int)$stmtU->fetchColumn();
    } catch (Exception $e) {}
}

// Contagem dinâmica baseada nos registros reais salvos no banco de dados map_locais
$cntEscolas = 0;
$cntEmpresas = 0;
$cntOngs = 0;

foreach ($parceiros as $p) {
    $str = strtolower(($p['tipo'] ?? '') . ' ' . ($p['setor'] ?? '') . ' ' . ($p['categoria'] ?? '') . ' ' . ($p['nome'] ?? ''));
    if (str_contains($str, 'escola') || str_contains($str, 'educa') || str_contains($str, 'colegio') || str_contains($str, 'governo') || str_contains($str, 'administracao') || str_contains($str, 'orgao') || str_contains($str, 'órgão') || str_contains($str, 'hospital') || str_contains($str, 'saude') || str_contains($str, 'saúde') || str_contains($str, '1º setor') || str_contains($str, 'patrimonio') || str_contains($str, 'patrimônio')) {
        $cntEscolas++;
    } elseif (str_contains($str, 'empresa') || str_contains($str, 'comercio') || str_contains($str, '2º setor') || str_contains($str, 'privado') || str_contains($str, 'feira') || str_contains($str, 'turismo')) {
        $cntEmpresas++;
    } else {
        $cntOngs++;
    }
}

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
require_once ROOT_PATH . '/components/cards/talent_card.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#0D5BA8] text-xs font-bold">
                <i data-lucide="handshake" class="w-4 h-4 text-[#FF8A00]"></i>
                <span>Articulação 1º, 2º e 3º Setor + Comunidade</span>
            </div>
            <h1 class="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
                Rede Intersetorial: Juntos pelo Território.
            </h1>
            <p class="text-sm text-slate-600">
                A comunicação e a formação cultural do FotoCidade conectam poder público, comércio local, organizações sociais e moradores.
            </p>
        </div>

        <!-- STATS COUNTERS DINÂMICOS DO MYSQL -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="school" class="w-6 h-6 text-[#0D5BA8] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900"><?php echo $cntEscolas; ?></div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Poder Público & Escolas</p>
            </div>

            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="building-2" class="w-6 h-6 text-[#00A7B5] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900"><?php echo $cntEmpresas; ?></div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Empresas & Comércio</p>
            </div>

            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="heart-handshake" class="w-6 h-6 text-[#FF8A00] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900"><?php echo $cntOngs; ?></div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">ONGs & Coletivos</p>
            </div>

            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="users" class="w-6 h-6 text-[#FFC107] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900"><?php echo $totalUsuariosRegistrados; ?></div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Moradores Mapeados</p>
            </div>
        </div>

        <!-- ACTION BOXES: Agenda Pauta + Kit de Comunicação -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Box 1 -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-5">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-[#FF8A00] font-bold text-xs uppercase tracking-wider">
                        <i data-lucide="megaphone" class="w-4 h-4"></i>
                        <span>Agenda Compartilhada</span>
                    </div>
                    <h3 class="font-heading font-bold text-xl text-slate-900">
                        Enviar Pauta para a Agenda
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Tem um sarau, feira comunitária, lançamento de livro ou ação social acontecendo em Sobradinho ou Fercal? Envie sua pauta para circular em nossa rede.
                    </p>
                </div>

                <button onclick="openModal('modal-agenda-pauta')"
                        class="w-full py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Cadastrar Pauta Comunitária</span>
                </button>
            </div>

            <!-- Box 2 -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-5">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-[#00A7B5] font-bold text-xs uppercase tracking-wider">
                        <i data-lucide="file-down" class="w-4 h-4"></i>
                        <span>Materiais para Download</span>
                    </div>
                    <h3 class="font-heading font-bold text-xl text-slate-900">
                        Kit de Comunicação Comunitária
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Baixe artes para redes sociais, modelos de cartazes para imprimir e colocar no comércio, e guias de fanzine da Trilha FotoCidade.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-2.5">
                    <button onclick="alert('Download do Kit Local iniciado!')" class="px-3 py-2.5 bg-blue-50 hover:bg-blue-100 text-[#0D5BA8] font-bold text-[11px] rounded-xl border border-blue-200 transition-colors flex flex-col items-center justify-center gap-1 text-center">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Kit Local</span>
                    </button>
                    <button onclick="alert('Download do Kit Setorial iniciado!')" class="px-3 py-2.5 bg-teal-50 hover:bg-teal-100 text-[#00A7B5] font-bold text-[11px] rounded-xl border border-teal-200 transition-colors flex flex-col items-center justify-center gap-1 text-center">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Kit Setor</span>
                    </button>
                    <button onclick="alert('Download do Kit Social iniciado!')" class="px-3 py-2.5 bg-amber-50 hover:bg-amber-100 text-[#FF8A00] font-bold text-[11px] rounded-xl border border-amber-200 transition-colors flex flex-col items-center justify-center gap-1 text-center">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Kit Social</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- BANCO DE TALENTOS LOCAIS -->
        <div class="space-y-6 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="font-heading font-black text-2xl text-slate-900">
                        Banco de Talentos Locais
                    </h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Fotógrafos, pesquisadores, videomakers e articuladores formados e atuantes no território.
                    </p>
                </div>
            </div>

            <?php if (!empty($talentos)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <?php foreach ($talentos as $talento): ?>
                        <?php render_talent_card($talento); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center max-w-xl mx-auto space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#FF8A00] flex items-center justify-center mx-auto">
                        <i data-lucide="sparkles" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-slate-900">Seja o Primeiro no Banco de Talentos!</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Nenhum talento se cadastrou publicamente ainda. Acesse o seu perfil e ative a opção <strong>"Exibir no Banco de Talentos"</strong> para aparecer nesta vitrine.
                    </p>
                    <div class="pt-2">
                        <a href="perfil.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0D5BA8] hover:bg-[#09427D] text-white font-bold text-xs rounded-xl shadow-xs transition-all">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Ir para Meu Perfil & Ativar</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- SERVIÇOS PÚBLICOS, PRIVADOS, TERCEIRO SETOR E TURISMO -->
        <div class="space-y-6 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="font-heading font-black text-2xl text-slate-900">
                        Poder Público, Empresas, ONGs, Turismo & Cultura
                    </h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Mapeamento de parceiros institucionais, órgãos públicos, empresas, ONGs e equipamentos culturais atuantes no território.
                    </p>
                </div>

                <?php if (is_admin()): ?>
                    <a href="dashboard.php?aba=parceiros" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs rounded-xl shadow-xs transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Gerenciar Organizações</span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($parceiros)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($parceiros as $parc): 
                        $rawPhone = preg_replace('/[^0-9]/', '', $parc['whatsapp'] ?? '');
                        $waUrl = '';
                        if (!empty($rawPhone)) {
                            if (strlen($rawPhone) <= 11 && !str_starts_with($rawPhone, '55')) {
                                $rawPhone = '55' . $rawPhone;
                            }
                            $waUrl = "https://wa.me/" . $rawPhone;
                        }
                    ?>
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#0D5BA8] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                        <?php echo htmlspecialchars($parc['tipo'] ?? 'Organização'); ?>
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-medium">📍 <?php echo htmlspecialchars(($parc['bairro'] ? $parc['bairro'] . ' • ' : '') . ($parc['cidade'] ?? 'DF')); ?></span>
                                </div>

                                <h3 class="font-heading font-bold text-base text-slate-900 leading-snug">
                                    <?php echo htmlspecialchars($parc['nome']); ?>
                                </h3>
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    <?php echo htmlspecialchars($parc['descricao'] ?? ''); ?>
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-100 space-y-2.5">
                                <?php if (!empty($parc['contribuicao'])): ?>
                                    <div class="text-[11px] text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                        <span class="font-bold text-[#FF8A00]">Atuação na rede: </span>
                                        <?php echo htmlspecialchars($parc['contribuicao']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Redes Sociais & Contato -->
                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <?php if (!empty($parc['instagram'])): ?>
                                            <a href="<?php echo htmlspecialchars(str_starts_with($parc['instagram'], 'http') ? $parc['instagram'] : 'https://instagram.com/' . ltrim($parc['instagram'], '@')); ?>" target="_blank" title="Instagram" class="p-1.5 bg-slate-100 hover:bg-pink-50 text-slate-600 hover:text-pink-600 rounded-lg transition-colors">
                                                <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($parc['site'])): ?>
                                            <a href="<?php echo htmlspecialchars(str_starts_with($parc['site'], 'http') ? $parc['site'] : 'https://' . $parc['site']); ?>" target="_blank" title="Website" class="p-1.5 bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 rounded-lg transition-colors">
                                                <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($parc['tiktok'])): ?>
                                            <a href="<?php echo htmlspecialchars(str_starts_with($parc['tiktok'], 'http') ? $parc['tiktok'] : 'https://tiktok.com/@' . ltrim($parc['tiktok'], '@')); ?>" target="_blank" title="TikTok" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-lg transition-colors">
                                                <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($waUrl): ?>
                                        <a href="<?php echo htmlspecialchars($waUrl); ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>WhatsApp</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center max-w-xl mx-auto space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center mx-auto">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-slate-900">Serviços e Organizações Mapeadas</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Nenhuma organização foi cadastrada ainda. Os Administradores podem cadastrar escolas, empresas, ONGs, órgãos públicos e pontos turísticos.
                    </p>
                    <?php if (is_admin()): ?>
                        <div class="pt-2">
                            <a href="dashboard.php?aba=parceiros" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs rounded-xl shadow-xs transition-all">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Cadastrar Nova Organização</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/forms/agenda_pauta_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
