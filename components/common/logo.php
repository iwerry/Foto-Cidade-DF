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

    $isCompact = ($variant === 'compact' || $variant === 'menu' || !$showSlogan);
    $logoSrc = $isCompact ? 'public/LogoMenus.png' : 'public/LogoNavBar.png';
    
    // Dimensões proporcionais e destacadas no layout
    $heightClass = 'h-14 sm:h-16';
    if ($isCompact) {
        $heightClass = 'h-11 sm:h-12';
    } elseif ($size === 'lg') {
        $heightClass = 'h-16 sm:h-20';
    } elseif ($size === 'sm') {
        $heightClass = 'h-10 sm:h-11';
    }
    ?>
    <a href="<?php echo htmlspecialchars($linkUrl); ?>" class="inline-flex items-center select-none group <?php echo htmlspecialchars($extraClass); ?>">
        <img src="<?php echo htmlspecialchars($logoSrc); ?>" 
             onerror="this.src='public/LogoNavBar.png'"
             alt="FotoCidade DF" 
             class="<?php echo $heightClass; ?> w-auto object-contain transition-transform duration-200 group-hover:scale-105" />
    </a>
    <?php
}


