<?php
/**
 * FotoCidade DF - Sala de Aula / Player do Aluno (curso.php)
 *
 * Interface moderna inspirada na referência com Player de Vídeo/Áudio,
 * Abas de Arquivos & Downloads, Anotações, Barra de Progresso e
 * Lista de Módulos & Aulas em Acordeão lateral.
 */

require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/auth_helper.php';
require_once ROOT_PATH . '/db_helper.php';

$cursoId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 1);
$curso = get_curso_completo($cursoId);

if (!$curso) {
    header("Location: trilha.php");
    exit;
}

$loggedUser = get_logged_user();

// Coleta todas as aulas do curso de forma linear para navegação
$todasAulas = [];
foreach ($curso['modulos'] as $m) {
    foreach ($m['aulas'] as $a) {
        $a['modulo_titulo'] = $m['titulo'];
        $todasAulas[] = $a;
    }
}

// Aula ativa
$aulaAtivaId = isset($_GET['aula']) ? (int)$_GET['aula'] : (!empty($todasAulas) ? $todasAulas[0]['id'] : null);
$aulaAtiva = null;
$aulaIndex = 0;

foreach ($todasAulas as $idx => $a) {
    if ($a['id'] === $aulaAtivaId) {
        $aulaAtiva = $a;
        $aulaIndex = $idx;
        break;
    }
}

if (!$aulaAtiva && !empty($todasAulas)) {
    $aulaAtiva = $todasAulas[0];
    $aulaIndex = 0;
}

$aulaAnterior = ($aulaIndex > 0) ? $todasAulas[$aulaIndex - 1] : null;
$aulaProxima = ($aulaIndex < count($todasAulas) - 1) ? $todasAulas[$aulaIndex + 1] : null;

$totalAulas = count($todasAulas);
$aulasConcluidas = max(1, round($totalAulas * 0.7)); // Demonstração de progresso
$porcentagemProgresso = $totalAulas > 0 ? round(($aulasConcluidas / $totalAulas) * 100) : 0;

