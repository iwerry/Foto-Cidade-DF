<?php
/**
 * FotoCidade DF - Vitrine do Território (vitrine.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'vitrine';
$pageTitle = 'Vitrine do Território • O que nossa rede está revelando';

$vitrineItems = get_vitrine();

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
require_once ROOT_PATH . '/components/cards/project_card.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-[#0D5BA8] uppercase tracking-wider mb-1">
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#FF8A00]"></i>
                    <span>Galeria Aberta da Comunidade</span>
                </div>
                <h1 class="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
                    Vitrine do Território: O que nossa rede está revelando.
                </h1>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                    Produções autorais dos alunos, fotografias documentais, histórias de mestres e memórias vivas de Sobradinho e Fercal.
                </p>
            </div>

            <div class="text-xs font-semibold text-slate-500">
                Mostrando <strong class="text-slate-900"><?php echo count($vitrineItems); ?></strong> produções
            </div>
        </div>

        <!-- Filter and Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <!-- Search Box -->
                <div class="md:col-span-6 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text"
                           id="search-vitrine-input"
                           placeholder="Buscar por fotos, relatos, autor, bairros..."
                           class="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]" />
                </div>

                <!-- Type Category Selector Buttons -->
                <div class="md:col-span-6 flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#00A7B5]"></i> Categoria:
                    </span>
                    <button data-filter="Todos" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-[#0D5BA8] text-white shadow-xs">
                        Todos
                    </button>
                    <button data-filter="Foto" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Foto
                    </button>
                    <button data-filter="Vídeo" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Vídeo
                    </button>
                    <button data-filter="Ensaio" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Ensaio
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid of Items -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($vitrineItems as $item): ?>
                <?php render_project_card($item); ?>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/forms/submission_form.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
