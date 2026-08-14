import React from 'react';
import { VitrineItem } from '../../types';
import { getTipoVitrineColor } from '../../utils/formatters';
import { Heart, Share2, MessageCircle, MapPin, Play, User, ExternalLink } from 'lucide-react';

interface ProjectCardProps {
  item: VitrineItem;
  onLike: (id: string | number) => void;
  onSelect?: (item: VitrineItem) => void;
  onShare?: (item: VitrineItem) => void;
}

export const ProjectCard: React.FC<ProjectCardProps> = ({
  item,
  onLike,
  onSelect,
  onShare
}) => {
  return (
    <div className="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col group">
      {/* Media Box */}
      <div 
        className="relative h-52 overflow-hidden bg-slate-900 cursor-pointer"
        onClick={() => onSelect && onSelect(item)}
      >
        <img
          src={item.thumbnailUrl || item.mediaUrl}
          alt={item.titulo}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-95"
          loading="lazy"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>

        {/* Type Tag Badge */}
        <span className={`absolute top-3 left-3 px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs flex items-center gap-1 ${getTipoVitrineColor(item.tipo)}`}>
          {item.tipo === 'Vídeo' && <Play className="w-3 h-3 fill-current" />}
          {item.tipo}
        </span>

        {/* Neighborhood Badge */}
        <span className="absolute top-3 right-3 bg-black/60 backdrop-blur-md text-white text-[11px] font-medium px-2.5 py-0.5 rounded-full flex items-center gap-1">
          <MapPin className="w-3 h-3 text-[#00A7B5]" />
          {item.bairro}
        </span>

        {/* Video Overlay Indicator */}
        {item.tipo === 'Vídeo' && (
          <div className="absolute inset-0 flex items-center justify-center">
            <div className="w-12 h-12 rounded-full bg-[#FF8A00]/90 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
              <Play className="w-6 h-6 fill-current translate-x-0.5" />
            </div>
          </div>
        )}
      </div>

      {/* Content */}
      <div className="p-4 flex-1 flex flex-col justify-between space-y-3">
        <div className="space-y-1.5">
          <h3 
            onClick={() => onSelect && onSelect(item)}
            className="font-heading font-bold text-base text-slate-800 hover:text-[#0D5BA8] cursor-pointer transition-colors leading-snug line-clamp-2"
          >
            {item.titulo}
          </h3>
          <p className="text-xs text-slate-500 font-medium line-clamp-1">
            {item.subtitulo}
          </p>
          <p className="text-xs text-slate-600 line-clamp-2 leading-relaxed pt-1">
            {item.descricao}
          </p>
        </div>

        {/* Tags */}
        <div className="flex flex-wrap gap-1">
          {item.tags.map((tag, idx) => (
            <span key={idx} className="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-medium">
              #{tag}
            </span>
          ))}
        </div>

        {/* Author info & Actions footer */}
        <div className="pt-3 border-t border-slate-100 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <img
              src={item.autorAvatar}
              alt={item.autorNome}
              className="w-7 h-7 rounded-full object-cover ring-1 ring-[#00A7B5]"
            />
            <div className="text-left">
              <p className="text-xs font-bold text-slate-800 leading-tight">{item.autorNome}</p>
              <p className="text-[10px] text-slate-400 leading-tight">{item.dataPublicacao}</p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            {/* Like */}
            <button
              onClick={() => onLike(item.id)}
              className={`flex items-center gap-1 text-xs px-2 py-1 rounded-md transition-all ${
                item.curtido 
                  ? 'text-rose-600 bg-rose-50 font-bold' 
                  : 'text-slate-500 hover:text-rose-600 hover:bg-slate-50'
              }`}
              title="Curtir publicação"
            >
              <Heart className={`w-3.5 h-3.5 ${item.curtido ? 'fill-rose-600 text-rose-600' : ''}`} />
              <span>{item.likes}</span>
            </button>

            {/* Share */}
            <button
              onClick={() => onShare && onShare(item)}
              className="p-1.5 text-slate-400 hover:text-[#0D5BA8] hover:bg-slate-50 rounded-md transition-colors"
              title="Compartilhar"
            >
              <Share2 className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
