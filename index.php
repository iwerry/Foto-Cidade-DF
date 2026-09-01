<?php
/**
 * FotoCidade DF - Página Inicial (index.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'inicio';
$pageTitle = 'Início • Olhar, Registrar e Revelar';

$vitrineItems = array_slice(get_vitrine(), 0, 3);
$artistas = array_slice(get_artistas(), 0, 3);
$mapPoints = get_map_data();

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
require_once ROOT_PATH . '/components/cards/project_card.php';
require_once ROOT_PATH . '/components/cards/artist_card.php';
?>

<main class="flex-1">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-[#0D5BA8] to-[#00A7B5] text-white pt-12 pb-20 lg:pt-20 lg:pb-28">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-[#FFC107]">
                        <i data-lucide="sparkles" class="w-4 h-4 text-[#FF8A00]"></i>
                        <span>Trilha Formativa Comunitária DF</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight font-heading">
                        Aprender o território, <br />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FFC107] via-[#FF8A00] to-white">
                            transformar a cidade.
                        </span>
                    </h1>

                    <p class="text-slate-100 text-base sm:text-lg max-w-2xl leading-relaxed mx-auto lg:mx-0">
                        Mapeamento territorial, produção cultural e comunicação comunitária em <strong class="text-white">Sobradinho, Fercal e Grande Colorado</strong>. Moradores tornam-se pesquisadores de suas próprias histórias.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                        <button onclick="openModal('modal-inscription')"
                                class="w-full sm:w-auto px-8 py-4 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-2 group">
                            <span>Inscrever-se na Trilha</span>
                            <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                        </button>

                        <a href="mapa.php"
                           class="w-full sm:w-auto px-7 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold rounded-2xl transition-all flex items-center justify-center gap-2">
                            <i data-lucide="map-pin" class="w-5 h-5 text-[#00A7B5]"></i>
                            <span>Explorar Mapa Cultural</span>
                        </a>
                    </div>

                    <!-- Counter Badges -->
                    <div class="grid grid-cols-3 gap-4 pt-8 border-t border-white/10 max-w-lg mx-auto lg:mx-0">
                        <div>
                            <p class="text-2xl sm:text-3xl font-black text-[#FFC107]">56h</p>
                            <p class="text-xs text-slate-200 font-medium mt-0.5">Carga Horária Total</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-black text-[#00A7B5]">15+</p>
                            <p class="text-xs text-slate-200 font-medium mt-0.5">Pontos Mapeados</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-black text-[#FF8A00]">100%</p>
                            <p class="text-xs text-slate-200 font-medium mt-0.5">Gratuito & Aberto</p>
                        </div>
                    </div>
                </div>

                <!-- Hero Image Showcase -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white/20 aspect-4/3">
                            <img src="assets/images/oficina-olhar-fercal.jpg" 
                                 alt="Oficina de Olhar Comunitário na Fercal - FotoCidade" 
                                 class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                            <div class="absolute bottom-6 left-6 right-6">
                                <span class="px-3 py-1 bg-[#FF8A00] text-white text-[10px] font-bold rounded-md uppercase">Cartografia Afetiva</span>
                                <h3 class="text-lg font-bold text-white mt-1 font-heading">Oficina de Olhar Comunitário na Fercal</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trilhas & Formação Section -->
    <section class="py-16 bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-blue-900 via-[#0D5BA8] to-teal-800 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 w-1/3 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                <div class="relative z-10 max-w-2xl space-y-4">
                    <span class="text-xs font-bold uppercase tracking-widest text-[#FFC107] bg-white/10 px-3.5 py-1 rounded-full border border-white/20">Formação Comunitária</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold font-heading">Trilhas de Aprendizado & Oficinas</h2>
                    <p class="text-slate-100 text-sm sm:text-base leading-relaxed">
                        Cursos práticos e imersões presenciais que capacitam agentes culturais, moradores e pesquisadores populares para mapear o território, produzir narrativas visuais e articular projetos de impacto no DF.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <button onclick="openModal('modal-inscription')" class="px-6 py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center gap-2">
                            <span>Quero Participar das Turmas</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                        <a href="trilha.php" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs border border-white/30 transition-all">
                            Conhecer as Trilhas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vitrine Destaques Section -->
    <section class="py-16 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-[#0D5BA8]">Galeria do Território</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 font-heading">Destaques da Vitrine</h2>
                </div>
                <a href="vitrine.php" class="mt-4 md:mt-0 text-xs font-bold text-[#0D5BA8] hover:text-[#00A7B5] flex items-center gap-1">
                    <span>Ver toda a vitrine</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($vitrineItems as $item): ?>
                    <?php render_project_card($item); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Mapa Cultural Preview Section -->
    <section class="py-16 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-8">
                <div class="lg:col-span-8">
                    <span class="text-xs font-bold uppercase tracking-widest text-[#00A7B5]">Georreferenciamento</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 font-heading">Mapa Cultural do DF e Entorno</h2>
                    <p class="text-xs sm:text-sm text-slate-600 mt-2">Explore pontos de memória, espaços culturais e feiras mapeadas pela comunidade.</p>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    <a href="mapa.php" class="px-5 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white font-bold text-xs rounded-xl shadow-md transition-all inline-flex items-center gap-2">
                        <span>Acessar Mapa Completo</span>
                        <i data-lucide="map" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <?php require ROOT_PATH . '/components/map/map_container.php'; ?>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.allMapPoints = <?php echo json_encode($mapPoints); ?>;
        initCulturalMap(window.allMapPoints, 'map-container');
    });
</script>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/forms/inscription_modal.php';
require_once ROOT_PATH . '/components/forms/agenda_pauta_modal.php';
require_once ROOT_PATH . '/components/map/point_detail_modal.php';
require_once ROOT_PATH . '/components/map/add_point_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
