<?php
/**
 * Componente Footer Principal (PHP Modular)
 */
?>
<footer class="bg-[#1E293B] text-slate-300 pt-16 pb-12 border-t border-slate-700 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-700/80">
            
            <!-- Column 1: Brand & Slogan -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white/95 p-3 rounded-xl inline-block shadow-sm">
                    <?php render_logo('md', true, 'index.php'); ?>
                </div>
                <p class="text-slate-300 text-sm leading-relaxed max-w-md">
                    Uma trilha de formação em Mapeamento Territorial, Produção Cultural e Comunicação Comunitária. 
                    Transformando moradores em pesquisadores, comunicadores e articuladores de seus próprios territórios.
                </p>
                <div class="flex items-center gap-2 text-xs font-semibold text-[#FF8A00] tracking-wide uppercase pt-1">
                    <span>Sobradinho</span>
                    <span>•</span>
                    <span>Fercal</span>
                    <span>•</span>
                    <span>Grande Colorado</span>
                    <span>•</span>
                    <span>Distrito Federal</span>
                </div>
            </div>

            <!-- Column 2: Eixos de Formação -->
            <div>
                <h4 class="text-white font-bold text-sm tracking-wide uppercase mb-4 flex items-center gap-1.5 font-heading">
                    <i data-lucide="compass" class="w-4 h-4 text-[#00A7B5]"></i>
                    Eixos da Trilha
                </h4>
                <ul class="space-y-2.5 text-xs text-slate-300">
                    <li>
                        <a href="trilha.php" class="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0D5BA8]"></span>
                            I – Mapeamento Territorial (20h)
                        </a>
                    </li>
                    <li>
                        <a href="trilha.php" class="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#00A7B5]"></span>
                            II – Fotografia & Audiovisual (14h)
                        </a>
                    </li>
                    <li>
                        <a href="trilha.php" class="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF8A00]"></span>
                            III – Comunicação Comunitária (10h)
                        </a>
                    </li>
                    <li>
                        <a href="trilha.php" class="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FFC107]"></span>
                            IV – Produção Cultural (12h)
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Column 3: Módulos & Recursos -->
            <div>
                <h4 class="text-white font-bold text-sm tracking-wide uppercase mb-4 flex items-center gap-1.5 font-heading">
                    <i data-lucide="camera" class="w-4 h-4 text-[#FF8A00]"></i>
                    Plataforma
                </h4>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li><a href="mapa.php" class="hover:text-white transition-colors">Mapa Cultural Georreferenciado</a></li>
                    <li><a href="vitrine.php" class="hover:text-white transition-colors">Vitrine do Território</a></li>
                    <li><a href="parceiros.php" class="hover:text-white transition-colors">Rede Intersetorial & Parceiros</a></li>
                    <li><a href="parceiros.php" class="hover:text-white transition-colors">Banco de Talentos Locais</a></li>
                    <li><a href="perfil.php" class="hover:text-white transition-colors">Painel do Agente Cultural</a></li>
                </ul>
            </div>

            <!-- Column 4: Contato & Pautas -->
            <div>
                <h4 class="text-white font-bold text-sm tracking-wide uppercase mb-4 flex items-center gap-1.5 font-heading">
                    <i data-lucide="mail" class="w-4 h-4 text-[#FFC107]"></i>
                    Contato & Pautas
                </h4>
                <div class="space-y-3 text-xs text-slate-300">
                    <p class="flex items-start gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#00A7B5] shrink-0 mt-0.5"></i>
                        <span>Sobradinho e Fercal, DF - Brasil</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-[#00A7B5] shrink-0"></i>
                        <a href="mailto:contato@fotocidade.org" class="hover:underline">contato@fotocidade.org</a>
                    </p>
                    <div class="pt-2">
                        <button onclick="openModal('modal-agenda-pauta')"
                                class="w-full text-center px-3 py-2 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold rounded-lg text-xs transition-colors shadow-xs">
                            Enviar Pauta para a Agenda
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <p>© <?php echo date('Y'); ?> FotoCidade. "Aprender o território, transformar a cidade."</p>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1 text-slate-300">
                    Feito com <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-500 fill-rose-500"></i> pela Cultura Comunitária
                </span>
                <span class="text-slate-600">•</span>
                <a href="login.php" class="text-slate-500 hover:text-slate-300 transition-colors flex items-center gap-1 text-[11px]" title="Acesso Administrativo / Painel Gestor">
                    <i data-lucide="lock" class="w-3 h-3"></i>
                    <span>Área Restrita</span>
                </a>
            </div>
        </div>
    </div>
</footer>
