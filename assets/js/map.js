/**
 * FotoCidade DF - Mapeamento Territorial em Leaflet
 * Gerencia a inicialização do mapa do DF, marcadores dinâmicos, modais e filtros.
 */

let mapInstance = null;
let mapMarkers = [];

function initCulturalMap(pointsData, containerId = 'map-container') {
  const container = document.getElementById(containerId);
  if (!container || typeof L === 'undefined') return;

  // Centro do DF (Brasília / Sobradinho)
  const dfCenter = [-15.6534, -47.7891];
  const defaultZoom = 12;

  // Se já existir mapa, remove para reinicializar limpo
  if (mapInstance) {
    mapInstance.remove();
    mapMarkers = [];
  }

  mapInstance = L.map(containerId, {
    zoomControl: false
  }).setView(dfCenter, defaultZoom);

  // Adiciona controles no canto superior direito
  L.control.zoom({ position: 'topright' }).addTo(mapInstance);

  // Tile layer do OpenStreetMap com estilo limpo
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(mapInstance);

  // Renderiza os pontos culturais
  renderMarkers(pointsData);
}

function getCategoryInfo(categoria) {
  switch (categoria) {
    case 'Patrimônio': return { pin: '#0D5BA8', bg: '#EFF6FF', text: '#1D4ED8' };
    case 'Espaço Cultural': return { pin: '#FF8A00', bg: '#FFF7ED', text: '#C2410C' };
    case 'Coletivo': return { pin: '#00A7B5', bg: '#F0FDFA', text: '#0F766E' };
    case 'Ponto de Memória': return { pin: '#8B5CF6', bg: '#FAF5FF', text: '#7E22CE' };
    case 'Feira Cultural': return { pin: '#10B981', bg: '#ECFDF5', text: '#047857' };
    case 'Órgão Público': return { pin: '#2563EB', bg: '#EFF6FF', text: '#1D4ED8' };
    case 'Hospital': return { pin: '#EF4444', bg: '#FEF2F2', text: '#B91C1C' };
    case 'Escola': return { pin: '#D97706', bg: '#FFFBEB', text: '#B45309' };
    case 'Empresa': return { pin: '#6366F1', bg: '#EEF2FF', text: '#4338CA' };
    case 'ONG': return { pin: '#EC4899', bg: '#FDF2F8', text: '#BE185D' };
    case 'Instituto': return { pin: '#059669', bg: '#ECFDF5', text: '#065F46' };
    case 'Associação': return { pin: '#0284C7', bg: '#F0F9FF', text: '#0369A1' };
    case 'Turismo': return { pin: '#EAB308', bg: '#FEFCE8', text: '#A16207' };
    default: return { pin: '#0D5BA8', bg: '#EFF6FF', text: '#1D4ED8' };
  }
}

function getCategoryColor(categoria) {
  return getCategoryInfo(categoria).pin;
}

function renderMarkers(points) {
  if (!mapInstance) return;

  // Limpa marcadores existentes
  mapMarkers.forEach(m => mapInstance.removeLayer(m));
  mapMarkers = [];

  points.forEach(point => {
    const lat = point.latitude || point.lat;
    const lng = point.longitude || point.lng;
    if (!lat || !lng) return;

    const catInfo = getCategoryInfo(point.categoria);
    const catColor = catInfo.pin;
    const regiao = point.regiaoAdministrativa || point.bairro || point.cidade || 'DF';

    // Ícone SVG customizado para o pino com cor dinâmica da categoria
    const customIcon = L.divIcon({
      className: 'custom-pin-container',
      html: `
        <div style="background-color: ${catColor}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
      `,
      iconSize: [32, 32],
      iconAnchor: [16, 16]
    });

    const marker = L.marker([lat, lng], { icon: customIcon }).addTo(mapInstance);

    // Conteúdo do Popup
    const fotoPopup = point.foto || point.imagem || point.logo || '';
    const imgPopupHtml = fotoPopup ? `<img src="${fotoPopup}" onerror="this.style.display='none'" style="width: 100%; height: 100px; object-fit: cover; border-radius: 12px; margin-bottom: 8px;" />` : '';

    const popupContent = `
      <div style="width: 240px; font-family: 'Plus Jakarta Sans', sans-serif;">
        ${imgPopupHtml}
        <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; background: ${catInfo.bg}; color: ${catInfo.text}; padding: 2px 8px; border-radius: 999px; border: 1px solid ${catInfo.pin}30;">${point.categoria || 'Ponto Cultural'}</span>
        <h4 style="margin: 6px 0 2px 0; font-size: 14px; font-weight: 700; color: #0F172A;">${point.nome}</h4>
        <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748B;">📍 ${regiao}</p>
        <p style="margin: 0 0 10px 0; font-size: 11px; color: #334155; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${point.descricao || ''}</p>
        <button onclick="openPointDetails('${point.id}')" style="width: 100%; background: ${catColor}; color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.2s;">Ver Detalhes</button>
      </div>
    `;

    marker.bindPopup(popupContent);
    mapMarkers.push(marker);
  });
}

function openPointDetails(pointId) {
  const modal = document.getElementById('modal-point-detail');
  if (modal && window.allMapPoints) {
    const point = window.allMapPoints.find(p => String(p.id) === String(pointId));
    if (point) {
      const reg = point.regiaoAdministrativa || point.bairro || point.cidade || 'DF';
      const catInfo = getCategoryInfo(point.categoria);
      const elNome = document.getElementById('detail-point-nome');
      const elRa = document.getElementById('detail-point-ra');
      const elCat = document.getElementById('detail-point-categoria');
      const elDesc = document.getElementById('detail-point-descricao');
      const elEnd = document.getElementById('detail-point-endereco');
      const elResp = document.getElementById('detail-point-responsavel');
      const elImg = document.getElementById('detail-point-img');
      const elDelId = document.getElementById('detail-point-delete-id');

      if (elNome) elNome.textContent = point.nome;
      if (elRa) elRa.textContent = reg;
      if (elCat) {
        elCat.textContent = point.categoria;
        elCat.style.backgroundColor = catInfo.pin;
      }
      if (elDesc) elDesc.textContent = point.descricao;
      if (elEnd) elEnd.textContent = point.endereco || reg;
      if (elResp) elResp.textContent = point.responsavel || point.autorRegistro || 'FotoCidade DF';
      if (elDelId) elDelId.value = point.id;
      
      const fotoUrl = point.foto || point.imagem || point.logo || point.imagemUrl || 'assets/images/oficina-olhar-fercal.jpg';
      if (elImg) {
        elImg.src = fotoUrl;
        elImg.onerror = function() {
          this.src = 'assets/images/oficina-olhar-fercal.jpg';
        };
      }
      openModal('modal-point-detail');
      if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
      }
    }
  }
}
