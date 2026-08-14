<?php
/**
 * Componente Modular: Talent Card (PHP)
 */
function render_talent_card($talent) {
    ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group">
        <div class="p-6 flex-1 flex flex-col">
            <div class="flex items-start gap-4">
                <img src="<?php echo htmlspecialchars($talent['fotoUrl'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80'); ?>"
                     alt="<?php echo htmlspecialchars($talent['nome']); ?>"
                     class="w-16 h-16 rounded-2xl object-cover ring-4 ring-blue-50 group-hover:scale-105 transition-transform duration-300" />
                <div class="flex-1 min-w-0">
                    <span class="inline-block px-2.5 py-0.5 bg-blue-50 text-[#0D5BA8] text-[10px] font-bold rounded-md uppercase tracking-wider mb-1">
                        <?php echo htmlspecialchars($talent['areaAtuacao'] ?? 'Talento'); ?>
                    </span>
                    <h3 class="text-base font-bold text-slate-900 truncate font-heading group-hover:text-[#0D5BA8] transition-colors">
                        <?php echo htmlspecialchars($talent['nome']); ?>
                    </h3>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                        <span><?php echo htmlspecialchars($talent['regiaoAdministrativa'] ?? 'DF'); ?></span>
                    </p>
                </div>
            </div>

            <p class="text-xs text-slate-600 mt-4 line-clamp-3 leading-relaxed">
                <?php echo htmlspecialchars($talent['bio'] ?? ''); ?>
            </p>

            <?php if (!empty($talent['habilidades'])): ?>
                <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap gap-1.5">
                    <?php foreach (array_slice($talent['habilidades'], 0, 3) as $hab): ?>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-semibold rounded-md">
                            <?php echo htmlspecialchars($hab); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[11px] font-medium text-slate-500">
                <?php echo htmlspecialchars($talent['disponibilidade'] ?? 'Disponível para pautas'); ?>
            </span>
            <a href="mailto:<?php echo htmlspecialchars($talent['contato'] ?? 'contato@fotocidade.org'); ?>"
               class="inline-flex items-center gap-1 text-xs font-bold text-[#0D5BA8] hover:text-[#00A7B5] transition-colors">
                <span>Contato</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
    <?php
}
