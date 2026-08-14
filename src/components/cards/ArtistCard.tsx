import React from 'react';
import { Artista } from '../../types';
import { MapPin, Phone, Instagram, ExternalLink, Sparkles } from 'lucide-react';

interface ArtistCardProps {
  artista: Artista;
  onSelectOnMap?: (artista: Artista) => void;
}

export const ArtistCard: React.FC<ArtistCardProps> = ({ artista, onSelectOnMap }) => {
  return (
    <div className="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col group">
      <div className="relative h-48 overflow-hidden bg-slate-100">
        <img
          src={artista.foto}
          alt={artista.nome}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          loading="lazy"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        
        {artista.destaque && (
          <span className="absolute top-3 left-3 bg-[#FF8A00] text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
            <Sparkles className="w-3 h-3" /> Mestre / Destaque
          </span>
        )}

        <div className="absolute bottom-3 left-3 right-3 text-white">
          <span className="text-[11px] font-bold uppercase tracking-wider text-[#00A7B5] bg-white/90 px-2 py-0.5 rounded-md inline-block mb-1">
            {artista.linguagem}
          </span>
          <h3 className="font-heading font-bold text-lg leading-snug drop-shadow-xs">
            {artista.nome}
          </h3>
        </div>
      </div>

      <div className="p-4 flex-1 flex flex-col justify-between space-y-3">
        <div className="space-y-2">
          <div className="flex items-center text-xs text-slate-500 gap-1 font-medium">
            <MapPin className="w-3.5 h-3.5 text-[#0D5BA8] shrink-0" />
            <span>{artista.bairro}</span>
          </div>
          
          <p className="text-xs text-slate-600 line-clamp-3 leading-relaxed">
            {artista.bio}
          </p>

          {artista.projetos && artista.projetos.length > 0 && (
            <div className="flex flex-wrap gap-1 pt-1">
              {artista.projetos.map((proj, idx) => (
                <span key={idx} className="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-medium">
                  {proj}
                </span>
              ))}
            </div>
          )}
        </div>

        <div className="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
          <div className="flex items-center gap-2">
            {artista.instagram && (
              <a
                href={`https://instagram.com/${artista.instagram.replace('@', '')}`}
                target="_blank"
                rel="noreferrer"
                className="p-1.5 text-slate-500 hover:text-[#00A7B5] hover:bg-slate-50 rounded-md transition-colors"
                title={artista.instagram}
              >
                <Instagram className="w-4 h-4" />
              </a>
            )}
            {artista.telefone && (
              <span className="text-[11px] text-slate-500 font-mono flex items-center gap-1">
                <Phone className="w-3 h-3 text-emerald-600" />
                {artista.telefone}
              </span>
            )}
          </div>

          {onSelectOnMap && (
            <button
              onClick={() => onSelectOnMap(artista)}
              className="text-xs font-bold text-[#0D5BA8] hover:text-[#00A7B5] flex items-center gap-1 px-2.5 py-1 rounded-md hover:bg-blue-50 transition-colors"
            >
              <MapPin className="w-3.5 h-3.5" />
              Ver no Mapa
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
