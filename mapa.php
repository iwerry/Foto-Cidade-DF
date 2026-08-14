<?php
/**
 * FotoCidade DF - Mapa Cultural (mapa.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'mapa';
$pageTitle = 'Mapa Cultural • Mapeamento Territorial do DF';

$points = get_map_data();

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-[#0D5BA8] uppercase tracking-wider mb-1">
                    <i data-lucide="navigation" class="w-4 h-4 text-[#00A7B5]"></i>
                    <span>Georreferenciamento Colaborativo</span>
                </div>
                <h1 class="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
                    Mapa Cultural do Território
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Mapeamento vivo de Sobradinho, Fercal e Grande Colorado com artistas, espaços de cultura e memórias da comunidade.
                </p>
            </div>

            <!-- Quick Legend -->
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 text-[11px] font-bold">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#0D5BA8]"></span>
                    <span>Patrimônio</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#FF8A00]"></span>
                    <span>Espaço</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#00A7B5]"></span>
                    <span>Coletivo</span>
                </div>
            </div>
        </div>

        <!-- Controls & Filters Component -->
        <?php require ROOT_PATH . '/components/map/map_controls.php'; ?>

        <!-- Interactive Map Layout (Side List + Canvas) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- Left Side List of Mapped Points -->
            <div class="lg:col-span-4 flex flex-col h-[520px] bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Pontos Mapeados (<?php echo count($points); ?>)</span>
                    <span class="text-[11px] text-slate-500 font-medium">Clique para ver no mapa</span>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    <?php foreach ($points as $point): ?>
                        <div onclick="openPointDetails('<?php echo htmlspecialchars($point['id']); ?>')"
                             class="p-3 rounded-xl border border-slate-100 hover:border-[#0D5BA8] hover:bg-blue-50/50 transition-all cursor-pointer flex gap-3">
                            <div class="w-14 h-14 rounded-lg bg-blue-50 text-[#0D5BA8] flex items-center justify-center shrink-0">
                                <i data-lucide="map-pin" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-blue-100 text-[#0D5BA8]">
                                        <?php echo htmlspecialchars($point['categoria']); ?>
                                    </span>
                                    <span class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($point['regiaoAdministrativa'] ?? 'DF'); ?></span>
                                </div>
                                <h4 class="font-heading font-bold text-xs text-slate-900 leading-snug truncate">
                                    <?php echo htmlspecialchars($point['nome']); ?>
                                </h4>
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">
                                    <?php echo htmlspecialchars($point['descricao']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Map Canvas Container -->
            <div class="lg:col-span-8 h-[520px]">
                <?php require ROOT_PATH . '/components/map/map_container.php'; ?>
            </div>

        </div>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.allMapPoints = <?php echo json_encode($points); ?>;
        initCulturalMap(window.allMapPoints, 'map-container');
    });
</script>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/map/point_detail_modal.php';
require_once ROOT_PATH . '/components/map/add_point_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
