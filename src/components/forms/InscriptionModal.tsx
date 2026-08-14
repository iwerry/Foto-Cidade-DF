import React, { useState } from 'react';
import { X, CheckCircle2, Sparkles, Compass, User, Mail, Phone, MapPin } from 'lucide-react';
import { Logo } from '../common/Logo';

interface InscriptionModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const InscriptionModal: React.FC<InscriptionModalProps> = ({ isOpen, onClose }) => {
  const [nome, setNome] = useState('');
  const [email, setEmail] = useState('');
  const [telefone, setTelefone] = useState('');
  const [bairro, setBairro] = useState('Sobradinho I');
  const [eixoInteresse, setEixoInteresse] = useState('Todos os Eixos (Formação Completa 56h)');
  const [motivacao, setMotivacao] = useState('');
  const [sucesso, setSucesso] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSucesso(true);
    setTimeout(() => {
      setSucesso(false);
      onClose();
    }, 2000);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="bg-gradient-to-r from-[#0D5BA8] to-[#00A7B5] text-white p-6 relative">
          <button
            onClick={onClose}
            className="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
          >
            <X className="w-4 h-4 text-white" />
          </button>

          <div className="flex items-center gap-2 mb-2 bg-white/10 w-fit px-2.5 py-1 rounded-full text-xs font-semibold">
            <Sparkles className="w-3.5 h-3.5 text-[#FFC107]" />
            <span>Inscrições Abertas • 100% Gratuito</span>
          </div>

          <h3 className="font-heading font-bold text-xl leading-tight">
            Faça o Território Acontecer
          </h3>
          <p className="text-xs text-blue-100 mt-1">
            Inscreva-se na Trilha de Formação em Mapeamento Territorial, Produção Cultural e Comunicação Comunitária.
          </p>
        </div>

        {sucesso ? (
          <div className="p-10 text-center space-y-4">
            <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto animate-bounce">
              <CheckCircle2 className="w-10 h-10" />
            </div>
            <h4 className="font-heading font-bold text-2xl text-slate-800">
              Inscrição Realizada com Sucesso!
            </h4>
            <p className="text-sm text-slate-600 max-w-sm mx-auto">
              Seja bem-vindo(a) à comunidade FotoCidade! Você receberá as instruções da primeira caminhada de reconhecimento por e-mail e WhatsApp.
            </p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="p-6 overflow-y-auto space-y-4 text-xs">
            <div>
              <label className="block font-bold text-slate-700 uppercase tracking-wider mb-1">
                Nome Completo *
              </label>
              <div className="relative">
                <User className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  required
                  value={nome}
                  onChange={(e) => setNome(e.target.value)}
                  placeholder="Seu nome completo"
                  className="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block font-bold text-slate-700 uppercase tracking-wider mb-1">
                  E-mail *
                </label>
                <div className="relative">
                  <Mail className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <input
                    type="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="exemplo@email.com"
                    className="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase tracking-wider mb-1">
                  WhatsApp / Celular *
                </label>
                <div className="relative">
                  <Phone className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <input
                    type="tel"
                    required
                    value={telefone}
                    onChange={(e) => setTelefone(e.target.value)}
                    placeholder="(61) 99999-9999"
                    className="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                  />
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Seu Bairro / Comunidade *
                </label>
                <select
                  value={bairro}
                  onChange={(e) => setBairro(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                >
                  <option value="Sobradinho I">Sobradinho I</option>
                  <option value="Sobradinho II">Sobradinho II</option>
                  <option value="Fercal - Bananal">Fercal - Bananal</option>
                  <option value="Fercal - Boa Vista">Fercal - Boa Vista</option>
                  <option value="Fercal - Rua da Mina">Fercal - Rua da Mina</option>
                  <option value="Grande Colorado">Grande Colorado</option>
                  <option value="Nova Colina">Nova Colina</option>
                  <option value="Outro Território do DF">Outro Território do DF</option>
                </select>
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Interesse Principal
                </label>
                <select
                  value={eixoInteresse}
                  onChange={(e) => setEixoInteresse(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                >
                  <option value="Todos os Eixos (Formação Completa 56h)">Trilha Completa (56h)</option>
                  <option value="Eixo I – Mapeamento Territorial">Eixo I – Mapeamento (20h)</option>
                  <option value="Eixo II – Fotografia & Audiovisual">Eixo II – Foto & Vídeo (14h)</option>
                  <option value="Eixo III – Comunicação Comunitária">Eixo III – Comunicação (10h)</option>
                  <option value="Eixo IV – Produção Cultural">Eixo IV – Produção Cultural (12h)</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block font-bold text-slate-700 uppercase tracking-wider mb-1">
                Por que você deseja participar? (Opcional)
              </label>
              <textarea
                rows={2}
                value={motivacao}
                onChange={(e) => setMotivacao(e.target.value)}
                placeholder="Conte um pouco sobre sua relação com o território, seus interesses ou o que gostaria de registrar..."
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
              />
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-between">
              <span className="text-[11px] text-slate-500">
                Certificado reconhecido ao final da trilha
              </span>
              <button
                type="submit"
                className="px-6 py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-102 flex items-center gap-1.5"
              >
                <Sparkles className="w-4 h-4 text-[#FFC107]" />
                Confirmar Inscrição
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};
