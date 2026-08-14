<?php
/**
 * Componente Navbar Principal (PHP Modular)
 */
require_once __DIR__ . '/logo.php';
$user = get_user_profile();
$currentTab = $activeTab ?? 'inicio';

$navLinks = [
    ['id' => 'inicio', 'url' => 'index.php', 'label' => 'Início', 'icon' => 'compass'],
    ['id' => 'trilha', 'url' => 'trilha.php', 'label' => 'A Trilha', 'icon' => 'book-open'],
    ['id' => 'vitrine', 'url' => 'vitrine.php', 'label' => 'Vitrine', 'icon' => 'grid'],
    ['id' => 'mapa', 'url' => 'mapa.php', 'label' => 'Mapa', 'icon' => 'map-pin'],
    ['id' => 'parceiros', 'url' => 'parceiros.php', 'label' => 'Parceiros', 'icon' => 'users'],
];
?>
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#E6E6E6] shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Brand Logo -->
            <div class="flex items-center">
                <?php render_logo('md', true, 'index.php'); ?>
            </div>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                <?php foreach ($navLinks as $link): 
                    $isActive = ($currentTab === $link['id']);
                ?>
                    <a href="<?php echo $link['url']; ?>"
                       class="px-3.5 py-2 rounded-lg font-medium text-sm transition-all duration-200 relative flex items-center gap-1.5 <?php echo $isActive ? 'text-[#0D5BA8] font-bold bg-blue-50/80 shadow-2xs' : 'text-[#333333] hover:text-[#00A7B5] hover:bg-slate-50'; ?>">
                        <i data-lucide="<?php echo $link['icon']; ?>" class="w-4 h-4 opacity-75"></i>
                        <span><?php echo $link['label']; ?></span>
                        <?php if ($isActive): ?>
                            <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-[#FF8A00] rounded-full"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Right Action Area -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Notifications Bell Trigger -->
                <button onclick="openModal('modal-notifications')"
                        title="Notificações e Avisos da Trilha"
                        class="relative p-2 text-slate-600 hover:text-[#0D5BA8] hover:bg-slate-100 rounded-full transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-[#FF8A00] rounded-full ring-2 ring-white animate-pulse"></span>
                </button>

                <!-- Quick Profile / Student Pill -->
                <a href="perfil.php"
                   class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-full border transition-all <?php echo $currentTab === 'perfil' ? 'border-[#0D5BA8] bg-blue-50/60 shadow-xs' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'; ?>">
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                         alt="<?php echo htmlspecialchars($user['nome']); ?>"
                         class="w-8 h-8 rounded-full object-cover ring-2 ring-[#00A7B5]" />
                    <div class="text-left hidden lg:block">
                        <p class="text-xs font-bold text-[#0D5BA8] leading-tight flex items-center gap-1">
                            <?php echo htmlspecialchars($user['nome']); ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        </p>
                        <p class="text-[10px] text-slate-500 leading-tight">Painel do Aluno</p>
                    </div>
                </a>
            </div>

            <!-- Mobile menu trigger -->
            <div class="flex md:hidden items-center space-x-2">
                <button onclick="openModal('modal-notifications')" class="p-2 text-slate-600 hover:text-[#0D5BA8] rounded-lg">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>
                <button id="btn-mobile-menu-toggle" class="p-2 text-slate-700 hover:text-[#0D5BA8] rounded-lg focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div id="mobile-drawer-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-2 shadow-lg">
        <?php foreach ($navLinks as $link): 
            $isActive = ($currentTab === $link['id']);
        ?>
            <a href="<?php echo $link['url']; ?>"
               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left font-medium text-sm <?php echo $isActive ? 'bg-blue-50 text-[#0D5BA8] font-bold border-l-4 border-[#FF8A00]' : 'text-slate-700 hover:bg-slate-50'; ?>">
                <i data-lucide="<?php echo $link['icon']; ?>" class="w-5 h-5 <?php echo $isActive ? 'text-[#0D5BA8]' : 'text-slate-400'; ?>"></i>
                <?php echo $link['label']; ?>
            </a>
        <?php endforeach; ?>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <a href="perfil.php" class="flex items-center gap-3 text-left w-full py-2 px-2 rounded-lg hover:bg-slate-50">
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                     alt="<?php echo htmlspecialchars($user['nome']); ?>"
                     class="w-10 h-10 rounded-full object-cover ring-2 ring-[#00A7B5]" />
                <div>
                    <p class="text-sm font-bold text-[#0D5BA8]"><?php echo htmlspecialchars($user['nome']); ?></p>
                    <p class="text-xs text-slate-500">Painel do Aluno (<?php echo $user['progressoTrilha']; ?>% concluído)</p>
                </div>
            </a>
        </div>
    </div>
</header>
