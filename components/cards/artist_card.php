<?php
/**
 * Componente Modular: Artist Card (PHP)
 */
function render_artist_card($artist) {
    ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group">
        <div class="h-40 overflow-hidden relative bg-slate-100">
            <img src="<?php echo htmlspecialchars($artist['imagemCapa'] ?? 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=600&q=80'); ?>"
                 alt="<?php echo htmlspecialchars($artist['nome']); ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
            <span class="absolute top-3 right-3 px-2.5 py-0.5 bg-white/90 backdrop-blur-md text-[#0D5BA8] text-[10px] font-bold rounded-md uppercase">
                <?php echo htmlspecialchars($artist['categoria'] ?? 'Coletivo'); ?>
            </span>
        </div>

        <div class="p-5 flex-1 flex flex-col">
            <h3 class="text-base font-bold text-slate-900 font-heading group-hover:text-[#0D5BA8] transition-colors">
                <?php echo htmlspecialchars($artist['nome']); ?>
            </h3>
            <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                <span><?php echo htmlspecialchars($artist['regiaoAdministrativa'] ?? 'DF'); ?></span>
            </p>
            <p class="text-xs text-slate-600 mt-3 line-clamp-3 leading-relaxed flex-1">
                <?php echo htmlspecialchars($artist['descricao'] ?? ''); ?>
            </p>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">
                    <?php echo count($artist['integrantes'] ?? []); ?> Integrantes
                </span>
                <button onclick="alert('Conectando ao coletivo <?php echo addslashes(htmlspecialchars($artist['nome'])); ?>...')"
                        class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#0D5BA8] text-xs font-bold rounded-xl transition-colors">
                    Conectar
                </button>
            </div>
        </div>
    </div>
    <?php
}
