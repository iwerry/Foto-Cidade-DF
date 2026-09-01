<?php
/**
 * Componente Container do Mapa Cultural (PHP)
 */
?>
<div class="relative w-full h-[520px] rounded-3xl overflow-hidden shadow-lg border border-slate-200">
    <!-- Elemento DOM onde o Leaflet carrega -->
    <div id="map-container" class="w-full h-full"></div>

    <!-- Floating Map Controls Header / Quick Actions -->
    <?php if (function_exists('is_admin') && is_admin()): ?>
        <div class="absolute top-4 left-4 z-20 flex flex-wrap items-center gap-2">
            <a href="dashboard.php?aba=mapa&novo=1"
               class="px-4 py-2 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2 backdrop-blur-md">
                <i data-lucide="plus-circle" class="w-4 h-4 text-[#FF8A00]"></i>
                <span>Mapear Novo Ponto</span>
            </a>
        </div>
    <?php endif; ?>
</div>
