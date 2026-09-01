<?php
/**
 * Componente Modal de Detalhes do Post / Artigo da Vitrine (PHP)
 */
?>
<div id="modal-post-detail" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl relative border border-slate-100 animate-in fade-in zoom-in duration-200">
        <!-- Close Button -->
        <button onclick="closeModal('modal-post-detail')" class="absolute top-4 right-4 z-20 p-2 bg-white/80 hover:bg-white text-slate-700 hover:text-slate-900 rounded-full shadow-md backdrop-blur-md transition-all">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <!-- Media Container (Image or Video) -->
        <div id="post-detail-media-container" class="relative h-64 sm:h-80 bg-slate-900 overflow-hidden">
            <img id="post-detail-img" src="assets/images/oficina-olhar-fercal.jpg" onerror="this.src='assets/images/oficina-olhar-fercal.jpg'" class="w-full h-full object-cover" />
            <div id="post-detail-video-box" class="hidden w-full h-full"></div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
            
            <span id="post-detail-tipo" class="absolute top-4 left-4 px-3 py-1 bg-[#0D5BA8] text-white text-xs font-bold rounded-lg shadow-md uppercase tracking-wider">
                Foto
            </span>
            
            <div class="absolute bottom-4 left-4 right-4 text-white">
                <span id="post-detail-bairro" class="inline-flex items-center gap-1 text-xs text-amber-300 font-semibold mb-1">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                    Sobradinho
                </span>
                <h2 id="post-detail-titulo" class="text-xl sm:text-2xl font-black font-heading leading-tight drop-shadow-md">
                    Título do Artigo
                </h2>
            </div>
        </div>

        <!-- Content Body -->
        <div class="p-6 sm:p-8 space-y-6">
            
            <!-- Author & Meta Bar -->
            <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img id="post-detail-avatar" src="assets/images/avatar-default.jpg" class="w-11 h-11 rounded-full object-cover ring-2 ring-slate-100" />
                    <div>
                        <h4 id="post-detail-autor" class="text-xs sm:text-sm font-bold text-slate-900 leading-tight">Autor</h4>
                        <p id="post-detail-role" class="text-[11px] text-slate-500">Administrador</p>
                    </div>
                </div>

                <div class="text-right text-xs text-slate-400">
                    <span id="post-detail-data" class="font-medium">01/09/2026</span>
                </div>
            </div>

            <!-- Audio Player (if audio post) -->
            <div id="post-detail-audio-box" class="hidden p-4 rounded-2xl bg-amber-50/80 border border-amber-200 space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-amber-900">
                    <i data-lucide="music" class="w-4 h-4 text-[#FF8A00]"></i>
                    <span>Áudio / Podcast / Relato Sonoro</span>
                </div>
                <div id="post-detail-audio-player"></div>
            </div>

            <!-- Video Embed (if video post and not in header) -->
            <div id="post-detail-video-embed" class="hidden rounded-2xl overflow-hidden border border-slate-200 shadow-sm aspect-video"></div>

            <!-- Subtitle -->
            <p id="post-detail-subtitulo" class="text-sm font-semibold text-[#00A7B5] leading-relaxed"></p>

            <!-- Full Article / Content (Blog) -->
            <div id="post-detail-conteudo" class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line space-y-3 font-normal">
            </div>

            <!-- Tags -->
            <div id="post-detail-tags-box" class="pt-4 border-t border-slate-100 flex flex-wrap gap-1.5">
            </div>

            <!-- Admin Action Bar -->
            <?php if (function_exists('is_admin') && is_admin()): ?>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-amber-50/60 p-3.5 rounded-2xl border border-amber-200">
                    <div class="flex items-center gap-1.5 text-amber-900 text-xs font-bold">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#FF8A00]"></i>
                        <span>Administrador:</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="dashboard.php?aba=vitrine" class="px-3.5 py-1.5 bg-[#0D5BA8] hover:bg-[#09427D] text-white font-bold text-xs rounded-xl shadow-xs inline-flex items-center gap-1.5">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Editar no Painel</span>
                        </a>
                        <form method="POST" action="dashboard.php" class="inline" onsubmit="return confirm('Deseja realmente EXCLUIR esta publicação da vitrine definitivamente?')">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <input type="hidden" name="action" value="excluir_vitrine">
                            <input type="hidden" name="redirect_to" value="vitrine.php">
                            <input type="hidden" id="post-detail-delete-id" name="id" value="">
                            <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Excluir</span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="pt-2 flex justify-end">
                <button onclick="closeModal('modal-post-detail')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>
