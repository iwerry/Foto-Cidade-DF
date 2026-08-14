import { CategoryType } from '../types';

export const getCategoryColor = (category: CategoryType | string) => {
  switch (category) {
    case 'Artista':
      return {
        bg: 'bg-[#FF8A00]',
        text: 'text-[#FF8A00]',
        border: 'border-[#FF8A00]',
        badge: 'bg-orange-50 text-[#FF8A00] border-orange-200',
        markerBg: '#FF8A00'
      };
    case 'Espaço':
    case 'Espaço Cultural':
      return {
        bg: 'bg-[#0D5BA8]',
        text: 'text-[#0D5BA8]',
        border: 'border-[#0D5BA8]',
        badge: 'bg-blue-50 text-[#0D5BA8] border-blue-200',
        markerBg: '#0D5BA8'
      };
    case 'Informação':
    case 'Ponto de Informação':
      return {
        bg: 'bg-[#00A7B5]',
        text: 'text-[#00A7B5]',
        border: 'border-[#00A7B5]',
        badge: 'bg-teal-50 text-[#00A7B5] border-teal-200',
        markerBg: '#00A7B5'
      };
    case 'Liderança':
      return {
        bg: 'bg-[#FFC107]',
        text: 'text-amber-700',
        border: 'border-[#FFC107]',
        badge: 'bg-amber-50 text-amber-800 border-amber-200',
        markerBg: '#FFC107'
      };
    case 'Patrimônio':
      return {
        bg: 'bg-indigo-600',
        text: 'text-indigo-600',
        border: 'border-indigo-600',
        badge: 'bg-indigo-50 text-indigo-700 border-indigo-200',
        markerBg: '#4F46E5'
      };
    default:
      return {
        bg: 'bg-[#00A7B5]',
        text: 'text-[#00A7B5]',
        border: 'border-[#00A7B5]',
        badge: 'bg-teal-50 text-[#00A7B5] border-teal-200',
        markerBg: '#00A7B5'
      };
  }
};

export const getTipoVitrineColor = (tipo: 'Foto' | 'Vídeo' | 'Perfil' | 'Espaço') => {
  switch (tipo) {
    case 'Foto':
      return 'bg-[#00A7B5] text-white';
    case 'Vídeo':
      return 'bg-[#FF8A00] text-white';
    case 'Perfil':
      return 'bg-[#0D5BA8] text-white';
    case 'Espaço':
      return 'bg-[#FFC107] text-[#333333] font-semibold';
    default:
      return 'bg-[#00A7B5] text-white';
  }
};
