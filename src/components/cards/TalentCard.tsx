import React from 'react';
import { TalentoLocal } from '../../types';
import { CheckCircle2, MessageSquare, Mail, MapPin, Sparkles } from 'lucide-react';

interface TalentCardProps {
  talento: TalentoLocal;
  onContact?: (talento: TalentoLocal) => void;
}

export const TalentCard: React.FC<TalentCardProps> = ({ talento, onContact }) => {
  return (
    <div className="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between">
      <div className="space-y-3">
        <div className="flex items-start gap-3.5">
          <div className="relative shrink-0">
            <img
              src={talento.avatar}
              alt={talento.nome}
              className="w-14 h-14 rounded-full object-cover ring-2 ring-[#0D5BA8]"
            />
            {talento.verificado && (
              <span className="absolute bottom-0 right-0 bg-white rounded-full p-0.5 shadow-xs" title="Agente Formado FotoCidade">
                <CheckCircle2 className="w-4 h-4 text-emerald-500 fill-emerald-100" />
              </span>
            )}
          </div>

          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <h3 className="font-heading font-bold text-slate-800 text-base truncate">
                {talento.nome}
              </h3>
            </div>
            <p className="text-xs font-semibold text-[#0D5BA8] truncate">{talento.funcao}</p>
            <div className="flex items-center gap-1 text-[11px] text-slate-500 mt-0.5">
              <MapPin className="w-3 h-3 text-[#00A7B5]" />
              <span>{talento.bairro}</span>
            </div>
          </div>
        </div>

        <p className="text-xs text-slate-600 leading-relaxed line-clamp-3">
          {talento.bio}
        </p>

        {/* Habilidades Chips */}
        <div className="flex flex-wrap gap-1.5 pt-1">
          {talento.habilidades.map((hab, idx) => (
            <span
              key={idx}
              className="text-[10px] font-medium bg-blue-50 text-[#0D5BA8] px-2 py-0.5 rounded-md border border-blue-100"
            >
              {hab}
            </span>
          ))}
        </div>
      </div>

      <div className="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
        <span className="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
          {talento.disponibilidade}
        </span>

        <button
          onClick={() => onContact && onContact(talento)}
          className="text-xs font-bold text-[#FF8A00] hover:text-[#E67A00] bg-orange-50 hover:bg-orange-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5"
        >
          <MessageSquare className="w-3.5 h-3.5" />
          Conectar
        </button>
      </div>
    </div>
  );
};
