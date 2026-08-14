import React, { useState, useMemo } from 'react';
import { Parceiro, TalentoLocal, PautaAgenda } from '../types';
import { TalentCard } from '../components/cards/TalentCard';
import { AgendaPautaModal } from '../components/forms/AgendaPautaModal';
import { 
  Building2, 
  School, 
  HeartHandshake, 
  Download, 
  Megaphone, 
  Plus, 
  Search, 
  Sparkles, 
  Mail, 
  Users, 
  FileDown, 
  CheckCircle2,
  Calendar,
  ExternalLink,
  MessageSquare
} from 'lucide-react';

interface ParceirosProps {
  parceiros: Parceiro[];
  talentos: TalentoLocal[];
  pautas: PautaAgenda[];
  onSavePauta: (pauta: Omit<PautaAgenda, 'id' | 'status'>) => void;
}

export const Parceiros: React.FC<ParceirosProps> = ({
  parceiros,
  talentos,
  pautas,
  onSavePauta
}) => {
  const [isPautaModalOpen, setIsPautaModalOpen] = useState(false);
  const [talentSearch, setTalentSearch] = useState('');
  const [selectedSetor, setSelectedSetor] = useState<string>('ALL');
  const [downloadToast, setDownloadToast] = useState<string | null>(null);
  const [contactTalent, setContactTalent] = useState<TalentoLocal | null>(null);

  const handleDownloadKit = (kitName: string) => {
    setDownloadToast(`Iniciando download do ${kitName}...`);
    setTimeout(() => setDownloadToast(null), 3000);
  };

  const filteredTalentos = useMemo(() => {
    return talentos.filter(t => 
      t.nome.toLowerCase().includes(talentSearch.toLowerCase()) ||
      t.funcao.toLowerCase().includes(talentSearch.toLowerCase()) ||
      t.bairro.toLowerCase().includes(talentSearch.toLowerCase()) ||
      t.habilidades.some(h => h.toLowerCase().includes(talentSearch.toLowerCase()))
    );
  }, [talentos, talentSearch]);

  const filteredParceiros = useMemo(() => {
    if (selectedSetor === 'ALL') return parceiros;
    return parceiros.filter(p => p.setor.includes(selectedSetor));
  }, [parceiros, selectedSetor]);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-in fade-in duration-200 space-y-12">
      
      {/* Header & Network Banner (matching SitePreview layout) */}
      <div className="text-center max-w-3xl mx-auto space-y-3">
        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#0D5BA8] text-xs font-bold">
          <HeartHandshake className="w-4 h-4 text-[#FF8A00]" />
          <span>Articulação 1º, 2º e 3º Setor + Comunidade</span>
        </div>
        <h1 className="font-heading font-black text-3xl sm:text-4xl text-slate-900 leading-tight">
          Rede Intersetorial: Juntos pelo Território.
        </h1>
        <p className="text-sm text-slate-600">
          A comunicação e a formação cultural do FotoCidade conectam poder público, comércio local, organizações sociais e moradores.
        </p>
      </div>

      {/* STATS COUNTERS (Directly from SitePreview.JPG) */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
        <div className="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
          <School className="w-6 h-6 text-[#0D5BA8] mx-auto mb-2" />
          <div className="text-4xl font-heading font-black text-slate-900">15</div>
          <p className="text-xs font-bold text-slate-600 uppercase tracking-wide">Escolas</p>
        </div>

        <div className="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
          <Building2 className="w-6 h-6 text-[#00A7B5] mx-auto mb-2" />
          <div className="text-4xl font-heading font-black text-slate-900">30</div>
          <p className="text-xs font-bold text-slate-600 uppercase tracking-wide">Empresas</p>
        </div>

        <div className="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
          <HeartHandshake className="w-6 h-6 text-[#FF8A00] mx-auto mb-2" />
          <div className="text-4xl font-heading font-black text-slate-900">10</div>
          <p className="text-xs font-bold text-slate-600 uppercase tracking-wide">ONGs</p>
        </div>

        <div className="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-xs space-y-1">
          <Users className="w-6 h-6 text-[#FFC107] mx-auto mb-2" />
          <div className="text-4xl font-heading font-black text-slate-900">+400</div>
          <p className="text-xs font-bold text-slate-600 uppercase tracking-wide">Moradores Mapeados</p>
        </div>
      </div>

      {/* ACTION BOXES: Enviar Pauta + Kit de Comunicação (SitePreview reference) */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {/* Box 1: Enviar Pauta para Agenda */}
        <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-5">
          <div className="space-y-2">
            <div className="flex items-center gap-2 text-[#FF8A00] font-bold text-xs uppercase tracking-wider">
              <Megaphone className="w-4 h-4" />
              <span>Agenda Compartilhada</span>
            </div>
            <h3 className="font-heading font-bold text-xl text-slate-900">
              Enviar Pauta para a Agenda
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Tem um sarau, feira comunitária, lançamento de livro ou ação social acontecendo em Sobradinho ou Fercal? Envie sua pauta para circular em nossa rede.
            </p>
          </div>

          {/* Published Pautas feed preview */}
          {pautas.length > 0 && (
            <div className="bg-orange-50/60 rounded-xl p-3 border border-orange-100 space-y-1 text-xs">
              <div className="flex items-center justify-between font-bold text-orange-950">
                <span className="flex items-center gap-1">
                  <Calendar className="w-3.5 h-3.5 text-[#FF8A00]" />
                  Próxima Pauta na Agenda:
                </span>
                <span className="text-[10px] text-orange-800">{pautas[0].bairro}</span>
              </div>
              <p className="text-slate-700 font-semibold">{pautas[0].titulo}</p>
            </div>
          )}

          <button
            id="btn-open-agenda-modal"
            onClick={() => setIsPautaModalOpen(true)}
            className="w-full py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2"
          >
            <Plus className="w-4 h-4" />
            <span>Cadastrar Pauta Comunitária</span>
          </button>
        </div>

        {/* Box 2: Kit de Comunicação */}
        <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-5">
          <div className="space-y-2">
            <div className="flex items-center gap-2 text-[#00A7B5] font-bold text-xs uppercase tracking-wider">
              <FileDown className="w-4 h-4" />
              <span>Materiais para Download</span>
            </div>
            <h3 className="font-heading font-bold text-xl text-slate-900">
              Kit de Comunicação Comunitária
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Baixe artes para redes sociais, modelos de cartazes para imprimir e colocar no comércio, e guias de fanzine da Trilha FotoCidade.
            </p>
          </div>

          {/* Download Buttons Row (from preview: Download Local, Download Setor, Download Social) */}
          <div className="grid grid-cols-3 gap-2.5">
            <button
              onClick={() => handleDownloadKit('Kit Local (Cartazes e Lambe-Lambe)')}
              className="px-3 py-2.5 bg-blue-50 hover:bg-blue-100 text-[#0D5BA8] font-bold text-[11px] rounded-xl border border-blue-200 transition-colors flex flex-col items-center justify-center gap-1 text-center"
            >
              <Download className="w-3.5 h-3.5" />
              <span>Download Local</span>
            </button>

            <button
              onClick={() => handleDownloadKit('Kit Setorial (Parcerias e Escolas)')}
              className="px-3 py-2.5 bg-teal-50 hover:bg-teal-100 text-[#00A7B5] font-bold text-[11px] rounded-xl border border-teal-200 transition-colors flex flex-col items-center justify-center gap-1 text-center"
            >
              <Download className="w-3.5 h-3.5" />
              <span>Download Setor</span>
            </button>

            <button
              onClick={() => handleDownloadKit('Kit Social (Cards para WhatsApp & Redes)')}
              className="px-3 py-2.5 bg-amber-50 hover:bg-amber-100 text-[#FF8A00] font-bold text-[11px] rounded-xl border border-amber-200 transition-colors flex flex-col items-center justify-center gap-1 text-center"
            >
              <Download className="w-3.5 h-3.5" />
              <span>Download Social</span>
            </button>
          </div>

          {downloadToast && (
            <div className="p-2.5 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-lg flex items-center gap-2 animate-in fade-in">
              <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
              <span>{downloadToast}</span>
            </div>
          )}
        </div>

      </div>

      {/* BANCO DE TALENTOS LOCAIS (Directly from SitePreview.JPG) */}
      <div className="space-y-6 pt-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
          <div>
            <h2 className="font-heading font-black text-2xl text-slate-900">
              Banco de Talentos Locais
            </h2>
            <p className="text-xs text-slate-600 mt-0.5">
              Fotógrafos, pesquisadores, videomakers e articuladores formados e atuantes no território.
            </p>
          </div>

          {/* Search Talent Input */}
          <div className="relative min-w-[260px]">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={talentSearch}
              onChange={(e) => setTalentSearch(e.target.value)}
              placeholder="Buscar fotógrafo, oficineiro, bairro..."
              className="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          {filteredTalentos.map((talento) => (
            <TalentCard
              key={talento.id}
              talento={talento}
              onContact={(t) => setContactTalent(t)}
            />
          ))}
        </div>
      </div>

      {/* ORGANIZAÇÕES PARCEIRAS */}
      <div className="space-y-6 pt-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
          <div>
            <h2 className="font-heading font-black text-2xl text-slate-900">
              Organizações & Pontos de Circulação
            </h2>
            <p className="text-xs text-slate-600 mt-0.5">
              Espaços, escolas e empresas que apoiam a circulação da cultura.
            </p>
          </div>

          <div className="flex items-center gap-1.5 flex-wrap">
            {['ALL', '1º Setor', '2º Setor', '3º Setor'].map((s) => (
              <button
                key={s}
                onClick={() => setSelectedSetor(s)}
                className={`px-3 py-1 rounded-lg text-xs font-bold transition-all ${
                  selectedSetor === s
                    ? 'bg-[#0D5BA8] text-white shadow-xs'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                }`}
              >
                {s === 'ALL' ? 'Todos os Setores' : s}
              </button>
            ))}
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredParceiros.map((parc) => (
            <div key={parc.id} className="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3 flex flex-col justify-between">
              <div className="space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-[#0D5BA8] bg-blue-50 px-2 py-0.5 rounded">
                    {parc.tipo}
                  </span>
                  <span className="text-[10px] text-slate-500 font-medium">{parc.bairro}</span>
                </div>

                <h3 className="font-heading font-bold text-base text-slate-900 leading-snug">
                  {parc.nome}
                </h3>
                <p className="text-xs text-slate-600 leading-relaxed">
                  {parc.descricao}
                </p>
              </div>

              <div className="pt-3 border-t border-slate-100 space-y-2">
                <div className="text-[11px] text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                  <span className="font-bold text-[#FF8A00]">Como atua na rede: </span>
                  {parc.contribuicao}
                </div>
                {parc.contatoEmail && (
                  <p className="text-[11px] text-slate-500 flex items-center gap-1 font-mono">
                    <Mail className="w-3 h-3 text-[#00A7B5]" />
                    {parc.contatoEmail}
                  </p>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Agenda Pauta Modal */}
      <AgendaPautaModal
        isOpen={isPautaModalOpen}
        onClose={() => setIsPautaModalOpen(false)}
        onSavePauta={onSavePauta}
      />

      {/* Contact Talent Modal */}
      {contactTalent && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center">
            <img
              src={contactTalent.avatar}
              alt={contactTalent.nome}
              className="w-16 h-16 rounded-full object-cover ring-2 ring-[#0D5BA8] mx-auto"
            />
            <div>
              <h3 className="font-heading font-bold text-xl text-slate-900">{contactTalent.nome}</h3>
              <p className="text-xs text-[#0D5BA8] font-semibold">{contactTalent.funcao} • {contactTalent.bairro}</p>
            </div>
            <p className="text-xs text-slate-600">
              Entre em contato direto para projetos, coberturas fotográficas ou oficinas comunitárias:
            </p>
            <div className="space-y-2 text-xs font-mono">
              {contactTalent.whatsapp && (
                <a
                  href={`https://wa.me/55${contactTalent.whatsapp.replace(/\D/g, '')}`}
                  target="_blank"
                  rel="noreferrer"
                  className="block py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors"
                >
                  Conversar no WhatsApp ({contactTalent.whatsapp})
                </a>
              )}
              {contactTalent.email && (
                <a
                  href={`mailto:${contactTalent.email}`}
                  className="block py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold rounded-xl transition-colors"
                >
                  Enviar E-mail ({contactTalent.email})
                </a>
              )}
            </div>
            <button
              onClick={() => setContactTalent(null)}
              className="text-xs text-slate-500 hover:text-slate-800 pt-2"
            >
              Fechar
            </button>
          </div>
        </div>
      )}

    </div>
  );
};
