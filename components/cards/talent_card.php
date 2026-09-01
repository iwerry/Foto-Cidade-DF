<?php
/**
 * Componente Modular: Talent Card (PHP)
 * Exibe o cartão do talento no Banco de Talentos com WhatsApp e Redes Sociais.
 */
function render_talent_card($talent) {
    $rawPhone = preg_replace('/[^0-9]/', '', $talent['telefone'] ?? '');
    $whatsappUrl = '';
    if (!empty($rawPhone)) {
        if (strlen($rawPhone) <= 11 && !str_starts_with($rawPhone, '55')) {
            $rawPhone = '55' . $rawPhone;
        }
        $whatsappUrl = "https://wa.me/" . $rawPhone . "?text=" . urlencode("Olá " . $talent['nome'] . ", vi seu perfil no Banco de Talentos do FotoCidade DF!");
    } elseif (!empty($talent['email'])) {
        $whatsappUrl = 'mailto:' . $talent['email'];
    } else {
        $whatsappUrl = '#';
    }

    // Formatação de redes sociais
    $instaUrl = '';
    if (!empty($talent['instagram'])) {
        $insta = trim($talent['instagram']);
        $instaHandle = ltrim($insta, '@');
        $instaUrl = str_starts_with($insta, 'http') ? $insta : "https://instagram.com/" . $instaHandle;
    }

    $linkedinUrl = '';
    if (!empty($talent['linkedin'])) {
        $linkd = trim($talent['linkedin']);
        $linkedinUrl = str_starts_with($linkd, 'http') ? $linkd : "https://linkedin.com/in/" . ltrim($linkd, '@');
    }

    $tiktokUrl = '';
    if (!empty($talent['tiktok'])) {
        $tt = trim($talent['tiktok']);
        $ttHandle = ltrim($tt, '@');
        $tiktokUrl = str_starts_with($tt, 'http') ? $tt : "https://tiktok.com/@" . $ttHandle;
    }
    ?>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group hover:-translate-y-0.5">
        <div class="p-6 flex-1 flex flex-col">
            <div class="flex items-start gap-4">
                <img src="<?php echo htmlspecialchars($talent['fotoUrl'] ?? 'assets/images/avatar-default.jpg'); ?>"
                     onerror="this.src='assets/images/avatar-default.jpg'"
                     alt="<?php echo htmlspecialchars($talent['nome']); ?>"
                     class="w-16 h-16 rounded-2xl object-cover ring-4 ring-blue-50 group-hover:scale-105 transition-transform duration-300 bg-white shadow-xs" />
                
                <div class="flex-1 min-w-0">
                    <span class="inline-block px-2.5 py-0.5 bg-blue-50 text-[#0D5BA8] text-[10px] font-bold rounded-md uppercase tracking-wider mb-1">
                        <?php echo htmlspecialchars($talent['areaAtuacao'] ?? 'Talento Local'); ?>
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
                <?php echo htmlspecialchars($talent['bio'] ?? 'Agente cultural integrante da rede FotoCidade DF.'); ?>
            </p>

            <!-- Categorias / Habilidades -->
            <?php if (!empty($talent['habilidades'])): ?>
                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-1.5">
                    <?php foreach ($talent['habilidades'] as $hab): ?>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-semibold rounded-md border border-slate-200/60">
                            <?php echo htmlspecialchars($hab); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Ícones de Redes Sociais -->
            <?php if ($instaUrl || $linkedinUrl || $tiktokUrl): ?>
                <div class="mt-3 flex items-center gap-2 pt-2 border-t border-slate-100/60 text-xs">
                    <?php if ($instaUrl): ?>
                        <a href="<?php echo htmlspecialchars($instaUrl); ?>" target="_blank" title="Instagram" class="p-1.5 bg-slate-100 hover:bg-pink-50 text-slate-600 hover:text-pink-600 rounded-lg transition-colors">
                            <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($linkedinUrl): ?>
                        <a href="<?php echo htmlspecialchars($linkedinUrl); ?>" target="_blank" title="LinkedIn" class="p-1.5 bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 rounded-lg transition-colors">
                            <i data-lucide="linkedin" class="w-3.5 h-3.5"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($tiktokUrl): ?>
                        <a href="<?php echo htmlspecialchars($tiktokUrl); ?>" target="_blank" title="TikTok" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-lg transition-colors">
                            <i data-lucide="video" class="w-3.5 h-3.5"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rodapé do Card com Contato Direto -->
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[11px] font-semibold text-emerald-700 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Ativo no Banco</span>
            </span>

            <a href="<?php echo htmlspecialchars($whatsappUrl); ?>" 
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition-all hover:scale-105">
                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                <span>Contato WhatsApp</span>
            </a>
        </div>
    </div>
    <?php
}
