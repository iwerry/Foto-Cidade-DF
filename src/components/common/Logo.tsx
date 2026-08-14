import React from 'react';

interface LogoProps {
  variant?: 'full' | 'icon' | 'horizontal';
  size?: 'sm' | 'md' | 'lg' | 'xl';
  showSlogan?: boolean;
  className?: string;
  onClick?: () => void;
}

export const Logo: React.FC<LogoProps> = ({
  variant = 'full',
  size = 'md',
  showSlogan = true,
  className = '',
  onClick
}) => {
  const iconSizes = {
    sm: 'w-7 h-7',
    md: 'w-10 h-10',
    lg: 'w-14 h-14',
    xl: 'w-20 h-20'
  };

  const textSizes = {
    sm: 'text-lg',
    md: 'text-2xl',
    lg: 'text-3xl',
    xl: 'text-4xl'
  };

  const sloganSizes = {
    sm: 'text-[8px] tracking-wider',
    md: 'text-[9.5px] tracking-widest',
    lg: 'text-xs tracking-widest',
    xl: 'text-sm tracking-widest'
  };

  const LogoIcon = (
    <div className={`relative ${iconSizes[size]} shrink-0 flex items-center justify-center`}>
      <svg viewBox="0 0 160 160" className="w-full h-full drop-shadow-sm" fill="none" xmlns="http://www.w3.org/2000/svg">
        {/* Shutter Blade 1 (Top Blue) */}
        <path d="M 80 10 C 110 10, 140 30, 150 60 L 105 75 L 75 40 Z" fill="#0D5BA8" />
        
        {/* Shutter Blade 2 (Right Orange) */}
        <path d="M 150 60 C 158 90, 145 125, 125 145 L 95 105 L 115 80 Z" fill="#FF8A00" />
        
        {/* Shutter Blade 3 (Bottom Teal) */}
        <path d="M 125 145 C 95 160, 50 155, 25 130 L 65 100 L 95 115 Z" fill="#00A7B5" />
        
        {/* Shutter Blade 4 (Left Bottom Dark Cyan) */}
        <path d="M 25 130 C 5 105, 5 65, 25 35 L 60 70 L 45 95 Z" fill="#0284C7" />
        
        {/* Shutter Blade 5 (Left Top Navy) */}
        <path d="M 25 35 C 45 15, 65 10, 80 10 L 80 50 L 50 45 Z" fill="#0369A1" />

        {/* Central Aperture Window Background */}
        <circle cx="80" cy="85" r="48" fill="#FFFFFF" />

        {/* Internal City Skyline & Landscape */}
        {/* Sky / subtle background tone */}
        <path d="M 35 95 C 40 55, 120 55, 125 95 Z" fill="#F0F9FF" />

        {/* Buildings (Gray/Navy) */}
        <rect x="68" y="52" width="16" height="38" rx="2" fill="#475569" />
        <rect x="74" y="44" width="14" height="46" rx="2" fill="#64748B" />
        
        {/* Houses */}
        {/* House 1 */}
        <path d="M 46 82 L 56 72 L 66 82 Z" fill="#0D5BA8" />
        <rect x="48" y="82" width="16" height="14" fill="#E2E8F0" />
        <rect x="53" y="86" width="6" height="10" fill="#0D5BA8" />
        
        {/* House 2 */}
        <path d="M 88 82 L 98 72 L 108 82 Z" fill="#00A7B5" />
        <rect x="90" y="82" width="16" height="14" fill="#E2E8F0" />
        <rect x="95" y="86" width="6" height="10" fill="#00A7B5" />

        {/* Trees */}
        <ellipse cx="44" cy="74" rx="8" ry="12" fill="#00A7B5" />
        <ellipse cx="118" cy="75" rx="7" ry="11" fill="#00A7B5" />
        <rect x="43" y="84" width="2" height="6" fill="#334155" />
        <rect x="117" y="84" width="2" height="6" fill="#334155" />

        {/* Terraced Streets / River Segments (Blue & Teal roads) */}
        <path d="M 34 100 L 78 100 L 78 112 L 38 112 Z" fill="#00A7B5" />
        <path d="M 82 100 L 126 100 L 122 112 L 82 112 Z" fill="#0284C7" />
        <path d="M 40 116 L 86 116 L 86 128 L 52 128 Z" fill="#00A7B5" />
        <path d="M 90 116 L 120 116 L 112 130 L 90 130 Z" fill="#0D5BA8" />

        {/* Orange Location Pin Marker */}
        <g transform="translate(100, 50)">
          <path d="M 8 0 C 3.5 0, 0 3.5, 0 8 C 0 14, 8 22, 8 22 C 8 22, 16 14, 16 8 C 16 3.5, 12.5 0, 8 0 Z" fill="#FF8A00" />
          <circle cx="8" cy="8" r="3.2" fill="#FFFFFF" />
        </g>
      </svg>
    </div>
  );

  if (variant === 'icon') {
    return (
      <div 
        onClick={onClick} 
        className={`inline-flex items-center cursor-pointer select-none ${className}`}
        role="button"
        tabIndex={0}
      >
        {LogoIcon}
      </div>
    );
  }

  return (
    <div 
      onClick={onClick} 
      className={`inline-flex items-center gap-3 cursor-pointer select-none group ${className}`}
      role="button"
      tabIndex={0}
    >
      {LogoIcon}
      
      <div className="flex flex-col">
        <div className={`font-black tracking-tight leading-none ${textSizes[size]} font-heading flex items-center`}>
          <span className="text-[#0D5BA8] relative">
            <span className="inline-block relative">
              <span className="absolute -top-1 left-0 w-2.5 h-1 bg-[#FF8A00] rounded-sm"></span>
              F
            </span>
            oto
          </span>
          <span className="text-[#00A7B5] ml-0.5">Cidade</span>
        </div>
        
        {showSlogan && (
          <div className={`font-bold text-[#333333] opacity-80 uppercase mt-0.5 ${sloganSizes[size]}`}>
            <span className="text-[#0D5BA8]">Olhar</span>
            <span className="text-[#FF8A00] mx-1">•</span>
            <span className="text-[#00A7B5]">Registrar</span>
            <span className="text-[#FF8A00] mx-1">•</span>
            <span className="text-[#FFC107]">Revelar Talentos</span>
          </div>
        )}
      </div>
    </div>
  );
};
