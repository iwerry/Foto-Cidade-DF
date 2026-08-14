import React, { useState } from 'react';
import { Logo } from './Logo';
import { UserProfile } from '../../types';
import { Bell, Menu, X, Compass, Award, User, MapPin, Grid, Users, Sparkles, BookOpen } from 'lucide-react';

interface NavbarProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  user: UserProfile;
  onOpenNotifications?: () => void;
  onOpenInscription?: () => void;
}

export const Navbar: React.FC<NavbarProps> = ({
  activeTab,
  setActiveTab,
  user,
  onOpenNotifications,
  onOpenInscription
}) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [hasUnreadNotification, setHasUnreadNotification] = useState(true);

  const navLinks = [
    { id: 'inicio', label: 'Início', icon: Compass },
    { id: 'trilha', label: 'A Trilha', icon: BookOpen },
    { id: 'vitrine', label: 'Vitrine', icon: Grid },
    { id: 'mapa', label: 'Mapa', icon: MapPin },
    { id: 'parceiros', label: 'Parceiros', icon: Users },
  ];

  const handleNavClick = (tabId: string) => {
    setActiveTab(tabId);
    setMobileMenuOpen(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#E6E6E6] shadow-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          {/* Brand Logo */}
          <div className="flex items-center">
            <Logo 
              size="md" 
              onClick={() => handleNavClick('inicio')}
            />
          </div>

          {/* Desktop Navigation Links */}
          <nav className="hidden md:flex items-center space-x-1 lg:space-x-2">
            {navLinks.map((link) => {
              const isActive = activeTab === link.id;
              return (
                <button
                  key={link.id}
                  id={`nav-link-${link.id}`}
                  onClick={() => handleNavClick(link.id)}
                  className={`px-3.5 py-2 rounded-lg font-medium text-sm transition-all duration-200 relative ${
                    isActive
                      ? 'text-[#0D5BA8] font-bold bg-blue-50/80 shadow-2xs'
                      : 'text-[#333333] hover:text-[#00A7B5] hover:bg-slate-50'
                  }`}
                >
                  {link.label}
                  {isActive && (
                    <span className="absolute bottom-0 left-3 right-3 h-0.5 bg-[#FF8A00] rounded-full"></span>
                  )}
                </button>
              );
            })}
          </nav>

          {/* Right Action Area (Notifications & User Avatar / Quick CTA) */}
          <div className="hidden md:flex items-center space-x-4">
            {/* Notification Bell */}
            <button
              id="btn-notifications"
              onClick={() => {
                setHasUnreadNotification(false);
                if (onOpenNotifications) onOpenNotifications();
              }}
              title="Notificações e Avisos da Trilha"
              aria-label="Notificações e Avisos da Trilha"
              className="relative p-2 text-slate-600 hover:text-[#0D5BA8] hover:bg-slate-100 rounded-full transition-colors"
            >
              <Bell className="w-5 h-5" />
              {hasUnreadNotification && (
                <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-[#FF8A00] rounded-full ring-2 ring-white animate-pulse"></span>
              )}
            </button>

            {/* Quick Inscription / User Pill */}
            <button
              id="btn-profile-header"
              onClick={() => handleNavClick('perfil')}
              className={`flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-full border transition-all ${
                activeTab === 'perfil'
                  ? 'border-[#0D5BA8] bg-blue-50/60 shadow-xs'
                  : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
              }`}
            >
              <img
                src={user.avatar}
                alt={user.nome}
                className="w-8 h-8 rounded-full object-cover ring-2 ring-[#00A7B5]"
              />
              <div className="text-left hidden lg:block">
                <p className="text-xs font-bold text-[#0D5BA8] leading-tight flex items-center gap-1">
                  {user.nome}
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </p>
                <p className="text-[10px] text-slate-500 leading-tight">Painel do Aluno</p>
              </div>
            </button>
          </div>

          {/* Mobile menu trigger */}
          <div className="flex md:hidden items-center space-x-2">
            <button
              onClick={() => {
                setHasUnreadNotification(false);
                if (onOpenNotifications) onOpenNotifications();
              }}
              title="Notificações da Trilha"
              aria-label="Notificações da Trilha"
              className="p-2 text-slate-600 hover:text-[#0D5BA8] rounded-lg"
            >
              <Bell className="w-5 h-5" />
            </button>
            <button
              id="btn-mobile-menu-toggle"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              title="Abrir Menu Principal"
              aria-label="Abrir Menu Principal"
              className="p-2 text-slate-700 hover:text-[#0D5BA8] rounded-lg focus:outline-none"
            >
              {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Drawer Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-2 shadow-lg animate-in slide-in-from-top-2">
          {navLinks.map((link) => {
            const Icon = link.icon;
            const isActive = activeTab === link.id;
            return (
              <button
                key={link.id}
                onClick={() => handleNavClick(link.id)}
                className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left font-medium text-sm ${
                  isActive
                    ? 'bg-blue-50 text-[#0D5BA8] font-bold border-l-4 border-[#FF8A00]'
                    : 'text-slate-700 hover:bg-slate-50'
                }`}
              >
                <Icon className={`w-5 h-5 ${isActive ? 'text-[#0D5BA8]' : 'text-slate-400'}`} />
                {link.label}
              </button>
            );
          })}
          
          <div className="pt-3 border-t border-slate-100 flex items-center justify-between">
            <button
              onClick={() => handleNavClick('perfil')}
              className="flex items-center gap-3 text-left w-full py-2 px-2 rounded-lg hover:bg-slate-50"
            >
              <img
                src={user.avatar}
                alt={user.nome}
                className="w-10 h-10 rounded-full object-cover ring-2 ring-[#00A7B5]"
              />
              <div>
                <p className="text-sm font-bold text-[#0D5BA8]">{user.nome}</p>
                <p className="text-xs text-slate-500">Painel do Aluno ({user.progressoTrilha}% concluído)</p>
              </div>
            </button>
          </div>
        </div>
      )}
    </header>
  );
};
