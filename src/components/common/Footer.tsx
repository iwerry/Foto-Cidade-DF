import React from 'react';
import { Logo } from './Logo';
import { MapPin, Mail, Phone, Heart, Compass, Camera, Megaphone, Sparkles, ExternalLink } from 'lucide-react';

interface FooterProps {
  onNavigate: (tab: string) => void;
}

export const Footer: React.FC<FooterProps> = ({ onNavigate }) => {
  return (
    <footer className="bg-[#1E293B] text-slate-300 pt-16 pb-12 border-t border-slate-700">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-700/80">
          
          {/* Column 1: Brand & Slogan */}
          <div className="lg:col-span-2 space-y-4">
            <div className="bg-white/95 p-3 rounded-xl inline-block shadow-sm">
              <Logo size="md" />
            </div>
            <p className="text-slate-300 text-sm leading-relaxed max-w-md">
              Uma trilha de formação em Mapeamento Territorial, Produção Cultural e Comunicação Comunitária. 
              Transformando moradores em pesquisadores, comunicadores e articuladores de seus próprios territórios.
            </p>
            <div className="flex items-center gap-2 text-xs font-semibold text-[#FF8A00] tracking-wide uppercase pt-1">
              <span>Sobradinho</span>
              <span>•</span>
              <span>Fercal</span>
              <span>•</span>
              <span>Grande Colorado</span>
              <span>•</span>
              <span>Distrito Federal</span>
            </div>
          </div>

          {/* Column 2: Eixos de Formação */}
          <div>
            <h4 className="text-white font-bold text-sm tracking-wide uppercase mb-4 flex items-center gap-1.5">
              <Compass className="w-4 h-4 text-[#00A7B5]" />
              Eixos da Trilha
            </h4>
            <ul className="space-y-2.5 text-xs text-slate-300">
              <li>
                <button 
                  onClick={() => onNavigate('trilha')}
                  className="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5"
                >
                  <span className="w-1.5 h-1.5 rounded-full bg-[#0D5BA8]"></span>
                  I – Mapeamento Territorial (20h)
                </button>
              </li>
              <li>
                <button 
                  onClick={() => onNavigate('trilha')}
                  className="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5"
                >
                  <span className="w-1.5 h-1.5 rounded-full bg-[#00A7B5]"></span>
                  II – Fotografia & Audiovisual (14h)
                </button>
              </li>
              <li>
                <button 
                  onClick={() => onNavigate('trilha')}
                  className="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5"
                >
                  <span className="w-1.5 h-1.5 rounded-full bg-[#FF8A00]"></span>
                  III – Comunicação Comunitária (10h)
                </button>
              </li>
              <li>
                <button 
                  onClick={() => onNavigate('trilha')}
                  className="hover:text-[#FF8A00] text-left transition-colors flex items-center gap-1.5"
                >
                  <span className="w-1.5 h-1.5 rounded-full bg-[#FFC107]"></span>
                  IV – Produção Cultural (12h)
                </button>
              </li>
            </ul>
          </div>

          {/* Column 3: Módulos & Recursos */}
          <div>
            <h4 className="text-white font-bold text-sm tracking-wide uppercase mb-4 flex items-center gap-1.5">
              <Camera className="w-4 h-4 text-[#FF8A00]" />
              Plataforma
            </h4>
            <ul className="space-y-2 text-xs text-slate-300">
              <li>
                <button onClick={() => onNavigate('mapa')} className="hover:text-white transition-colors">
                  Mapa Cultural Georreferenciado
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('vitrine')} className="hover:text-white transition-colors">
                  Vitrine do Território
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('parceiros')} className="hover:text-white transition-colors">
                  Rede Intersetorial & Parceiros
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('parceiros')} className="hover:text-white transition-colors">
                  Banco de Talentos Locais
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('perfil')} className="hover:text-white transition-colors">
                  Painel do Agente Cultural
                </button>
              </li>
            </ul>
          </div>

          {/* Column 4: Contato & Rede */}
          <div>
            <h4 className="text-white font-bold text-sm tracking-wide uppercase mb-4 flex items-center gap-1.5">
              <Mail className="w-4 h-4 text-[#FFC107]" />
              Contato & Pautas
            </h4>
            <div className="space-y-3 text-xs text-slate-300">
              <p className="flex items-start gap-2">
                <MapPin className="w-4 h-4 text-[#00A7B5] shrink-0 mt-0.5" />
                <span>Sobradinho e Fercal, DF - Brasil</span>
              </p>
              <p className="flex items-center gap-2">
                <Mail className="w-4 h-4 text-[#00A7B5] shrink-0" />
                <a href="mailto:contato@fotocidade.org" className="hover:underline">contato@fotocidade.org</a>
              </p>
              <div className="pt-2">
                <button
                  onClick={() => onNavigate('parceiros')}
                  className="w-full text-center px-3 py-2 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold rounded-lg text-xs transition-colors shadow-xs"
                >
                  Enviar Pauta para a Agenda
                </button>
              </div>
            </div>
          </div>

        </div>

        {/* Bottom Bar */}
        <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
          <p>© {new Date().getFullYear()} FotoCidade. "Aprender o território, transformar a cidade."</p>
          <div className="flex items-center gap-4">
            <span className="flex items-center gap-1 text-slate-300">
              Feito com <Heart className="w-3.5 h-3.5 text-rose-500 fill-rose-500" /> pela Cultura Comunitária
            </span>
          </div>
        </div>
      </div>
    </footer>
  );
};
