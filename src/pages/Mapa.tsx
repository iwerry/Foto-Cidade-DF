import React, { useState, useMemo } from 'react';
import { MapPoint, CategoryType } from '../types';
import { MapContainer } from '../components/map/MapContainer';
import { MapControls } from '../components/map/MapControls';
import { PointDetailModal } from '../components/map/PointDetailModal';
import { AddPointModal } from '../components/map/AddPointModal';
import { MapPin, Sparkles, Navigation, Layers, ChevronRight, Phone } from 'lucide-react';
import { getCategoryColor } from '../utils/formatters';

interface MapaProps {
  points: MapPoint[];
  onSavePoint: (point: Omit<MapPoint, 'id'>) => void;
}

export const Mapa: React.FC<MapaProps> = ({ points, onSavePoint }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('ALL');
  const [selectedBairro, setSelectedBairro] = useState<string>('ALL');
  const [selectedPoint, setSelectedPoint] = useState<MapPoint | null>(null);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [activeSideTab, setActiveSideTab] = useState<'pontos' | 'destaques'>('pontos');

  const bairrosList = useMemo(() => {
    const set = new Set<string>();
    points.forEach(p => set.add(p.bairro));
    return Array.from(set);
  }, [points]);

  const filteredPoints = useMemo(() => {
    return points.filter(p => {
      const matchSearch =
        p.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.descricao.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.endereco.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (p.tags && p.tags.some(t => t.toLowerCase().includes(searchTerm.toLowerCase())));

      const matchCat = selectedCategory === 'ALL' || p.categoria === selectedCategory;
      const matchBairro = selectedBairro === 'ALL' || p.bairro === selectedBairro;

      return matchSearch && matchCat && matchBairro;
    });
  }, [points, searchTerm, selectedCategory, selectedBairro]);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-in fade-in duration-200 space-y-6">
      
      {/* Title Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2 text-xs font-bold text-[#0D5BA8] uppercase tracking-wider mb-1">
            <Navigation className="w-4 h-4 text-[#00A7B5]" />
            <span>Georreferenciamento Colaborativo</span>
          </div>
          <h1 className="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
            Mapa Cultural do Território
          </h1>
          <p className="text-sm text-slate-600 mt-1">
            Mapeamento vivo de Sobradinho e Fercal com artistas, espaços de cultura e memórias da comunidade.
          </p>
        </div>

        {/* Quick Map Legend */}
        <div className="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 text-[11px] font-bold">
          <div className="flex items-center gap-1.5">
            <span className="w-3 h-3 rounded-full bg-[#FF8A00]"></span>
            <span>Artista</span>
          </div>
          <div className="flex items-center gap-1.5">
            <span className="w-3 h-3 rounded-full bg-[#0D5BA8]"></span>
            <span>Espaço Cultural</span>
          </div>
          <div className="flex items-center gap-1.5">
            <span className="w-3 h-3 rounded-full bg-[#00A7B5]"></span>
            <span>Informação</span>
          </div>
        </div>
      </div>

      {/* Map Controls */}
      <MapControls
        searchTerm={searchTerm}
        setSearchTerm={setSearchTerm}
        selectedCategory={selectedCategory}
        setSelectedCategory={setSelectedCategory}
        selectedBairro={selectedBairro}
        setSelectedBairro={setSelectedBairro}
        bairros={bairrosList}
        onOpenAddPoint={() => setIsAddModalOpen(true)}
        totalCount={filteredPoints.length}
      />

      {/* Interactive Map Layout (Side List + Full Map) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        {/* Left Side List of Mapped Points (lg:col-span-4) */}
        <div className="lg:col-span-4 flex flex-col h-[560px] bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
          
          <div className="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <button
                onClick={() => setActiveSideTab('pontos')}
                className={`px-3 py-1 text-xs font-bold rounded-lg transition-colors ${
                  activeSideTab === 'pontos'
                    ? 'bg-[#0D5BA8] text-white'
                    : 'text-slate-600 hover:bg-slate-200'
                }`}
              >
                Lista ({filteredPoints.length})
              </button>
              <button
                onClick={() => setActiveSideTab('destaques')}
                className={`px-3 py-1 text-xs font-bold rounded-lg transition-colors ${
                  activeSideTab === 'destaques'
                    ? 'bg-[#0D5BA8] text-white'
                    : 'text-slate-600 hover:bg-slate-200'
                }`}
              >
                Destaques
              </button>
            </div>
            
            <span className="text-[11px] text-slate-500 font-medium">
              Clique para focar no mapa
            </span>
          </div>

          <div className="flex-1 overflow-y-auto p-3 space-y-2.5">
            {filteredPoints.length === 0 ? (
              <div className="p-8 text-center text-slate-500 text-xs">
                Nenhum ponto encontrado com os filtros selecionados.
              </div>
            ) : (
              filteredPoints.map((point) => {
                const colorScheme = getCategoryColor(point.categoria);
                const isSelected = selectedPoint?.id === point.id;

                return (
                  <div
                    key={point.id}
                    onClick={() => setSelectedPoint(point)}
                    className={`p-3 rounded-xl border transition-all cursor-pointer flex gap-3 ${
                      isSelected
                        ? 'bg-blue-50/80 border-[#0D5BA8] shadow-xs'
                        : 'bg-white border-slate-100 hover:border-slate-300'
                    }`}
                  >
                    {point.foto ? (
                      <img
                        src={point.foto}
                        alt={point.nome}
                        className="w-16 h-16 rounded-lg object-cover shrink-0"
                      />
                    ) : (
                      <div className="w-16 h-16 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 text-slate-400">
                        <MapPin className="w-6 h-6" />
                      </div>
                    )}

                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-1.5 mb-1">
                        <span className={`text-[9px] font-bold uppercase px-1.5 py-0.5 rounded ${colorScheme.badge}`}>
                          {point.categoria}
                        </span>
                        <span className="text-[10px] text-slate-500 truncate">{point.bairro}</span>
                      </div>
                      <h4 className="font-heading font-bold text-xs text-slate-900 leading-snug line-clamp-1">
                        {point.nome}
                      </h4>
                      <p className="text-[11px] text-slate-500 line-clamp-2 mt-0.5">
                        {point.descricao}
                      </p>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>

        {/* Right Map Canvas Container (lg:col-span-8) */}
        <div className="lg:col-span-8 h-[560px] relative">
          <MapContainer
            points={filteredPoints}
            selectedPoint={selectedPoint}
            onSelectPoint={(p) => setSelectedPoint(p)}
          />
        </div>

      </div>

      {/* Point Detail Modal */}
      {selectedPoint && (
        <PointDetailModal
          point={selectedPoint}
          onClose={() => setSelectedPoint(null)}
        />
      )}

      {/* Add New Point Modal */}
      <AddPointModal
        isOpen={isAddModalOpen}
        onClose={() => setIsAddModalOpen(false)}
        onSave={onSavePoint}
      />

    </div>
  );
};
