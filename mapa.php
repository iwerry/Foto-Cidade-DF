<?php
/**
 * FotoCidade DF - Mapa Cultural (mapa.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'mapa';
$pageTitle = 'Mapa Cultural • Mapeamento Territorial do DF';

$points = get_map_data();

function getCategoryBadgeClass($cat) {
    switch ($cat) {
        case 'Hospital': return 'bg-rose-50 text-rose-700 border-rose-200';
        case 'Espaço Cultural': return 'bg-orange-50 text-[#FF8A00] border-orange-200';
        case 'Coletivo': return 'bg-teal-50 text-[#00A7B5] border-teal-200';
        case 'Ponto de Memória': return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'Feira Cultural': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'Órgão Público': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'Escola': return 'bg-amber-50 text-amber-800 border-amber-200';
        case 'Empresa': return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        case 'ONG': return 'bg-pink-50 text-pink-700 border-pink-200';
        case 'Instituto': return 'bg-emerald-50 text-emerald-800 border-emerald-200';
        case 'Associação': return 'bg-sky-50 text-sky-700 border-sky-200';
        case 'Turismo': return 'bg-yellow-50 text-yellow-800 border-yellow-200';
        case 'Patrimônio':
        default: return 'bg-blue-50 text-[#0D5BA8] border-blue-200';
    }
}

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    <span>Ponto cultural removido com sucesso do banco de dados!</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    <span>Ponto cultural salvo com sucesso no mapa!</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        <?php endif; ?>

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
                    <?php if (!empty($points)): ?>
                        <?php foreach ($points as $point): ?>
                            <div class="p-3 rounded-xl border border-slate-100 hover:border-[#0D5BA8] hover:bg-blue-50/50 transition-all flex items-center justify-between gap-3 group">
                                <div onclick="openPointDetails('<?php echo htmlspecialchars($point['id']); ?>')" class="flex gap-3 flex-1 min-w-0 cursor-pointer">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                        <img src="<?php echo htmlspecialchars(!empty($point['foto']) ? $point['foto'] : 'assets/images/oficina-olhar-fercal.jpg'); ?>" 
                                             onerror="this.src='assets/images/oficina-olhar-fercal.jpg'" 
                                             alt="<?php echo htmlspecialchars($point['nome']); ?>" 
                                             class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded border <?php echo getCategoryBadgeClass($point['categoria']); ?>">
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

                                <?php if (function_exists('is_admin') && is_admin()): ?>
                                    <div class="shrink-0 pl-1">
                                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Deseja excluir este ponto (<?php echo addslashes($point['nome']); ?>)?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="excluir_ponto_mapa">
                                            <input type="hidden" name="redirect_to" value="mapa.php">
                                            <input type="hidden" name="id" value="<?php echo $point['id']; ?>">
                                            <button type="submit" class="p-1.5 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Excluir Ponto (Admin)">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-6 text-center space-y-3 my-auto">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-[#FF8A00] flex items-center justify-center mx-auto">
                                <i data-lucide="map-pin-off" class="w-5 h-5"></i>
                            </div>
                            <h4 class="font-bold text-xs text-slate-800">Nenhum Ponto Mapeado Ainda</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Os pontos culturais e serviços aparecerão no mapa assim que cadastrados pelos administradores.
                            </p>
                            <?php if (is_admin()): ?>
                                <a href="dashboard.php?aba=mapa&novo=1" class="px-4 py-2 bg-[#0D5BA8] text-white text-xs font-bold rounded-xl shadow-xs hover:bg-[#0A4B8A] inline-block">
                                    + Mapear Primeiro Ponto
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
