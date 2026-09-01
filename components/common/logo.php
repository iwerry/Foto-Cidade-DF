<?php
/**
 * Componente Logo FotoCidade (PNG Oficial)
 * - Navbar / Fundo Claro: public/LogoNavBar.png (Colorida)
 * - Footer / Login / Fundo Escuro: public/LogoMenus.png (Branca)
 */
function render_logo($size = 'md', $showSlogan = true, $linkUrl = 'index.php', $extraClass = '', $variant = 'color') {
    // Se variant for 'white' ou 'footer', usa a logo branca
    $isWhite = ($variant === 'white' || $variant === 'footer');
    $logoSrc = $isWhite ? 'public/LogoMenus.png' : 'public/LogoNavBar.png';
    
    // Dimensões com presença visual marcante e proporção adequada
    $heightStyle = 'height: 68px; max-height: 72px;';
    if ($size === 'xl') {
        $heightStyle = 'height: 84px; max-height: 96px;';
    } elseif ($size === 'lg') {
        $heightStyle = 'height: 72px; max-height: 80px;';
    } elseif ($size === 'sm') {
        $heightStyle = 'height: 48px; max-height: 52px;';
    }
    ?>
    <a href="<?php echo htmlspecialchars($linkUrl); ?>" class="inline-flex items-center select-none group <?php echo htmlspecialchars($extraClass); ?>">
        <img src="<?php echo htmlspecialchars($logoSrc); ?>" 
             onerror="this.src='public/LogoNavBar.png'"
             alt="FotoCidade DF" 
             style="<?php echo $heightStyle; ?> width: auto;"
             class="object-contain transition-transform duration-200 group-hover:scale-105" />
    </a>
    <?php
}




