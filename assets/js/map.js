/**
 * FotoCidade DF - Mapeamento Territorial em Leaflet
 * Gerencia a inicialização do mapa do DF, marcadores dinâmicos, modais e filtros.
 */

let mapInstance = null;
let mapMarkers = [];

function initCulturalMap(pointsData, containerId = 'map-container') {
  const container = document.getElementById(containerId);
  if (!container || typeof L === 'undefined') return;

  // Centro do DF (Brasília)
  const dfCenter = [-15.7975, -47.8919];
  const defaultZoom = 11;

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

function getCategoryColor(categoria) {
  switch (categoria) {
    case 'Patrimônio': return '#0D5BA8'; // Azul
    case 'Espaço Cultural': return '#FF8A00'; // Laranja
    case 'Coletivo': return '#00A7B5'; // Teal
    case 'Ponto de Memória': return '#8B5CF6'; // Roxo
    case 'Arte Urbana': return '#EC4899'; // Rosa
    case 'Feira Cultural': return '#10B981'; // Verde
    default: return '#0D5BA8';
  }
}

function renderMarkers(points) {
  // Limpa marcadores existentes
  mapMarkers.forEach(m => mapInstance.removeLayer(m));
  mapMarkers = [];

  points.forEach(point => {
    if (!point.latitude || !point.longitude) return;

    const catColor = getCategoryColor(point.categoria);

    // Ícone SVG customizado para o pino
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

    const marker = L.marker([point.latitude, point.longitude], { icon: customIcon }).addTo(mapInstance);

    // Conteúdo do Popup
    const popupContent = `
      <div style="width: 220px; font-family: 'Plus Jakarta Sans', sans-serif;">
        <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; background: #E0F2FE; color: #0369A1; padding: 2px 8px; border-radius: 999px;">${point.categoria}</span>
        <h4 style="margin: 6px 0 2px 0; font-size: 14px; font-weight: 700; color: #0F172A;">${point.nome}</h4>
        <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748B;">${point.regiaoAdministrativa}</p>
        <p style="margin: 0 0 10px 0; font-size: 11px; color: #334155; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${point.descricao}</p>
        <button onclick="openPointDetails('${point.id}')" style="width: 100%; background: #0D5BA8; color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.2s;">Ver Detalhes</button>
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
      document.getElementById('detail-point-nome').textContent = point.nome;
      document.getElementById('detail-point-ra').textContent = point.regiaoAdministrativa;
      document.getElementById('detail-point-categoria').textContent = point.categoria;
      document.getElementById('detail-point-descricao').textContent = point.descricao;
      document.getElementById('detail-point-endereco').textContent = point.endereco || point.regiaoAdministrativa;
      document.getElementById('detail-point-responsavel').textContent = point.responsavel || 'Comunidade Local';
      if (point.imagemUrl) {
        document.getElementById('detail-point-img').src = point.imagemUrl;
      }
      openModal('modal-point-detail');
    }
  }
}
