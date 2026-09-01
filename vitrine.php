<?php
/**
 * FotoCidade DF - Vitrine do Território (vitrine.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'vitrine';
$pageTitle = 'Vitrine do Território • O que nossa rede está revelando';

$vitrineItems = get_vitrine();

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
require_once ROOT_PATH . '/components/cards/project_card.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs animate-in fade-in">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    <span>Publicação removida com sucesso da Vitrine Cultural!</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs animate-in fade-in">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    <span>Publicação salva com sucesso na Vitrine do Território!</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-[#0D5BA8] uppercase tracking-wider mb-1">
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#FF8A00]"></i>
                    <span>Galeria & Blog Aberto do Território</span>
                </div>
                <h1 class="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
                    Vitrine do Território: O que nossa rede está revelando.
                </h1>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                    Produções autorais, fotografias documentais, reportagens, minidocs, áudios e memórias vivas de Sobradinho e Fercal.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <?php if (is_admin()): ?>
                    <a href="dashboard.php?aba=vitrine&novo=1" class="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md flex items-center gap-2 transition-all hover:scale-105">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Novo Post (Painel)</span>
                    </a>
                <?php endif; ?>
                <div class="text-xs font-semibold text-slate-500 bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-2xs">
                    Mostrando <strong class="text-slate-900"><?php echo count($vitrineItems); ?></strong> produções
                </div>
            </div>
        </div>

        <!-- Filter and Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <!-- Search Box -->
                <div class="md:col-span-5 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text"
                           id="search-vitrine-input"
                           placeholder="Buscar por fotos, relatos, autor, bairros..."
                           class="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]" />
                </div>

                <!-- Type Category Selector Buttons -->
                <div class="md:col-span-7 flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#00A7B5]"></i> Categoria:
                    </span>
                    <button data-filter="Todos" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-[#0D5BA8] text-white shadow-xs">
                        Todos
                    </button>
                    <button data-filter="Foto" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Foto
                    </button>
                    <button data-filter="Vídeo" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Vídeo
                    </button>
                    <button data-filter="Ensaio" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Ensaio
                    </button>
                    <button data-filter="Áudio" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Áudio
                    </button>
                    <button data-filter="Perfil" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Perfil
                    </button>
                    <button data-filter="Espaço" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">
                        Espaço
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid of Items or Empty State -->
        <?php if (!empty($vitrineItems)): ?>
            <div id="vitrine-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($vitrineItems as $item): ?>
                    <?php render_project_card($item); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-xs space-y-4 max-w-lg mx-auto my-8">
                <div class="w-16 h-16 rounded-3xl bg-amber-50 text-[#FF8A00] flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="grid" class="w-8 h-8"></i>
                </div>
                <h3 class="font-heading font-black text-xl text-slate-900">Nenhuma Publicação na Vitrine Ainda</h3>
                <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                    A Vitrine Cultural está pronta no banco de dados. Os materiais, fotos, vídeos, podcasts e ensaios publicados pelos administradores aparecerão aqui.
                </p>
                <?php if (is_admin()): ?>
                    <div class="pt-2">
                        <a href="dashboard.php?aba=vitrine&novo=1" class="px-6 py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md inline-flex items-center gap-2 transition-all hover:scale-105">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Publicar Primeiro Post na Vitrine</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
function abrirDetalhesPost(item) {
    if (!item) return;
    
    document.getElementById('post-detail-titulo').innerText = item.titulo || 'Publicação';
    document.getElementById('post-detail-tipo').innerText = item.tipo || 'Foto';
    document.getElementById('post-detail-bairro').innerText = item.bairro || 'Sobradinho';
    document.getElementById('post-detail-autor').innerText = item.autorNome || 'FotoCidade DF';
    document.getElementById('post-detail-role').innerText = item.autorRole || 'Administrador';
    document.getElementById('post-detail-data').innerText = item.dataPublicacao || '';
    document.getElementById('post-detail-avatar').src = item.autorAvatar || 'assets/images/avatar-default.jpg';
    
    const elSub = document.getElementById('post-detail-subtitulo');
    if (elSub) {
        if (item.subtitulo) {
            elSub.innerText = item.subtitulo;
            elSub.classList.remove('hidden');
        } else {
            elSub.classList.add('hidden');
        }
    }

    const elConteudo = document.getElementById('post-detail-conteudo');
    if (elConteudo) {
        const textoCompleto = item.conteudo ? item.conteudo : item.descricao;
        elConteudo.innerText = textoCompleto || '';
    }

    const imgEl = document.getElementById('post-detail-img');
    const videoBox = document.getElementById('post-detail-video-box');
    const audioBox = document.getElementById('post-detail-audio-box');
    const audioPlayer = document.getElementById('post-detail-audio-player');
    const videoEmbed = document.getElementById('post-detail-video-embed');
    const delId = document.getElementById('post-detail-delete-id');

    if (delId) delId.value = item.id;

    // Imagem principal
    if (imgEl) {
        imgEl.src = item.mediaUrl || item.imagem || 'assets/images/oficina-olhar-fercal.jpg';
    }

    // Vídeo Embed (YouTube, Vimeo, MP4)
    if (videoEmbed) {
        if (item.videoUrl && item.videoUrl.trim() !== '') {
            let vUrl = item.videoUrl.trim();
            if (vUrl.includes('youtube.com/watch?v=')) {
                const vidId = vUrl.split('watch?v=')[1].split('&')[0];
                videoEmbed.innerHTML = `<iframe class="w-full h-full" src="https://www.youtube.com/embed/${vidId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            } else if (vUrl.includes('youtu.be/')) {
                const vidId = vUrl.split('youtu.be/')[1].split('?')[0];
                videoEmbed.innerHTML = `<iframe class="w-full h-full" src="https://www.youtube.com/embed/${vidId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            } else if (vUrl.endsWith('.mp4') || vUrl.includes('public/vitrine/')) {
                videoEmbed.innerHTML = `<video controls class="w-full h-full rounded-xl"><source src="${vUrl}" type="video/mp4"></video>`;
            } else {
                videoEmbed.innerHTML = `<iframe class="w-full h-full" src="${vUrl}" frameborder="0" allowfullscreen></iframe>`;
            }
            videoEmbed.classList.remove('hidden');
        } else {
            videoEmbed.innerHTML = '';
            videoEmbed.classList.add('hidden');
        }
    }

    // Áudio Player (MP3 ou Link)
    if (audioBox && audioPlayer) {
        if (item.audioUrl && item.audioUrl.trim() !== '') {
            let aUrl = item.audioUrl.trim();
            if (aUrl.endsWith('.mp3') || aUrl.endsWith('.wav') || aUrl.includes('public/vitrine/')) {
                audioPlayer.innerHTML = `<audio controls class="w-full"><source src="${aUrl}"></audio>`;
            } else if (aUrl.includes('spotify.com')) {
                audioPlayer.innerHTML = `<iframe src="${aUrl.replace('spotify.com/', 'spotify.com/embed/')}" width="100%" height="80" frameBorder="0" allowtransparency="true" allow="encrypted-media"></iframe>`;
            } else if (aUrl.includes('soundcloud.com')) {
                audioPlayer.innerHTML = `<iframe width="100%" height="120" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=${encodeURIComponent(aUrl)}&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>`;
            } else {
                audioPlayer.innerHTML = `<a href="${aUrl}" target="_blank" class="text-xs font-bold text-[#0D5BA8] hover:underline flex items-center gap-1.5"><i data-lucide="external-link" class="w-3.5 h-3.5"></i> Ouvir Áudio Externo</a>`;
            }
            audioBox.classList.remove('hidden');
        } else {
            audioPlayer.innerHTML = '';
            audioBox.classList.add('hidden');
        }
    }

    // Tags
    const tagsBox = document.getElementById('post-detail-tags-box');
    if (tagsBox) {
        tagsBox.innerHTML = '';
        if (item.tags && Array.isArray(item.tags)) {
            item.tags.forEach(t => {
                const sp = document.createElement('span');
                sp.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200';
                sp.textContent = '#' + t;
                tagsBox.appendChild(sp);
            });
        }
    }

    openModal('modal-post-detail');
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}
</script>

<?php
require_once ROOT_PATH . '/components/vitrine/post_detail_modal.php';
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>