$pageTitle = htmlspecialchars($curso['titulo']) . ' • FotoCidade DF';
require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<!-- Estilo Dark Mode Profissional para o Ambiente de Estudos (LMS) -->
<div class="min-h-screen bg-[#121418] text-slate-100 flex flex-col">

    <!-- Topbar do Player de Aula -->
    <div class="bg-[#181B20] border-b border-slate-800/80 px-4 sm:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="trilha.php" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors" title="Voltar para Trilhas">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#FFC107]"><?php echo htmlspecialchars($curso['trilha_titulo'] ?? 'Trilha de Formação'); ?></span>
                <h1 class="font-heading font-extrabold text-sm sm:text-base text-white truncate max-w-md sm:max-w-xl">
                    <?php echo htmlspecialchars($curso['titulo']); ?>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-[11px] font-bold text-slate-300"><?php echo $porcentagemProgresso; ?>% concluído (<?php echo $aulasConcluidas; ?> de <?php echo $totalAulas; ?> aulas)</p>
                <div class="w-36 h-1.5 bg-slate-800 rounded-full overflow-hidden mt-1">
                    <div class="h-full bg-gradient-to-r from-[#FF8A00] to-[#FFC107] rounded-full" style="width: <?php echo $porcentagemProgresso; ?>%"></div>
                </div>
            </div>
            <a href="trilha.php" class="px-3.5 py-1.5 bg-amber-500/10 border border-amber-500/30 text-[#FFC107] hover:bg-amber-500/20 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
                <i data-lucide="award" class="w-3.5 h-3.5"></i>
                <span>Certificado</span>
            </a>
        </div>
    </div>

    <!-- Container Principal (Player + Conteúdo + Sidebar) -->
    <main class="flex-1 max-w-[1600px] w-full mx-auto p-4 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Coluna Esquerda: Player de Vídeo/Áudio + Abas de Conteúdo & Anexos -->
        <div class="lg:col-span-8 space-y-6">
            
            <?php if ($aulaAtiva): ?>
                <!-- Título da Aula & Breadcrumb -->
                <div>
                    <span class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-[#00A7B5]"></i>
                        <span><?php echo htmlspecialchars($aulaAtiva['modulo_titulo']); ?></span>
                    </span>
                    <h2 class="font-heading font-extrabold text-xl sm:text-2xl text-white mt-1">
                        <?php echo htmlspecialchars($aulaAtiva['titulo']); ?>
                    </h2>
                </div>

                <!-- PLAYER PRINCIPAL (VÍDEO / ÁUDIO) -->
                <div class="bg-black rounded-3xl overflow-hidden shadow-2xl border border-slate-800 aspect-video relative flex items-center justify-center">
                    <?php if ($aulaAtiva['tipo_video'] === 'youtube' && !empty($aulaAtiva['url_video'])): 
                        // Converte URL comum do YouTube em embed URL
                        $youtubeUrl = $aulaAtiva['url_video'];
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeUrl, $match)) {
                            $embedUrl = "https://www.youtube.com/embed/" . $match[1] . "?autoplay=0&rel=0";
                        } else {
                            $embedUrl = $youtubeUrl;
                        }
                    ?>
                        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    
                    <?php elseif ($aulaAtiva['tipo_video'] === 'upload' && !empty($aulaAtiva['url_video'])): ?>
                        <video controls class="w-full h-full object-contain bg-black">
                            <source src="<?php echo htmlspecialchars($aulaAtiva['url_video']); ?>" type="video/mp4">
                            Seu navegador não suporta reprodução direta de vídeos.
                        </video>
                    
                    <?php else: ?>
                        <!-- Capa do Curso com Player de Áudio ou Informativo -->
                        <div class="relative w-full h-full flex flex-col items-center justify-center p-6 text-center bg-slate-900/90">
                            <img src="<?php echo htmlspecialchars($curso['capa_url'] ?: 'assets/images/oficina-olhar-fercal.jpg'); ?>" class="absolute inset-0 w-full h-full object-cover opacity-20 blur-xs" />
                            <div class="relative z-10 space-y-4 max-w-md">
                                <div class="w-16 h-16 rounded-2xl bg-blue-500/20 border border-blue-400/30 text-[#00A7B5] flex items-center justify-center mx-auto">
                                    <i data-lucide="book-open" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading font-bold text-lg text-white"><?php echo htmlspecialchars($aulaAtiva['titulo']); ?></h3>
                                <p class="text-xs text-slate-300">Material teórico e roteiro prático disponível no texto e anexos abaixo.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PLAYER DE ÁUDIO (Caso o módulo/aula contenha áudio) -->
                <?php if (!empty($aulaAtiva['audio_url'])): ?>
                    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="headphones" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-white">Áudio / Podcast da Aula</p>
                            <audio controls class="w-full mt-2 h-8">
                                <source src="<?php echo htmlspecialchars($aulaAtiva['audio_url']); ?>">
                                Seu navegador não suporta áudio HTML5.
                            </audio>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- BARRA DE CONTROLE: AULA ANTERIOR / CONCLUIR / PRÓXIMA (Referência: image_f0ae6e.jpg) -->
                <div class="p-4 rounded-2xl bg-[#181B20] border border-slate-800 flex flex-wrap items-center justify-between gap-4">
                    <?php if ($aulaAnterior): ?>
                        <a href="curso.php?id=<?php echo $curso['id']; ?>&aula=<?php echo $aulaAnterior['id']; ?>" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <div class="text-left">
                                <p class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Aula Anterior</p>
                                <p class="truncate max-w-[140px] text-white"><?php echo htmlspecialchars($aulaAnterior['titulo']); ?></p>
                            </div>
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <button onclick="toggleAulaConcluida(this)" class="px-6 py-3 bg-white text-slate-900 hover:bg-emerald-500 hover:text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer group">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 group-hover:text-white"></i>
                        <span>Aula Concluída</span>
                    </button>

                    <?php if ($aulaProxima): ?>
                        <a href="curso.php?id=<?php echo $curso['id']; ?>&aula=<?php echo $aulaProxima['id']; ?>" class="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl transition-all flex items-center gap-2 shadow-md">
                            <div class="text-right">
                                <p class="text-[9px] uppercase tracking-wider text-amber-100 font-bold">Próxima Aula</p>
                                <p class="truncate max-w-[140px]"><?php echo htmlspecialchars($aulaProxima['titulo']); ?></p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php else: ?>
                        <a href="trilha.php" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                            <span>Finalizar Curso</span>
                            <i data-lucide="award" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- ABAS INFERIORES: ARQUIVOS / CONTEÚDO / ANOTAÇÕES (Referência: image_f0ae6e.jpg) -->
                <div class="bg-[#181B20] border border-slate-800 rounded-3xl overflow-hidden">
                    <!-- Nav das Abas -->
                    <div class="flex items-center gap-2 px-6 pt-4 border-b border-slate-800 text-xs font-bold">
                        <button onclick="switchAulaTab('arquivos')" id="tab-btn-arquivos" class="pb-3 px-3 text-[#FFC107] border-b-2 border-[#FFC107] flex items-center gap-1.5 transition-colors">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                            <span>Arquivos & Anexos (<?php echo count($aulaAtiva['anexos'] ?? []); ?>)</span>
                        </button>
                        <button onclick="switchAulaTab('conteudo')" id="tab-btn-conteudo" class="pb-3 px-3 text-slate-400 hover:text-slate-200 border-b-2 border-transparent flex items-center gap-1.5 transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <span>Roteiro & Texto</span>
                        </button>
                        <button onclick="switchAulaTab('anotacoes')" id="tab-btn-anotacoes" class="pb-3 px-3 text-slate-400 hover:text-slate-200 border-b-2 border-transparent flex items-center gap-1.5 transition-colors">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                            <span>Minhas Anotações</span>
                        </button>
                    </div>

                    <!-- Conteúdo das Abas -->
                    <div class="p-6">
                        <!-- Aba 1: Arquivos & Anexos para Download -->
                        <div id="tab-panel-arquivos" class="space-y-4">
                            <?php if (empty($aulaAtiva['anexos'])): ?>
                                <div class="text-center py-10 space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
                                        <i data-lucide="paperclip" class="w-6 h-6"></i>
                                    </div>
                                    <h4 class="font-bold text-sm text-slate-300">Nenhum arquivo anexado nesta aula</h4>
                                    <p class="text-xs text-slate-500">Materiais extras, PDFs e planilhas de apoio aparecerão aqui quando cadastrados pelo professor.</p>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php foreach ($aulaAtiva['anexos'] as $anx): ?>
                                        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-[#0D5BA8] transition-all flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-[#00A7B5] flex items-center justify-center shrink-0">
                                                    <i data-lucide="file-down" class="w-5 h-5"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="font-bold text-xs text-white truncate"><?php echo htmlspecialchars($anx['nome_arquivo']); ?></h5>
                                                    <p class="text-[10px] text-slate-400"><?php echo round(($anx['tamanho_bytes'] ?? 0) / 1024); ?> KB • Documento de Apoio</p>
                                                </div>
                                            </div>
                                            <a href="<?php echo htmlspecialchars($anx['url_arquivo']); ?>" target="_blank" download class="px-3 py-1.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-bold text-xs rounded-xl transition-colors flex items-center gap-1 shrink-0">
                                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                                <span>Baixar</span>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Aba 2: Roteiro & Conteúdo Rico Formatado -->
                        <div id="tab-panel-conteudo" class="hidden prose prose-invert prose-sm max-w-none leading-relaxed text-slate-300">
                            <?php if (!empty($aulaAtiva['conteudo'])): ?>
                                <?php echo $aulaAtiva['conteudo']; ?>
                            <?php else: ?>
                                <p class="italic text-slate-500 text-center py-6">Nenhum texto adicional publicado para esta aula.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Aba 3: Minhas Anotações -->
                        <div id="tab-panel-anotacoes" class="hidden space-y-3">
                            <label class="block text-xs font-bold text-slate-400">Caderno Digital do Aluno:</label>
                            <textarea rows="5" placeholder="Escreva suas anotações, insights e dúvidas sobre esta aula..." class="w-full p-4 rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-[#FFC107]"></textarea>
                            <div class="flex justify-end">
                                <button onclick="alert('Anotação salva no seu navegador com sucesso!')" class="px-4 py-2 bg-[#FF8A00] text-white font-bold text-xs rounded-xl">Salvar Anotação</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-12 text-center bg-[#181B20] rounded-3xl border border-slate-800">
                    <p class="text-slate-400 text-sm">Este curso ainda não possui aulas cadastradas.</p>
                </div>
            <?php endif; ?>

        </div>

        <!-- Coluna Direita: Sidebar com Acordeão de Módulos & Aulas (Referência: image_f0ae6e.jpg / image_f0ab2f.jpg) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-[#181B20] rounded-3xl border border-slate-800 p-5 sticky top-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="font-heading font-extrabold text-sm text-white flex items-center gap-2">
                        <i data-lucide="list" class="w-4 h-4 text-[#FF8A00]"></i>
                        <span>Conteúdo do Curso</span>
                    </h3>
                    <span class="text-[11px] font-bold text-slate-400"><?php echo count($curso['modulos']); ?> Módulos</span>
                </div>

                <!-- Lista Acordeão de Módulos e Aulas -->
                <div class="space-y-2.5 max-h-[75vh] overflow-y-auto pr-1">
                    <?php foreach ($curso['modulos'] as $mIdx => $mod): 
                        $isCurrentModulo = false;
                        foreach ($mod['aulas'] as $ca) {
                            if ($aulaAtiva && $ca['id'] === $aulaAtiva['id']) {
                                $isCurrentModulo = true;
                                break;
                            }
                        }
                    ?>
                        <div class="border border-slate-800 rounded-2xl overflow-hidden bg-slate-900/50">
                            <!-- Header do Módulo -->
                            <button onclick="toggleModuloAccordion(<?php echo $mod['id']; ?>)" class="w-full p-3.5 text-left flex items-center justify-between gap-3 bg-slate-900 hover:bg-slate-800/80 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-6 h-6 rounded-full bg-slate-800 text-[#FFC107] font-bold text-[11px] flex items-center justify-center shrink-0">
                                        <?php echo str_pad($mIdx + 1, 2, '0', STR_PAD_LEFT); ?>
                                    </span>
                                    <div class="min-w-0">
                                        <h4 class="font-heading font-bold text-xs text-white truncate"><?php echo htmlspecialchars($mod['titulo']); ?></h4>
                                        <p class="text-[10px] text-slate-400"><?php echo count($mod['aulas']); ?> aulas</p>
                                    </div>
                                </div>
                                <i id="mod-arrow-<?php echo $mod['id']; ?>" data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform <?php echo $isCurrentModulo ? 'rotate-180' : ''; ?>"></i>
                            </button>

                            <!-- Lista de Aulas do Módulo -->
                            <div id="mod-body-<?php echo $mod['id']; ?>" class="p-2 space-y-1 bg-[#14171C] <?php echo $isCurrentModulo ? '' : 'hidden'; ?>">
                                <?php if (empty($mod['aulas'])): ?>
                                    <p class="text-[10px] text-slate-500 italic py-1 px-3">Sem aulas no momento.</p>
                                <?php else: ?>
                                    <?php foreach ($mod['aulas'] as $aIdx => $a): 
                                        $isActive = ($aulaAtiva && $aulaAtiva['id'] === $a['id']);
                                    ?>
                                        <a href="curso.php?id=<?php echo $curso['id']; ?>&aula=<?php echo $a['id']; ?>" class="p-2.5 rounded-xl flex items-center justify-between gap-2 transition-all <?php echo $isActive ? 'bg-[#FF8A00] text-white font-bold shadow-md' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'; ?>">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <i data-lucide="<?php echo ($a['tipo_video'] === 'youtube' || $a['tipo_video'] === 'upload') ? 'play-circle' : 'file-text'; ?>" class="w-4 h-4 shrink-0 <?php echo $isActive ? 'text-white' : 'text-slate-400'; ?>"></i>
                                                <span class="text-xs truncate"><?php echo htmlspecialchars($a['titulo']); ?></span>
                                            </div>
                                            <?php if ($a['duracao_minutos'] > 0): ?>
                                                <span class="text-[10px] shrink-0 opacity-70"><?php echo $a['duracao_minutos']; ?>m</span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>

</div>

<script>
function switchAulaTab(tab) {
    const panels = ['arquivos', 'conteudo', 'anotacoes'];
    panels.forEach(p => {
        const panelEl = document.getElementById('tab-panel-' + p);
        const btnEl = document.getElementById('tab-btn-' + p);
        if (p === tab) {
            panelEl.classList.remove('hidden');
            btnEl.classList.add('text-[#FFC107]', 'border-[#FFC107]');
            btnEl.classList.remove('text-slate-400', 'border-transparent');
        } else {
            panelEl.classList.add('hidden');
            btnEl.classList.remove('text-[#FFC107]', 'border-[#FFC107]');
            btnEl.classList.add('text-slate-400', 'border-transparent');
        }
    });
}

function toggleModuloAccordion(modId) {
    const body = document.getElementById('mod-body-' + modId);
    const arrow = document.getElementById('mod-arrow-' + modId);
    if (body) {
        body.classList.toggle('hidden');
    }
    if (arrow) {
        arrow.classList.toggle('rotate-180');
    }
}

function toggleAulaConcluida(btn) {
    btn.classList.toggle('bg-emerald-600');
    btn.classList.toggle('text-white');
    const span = btn.querySelector('span');
    if (span) {
        span.innerText = span.innerText === 'Aula Concluída' ? 'Concluída ✔' : 'Aula Concluída';
    }
}
</script>

<?php require_once ROOT_PATH . '/components/common/footer.php'; ?>
