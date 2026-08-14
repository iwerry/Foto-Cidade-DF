import React, { useState, useMemo } from 'react';
import { VitrineItem } from '../types';
import { ProjectCard } from '../components/cards/ProjectCard';
import { Search, Filter, Grid, Tag, MapPin, Sparkles, X, Heart, Share2, Play, User } from 'lucide-react';
import { getTipoVitrineColor } from '../utils/formatters';

interface VitrineProps {
  items: VitrineItem[];
  onLike: (id: string | number) => void;
  selectedItem: VitrineItem | null;
  onSelectItem: (item: VitrineItem | null) => void;
}

export const Vitrine: React.FC<VitrineProps> = ({
  items,
  onLike,
  selectedItem,
  onSelectItem
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedTag, setSelectedTag] = useState<string>('ALL');
  const [selectedBairro, setSelectedBairro] = useState<string>('ALL');

  const tagsList = ['ALL', 'Foto', 'Vídeo', 'Perfil', 'Espaço'];
  const bairrosList = ['ALL', 'Sobradinho I', 'Sobradinho II', 'Fercal', 'Grande Colorado'];

  const filteredItems = useMemo(() => {
    return items.filter(item => {
      const matchSearch =
        item.titulo.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.descricao.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.autorNome.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.tags.some(t => t.toLowerCase().includes(searchTerm.toLowerCase()));

      const matchTag = selectedTag === 'ALL' || item.tipo === selectedTag;
      const matchBairro = selectedBairro === 'ALL' || item.bairro.includes(selectedBairro);

      return matchSearch && matchTag && matchBairro;
    });
  }, [items, searchTerm, selectedTag, selectedBairro]);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-in fade-in duration-200 space-y-8">
      
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
          <div className="flex items-center gap-2 text-xs font-bold text-[#0D5BA8] uppercase tracking-wider mb-1">
            <Sparkles className="w-4 h-4 text-[#FF8A00]" />
            <span>Galeria Aberta da Comunidade</span>
          </div>
          <h1 className="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
            Vitrine do Território: O que nossa rede está revelando.
          </h1>
          <p className="text-sm text-slate-600 mt-1 max-w-2xl">
            Produções autorais dos alunos, fotografias documentais, histórias de mestres e memórias vivas de Sobradinho e Fercal.
          </p>
        </div>

        <div className="text-xs font-semibold text-slate-500">
          Mostrando <strong className="text-slate-900">{filteredItems.length}</strong> produções
        </div>
      </div>

      {/* Filter and Search Bar (matching SitePreview layout) */}
      <div className="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
          {/* Search Box */}
          <div className="md:col-span-6 relative">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Buscar por fotos, relatos, autor, instrumentos..."
              className="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
            />
            {searchTerm && (
              <button
                onClick={() => setSearchTerm('')}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"
              >
                ✕
              </button>
            )}
          </div>

          {/* Type Tags Selector */}
          <div className="md:col-span-6 flex items-center gap-1.5 flex-wrap">
            <span className="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1">
              <Tag className="w-3.5 h-3.5 text-[#00A7B5]" /> Tag:
            </span>
            {tagsList.map(tag => {
              const isSelected = selectedTag === tag;
              return (
                <button
                  key={tag}
                  onClick={() => setSelectedTag(tag)}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all ${
                    isSelected
                      ? 'bg-[#0D5BA8] text-white shadow-xs'
                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                  }`}
                >
                  {tag === 'ALL' ? 'Todos' : tag}
                </button>
              );
            })}
          </div>
        </div>

        {/* Bairro Selector Row */}
        <div className="flex items-center gap-2 pt-3 border-t border-slate-100 overflow-x-auto no-scrollbar">
          <span className="text-xs font-bold text-slate-500 flex items-center gap-1 shrink-0">
            <MapPin className="w-3.5 h-3.5 text-[#FF8A00]" /> Local:
          </span>
          {bairrosList.map(bairro => {
            const isSelected = selectedBairro === bairro;
            return (
              <button
                key={bairro}
                onClick={() => setSelectedBairro(bairro)}
                className={`px-3 py-1 rounded-md text-xs font-medium whitespace-nowrap transition-colors ${
                  isSelected
                    ? 'bg-[#00A7B5] text-white font-bold'
                    : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'
                }`}
              >
                {bairro === 'ALL' ? 'Todos os Bairros' : bairro}
              </button>
            );
          })}
        </div>
      </div>

      {/* Grid of Items */}
      {filteredItems.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-3">
          <p className="text-sm font-bold text-slate-700">Nenhum resultado encontrado com os filtros atuais.</p>
          <button
            onClick={() => {
              setSearchTerm('');
              setSelectedTag('ALL');
              setSelectedBairro('ALL');
            }}
            className="text-xs text-[#0D5BA8] underline font-bold"
          >
            Limpar todos os filtros
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredItems.map(item => (
            <ProjectCard
              key={item.id}
              item={item}
              onLike={onLike}
              onSelect={onSelectItem}
            />
          ))}
        </div>
      )}

      {/* Modal Detail for Item */}
      {selectedItem && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
            <div className="relative h-72 sm:h-96 bg-black shrink-0">
              <img
                src={selectedItem.mediaUrl}
                alt={selectedItem.titulo}
                className="w-full h-full object-cover"
              />
              <button
                onClick={() => onSelectItem(null)}
                className="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center hover:bg-black/90"
              >
                <X className="w-4 h-4" />
              </button>
              <div className="absolute bottom-4 left-4">
                <span className={`px-3 py-1 rounded-md text-xs font-bold ${getTipoVitrineColor(selectedItem.tipo)}`}>
                  {selectedItem.tipo}
                </span>
              </div>
            </div>

            <div className="p-6 overflow-y-auto space-y-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2 text-xs text-slate-500">
                  <MapPin className="w-3.5 h-3.5 text-[#00A7B5]" />
                  <span>{selectedItem.bairro}</span>
                  <span>•</span>
                  <span>{selectedItem.dataPublicacao}</span>
                </div>

                <button
                  onClick={() => onLike(selectedItem.id)}
                  className={`flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border ${
                    selectedItem.curtido
                      ? 'text-rose-600 bg-rose-50 border-rose-200'
                      : 'text-slate-600 bg-slate-50 border-slate-200'
                  }`}
                >
                  <Heart className={`w-4 h-4 ${selectedItem.curtido ? 'fill-rose-600' : ''}`} />
                  <span>{selectedItem.likes} Curtidas</span>
                </button>
              </div>

              <div>
                <h3 className="font-heading font-bold text-2xl text-slate-900">
                  {selectedItem.titulo}
                </h3>
                <p className="text-sm font-medium text-slate-600 mt-0.5">
                  {selectedItem.subtitulo}
                </p>
              </div>

              <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                {selectedItem.descricao}
              </p>

              {/* Author box */}
              <div className="bg-slate-50 rounded-xl p-4 flex items-center gap-3 border border-slate-100">
                <img
                  src={selectedItem.autorAvatar}
                  alt={selectedItem.autorNome}
                  className="w-11 h-11 rounded-full object-cover ring-2 ring-[#00A7B5]"
                />
                <div>
                  <h4 className="font-heading font-bold text-sm text-slate-900">{selectedItem.autorNome}</h4>
                  <p className="text-xs text-slate-500">{selectedItem.autorRole || 'Agente Cultural FotoCidade'}</p>
                </div>
              </div>
            </div>

            <div className="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
              <button
                onClick={() => onSelectItem(null)}
                className="px-6 py-2 bg-[#0D5BA8] text-white text-xs font-bold rounded-xl"
              >
                Fechar
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};
