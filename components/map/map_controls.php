<?php
/**
 * Componente Filtros de Categoria e RA do Mapa (PHP)
 */
$categoriasMapa = [
    ['nome' => 'Patrimônio', 'cor' => 'bg-[#0D5BA8]', 'badge' => 'bg-blue-50 text-[#0D5BA8]'],
    ['nome' => 'Espaço Cultural', 'cor' => 'bg-[#FF8A00]', 'badge' => 'bg-orange-50 text-[#FF8A00]'],
    ['nome' => 'Coletivo', 'cor' => 'bg-[#00A7B5]', 'badge' => 'bg-teal-50 text-[#00A7B5]'],
    ['nome' => 'Ponto de Memória', 'cor' => 'bg-purple-500', 'badge' => 'bg-purple-50 text-purple-600'],
    ['nome' => 'Feira Cultural', 'cor' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-600'],
    ['nome' => 'Órgão Público', 'cor' => 'bg-blue-600', 'badge' => 'bg-blue-50 text-blue-700'],
    ['nome' => 'Hospital', 'cor' => 'bg-rose-500', 'badge' => 'bg-rose-50 text-rose-700'],
    ['nome' => 'Escola', 'cor' => 'bg-amber-600', 'badge' => 'bg-amber-50 text-amber-800'],
    ['nome' => 'Empresa', 'cor' => 'bg-indigo-600', 'badge' => 'bg-indigo-50 text-indigo-700'],
    ['nome' => 'ONG', 'cor' => 'bg-pink-500', 'badge' => 'bg-pink-50 text-pink-700'],
    ['nome' => 'Instituto', 'cor' => 'bg-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-800'],
    ['nome' => 'Associação', 'cor' => 'bg-sky-600', 'badge' => 'bg-sky-50 text-sky-700'],
    ['nome' => 'Turismo', 'cor' => 'bg-yellow-500', 'badge' => 'bg-yellow-50 text-yellow-800']
];
?>
<div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
    <div>
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filtrar por Região Administrativa</h4>
        <div class="flex flex-wrap gap-1.5">
            <?php
            $ras = ['Todas', 'Sobradinho', 'Sobradinho II', 'Fercal', 'Grande Colorado', 'Outra RA / Cidade'];
            foreach ($ras as $index => $ra):
            ?>
                <button onclick="filterMapByRa('<?php echo htmlspecialchars($ra); ?>')" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo $index === 0 ? 'bg-[#0D5BA8] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                    <?php echo htmlspecialchars($ra); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pt-3 border-t border-slate-100">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Categorias de Mapeamento (13 Categorias)</h4>
        <div class="flex flex-wrap gap-1.5">
            <?php foreach ($categoriasMapa as $cat): ?>
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg flex items-center gap-1.5 border border-slate-100 <?php echo $cat['badge']; ?>">
                    <span class="w-2 h-2 rounded-full <?php echo $cat['cor']; ?>"></span>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
