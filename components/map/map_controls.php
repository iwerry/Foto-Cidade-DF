<?php
/**
 * Componente Filtros de Categoria e RA do Mapa (PHP)
 */
?>
<div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-4">
    <div>
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filtrar por Região Administrativa</h4>
        <div class="flex flex-wrap gap-1.5">
            <?php
            $ras = ['Todas', 'Sobradinho I', 'Sobradinho II', 'Fercal', 'Grande Colorado'];
            foreach ($ras as $index => $ra):
            ?>
                <button class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo $index === 0 ? 'bg-[#0D5BA8] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                    <?php echo htmlspecialchars($ra); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pt-3 border-t border-slate-100">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Categorias de Mapeamento</h4>
        <div class="flex flex-wrap gap-2">
            <span class="px-2.5 py-1 bg-blue-50 text-[#0D5BA8] text-xs font-bold rounded-lg flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#0D5BA8]"></span>
                Patrimônio
            </span>
            <span class="px-2.5 py-1 bg-orange-50 text-[#FF8A00] text-xs font-bold rounded-lg flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#FF8A00]"></span>
                Espaço Cultural
            </span>
            <span class="px-2.5 py-1 bg-teal-50 text-[#00A7B5] text-xs font-bold rounded-lg flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#00A7B5]"></span>
                Coletivo
            </span>
            <span class="px-2.5 py-1 bg-purple-50 text-purple-600 text-xs font-bold rounded-lg flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                Ponto de Memória
            </span>
            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-lg flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Feira Cultural
            </span>
        </div>
    </div>
</div>
