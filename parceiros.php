<?php
/**
 * FotoCidade DF - Rede Intersetorial & Parceiros (parceiros.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'parceiros';
$pageTitle = 'Rede Intersetorial & Parceiros • Juntos pelo Território';

$parceiros = get_parceiros();
$talentos = get_talentos();

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

        <!-- STATS COUNTERS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="school" class="w-6 h-6 text-[#0D5BA8] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900">15</div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Escolas</p>
            </div>

            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="building-2" class="w-6 h-6 text-[#00A7B5] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900">30</div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Empresas</p>
            </div>

            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="heart-handshake" class="w-6 h-6 text-[#FF8A00] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900">10</div>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">ONGs</p>
            </div>

            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
                <i data-lucide="users" class="w-6 h-6 text-[#FFC107] mx-auto mb-2"></i>
                <div class="text-4xl font-heading font-black text-slate-900">+400</div>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php foreach ($talentos as $talento): ?>
                    <?php render_talent_card($talento); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ORGANIZAÇÕES PARCEIRAS -->
        <div class="space-y-6 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="font-heading font-black text-2xl text-slate-900">
                        Organizações & Pontos de Circulação
                    </h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Espaços, escolas e empresas que apoiam a circulação da cultura.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($parceiros as $parc): ?>
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#0D5BA8] bg-blue-50 px-2 py-0.5 rounded">
                                    <?php echo htmlspecialchars($parc['tipo'] ?? 'Parceiro'); ?>
                                </span>
                                <span class="text-[10px] text-slate-500 font-medium"><?php echo htmlspecialchars($parc['bairro'] ?? 'DF'); ?></span>
                            </div>

                            <h3 class="font-heading font-bold text-base text-slate-900 leading-snug">
                                <?php echo htmlspecialchars($parc['nome']); ?>
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                <?php echo htmlspecialchars($parc['descricao'] ?? ''); ?>
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            <div class="text-[11px] text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                <span class="font-bold text-[#FF8A00]">Como atua na rede: </span>
                                <?php echo htmlspecialchars($parc['contribuicao'] ?? 'Apoio cultural'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/forms/agenda_pauta_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
