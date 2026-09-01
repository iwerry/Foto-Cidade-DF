<?php
/**
 * Componente Logo FotoCidade (PNG Oficial)
 * - Modo Completo / Padrão / Footer / Deslogado: public/LogoNavBar.png
 * - Modo Compacto / Logado / Painel Gestor / Menus: public/LogoMenus.png
 */
function render_logo($size = 'md', $showSlogan = true, $linkUrl = 'index.php', $extraClass = '', $variant = 'auto') {
    // Se variant for 'auto', detecta se o usuário está logado
    if ($variant === 'auto') {
        $isLogged = function_exists('is_logged_in') && is_logged_in();
        $variant = $isLogged ? 'compact' : 'full';
    }

    $isCompact = ($variant === 'compact' || $variant === 'menu' || $size === 'sm' || !$showSlogan);
    $logoSrc = $isCompact ? 'public/LogoMenus.png' : 'public/LogoNavBar.png';
    
    $heightClass = 'h-10 sm:h-12';
    if ($isCompact) {
        $heightClass = 'h-8 sm:h-9';
    } elseif ($size === 'lg') {
        $heightClass = 'h-12 sm:h-14';
    } elseif ($size === 'sm') {
        $heightClass = 'h-8 sm:h-9';
    }
    ?>
    <a href="<?php echo htmlspecialchars($linkUrl); ?>" class="inline-flex items-center select-none group <?php echo htmlspecialchars($extraClass); ?>">
        <img src="<?php echo htmlspecialchars($logoSrc); ?>" 
             onerror="this.src='public/LogoNavBar.png'"
             alt="FotoCidade DF" 
             class="<?php echo $heightClass; ?> w-auto object-contain transition-transform duration-200 group-hover:scale-102" />
    </a>
    <?php
}

