<?php
/**
 * Componente Modular: Project / Vitrine Card (PHP)
 */
function render_project_card($item) {
    ?>
    <div class="vitrine-card bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col group"
         data-category="<?php echo htmlspecialchars($item['tipo'] ?? 'Foto'); ?>">
        <!-- Media Container -->
        <div class="relative h-52 overflow-hidden bg-slate-100">
            <img src="<?php echo htmlspecialchars($item['mediaUrl']); ?>"
                 alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-80"></div>
            
            <!-- Type badge -->
            <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/90 backdrop-blur-md text-[#0D5BA8] text-[10px] font-bold rounded-lg shadow-xs uppercase tracking-wider">
                <?php echo htmlspecialchars($item['tipo']); ?>
            </span>

            <!-- Location badge -->
            <span class="absolute bottom-3 left-3 flex items-center gap-1 text-white text-xs font-semibold drop-shadow-md">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                <?php echo htmlspecialchars($item['bairro']); ?>
            </span>
        </div>

        <!-- Body Content -->
        <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-[#0D5BA8] transition-colors leading-snug font-heading">
                    <?php echo htmlspecialchars($item['titulo']); ?>
                </h3>
                <p class="text-xs font-medium text-[#00A7B5] mt-0.5">
                    <?php echo htmlspecialchars($item['subtitulo']); ?>
                </p>
                <p class="text-xs text-slate-600 mt-2.5 line-clamp-2 leading-relaxed">
                    <?php echo htmlspecialchars($item['descricao']); ?>
                </p>
            </div>

            <!-- Author & Actions Footer -->
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <img src="<?php echo htmlspecialchars($item['autorAvatar']); ?>"
                         alt="<?php echo htmlspecialchars($item['autorNome']); ?>"
                         class="w-7 h-7 rounded-full object-cover ring-2 ring-slate-100" />
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($item['autorNome']); ?></p>
                        <p class="text-[10px] text-slate-400 leading-tight"><?php echo htmlspecialchars($item['autorRole']); ?></p>
                    </div>
                </div>

                <!-- Like button -->
                <button onclick="toggleLike(this)"
                        data-liked="<?php echo !empty($item['curtido']) ? 'true' : 'false'; ?>"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-semibold transition-colors <?php echo !empty($item['curtido']) ? 'text-rose-600 bg-rose-50' : 'text-slate-500 hover:text-rose-500 hover:bg-slate-50'; ?>">
                    <i data-lucide="heart" class="w-4 h-4 heart-icon <?php echo !empty($item['curtido']) ? 'fill-rose-500' : ''; ?>"></i>
                    <span class="like-count"><?php echo $item['likes'] ?? 0; ?></span>
                </button>
            </div>
        </div>
    </div>
    <?php
}
