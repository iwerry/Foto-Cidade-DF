<?php
/**
 * FotoCidade DF - A Trilha Formativa (trilha.php)
 */
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/auth_helper.php';
require_once ROOT_PATH . '/db_helper.php';

$activeTab = 'trilha';
$pageTitle = 'Trilhas de Aprendizado & Cursos • FotoCidade DF';

$trilhas = get_all_trilhas();
$cursos = get_cursos();
$loggedUser = get_logged_user();

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<main class="flex-1 py-10 bg-[#F8FAFC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header Principal da Seção de Formação -->
        <div class="bg-gradient-to-br from-[#0D5BA8] via-[#09427D] to-[#0A2540] rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-cyan-400/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 max-w-3xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[#FFC107] text-xs font-bold border border-white/10">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    <span>Ambiente Pedagógico de Formação</span>
                </div>
                <h1 class="font-heading font-black text-2xl sm:text-4xl text-white tracking-tight">
                    Trilhas de Aprendizado & Cursos
                </h1>
                <p class="text-sm text-slate-200 leading-relaxed max-w-2xl">
                    Formação prática em fotografia documental, cartografia afetiva, comunicação comunitária e produção cultural para agentes do Distrito Federal.
                </p>
            </div>
        </div>

        <?php if (empty($cursos)): ?>
            <!-- Empty State quando não há cursos cadastrados -->
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-2xl mx-auto shadow-xs space-y-4 my-8">
                <div class="w-16 h-16 bg-blue-50 text-[#0D5BA8] rounded-2xl flex items-center justify-center mx-auto">
                    <i data-lucide="book-open" class="w-8 h-8"></i>
                </div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Nenhum Curso Ativo no Momento</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Fique atento aos novos módulos e turmas de formação que serão abertos pela coordenação do FotoCidade DF.
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
            
            <!-- Grid de Cursos Disponíveis -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-heading font-extrabold text-xl text-slate-900 flex items-center gap-2">
                        <i data-lucide="book-marked" class="w-5 h-5 text-[#FF8A00]"></i>
                        <span>Cursos e Oficinas Abertas</span>
                    </h2>
                    <span class="text-xs font-bold text-slate-400"><?php echo count($cursos); ?> cursos cadastrados</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($cursos as $c): ?>
                        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col group">
                            <!-- Capa do Curso -->
                            <div class="relative aspect-video overflow-hidden bg-slate-900">
                                <img src="<?php echo htmlspecialchars($c['capa_url'] ?: 'assets/images/oficina-olhar-fercal.jpg'); ?>"
                                     onerror="this.src='assets/images/oficina-olhar-fercal.jpg'"
                                     alt="<?php echo htmlspecialchars($c['titulo']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                                <div class="absolute top-3 left-3 flex items-center gap-2">
                                    <?php if (!empty($c['trilha_titulo'])): ?>
                                        <span class="px-2.5 py-1 rounded-full bg-[#0D5BA8] text-white text-[10px] font-bold shadow-xs">
                                            <?php echo htmlspecialchars($c['trilha_titulo']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-xs font-bold">
                                    <span class="flex items-center gap-1 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#FFC107]"></i>
                                        <span><?php echo htmlspecialchars($c['carga_horaria'] ?? '12h'); ?></span>
                                    </span>
                                    <span class="flex items-center gap-1 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                        <i data-lucide="play" class="w-3.5 h-3.5 text-rose-400"></i>
                                        <span><?php echo $c['total_aulas']; ?> aulas</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Corpo do Card -->
                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div>
                                    <h3 class="font-heading font-extrabold text-base text-slate-900 group-hover:text-[#0D5BA8] transition-colors line-clamp-2">
                                        <?php echo htmlspecialchars($c['titulo']); ?>
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-2 line-clamp-3 leading-relaxed">
                                        <?php echo htmlspecialchars($c['descricao'] ?? ''); ?>
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <div class="text-[11px] text-slate-400 font-semibold">
                                        <span>Instrutor:</span>
                                        <p class="font-bold text-slate-700 truncate max-w-[120px]"><?php echo htmlspecialchars($c['professor_nome'] ?? 'FotoCidade'); ?></p>
                                    </div>

                                    <a href="curso.php?id=<?php echo $c['id']; ?>" class="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 flex items-center gap-1.5 shrink-0">
                                        <span>Acessar Sala</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php require_once ROOT_PATH . '/components/common/footer.php'; ?>
