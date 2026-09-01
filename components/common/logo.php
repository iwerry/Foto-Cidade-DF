<?php
/**
 * Componente Logo FotoCidade (PNG Oficial)
 * - Navbar / Fundo Claro: public/LogoNavBar.png (Colorida)
 * - Footer / Fundo Escuro: public/LogoMenus.png (Branca)
 */
function render_logo($size = 'md', $showSlogan = true, $linkUrl = 'index.php', $extraClass = '', $variant = 'color') {
    // Se variant for 'white' ou 'footer', usa a logo branca
    $isWhite = ($variant === 'white' || $variant === 'footer');
    $logoSrc = $isWhite ? 'public/LogoMenus.png' : 'public/LogoNavBar.png';
    
    // Dimensões com presença visual marcante no layout
    $heightClass = 'h-14 sm:h-16 md:h-18';
    if ($size === 'lg') {
        $heightClass = 'h-16 sm:h-20';
    } elseif ($size === 'sm') {
        $heightClass = 'h-11 sm:h-12';
    }
    ?>
    <a href="<?php echo htmlspecialchars($linkUrl); ?>" class="inline-flex items-center select-none group <?php echo htmlspecialchars($extraClass); ?>">
        <img src="<?php echo htmlspecialchars($logoSrc); ?>" 
             onerror="this.src='public/LogoNavBar.png'"
             alt="FotoCidade DF" 
             style="max-height: 64px;"
             class="<?php echo $heightClass; ?> w-auto object-contain transition-transform duration-200 group-hover:scale-105" />
    </a>
    <?php
}



