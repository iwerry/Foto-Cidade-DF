import React, { useEffect, useRef } from 'react';
import L from 'leaflet';
import { MapPoint, CategoryType } from '../../types';
import { getCategoryColor } from '../../utils/formatters';

interface MapContainerProps {
  points: MapPoint[];
  selectedPoint: MapPoint | null;
  onSelectPoint: (point: MapPoint) => void;
  center?: [number, number];
  zoom?: number;
}

export const MapContainer: React.FC<MapContainerProps> = ({
  points,
  selectedPoint,
  onSelectPoint,
  center = [-15.6350, -47.8150], // Center between Sobradinho & Fercal
  zoom = 12
}) => {
  const mapRef = useRef<HTMLDivElement>(null);
  const leafletMapRef = useRef<L.Map | null>(null);
  const markersLayerRef = useRef<L.LayerGroup | null>(null);

  // Initialize Map
  useEffect(() => {
    if (!mapRef.current) return;

    if (!leafletMapRef.current) {
      const centerCoords: L.LatLngTuple = [center[0], center[1]];
      const map = L.map(mapRef.current, {
        center: centerCoords,
        zoom: zoom,
        zoomControl: false,
        attributionControl: false
      });

      // OpenStreetMap Tiles
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      // Custom Zoom Control at bottom right
      L.control.zoom({ position: 'bottomright' }).addTo(map);

      leafletMapRef.current = map;
      markersLayerRef.current = L.layerGroup().addTo(map);
    }

    return () => {
      // Keep map alive across standard renders, cleanup if container unmounts
    };
  }, []);

  // Update Markers whenever points or selectedPoint changes
  useEffect(() => {
    const map = leafletMapRef.current;
    const markersLayer = markersLayerRef.current;
    if (!map || !markersLayer) return;

    markersLayer.clearLayers();

    points.forEach((point) => {
      const isSelected = selectedPoint?.id === point.id;
      const colorScheme = getCategoryColor(point.categoria);

      // Create Custom SVG Icon Pin
      const iconHtml = `
        <div class="relative group cursor-pointer" style="transform: translate(-50%, -100%);">
          <div class="w-9 h-9 rounded-full ${isSelected ? 'ring-4 ring-[#FF8A00] scale-125' : 'ring-2 ring-white shadow-md'} flex items-center justify-center transition-all duration-200" style="background-color: ${colorScheme.markerBg};">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
              ${
                point.categoria === 'Artista'
                  ? '<path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>'
                  : point.categoria === 'Espaço'
                  ? '<path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/>'
                  : point.categoria === 'Informação'
                  ? '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>'
                  : '<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>'
              }
            </svg>
          </div>
          <div class="w-2.5 h-2.5 bg-slate-800 rotate-45 mx-auto -mt-1 shadow-xs" style="background-color: ${colorScheme.markerBg};"></div>
        </div>
      `;

      const customIcon = L.divIcon({
        html: iconHtml,
        className: 'custom-leaflet-marker',
        iconSize: [36, 42],
        iconAnchor: [18, 42],
        popupAnchor: [0, -42]
      });

      const marker = L.marker([point.lat, point.lng], { icon: customIcon });

      // Interactive popup preview
      const popupContent = document.createElement('div');
      popupContent.className = 'p-1 max-w-xs';
      popupContent.innerHTML = `
        <div class="space-y-1.5">
          ${point.foto ? `<img src="${point.foto}" alt="${point.nome}" class="w-full h-24 object-cover rounded-lg mb-1" />` : ''}
          <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded ${colorScheme.badge}">
              ${point.categoria}
            </span>
            <span class="text-[10px] text-slate-500 font-medium">${point.bairro}</span>
          </div>
          <h4 class="font-bold text-sm text-slate-900 leading-tight">${point.nome}</h4>
          <p class="text-xs text-slate-600 line-clamp-2">${point.descricao}</p>
          <button id="btn-popup-${point.id}" class="mt-2 w-full py-1.5 bg-[#0D5BA8] hover:bg-[#00A7B5] text-white text-xs font-bold rounded-md text-center transition-colors shadow-2xs">
            Ver Ficha Completa
          </button>
        </div>
      `;

      popupContent.querySelector(`#btn-popup-${point.id}`)?.addEventListener('click', (e) => {
        e.stopPropagation();
        onSelectPoint(point);
      });

      marker.bindPopup(popupContent);

      marker.on('click', () => {
        onSelectPoint(point);
      });

      markersLayer.addLayer(marker);
    });
  }, [points, selectedPoint, onSelectPoint]);

  // Pan to selected point if it changes
  useEffect(() => {
    if (selectedPoint && leafletMapRef.current) {
      leafletMapRef.current.flyTo([selectedPoint.lat, selectedPoint.lng], 14, {
        duration: 1.2
      });
    }
  }, [selectedPoint]);

  return (
    <div className="w-full h-full relative rounded-2xl overflow-hidden shadow-inner border border-slate-200">
      <div ref={mapRef} className="w-full h-full min-h-[500px]" />
    </div>
  );
};
