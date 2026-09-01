<?php
/**
 * FotoCidade DF - A Trilha Formativa (trilha.php)
 */
require_once __DIR__ . '/config.php';

require_once ROOT_PATH . '/auth_helper.php';

$activeTab = 'trilha';
$pageTitle = 'Trilha de Formação FotoCidade DF';

$trilhas = get_trilhas();
$loggedUser = get_logged_user();
$user = get_user_profile($loggedUser['id'] ?? null);

$submissions = [];
if ($loggedUser) {
    $submissions = [
        [
            'id' => 'sub-1',
            'tituloTrabalho' => 'Mapeamento Cultural do Território',
            'alunoNome' => $loggedUser['nome'],
            'bairro' => $loggedUser['cidade'] ?? 'Sobradinho',
            'dataEnvio' => 'Recente',
            'status' => 'Em Andamento'
        ]
    ];
}

$selectedEixoId = isset($_GET['eixo']) ? (int)$_GET['eixo'] : 1;
$currentEixo = null;
if (!empty($trilhas)) {
    foreach ($trilhas as $t) {
        if ($t['id'] === $selectedEixoId) {
            $currentEixo = $t;
            break;
        }
    }
    if (!$currentEixo) {
        $currentEixo = $trilhas[0];
    }
}

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if (empty($trilhas)): ?>
            <!-- Empty State quando não há cursos cadastrados -->
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-2xl mx-auto shadow-xs space-y-4 my-8">
                <div class="w-16 h-16 bg-blue-50 text-[#0D5BA8] rounded-2xl flex items-center justify-center mx-auto">
                    <i data-lucide="book-open" class="w-8 h-8"></i>
                </div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Trilhas de Aprendizado</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Nenhuma trilha de formação ou curso ativo no momento. Fique atento aos novos módulos e turmas de formação que serão abertos pela coordenação do FotoCidade DF.
                </p>
                <div class="pt-4 flex items-center justify-center gap-3">
                    <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
                        Voltar ao Início
                    </a>
                    <a href="vitrine.php" class="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        Conhecer a Vitrine
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Top Eixo Switcher Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-slate-200">
                <?php foreach ($trilhas as $eixo): 
                    $isSelected = ($currentEixo['id'] === $eixo['id']);
                ?>
                    <a href="trilha.php?eixo=<?php echo $eixo['id']; ?>"
                       class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 <?php echo $isSelected ? 'bg-[#0D5BA8] text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'; ?>">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] <?php echo $isSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'; ?>">
                            <?php echo $eixo['id']; ?>
                        </span>
                        <span><?php echo htmlspecialchars($eixo['titulo']); ?></span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded <?php echo $isSelected ? 'bg-white/20' : 'bg-slate-100 text-slate-500'; ?>">
                            <?php echo htmlspecialchars($eixo['cargaHoraria']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

        <!-- Student Portal Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Student Sidebar -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Student Profile Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
                    <?php if ($loggedUser): ?>
                        <div class="flex items-center gap-3">
                            <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'assets/images/avatar-default.jpg'); ?>"
                                 onerror="this.src='assets/images/avatar-default.jpg'"
                                 alt="<?php echo htmlspecialchars($user['nome']); ?>"
                                 class="w-12 h-12 rounded-full object-cover ring-2 ring-[#00A7B5] bg-white shadow-xs" />
                            <div class="min-w-0">
                                <h3 class="font-heading font-bold text-slate-900 text-sm truncate"><?php echo htmlspecialchars($user['nome']); ?></h3>
                                <p class="text-[11px] text-slate-500 truncate"><?php echo htmlspecialchars($user['cidade'] ?? 'DF'); ?></p>
                                <span class="inline-block mt-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    Agente Ativo
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center space-y-2 py-2">
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-[#0D5BA8] flex items-center justify-center mx-auto">
                                <i data-lucide="user" class="w-6 h-6"></i>
                            </div>
                            <h3 class="font-heading font-bold text-slate-900 text-sm">Área de Missões</h3>
                            <p class="text-[11px] text-slate-500">Faça login para salvar suas atividades e obter certificados.</p>
                            <a href="login.php" class="inline-block w-full py-2 px-3 bg-[#0D5BA8] text-white text-xs font-bold rounded-xl shadow-xs hover:bg-[#09427D] transition-colors">
                                Entrar na Minha Conta
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation links inside sidebar -->
                    <div class="space-y-1 pt-2 border-t border-slate-100 text-xs font-semibold">
                        <a href="trilha.php" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left bg-blue-50 text-[#0D5BA8] font-bold">
                            <i data-lucide="compass" class="w-4 h-4 text-[#0D5BA8]"></i>
                            <span>Painel & Missões</span>
                        </a>

                        <a href="perfil.php" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left text-slate-600 hover:bg-slate-50">
                            <i data-lucide="award" class="w-4 h-4 text-[#FF8A00]"></i>
                            <span>Minhas Insígnias (<?php echo count($user['selos'] ?? []); ?>)</span>
                        </a>

                        <a href="parceiros.php" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left text-slate-600 hover:bg-slate-50">
                            <i data-lucide="users" class="w-4 h-4 text-[#FFC107]"></i>
                            <span>Rede Intersetorial</span>
                        </a>
                    </div>
                </div>

                <!-- Submissions History Preview -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
                    <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center justify-between">
                        <span>Últimos Envios</span>
                        <span class="text-[10px] text-[#00A7B5] font-bold"><?php echo count($submissions); ?></span>
                    </h4>
                    
                    <?php foreach ($submissions as $sub): ?>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                            <p class="font-bold text-slate-800 line-clamp-1"><?php echo htmlspecialchars($sub['tituloTrabalho']); ?></p>
                            <div class="flex items-center justify-between text-[10px] text-slate-500">
                                <span><?php echo htmlspecialchars($sub['dataEnvio']); ?></span>
                                <span class="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">
                                    <?php echo htmlspecialchars($sub['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Main Learning Area -->
            <div class="lg:col-span-9 space-y-6">
                <!-- Header Banner -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#0D5BA8]">
                                <?php echo htmlspecialchars($currentEixo['cargaHoraria']); ?> de Investigação Prática
                            </span>
                            <h2 class="font-heading font-black text-2xl text-slate-900 mt-0.5">
                                Trilha de Formação: <?php echo htmlspecialchars($currentEixo['titulo']); ?>
                            </h2>
                            <p class="text-xs text-slate-600 mt-1 max-w-xl">
                                <?php echo htmlspecialchars($currentEixo['subtitulo']); ?>
                            </p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 sm:w-56 shrink-0">
                            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                <span class="text-slate-600">Progresso Geral:</span>
                                <span class="text-[#0D5BA8]"><?php echo $user['progressoTrilha']; ?>%</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-[#0D5BA8] to-[#00A7B5] rounded-full"
                                     style="width: <?php echo $user['progressoTrilha']; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Objectives -->
                    <?php if (!empty($currentEixo['objetivos'])): ?>
                        <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                            <?php foreach ($currentEixo['objetivos'] as $obj): ?>
                                <div class="flex items-center gap-1.5 text-xs text-slate-700 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#00A7B5]"></i>
                                    <span><?php echo htmlspecialchars($obj); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Missions & Interactive Form -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <!-- Missions Accordion -->
                    <div class="md:col-span-5 space-y-3">
                        <h3 class="font-heading font-bold text-xs uppercase tracking-wider text-slate-500 px-1">
                            Etapas do Eixo
                        </h3>

                        <?php foreach ($currentEixo['missoes'] as $index => $missao): 
                            $isSelected = ($index === 0);
                        ?>
                            <div class="p-4 rounded-xl border transition-all cursor-pointer <?php echo $isSelected ? 'bg-blue-50/80 border-[#0D5BA8] shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'; ?>">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 <?php echo $isSelected ? 'bg-[#0D5BA8] text-white' : 'bg-slate-100 text-slate-700'; ?>">
                                            <?php echo $missao['numero']; ?>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-heading font-bold text-sm text-slate-900 leading-snug">
                                                <?php echo htmlspecialchars($missao['titulo']); ?>
                                            </h4>
                                            <p class="text-xs text-slate-500 line-clamp-2 mt-0.5">
                                                <?php echo htmlspecialchars($missao['descricaoCurta']); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <?php if (($missao['status'] ?? '') === 'em_andamento'): ?>
                                            <span class="text-[10px] font-bold bg-[#00A7B5]/15 text-[#00A7B5] px-2 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="eye" class="w-3 h-3"></i> Em Curso
                                            </span>
                                        <?php elseif (($missao['status'] ?? '') === 'concluido'): ?>
                                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                                        <?php else: ?>
                                            <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Submission Action Panel -->
                    <div class="md:col-span-7">
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#00A7B5] bg-teal-50 px-2 py-0.5 rounded">Missão em Destaque</span>
                                    <h3 class="font-heading font-bold text-lg text-slate-900 mt-1">Enviar Registro Prático</h3>
                                </div>
                                <button onclick="openModal('modal-submission-form')"
                                        class="px-4 py-2 bg-[#FF8A00] hover:bg-[#E67A00] text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5">
                                    <i data-lucide="upload" class="w-4 h-4"></i>
                                    <span>Enviar Agora</span>
                                </button>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed">
                                Ao concluir o exercício de campo, faça o envio das imagens ou relatório. O seu trabalho será avaliado pelos facilitadores e disponibilizado na Vitrine do Território.
                            </p>

                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3">
                                <i data-lucide="file-text" class="w-8 h-8 text-[#0D5BA8]"></i>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">Guia de Apoio em PDF</h4>
                                    <p class="text-[11px] text-slate-500">Baixe o roteiro metodológico para aplicação comunitária.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <?php endif; ?>

    </div>
</main>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/forms/submission_form.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
