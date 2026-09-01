<?php
/**
 * Componente Modular: Project / Vitrine Card (PHP)
 */
function render_project_card($item) {
    $hasVideo = !empty($item['videoUrl']);
    $hasAudio = !empty($item['audioUrl']);
    ?>
    <div class="vitrine-card bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col group cursor-pointer"
         data-category="<?php echo htmlspecialchars($item['tipo'] ?? 'Foto'); ?>"
         onclick='abrirDetalhesPost(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)'>
        
        <!-- Media Container -->
        <div class="relative h-52 overflow-hidden bg-slate-100">
            <img src="<?php echo htmlspecialchars($item['mediaUrl']); ?>"
                 onerror="this.src='assets/images/oficina-olhar-fercal.jpg'"
                 alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-80"></div>
            
            <!-- Type badge -->
            <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/90 backdrop-blur-md text-[#0D5BA8] text-[10px] font-bold rounded-lg shadow-xs uppercase tracking-wider">
                <?php echo htmlspecialchars($item['tipo']); ?>
            </span>

            <!-- Media Indicators (Video / Audio) -->
            <div class="absolute top-3 right-3 flex items-center gap-1.5">
                <?php if ($hasVideo): ?>
                    <span class="p-1.5 rounded-lg bg-red-600/90 backdrop-blur-md text-white shadow-xs" title="Contém Vídeo">
                        <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                    </span>
                <?php endif; ?>
                <?php if ($hasAudio): ?>
                    <span class="p-1.5 rounded-lg bg-[#FF8A00]/90 backdrop-blur-md text-white shadow-xs" title="Contém Áudio">
                        <i data-lucide="music" class="w-3.5 h-3.5"></i>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Location badge -->
            <span class="absolute bottom-3 left-3 flex items-center gap-1 text-white text-xs font-semibold drop-shadow-md">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                <?php echo htmlspecialchars($item['bairro'] ?? 'Sobradinho'); ?>
            </span>
        </div>

        <!-- Body Content -->
        <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-[#0D5BA8] transition-colors leading-snug font-heading">
                    <?php echo htmlspecialchars($item['titulo']); ?>
                </h3>
                <?php if (!empty($item['subtitulo'])): ?>
                    <p class="text-xs font-medium text-[#00A7B5] mt-0.5">
                        <?php echo htmlspecialchars($item['subtitulo']); ?>
                    </p>
                <?php endif; ?>
                <p class="text-xs text-slate-600 mt-2.5 line-clamp-2 leading-relaxed">
                    <?php echo htmlspecialchars($item['descricao']); ?>
                </p>
            </div>

            <!-- Author & Actions Footer -->
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <img src="<?php echo htmlspecialchars($item['autorAvatar']); ?>"
                         onerror="this.src='assets/images/avatar-default.jpg'"
                         alt="<?php echo htmlspecialchars($item['autorNome']); ?>"
                         class="w-7 h-7 rounded-full object-cover ring-2 ring-slate-100" />
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($item['autorNome']); ?></p>
                        <p class="text-[10px] text-slate-400 leading-tight"><?php echo htmlspecialchars($item['autorRole']); ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-2" onclick="event.stopPropagation();">
                    <?php if (function_exists('is_admin') && is_admin()): ?>
                        <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja excluir este post (<?php echo addslashes($item['titulo']); ?>)?')">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <input type="hidden" name="action" value="excluir_vitrine">
                            <input type="hidden" name="redirect_to" value="vitrine.php">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="p-1.5 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Excluir Post (Admin)">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    <?php endif; ?>

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
    </div>
    <?php
}

