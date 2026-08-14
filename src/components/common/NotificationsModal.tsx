import React from 'react';
import { X, Bell, Calendar, Sparkles, Award, MapPin } from 'lucide-react';

interface NotificationsModalProps {
  isOpen: boolean;
  onClose: () => void;
  onNavigate: (tab: string) => void;
}

export const NotificationsModal: React.FC<NotificationsModalProps> = ({
  isOpen,
  onClose,
  onNavigate
}) => {
  if (!isOpen) return null;

  const notifications = [
    {
      id: 1,
      titulo: "Exercício Prático Ativo!",
      mensagem: "Envie suas 10 imagens da caminhada territorial no Eixo I para desbloquear sua insígnia.",
      data: "Hoje, 10:15",
      tipo: "missao",
      link: "trilha"
    },
    {
      id: 2,
      titulo: "Novo Ponto Cultural Mapeado",
      mensagem: "Espaço Cultural Casa de Farinha & Memória foi adicionado na Fercal.",
      data: "Ontem, 16:40",
      tipo: "mapa",
      link: "mapa"
    },
    {
      id: 3,
      titulo: "Pauta Aberta na Agenda Intersetorial",
      mensagem: "Mostra comunitária aberta para adesão de escolas em Sobradinho.",
      data: "Há 2 dias",
      tipo: "pauta",
      link: "parceiros"
    }
  ];

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-end p-4 sm:p-6 bg-black/40 backdrop-blur-xs animate-in fade-in duration-150">
      <div className="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl border border-slate-200 mt-16 sm:mt-12">
        <div className="bg-[#0D5BA8] text-white p-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Bell className="w-4 h-4 text-[#FF8A00]" />
            <h3 className="font-heading font-bold text-sm">Avisos da Trilha FotoCidade</h3>
          </div>
          <button
            onClick={onClose}
            className="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white"
          >
            <X className="w-3.5 h-3.5" />
          </button>
        </div>

        <div className="p-3 divide-y divide-slate-100 max-h-[70vh] overflow-y-auto">
          {notifications.map((n) => (
            <div
              key={n.id}
              onClick={() => {
                onNavigate(n.link);
                onClose();
              }}
              className="p-3 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer space-y-1"
            >
              <div className="flex items-center justify-between">
                <h4 className="font-heading font-bold text-xs text-slate-900">{n.titulo}</h4>
                <span className="text-[10px] text-slate-400">{n.data}</span>
              </div>
              <p className="text-xs text-slate-600 leading-relaxed">{n.mensagem}</p>
            </div>
          ))}
        </div>

        <div className="p-3 bg-slate-50 border-t border-slate-100 text-center">
          <button
            onClick={onClose}
            className="text-xs text-[#0D5BA8] font-bold hover:underline"
          >
            Fechar Avisos
          </button>
        </div>
      </div>
    </div>
  );
};
