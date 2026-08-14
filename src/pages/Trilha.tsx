import React, { useState } from 'react';
import { EixoTrilha, Missao, SubmissaoMissao, UserProfile } from '../types';
import { SubmissionForm } from '../components/forms/SubmissionForm';
import { 
  CheckCircle2, 
  Lock, 
  Eye, 
  BookOpen, 
  Award, 
  Compass, 
  Upload, 
  FileText, 
  Calendar, 
  Users, 
  Sparkles,
  ChevronRight,
  Clock,
  Layers
} from 'lucide-react';

interface TrilhaProps {
  trilhas: EixoTrilha[];
  user: UserProfile;
  submissions: SubmissaoMissao[];
  onSubmitMission: (sub: Omit<SubmissaoMissao, 'id' | 'dataEnvio' | 'status'>) => void;
  onNavigate: (tab: string) => void;
}

export const Trilha: React.FC<TrilhaProps> = ({
  trilhas,
  user,
  submissions,
  onSubmitMission,
  onNavigate
}) => {
  const [selectedEixoId, setSelectedEixoId] = useState<number>(1);
  const [activeTabSub, setActiveTabSub] = useState<'missoes' | 'recursos' | 'rede'>('missoes');
  const [selectedMissaoId, setSelectedMissaoId] = useState<string>('m-101');

  const currentEixo = trilhas.find(e => e.id === selectedEixoId) || trilhas[0];
  const activeMission = currentEixo.missoes.find(m => m.id === selectedMissaoId) || currentEixo.missoes[0];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-in fade-in duration-200">
      
      {/* Top Eixo Switcher Tabs */}
      <div className="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-slate-200 no-scrollbar">
        {trilhas.map((eixo) => {
          const isSelected = selectedEixoId === eixo.id;
          return (
            <button
              key={eixo.id}
              onClick={() => {
                setSelectedEixoId(eixo.id);
                setSelectedMissaoId(eixo.missoes[0]?.id || '');
              }}
              className={`px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 ${
                isSelected
                  ? 'bg-[#0D5BA8] text-white shadow-md'
                  : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
              }`}
            >
              <span className={`w-5 h-5 rounded-full flex items-center justify-center text-[10px] ${
                isSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'
              }`}>
                {eixo.id}
              </span>
              <span>{eixo.titulo.split('–')[1]?.trim() || eixo.titulo}</span>
              <span className={`text-[10px] px-1.5 py-0.5 rounded ${isSelected ? 'bg-white/20' : 'bg-slate-100 text-slate-500'}`}>
                {eixo.cargaHoraria}
              </span>
            </button>
          );
        })}
      </div>

      {/* Main Student Portal Layout (Directly from SitePreview.JPG) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* Left Student Sidebar */}
        <div className="lg:col-span-3 space-y-6">
          
          {/* Student Profile Card */}
          <div className="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div className="flex items-center gap-3">
              <img
                src={user.avatar}
                alt={user.nome}
                className="w-12 h-12 rounded-full object-cover ring-2 ring-[#00A7B5]"
              />
              <div className="min-w-0">
                <h3 className="font-heading font-bold text-slate-900 text-sm truncate">{user.nome}</h3>
                <p className="text-[11px] text-slate-500 truncate">{user.bairro}</p>
                <span className="inline-block mt-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                  Agente Ativo
                </span>
              </div>
            </div>

            {/* Quick Navigation Links */}
            <div className="space-y-1 pt-2 border-t border-slate-100 text-xs font-semibold">
              <button
                onClick={() => setActiveTabSub('missoes')}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left transition-colors ${
                  activeTabSub === 'missoes'
                    ? 'bg-blue-50 text-[#0D5BA8] font-bold'
                    : 'text-slate-600 hover:bg-slate-50'
                }`}
              >
                <Compass className="w-4 h-4 text-[#0D5BA8]" />
                <span>Painel & Missões</span>
              </button>

              <button
                onClick={() => onNavigate('perfil')}
                className="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left text-slate-600 hover:bg-slate-50 transition-colors"
              >
                <Award className="w-4 h-4 text-[#FF8A00]" />
                <span>Minhas Insígnias ({user.insignias.length})</span>
              </button>

              <button
                onClick={() => setActiveTabSub('recursos')}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left transition-colors ${
                  activeTabSub === 'recursos'
                    ? 'bg-blue-50 text-[#0D5BA8] font-bold'
                    : 'text-slate-600 hover:bg-slate-50'
                }`}
              >
                <FileText className="w-4 h-4 text-[#00A7B5]" />
                <span>Recursos & Guias PDF</span>
              </button>

              <button
                onClick={() => onNavigate('parceiros')}
                className="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-left text-slate-600 hover:bg-slate-50 transition-colors"
              >
                <Users className="w-4 h-4 text-[#FFC107]" />
                <span>Rede Intersetorial</span>
              </button>
            </div>
          </div>

          {/* Submissions History Preview */}
          <div className="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
            <h4 className="font-heading font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center justify-between">
              <span>Últimos Envios</span>
              <span className="text-[10px] text-[#00A7B5] font-bold">{submissions.length}</span>
            </h4>
            
            {submissions.map((sub) => (
              <div key={sub.id} className="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                <p className="font-bold text-slate-800 line-clamp-1">{sub.tituloTrabalho}</p>
                <div className="flex items-center justify-between text-[10px] text-slate-500">
                  <span>{sub.dataEnvio}</span>
                  <span className="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">
                    {sub.status}
                  </span>
                </div>
              </div>
            ))}
          </div>

        </div>

        {/* Right Main Learning Area */}
        <div className="lg:col-span-9 space-y-6">
          
          {/* Header Progress Banner */}
          <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <span className="text-xs font-bold uppercase tracking-wider text-[#0D5BA8]">
                  {currentEixo.cargaHoraria} de Investigação Prática
                </span>
                <h2 className="font-heading font-black text-2xl text-slate-900 mt-0.5">
                  Trilha de Formação: {currentEixo.titulo.split('–')[1]?.trim() || currentEixo.titulo}
                </h2>
                <p className="text-xs text-slate-600 mt-1 max-w-xl">
                  {currentEixo.subtitulo}
                </p>
              </div>

              {/* Progress Bar Display */}
              <div className="bg-slate-50 p-3.5 rounded-xl border border-slate-100 sm:w-56 shrink-0">
                <div className="flex items-center justify-between text-xs font-bold mb-1.5">
                  <span className="text-slate-600">Progresso Geral:</span>
                  <span className="text-[#0D5BA8]">{user.progressoTrilha}%</span>
                </div>
                <div className="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-gradient-to-r from-[#0D5BA8] to-[#00A7B5] rounded-full transition-all duration-500"
                    style={{ width: `${user.progressoTrilha}%` }}
                  />
                </div>
              </div>
            </div>

            {/* Eixo Objectives Pills */}
            <div className="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
              {currentEixo.objetivos.map((obj, i) => (
                <div key={i} className="flex items-center gap-1.5 text-xs text-slate-700 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                  <CheckCircle2 className="w-3.5 h-3.5 text-[#00A7B5] shrink-0" />
                  <span>{obj}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Missions List + Active Exercise Form Area */}
          <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {/* Missions Accordion List (md:col-span-5) */}
            <div className="md:col-span-5 space-y-3">
              <h3 className="font-heading font-bold text-xs uppercase tracking-wider text-slate-500 px-1">
                Etapas do Eixo
              </h3>

              {currentEixo.missoes.map((missao) => {
                const isSelected = activeMission?.id === missao.id;
                const isCompleted = missao.status === 'concluido';
                const isInProgress = missao.status === 'em_andamento';

                return (
                  <div
                    key={missao.id}
                    onClick={() => setSelectedMissaoId(missao.id)}
                    className={`p-4 rounded-xl border transition-all cursor-pointer ${
                      isSelected
                        ? 'bg-blue-50/80 border-[#0D5BA8] shadow-xs'
                        : 'bg-white border-slate-200 hover:border-slate-300'
                    }`}
                  >
                    <div className="flex items-start justify-between gap-3">
                      <div className="flex items-start gap-3">
                        <div className={`w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 ${
                          isSelected
                            ? 'bg-[#0D5BA8] text-white'
                            : 'bg-slate-100 text-slate-700'
                        }`}>
                          {missao.numero}
                        </div>
                        
                        <div>
                          <h4 className="font-heading font-bold text-sm text-slate-900 leading-snug">
                            {missao.titulo}
                          </h4>
                          <p className="text-xs text-slate-500 line-clamp-2 mt-0.5">
                            {missao.descricaoCurta}
                          </p>
                        </div>
                      </div>

                      {/* Status pill / lock */}
                      <div>
                        {isInProgress && (
                          <span className="text-[10px] font-bold bg-[#00A7B5]/15 text-[#00A7B5] px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0">
                            <Eye className="w-3 h-3" />
                            Em Curso
                          </span>
                        )}
                        {missao.status === 'bloqueado' && (
                          <Lock className="w-4 h-4 text-slate-400 shrink-0" />
                        )}
                        {isCompleted && (
                          <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
                        )}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>

            {/* Active Exercise Interactive Form (md:col-span-7) */}
            <div className="md:col-span-7">
              {activeMission && (
                <SubmissionForm
                  missao={activeMission}
                  onSuccess={onSubmitMission}
                />
              )}
            </div>

          </div>

        </div>

      </div>

    </div>
  );
};
