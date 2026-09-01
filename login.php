<?php
/**
 * FotoCidade DF - Página de Autenticação Unificada (login.php)
 *
 * Acesso para Administradores (Dashboard) e Alunos (Painel do Aluno e Trilha).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

// Se já estiver logado, redireciona conforme o nível
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: dashboard.php");
    } else {
        header("Location: perfil.php");
    }
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrfToken)) {
        $erro = 'Sessão expirada. Por favor, tente novamente.';
    } else {
        $auth = authenticate_user($email, $senha);
        if ($auth['success']) {
            if ($auth['user']['nivel'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: trilha.php");
            }
            exit;
        } else {
            $erro = $auth['message'];
        }
    }
}

$pageTitle = 'Acesso ao Sistema';
require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/logo.php';
?>

<div class="min-h-screen bg-gradient-to-b from-slate-900 via-[#0D5BA8] to-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center space-y-4">
        <div class="inline-block bg-white/95 p-4 rounded-3xl shadow-2xl backdrop-blur-md">
            <?php render_logo('lg', true, 'index.php'); ?>
        </div>
        
        <h2 class="text-2xl font-black font-heading text-white tracking-tight">
            Acesso ao FotoCidade DF
        </h2>
        <p class="text-xs text-blue-200">
            Ambiente Integrado de Gestão, Formação e Cartografia Comunitária
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
        <div class="bg-white/95 backdrop-blur-xl py-8 px-6 sm:px-10 shadow-2xl rounded-3xl border border-white/20">
            
            <?php if (!empty($erro)): ?>
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-center gap-3 text-rose-700 text-xs animate-shake">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-500"></i>
                    <span><?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-5" method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        E-mail ou Usuário
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input id="email" 
                               name="email" 
                               type="text" 
                               autocomplete="username" 
                               required
                               placeholder="seu.email@fotocidade.org ou usuário"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="block w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0D5BA8] focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="senha" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Senha
                        </label>
                    </div>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input id="senha" 
                               name="senha" 
                               type="password" 
                               autocomplete="current-password" 
                               required
                               placeholder="••••••••"
                               class="block w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0D5BA8] focus:border-transparent transition-all">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" id="icon-toggle-pass" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full py-3.5 px-4 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold font-heading text-sm rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition-all flex items-center justify-center gap-2 group cursor-pointer">
                        <span>Entrar no Sistema</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-xs font-semibold text-slate-500 hover:text-[#0D5BA8] transition-colors inline-flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Voltar para o site público</span>
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('senha');
    const icon = document.getElementById('icon-toggle-pass');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}
</script>

<?php
// Não inclui o footer completo na tela de login para manter a imersão
?>
</body>
</html>
