import React, { useState } from 'react';
import { PautaAgenda } from '../../types';
import { X, Calendar, Megaphone, CheckCircle2, Building } from 'lucide-react';

interface AgendaPautaModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSavePauta: (pauta: Omit<PautaAgenda, 'id' | 'status'>) => void;
}

export const AgendaPautaModal: React.FC<AgendaPautaModalProps> = ({
  isOpen,
  onClose,
  onSavePauta
}) => {
  const [titulo, setTitulo] = useState('');
  const [setor, setSetor] = useState('3º Setor (OSCs/Coletivos)');
  const [organizacao, setOrganizacao] = useState('');
  const [dataEvento, setDataEvento] = useState('');
  const [bairro, setBairro] = useState('Sobradinho I');
  const [localizacao, setLocalizacao] = useState('');
  const [descricao, setDescricao] = useState('');
  const [contatoResponsavel, setContatoResponsavel] = useState('');
  const [email, setEmail] = useState('');
  const [sentSuccess, setSentSuccess] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!titulo.trim() || !descricao.trim()) return;

    onSavePauta({
      titulo,
      setor,
      organizacao: organizacao || 'Coletivo Comunitário',
      dataEvento: dataEvento || new Date().toISOString().split('T')[0],
      bairro,
      localizacao: localizacao || `${bairro} - DF`,
      descricao,
      contatoResponsavel: contatoResponsavel || 'Comunicação',
      email: email || 'contato@territorio.org'
    });

    setSentSuccess(true);
    setTimeout(() => {
      setSentSuccess(false);
      onClose();
    }, 1300);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="bg-[#FF8A00] text-white p-5 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
              <Megaphone className="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 className="font-heading font-bold text-lg">Enviar Pauta para a Agenda</h3>
              <p className="text-xs text-orange-100">Divulgue eventos e iniciativas na Rede Intersetorial</p>
            </div>
          </div>

          <button
            onClick={onClose}
            className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {sentSuccess ? (
          <div className="p-10 text-center space-y-3">
            <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
              <CheckCircle2 className="w-10 h-10" />
            </div>
            <h4 className="font-heading font-bold text-xl text-slate-800">Pauta Enviada para a Rede!</h4>
            <p className="text-sm text-slate-600">
              Sua sugestão de pauta foi compartilhada com escolas, comércios parceiros e veículos comunitários.
            </p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="p-6 overflow-y-auto space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Título do Evento ou Pauta *
              </label>
              <input
                type="text"
                required
                value={titulo}
                onChange={(e) => setTitulo(e.target.value)}
                placeholder="Ex: Mostra Fotográfica na Feira, Oficina de Bordado..."
                className="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Setor Articulador *
                </label>
                <select
                  value={setor}
                  onChange={(e) => setSetor(e.target.value)}
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
                >
                  <option value="1º Setor (Poder Público)">1º Setor (Poder Público)</option>
                  <option value="2º Setor (Empresas/Comércio)">2º Setor (Empresas/Comércio)</option>
                  <option value="3º Setor (OSCs/Coletivos)">3º Setor (OSCs/Coletivos)</option>
                  <option value="Comunidade">Comunidade e Moradores</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Organização / Coletivo
                </label>
                <input
                  type="text"
                  value={organizacao}
                  onChange={(e) => setOrganizacao(e.target.value)}
                  placeholder="Ex: Coletivo Sobradinho Vivo"
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Data Prevista
                </label>
                <input
                  type="date"
                  value={dataEvento}
                  onChange={(e) => setDataEvento(e.target.value)}
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Bairro / Localidade *
                </label>
                <select
                  value={bairro}
                  onChange={(e) => setBairro(e.target.value)}
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
                >
                  <option value="Sobradinho I">Sobradinho I</option>
                  <option value="Sobradinho II">Sobradinho II</option>
                  <option value="Fercal">Fercal</option>
                  <option value="Grande Colorado">Grande Colorado</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Localização / Ponto de Encontro
              </label>
              <input
                type="text"
                value={localizacao}
                onChange={(e) => setLocalizacao(e.target.value)}
                placeholder="Ex: Praça das Artes ou Quadra 8"
                className="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Descrição da Pauta / Detalhes da Ação *
              </label>
              <textarea
                required
                rows={3}
                value={descricao}
                onChange={(e) => setDescricao(e.target.value)}
                placeholder="Explique os objetivos, atrações, horários e como os parceiros e a comunidade podem se envolver..."
                className="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Nome do Responsável
                </label>
                <input
                  type="text"
                  value={contatoResponsavel}
                  onChange={(e) => setContatoResponsavel(e.target.value)}
                  placeholder="Seu nome"
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  E-mail de Contato
                </label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="seuemail@exemplo.com"
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF8A00]"
                />
              </div>
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
              <button
                type="button"
                onClick={onClose}
                className="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900"
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-xs transition-colors"
              >
                Submeter Pauta
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};
