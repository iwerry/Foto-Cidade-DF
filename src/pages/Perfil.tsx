import React from 'react';
import { UserProfile, SubmissaoMissao, VitrineItem } from '../types';
import { 
  Award, 
  CheckCircle2, 
  MapPin, 
  Camera, 
  Sparkles, 
  Compass, 
  BookOpen, 
  ArrowRight, 
  Upload, 
  Share2, 
  Layers,
  Network
} from 'lucide-react';

interface PerfilProps {
  user: UserProfile;
  submissions: SubmissaoMissao[];
  vitrine: VitrineItem[];
  onNavigate: (tab: string) => void;
}

export const Perfil: React.FC<PerfilProps> = ({
  user,
  submissions,
  vitrine,
  onNavigate
}) => {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-in fade-in duration-200 space-y-8">
      
      {/* Welcome Banner (SitePreview reference: Bem-vinda, Ana Silva) */}
      <div className="bg-gradient-to-r from-[#0D5BA8] via-[#09427D] to-[#00A7B5] rounded-3xl p-6 sm:p-10 text-white shadow-lg relative overflow-hidden">
        <div className="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6">
          <img
            src={user.avatar}
            alt={user.nome}
            className="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-white/80 shadow-md"
          />

          <div className="text-center sm:text-left space-y-2 flex-1">
            <div className="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 text-xs font-semibold text-teal-200">
              <Sparkles className="w-3.5 h-3.5 text-[#FFC107]" />
              <span>Agente Cultural Formada</span>
            </div>

            <h1 className="font-heading font-black text-2xl sm:text-4xl">
              Bem-vinda, {user.nome}
            </h1>

            <p className="text-xs sm:text-sm text-blue-100 max-w-xl">
              {user.bio}
            </p>

            <div className="flex flex-wrap items-center justify-center sm:justify-start gap-3 pt-2 text-xs">
              <span className="flex items-center gap-1 text-slate-200">
                <MapPin className="w-3.5 h-3.5 text-[#FFC107]" />
                {user.bairro}
              </span>
              <span>•</span>
              <span className="text-emerald-300 font-semibold">
                {user.pontosCadastrados} Pontos Mapeados
              </span>
              <span>•</span>
              <span className="text-orange-300 font-semibold">
                {submissions.length} Missões Enviadas
              </span>
            </div>
          </div>

          <button
            onClick={() => onNavigate('trilha')}
            className="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 shrink-0 flex items-center gap-2"
          >
            <Compass className="w-4 h-4" />
            <span>Continuar Trilha</span>
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* Left Col: Progress & Badges */}
        <div className="lg:col-span-8 space-y-6">
          
          {/* Progress Card (SitePreview layout) */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-heading font-bold text-lg text-slate-900">
                  Meu Progresso: Mapeamento Territorial
                </h3>
                <p className="text-xs text-slate-500 mt-0.5">Eixo I em andamento</p>
              </div>
              <span className="text-2xl font-black font-heading text-[#0D5BA8]">{user.progressoTrilha}%</span>
            </div>

            <div className="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-[#0D5BA8] to-[#00A7B5] rounded-full transition-all"
                style={{ width: `${user.progressoTrilha}%` }}
              />
            </div>

            {/* Next Step Callout */}
            <div className="bg-blue-50/70 rounded-xl p-4 border border-blue-100 flex flex-col sm:flex-row items-center justify-between gap-3">
              <div>
                <h4 className="font-heading font-bold text-xs text-[#0D5BA8]">
                  Próximo Desafio: Descubra um Espaço Cultural
                </h4>
                <p className="text-[11px] text-slate-600">
                  Eixo I • Etapa 3/9: Mapeie e entreviste um ponto de cultura em seu bairro.
                </p>
              </div>
              <button
                onClick={() => onNavigate('trilha')}
                className="px-4 py-2 bg-[#0D5BA8] text-white text-xs font-bold rounded-lg shadow-xs hover:bg-[#09427D] shrink-0"
              >
                Ir para Missão
              </button>
            </div>
          </div>

          {/* Insígnias Conquistadas (SitePreview: Repórter Local, Articulador de Rede) */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="font-heading font-bold text-lg text-slate-900 flex items-center gap-2">
                <Award className="w-5 h-5 text-[#FF8A00]" />
                Minhas Insígnias
              </h3>
              <span className="text-xs text-slate-500 font-semibold">{user.insignias.length} desbloqueadas</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              {user.insignias.map((badge) => (
                <div
                  key={badge.id}
                  className="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center gap-3.5 shadow-2xs"
                >
                  <div
                    className="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-xs shrink-0"
                    style={{ backgroundColor: badge.cor }}
                  >
                    {badge.icone === 'Camera' ? <Camera className="w-6 h-6" /> : <Network className="w-6 h-6" />}
                  </div>
                  <div>
                    <h4 className="font-heading font-bold text-sm text-slate-900">{badge.titulo}</h4>
                    <p className="text-[10px] text-slate-500 mt-0.5">Conquistado em {badge.data}</p>
                    <span className="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-bold mt-1">
                      <CheckCircle2 className="w-3 h-3" /> Verificado
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Conteúdos Enviados Gallery (SitePreview layout) */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="font-heading font-bold text-lg text-slate-900 flex items-center gap-2">
                <Layers className="w-5 h-5 text-[#00A7B5]" />
                Conteúdos Enviados
              </h3>
              <span className="text-xs text-slate-500">{submissions.length} registros</span>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
              {submissions.map((sub) => (
                <div key={sub.id} className="relative group rounded-xl overflow-hidden bg-slate-900 h-32 border border-slate-200">
                  <img
                    src={sub.arquivos[0] || 'https://images.unsplash.com/photo-1469488865564-c2de10f69f96?auto=format&fit=crop&w=800&q=80'}
                    alt={sub.tituloTrabalho}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                  <div className="absolute bottom-2 left-2 right-2 text-white">
                    <p className="text-[11px] font-bold truncate">{sub.tituloTrabalho}</p>
                    <span className="text-[9px] text-teal-300 font-medium">{sub.status}</span>
                  </div>
                </div>
              ))}

              <div 
                onClick={() => onNavigate('trilha')}
                className="border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center p-4 text-center cursor-pointer hover:bg-slate-50 transition-colors h-32"
              >
                <Upload className="w-5 h-5 text-slate-400 mb-1" />
                <span className="text-xs font-bold text-slate-600">+ Novo Envio</span>
              </div>
            </div>
          </div>

        </div>

        {/* Right Col: Quick Actions & Certificates */}
        <div className="lg:col-span-4 space-y-6">
          
          {/* Certificate Progress Card */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="w-10 h-10 rounded-xl bg-amber-50 text-[#FF8A00] flex items-center justify-center mb-2">
              <Award className="w-6 h-6" />
            </div>
            <h3 className="font-heading font-bold text-base text-slate-900">
              Certificação em Andamento
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Complete todos os 4 eixos para obter seu certificado digital oficial de 56 horas de Agente Territorial FotoCidade.
            </p>
            <div className="p-3 bg-slate-50 rounded-xl text-xs space-y-1.5 font-medium text-slate-700">
              <div className="flex items-center justify-between">
                <span>Eixo I - Mapeamento:</span>
                <span className="text-emerald-600 font-bold">1/3 Concluído</span>
              </div>
              <div className="flex items-center justify-between">
                <span>Eixo II - Fotografia:</span>
                <span className="text-slate-400">Em Breve</span>
              </div>
              <div className="flex items-center justify-between">
                <span>Eixo III - Comunicação:</span>
                <span className="text-slate-400">Em Breve</span>
              </div>
              <div className="flex items-center justify-between">
                <span>Eixo IV - Produção:</span>
                <span className="text-slate-400">Em Breve</span>
              </div>
            </div>
          </div>

          {/* Quick Help / Community Chat */}
          <div className="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white space-y-4">
            <h3 className="font-heading font-bold text-base">Grupo de Campo & Articulação</h3>
            <p className="text-xs text-slate-300">
              Dúvidas sobre o preenchimento da cartografia afetiva ou saídas de campo com a turma?
            </p>
            <a
              href="https://chat.whatsapp.com"
              target="_blank"
              rel="noreferrer"
              className="block w-full py-2.5 bg-[#00A7B5] hover:bg-[#008f9c] text-white text-center font-bold text-xs rounded-xl shadow-xs transition-colors"
            >
              Entrar no WhatsApp da Turma
            </a>
          </div>

        </div>

      </div>

    </div>
  );
};
