import React from 'react';
import { CategoryType } from '../../types';
import { Search, Plus, Filter, MapPin, Layers } from 'lucide-react';

interface MapControlsProps {
  searchTerm: string;
  setSearchTerm: (s: string) => void;
  selectedCategory: string;
  setSelectedCategory: (cat: string) => void;
  selectedBairro: string;
  setSelectedBairro: (b: string) => void;
  bairros: string[];
  onOpenAddPoint: () => void;
  totalCount: number;
}

export const MapControls: React.FC<MapControlsProps> = ({
  searchTerm,
  setSearchTerm,
  selectedCategory,
  setSelectedCategory,
  selectedBairro,
  setSelectedBairro,
  bairros,
  onOpenAddPoint,
  totalCount
}) => {
  const categories = [
    { id: 'ALL', label: 'Todos', color: 'bg-slate-800 text-white' },
    { id: 'Artista', label: 'Artista', color: 'bg-[#FF8A00] text-white' },
    { id: 'Espaço', label: 'Espaço Cultural', color: 'bg-[#0D5BA8] text-white' },
    { id: 'Informação', label: 'Ponto de Informação', color: 'bg-[#00A7B5] text-white' },
    { id: 'Liderança', label: 'Liderança', color: 'bg-[#FFC107] text-slate-900 font-semibold' },
  ];

  return (
    <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-sm space-y-4">
      {/* Top Search & Add Row */}
      <div className="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        {/* Search Bar */}
        <div className="relative flex-1">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="Buscar por nome, artista, espaço, música, memórias..."
            className="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8] focus:border-transparent transition-all"
          />
          {searchTerm && (
            <button
              onClick={() => setSearchTerm('')}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-slate-600"
            >
              ✕
            </button>
          )}
        </div>

        {/* Bairro Filter */}
        <div className="flex items-center gap-2">
          <div className="relative min-w-[140px]">
            <MapPin className="w-3.5 h-3.5 text-[#00A7B5] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <select
              value={selectedBairro}
              onChange={(e) => setSelectedBairro(e.target.value)}
              className="w-full pl-8 pr-8 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00A7B5] appearance-none cursor-pointer"
            >
              <option value="ALL">Todos os Bairros</option>
              {bairros.map((b) => (
                <option key={b} value={b}>
                  {b}
                </option>
              ))}
            </select>
          </div>

          {/* Add Point CTA */}
          <button
            id="btn-add-map-point"
            onClick={onOpenAddPoint}
            className="px-4 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-lg flex items-center gap-1.5 shadow-xs transition-colors shrink-0"
          >
            <Plus className="w-4 h-4" />
            <span className="hidden sm:inline">Adicionar Novo Ponto</span>
            <span className="sm:hidden">Novo Ponto</span>
          </button>
        </div>
      </div>

      {/* Category Pills Row */}
      <div className="flex items-center justify-between flex-wrap gap-2 pt-1 border-t border-slate-100">
        <div className="flex items-center gap-1.5 flex-wrap">
          <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1 hidden sm:inline">
            Filtrar:
          </span>
          {categories.map((cat) => {
            const isSelected = selectedCategory === cat.id;
            return (
              <button
                key={cat.id}
                onClick={() => setSelectedCategory(cat.id)}
                className={`px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 ${
                  isSelected
                    ? `${cat.color} shadow-xs scale-105 ring-2 ring-offset-1 ring-slate-400`
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                }`}
              >
                {cat.label}
              </button>
            );
          })}
        </div>

        <span className="text-xs text-slate-400 font-medium ml-auto">
          <strong className="text-slate-700">{totalCount}</strong> pontos no território
        </span>
      </div>
    </div>
  );
};
