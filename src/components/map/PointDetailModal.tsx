import React from 'react';
import { MapPoint } from '../../types';
import { getCategoryColor } from '../../utils/formatters';
import { X, MapPin, Phone, Share2, Calendar, User, Tag, ExternalLink, Bookmark } from 'lucide-react';

interface PointDetailModalProps {
  point: MapPoint | null;
  onClose: () => void;
}

export const PointDetailModal: React.FC<PointDetailModalProps> = ({ point, onClose }) => {
  if (!point) return null;

  const colorScheme = getCategoryColor(point.categoria);

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: point.nome,
        text: `${point.nome} - Ponto cultural em ${point.bairro} mapeado pela rede FotoCidade`,
        url: window.location.href
      }).catch(() => {});
    } else {
      navigator.clipboard.writeText(`${point.nome} (${point.bairro}) - Mapeado no FotoCidade`);
      alert('Link copiado para a área de transferência!');
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        {/* Header Media */}
        <div className="relative h-60 bg-slate-900 shrink-0">
          {point.foto ? (
            <img
              src={point.foto}
              alt={point.nome}
              className="w-full h-full object-cover"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-slate-800 text-slate-400">
              Sem foto disponível
            </div>
          )}
          <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

          {/* Close button */}
          <button
            onClick={onClose}
            className="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/50 text-white hover:bg-black/80 flex items-center justify-center transition-colors"
          >
            <X className="w-4 h-4" />
          </button>

          {/* Category Pill */}
          <div className="absolute top-4 left-4">
            <span className={`px-3 py-1 rounded-full text-xs font-bold shadow-xs ${colorScheme.badge}`}>
              {point.categoria}
            </span>
          </div>

          {/* Title & Neighborhood */}
          <div className="absolute bottom-4 left-4 right-4 text-white">
            <div className="flex items-center gap-1 text-xs text-teal-300 font-medium mb-1">
              <MapPin className="w-3.5 h-3.5" />
              <span>{point.bairro}</span>
            </div>
            <h3 className="font-heading font-bold text-xl leading-snug drop-shadow-sm">
              {point.nome}
            </h3>
          </div>
        </div>

        {/* Modal Body */}
        <div className="p-6 overflow-y-auto space-y-4">
          <div>
            <h4 className="text-xs font-bold uppercase text-slate-400 tracking-wider mb-1">Sobre o Ponto</h4>
            <p className="text-sm text-slate-700 leading-relaxed">
              {point.descricao}
            </p>
          </div>

          {/* Details List */}
          <div className="bg-slate-50 rounded-xl p-4 space-y-2.5 border border-slate-100 text-xs">
            <div className="flex items-start gap-2.5">
              <MapPin className="w-4 h-4 text-[#0D5BA8] shrink-0 mt-0.5" />
              <div>
                <span className="font-bold text-slate-700">Endereço / Referência:</span>
                <p className="text-slate-600">{point.endereco}</p>
              </div>
            </div>

            {point.contato && (
              <div className="flex items-center gap-2.5">
                <Phone className="w-4 h-4 text-emerald-600 shrink-0" />
                <div>
                  <span className="font-bold text-slate-700">Contato / WhatsApp: </span>
                  <span className="text-slate-600 font-mono">{point.contato}</span>
                </div>
              </div>
            )}

            {point.autorRegistro && (
              <div className="flex items-center gap-2.5">
                <User className="w-4 h-4 text-[#00A7B5] shrink-0" />
                <div>
                  <span className="font-bold text-slate-700">Mapeado por: </span>
                  <span className="text-slate-600">{point.autorRegistro}</span>
                </div>
              </div>
            )}

            {point.dataCriacao && (
              <div className="flex items-center gap-2.5">
                <Calendar className="w-4 h-4 text-slate-400 shrink-0" />
                <div>
                  <span className="font-bold text-slate-700">Data de Registro: </span>
                  <span className="text-slate-600">{point.dataCriacao}</span>
                </div>
              </div>
            )}
          </div>

          {/* Tags */}
          {point.tags && point.tags.length > 0 && (
            <div>
              <div className="flex items-center gap-1 text-xs font-bold text-slate-500 mb-2">
                <Tag className="w-3.5 h-3.5 text-[#FF8A00]" />
                <span>Tags e Temas</span>
              </div>
              <div className="flex flex-wrap gap-1.5">
                {point.tags.map((tag, i) => (
                  <span key={i} className="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-medium">
                    #{tag}
                  </span>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Footer Actions */}
        <div className="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
          <button
            onClick={handleShare}
            className="flex-1 py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-white transition-colors flex items-center justify-center gap-2"
          >
            <Share2 className="w-4 h-4 text-slate-500" />
            Compartilhar Ponto
          </button>
          
          <button
            onClick={onClose}
            className="py-2.5 px-6 rounded-xl bg-[#0D5BA8] hover:bg-[#09427D] text-white font-bold text-xs transition-colors shadow-xs"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  );
};
