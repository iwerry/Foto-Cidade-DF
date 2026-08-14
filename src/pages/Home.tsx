import React from 'react';
import { MapPoint, VitrineItem, Artista } from '../types';
import { ProjectCard } from '../components/cards/ProjectCard';
import { MapPin, Camera, Tent, Sparkles, ArrowRight, Users, Compass, CheckCircle2 } from 'lucide-react';

interface HomeProps {
  onNavigate: (tab: string) => void;
  onOpenInscription: () => void;
  vitrineItems: VitrineItem[];
  mapPoints: MapPoint[];
  artistas: Artista[];
  onLikeVitrine: (id: string | number) => void;
  onSelectVitrineItem: (item: VitrineItem) => void;
}

export const Home: React.FC<HomeProps> = ({
  onNavigate,
  onOpenInscription,
  vitrineItems,
  mapPoints,
  artistas,
  onLikeVitrine,
  onSelectVitrineItem
}) => {
  return (
    <div className="space-y-16 pb-16 animate-in fade-in duration-300">
      
      {/* HERO SECTION */}
      <section className="relative overflow-hidden bg-white border-b border-slate-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 lg:py-16">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            {/* Left Content Column */}
            <div className="lg:col-span-7 space-y-6">
              
              {/* Badge */}
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-[#0D5BA8] text-xs font-bold tracking-wide">
                <Sparkles className="w-3.5 h-3.5 text-[#FF8A00]" />
                <span>Trilha de Formação Comunitária • Sobradinho & Fercal</span>
              </div>

              {/* Main Headline */}
              <h1 className="font-heading font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 leading-[1.08] tracking-tight">
                Aprender o Território.<br />
                <span className="text-[#0D5BA8]">Transformar a Cidade.</span>
              </h1>

              {/* Subtitle */}
              <p className="text-base sm:text-lg text-slate-600 leading-relaxed max-w-xl">
                Junte-se à Trilha de Formação em Mapeamento, Produção Cultural e Comunicação Comunitária. 
                Transforme sua visão em pesquisa, fotografia e impacto real no seu bairro.
              </p>

              {/* CTA Buttons */}
              <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
                <button
                  id="btn-hero-subscribe"
                  onClick={onOpenInscription}
                  className="px-8 py-4 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-heading font-bold text-sm sm:text-base rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35 transition-all hover:scale-[1.02] flex items-center justify-center gap-2"
                >
                  <Sparkles className="w-5 h-5 text-[#FFC107]" />
                  <span>Faça o Território Acontecer - Inscreva-se</span>
                </button>

                <button
                  onClick={() => onNavigate('trilha')}
                  className="px-6 py-4 bg-slate-100 hover:bg-slate-200 text-[#0D5BA8] font-heading font-bold text-sm sm:text-base rounded-xl transition-colors flex items-center justify-center gap-2"
                >
                  <Compass className="w-5 h-5" />
                  <span>Conhecer a Trilha</span>
                </button>
              </div>

              {/* Slogan pill row */}
              <div className="flex items-center gap-4 text-xs font-bold text-slate-500 pt-3">
                <span className="flex items-center gap-1 text-[#0D5BA8]">
                  <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                  100% Gratuito
                </span>
                <span>•</span>
                <span className="flex items-center gap-1 text-[#00A7B5]">
                  <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                  56 Horas Práticas
                </span>
                <span>•</span>
                <span className="flex items-center gap-1 text-[#FF8A00]">
                  <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                  Certificação Oficial
                </span>
              </div>
            </div>

            {/* Right Hero Visual Column (Young photographers in community) */}
            <div className="lg:col-span-5 relative">
              <div className="relative mx-auto max-w-md lg:max-w-none">
                {/* Decorative border frame */}
                <div className="absolute -inset-2 bg-gradient-to-tr from-[#0D5BA8] via-[#00A7B5] to-[#FF8A00] rounded-3xl opacity-30 blur-md"></div>
                
                <div className="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-white bg-slate-100">
                  <img
                    src="/oficina-olhar-fercal.jpg"
                    alt="Jovens comunicadores e fotógrafos no território da Fercal e Sobradinho"
                    className="w-full h-[380px] sm:h-[420px] object-cover"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>

                  <div className="absolute bottom-4 left-4 right-4 text-white">
                    <span className="bg-[#00A7B5] text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded-md inline-block mb-1">
                      Em Campo
                    </span>
                    <p className="font-heading font-bold text-sm">
                      Juventude mapeando saberes e memórias em Sobradinho & Fercal
                    </p>
                  </div>
                </div>
              </div>
            </div>

          </div>

          {/* 3 FEATURE ACTION CARDS (Exactly from SitePreview.JPG) */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mt-12 pt-6">
            
            {/* Card 1 */}
            <div 
              onClick={() => onNavigate('mapa')}
              className="bg-[#0D5BA8] hover:bg-[#0b4d8f] text-white p-6 rounded-2xl cursor-pointer transition-all duration-200 hover:-translate-y-1 shadow-md group relative overflow-hidden flex items-center justify-between"
            >
              <div className="space-y-1 z-10">
                <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                  <MapPin className="w-5 h-5 text-white" />
                </div>
                <span className="text-white/70 text-xs font-bold tracking-widest uppercase">Eixo 01</span>
                <h3 className="font-heading font-bold text-xl">1. Mapeie Seu Bairro</h3>
                <p className="text-xs text-blue-100 pt-1">Identifique mestres, espaços culturais e memórias orais.</p>
              </div>
              <div className="text-5xl font-black text-white/15 select-none font-heading mr-2">
                1
              </div>
            </div>

            {/* Card 2 */}
            <div 
              onClick={() => onNavigate('vitrine')}
              className="bg-[#00A7B5] hover:bg-[#008f9c] text-white p-6 rounded-2xl cursor-pointer transition-all duration-200 hover:-translate-y-1 shadow-md group relative overflow-hidden flex items-center justify-between"
            >
              <div className="space-y-1 z-10">
                <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                  <Camera className="w-5 h-5 text-white" />
                </div>
                <span className="text-white/80 text-xs font-bold tracking-widest uppercase">Eixo 02</span>
                <h3 className="font-heading font-bold text-xl">2. Conte Histórias Visuais</h3>
                <p className="text-xs text-teal-100 pt-1">Aprenda fotografia mobile, mini docs e narrativas de afeto.</p>
              </div>
              <div className="text-5xl font-black text-white/15 select-none font-heading mr-2">
                2
              </div>
            </div>

            {/* Card 3 */}
            <div 
              onClick={() => onNavigate('parceiros')}
              className="bg-[#FF8A00] hover:bg-[#e67a00] text-white p-6 rounded-2xl cursor-pointer transition-all duration-200 hover:-translate-y-1 shadow-md group relative overflow-hidden flex items-center justify-between"
            >
              <div className="space-y-1 z-10">
                <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                  <Tent className="w-5 h-5 text-white" />
                </div>
                <span className="text-white/80 text-xs font-bold tracking-widest uppercase">Eixo 03 & 04</span>
                <h3 className="font-heading font-bold text-xl">3. Produza Cultura Local</h3>
                <p className="text-xs text-orange-100 pt-1">Ocupe praças, monte mostras e conecte a rede intersetorial.</p>
              </div>
              <div className="text-5xl font-black text-white/15 select-none font-heading mr-2">
                3
              </div>
            </div>

          </div>

        </div>
      </section>

      {/* VITRINE PREVIEW SECTION */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
          <div>
            <div className="flex items-center gap-2 text-xs font-bold text-[#0D5BA8] uppercase tracking-wider mb-1">
              <Camera className="w-4 h-4 text-[#00A7B5]" />
              <span>Produção dos Alunos</span>
            </div>
            <h2 className="font-heading font-black text-2xl sm:text-3xl text-slate-900">
              Vitrine do Território: O que nossa rede está revelando.
            </h2>
            <p className="text-sm text-slate-600 mt-1">
              Ensaios fotográficos, minidocs em vídeo e perfis de mestres registrados durante a formação.
            </p>
          </div>

          <button
            onClick={() => onNavigate('vitrine')}
            className="text-xs font-bold text-[#0D5BA8] hover:text-[#00A7B5] flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors shrink-0"
          >
            <span>Ver Toda a Vitrine</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {vitrineItems.slice(0, 3).map((item) => (
            <ProjectCard
              key={item.id}
              item={item}
              onLike={onLikeVitrine}
              onSelect={onSelectVitrineItem}
            />
          ))}
        </div>
      </section>

      {/* MAPA TEASER & RECOGNITION CALLOUT */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-gradient-to-br from-[#0D5BA8] to-[#00A7B5] rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
          {/* Subtle Background Pattern */}
          <div className="absolute right-0 top-0 bottom-0 w-1/2 opacity-10 pointer-events-none flex items-center justify-center">
            <MapPin className="w-96 h-96 -mr-20" />
          </div>

          <div className="relative z-10 max-w-2xl space-y-6">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold">
              <MapPin className="w-3.5 h-3.5 text-[#FFC107]" />
              <span>Cartografia Viva & Georreferenciada</span>
            </div>

            <h2 className="font-heading font-black text-3xl sm:text-4xl leading-tight">
              Descubra os pontos culturais, artistas e guardiões de memórias de Sobradinho e Fercal.
            </h2>

            <p className="text-sm sm:text-base text-blue-100 leading-relaxed">
              Mapear não é apenas criar uma lista de nomes e lugares. É reconhecer relações, memórias, talentos e canais de comunicação que fazem a cidade funcionar.
            </p>

            <div className="flex flex-wrap items-center gap-4 pt-2">
              <button
                onClick={() => onNavigate('mapa')}
                className="px-6 py-3.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-heading font-bold text-xs sm:text-sm rounded-xl shadow-lg transition-all hover:scale-[1.02] flex items-center gap-2"
              >
                <MapPin className="w-4 h-4 text-[#FFC107]" />
                <span>Explorar Mapa Interativo</span>
              </button>

              <button
                onClick={() => onNavigate('parceiros')}
                className="px-6 py-3.5 bg-white/15 hover:bg-white/25 text-white font-heading font-bold text-xs sm:text-sm rounded-xl transition-colors flex items-center gap-2"
              >
                <Users className="w-4 h-4" />
                <span>Conectar com Parceiros</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* OS 4 EIXOS DE FORMAÇÃO */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-12">
          <span className="text-xs font-bold uppercase tracking-wider text-[#FF8A00]">Estrutura Pedagógica</span>
          <h2 className="font-heading font-black text-3xl text-slate-900 mt-1">
            Os 4 Eixos da Trilha FotoCidade
          </h2>
          <p className="text-sm text-slate-600 mt-2">
            Percurso integrado de investigação, registro, articulação e difusão cultural.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {/* Eixo 1 */}
          <div className="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all border-t-4 border-t-[#0D5BA8]">
            <div className="w-10 h-10 rounded-xl bg-blue-50 text-[#0D5BA8] flex items-center justify-center font-bold font-heading mb-4">
              I
            </div>
            <span className="text-[11px] font-bold text-[#0D5BA8] uppercase">20 Horas</span>
            <h3 className="font-heading font-bold text-base text-slate-900 mt-1 mb-2">
              Mapeamento Territorial
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Pesquisa de campo, cartografia afetiva, reconhecimento de mestres e criação do banco de dados georreferenciado.
            </p>
          </div>

          {/* Eixo 2 */}
          <div className="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all border-t-4 border-t-[#00A7B5]">
            <div className="w-10 h-10 rounded-xl bg-teal-50 text-[#00A7B5] flex items-center justify-center font-bold font-heading mb-4">
              II
            </div>
            <span className="text-[11px] font-bold text-[#00A7B5] uppercase">14 Horas</span>
            <h3 className="font-heading font-bold text-base text-slate-900 mt-1 mb-2">
              Fotografia e Audiovisual
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Registro documental, narrativa visual, técnicas de entrevista e produção de vídeo e ensaios com smartphone.
            </p>
          </div>

          {/* Eixo 3 */}
          <div className="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all border-t-4 border-t-[#FF8A00]">
            <div className="w-10 h-10 rounded-xl bg-orange-50 text-[#FF8A00] flex items-center justify-center font-bold font-heading mb-4">
              III
            </div>
            <span className="text-[11px] font-bold text-[#FF8A00] uppercase">10 Horas</span>
            <h3 className="font-heading font-bold text-base text-slate-900 mt-1 mb-2">
              Comunicação Comunitária
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Produção de conteúdo, fanzines, canais digitais, mobilização, difusão e articulação na rede intersetorial.
            </p>
          </div>

          {/* Eixo 4 */}
          <div className="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all border-t-4 border-t-[#FFC107]">
            <div className="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold font-heading mb-4">
              IV
            </div>
            <span className="text-[11px] font-bold text-amber-700 uppercase">12 Horas</span>
            <h3 className="font-heading font-bold text-base text-slate-900 mt-1 mb-2">
              Produção & Ocupação
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Curadoria, planejamento, ocupação de espaços públicos, mostras fotográficas e formação de plateia.
            </p>
          </div>
        </div>
      </section>

    </div>
  );
};
