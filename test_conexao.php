<?php
/**
 * FotoCidade DF - Verificador de Conexão MySQL (test_conexao.php)
 *
 * Exibe relatório visual completo do status da conexão com o banco de dados.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/conexao.php';

$pdo = get_db_connection();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão MySQL • FotoCidade DF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8 font-sans">
    <div class="max-w-2xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-black text-white font-heading">Status do Banco de Dados</h1>
            <p class="text-xs text-slate-400">Verificação em tempo real da conexão MySQL (PDO) no servidor</p>
        </div>

        <?php if ($pdo): ?>
            <?php
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            ?>
            <!-- Success Card -->
            <div class="bg-slate-800 border border-emerald-500/40 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl font-black">
                        ✓
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">
                            Conexão Ativa & Operacional
                        </span>
                        <h2 class="text-xl font-bold text-white mt-1">Conectado ao MySQL com Sucesso!</h2>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs">
                    <div class="p-3.5 bg-slate-900/70 rounded-xl border border-slate-700 space-y-1">
                        <span class="text-slate-400 font-bold text-[10px] uppercase">Banco Conectado:</span>
                        <p class="font-mono text-emerald-400 font-bold text-sm"><?php echo htmlspecialchars($dbName); ?></p>
                    </div>
                    <div class="p-3.5 bg-slate-900/70 rounded-xl border border-slate-700 space-y-1">
                        <span class="text-slate-400 font-bold text-[10px] uppercase">Versão do MySQL:</span>
                        <p class="font-mono text-cyan-400 font-bold text-sm"><?php echo htmlspecialchars($version); ?></p>
                    </div>
                </div>

                <!-- Tables List -->
                <div class="space-y-3 pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">
                        Tabelas Encontradas no Banco (<?php echo count($tables); ?>):
                    </h3>

                    <?php if (!empty($tables)): ?>
                        <div class="divide-y divide-slate-700 bg-slate-900/80 rounded-2xl border border-slate-700 overflow-hidden text-xs">
                            <?php foreach ($tables as $table): ?>
                                <?php
                                $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                                ?>
                                <div class="p-3 flex items-center justify-between hover:bg-slate-800/60 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-400 font-mono font-bold">▦ <?php echo htmlspecialchars($table); ?></span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                        <?php echo $count; ?> registros
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-amber-950/40 border border-amber-800 text-amber-200 rounded-xl text-xs">
                            Nenhuma tabela encontrada ainda. Execute <a href="db_init.php" class="underline font-bold text-amber-300">db_init.php</a> para criar a estrutura inicial.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-700">
                    <a href="login.php" class="flex-1 py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl text-center shadow-lg transition-all">
                        Ir para Login
                    </a>
                    <a href="index.php" class="flex-1 py-3 bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs rounded-xl text-center transition-all">
                        Acessar Site Público
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Error Card -->
            <div class="bg-slate-800 border border-rose-500/40 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl font-black">
                        ✕
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 uppercase tracking-wider">
                            Falha na Conexão
                        </span>
                        <h2 class="text-xl font-bold text-white mt-1">Não foi possível conectar ao MySQL</h2>
                    </div>
                </div>

                <div class="p-4 bg-rose-950/50 border border-rose-800 text-rose-200 rounded-xl text-xs leading-relaxed space-y-2">
                    <p class="font-bold text-rose-300">Mensagem do Servidor MySQL:</p>
                    <p class="font-mono bg-black/40 p-2.5 rounded-lg border border-rose-900 text-rose-300 text-[11px]">
                        <?php echo htmlspecialchars($lastDbError ?: 'Access denied ou falha de comunicação'); ?>
                    </p>
                    <div class="pt-2 space-y-1">
                        <p class="font-semibold text-white">💡 Passo a passo no cPanel para resolver em 1 minuto:</p>
                        <ol class="list-decimal pl-4 space-y-1 text-slate-300">
                            <li>Vá na tela <strong>Bancos de dados MySQL</strong> do seu cPanel.</li>
                            <li>Role até a seção <strong>Adicionar usuário ao banco de dados</strong> (Add User to Database).</li>
                            <li>Selecione o Usuário: <code class="text-amber-300 font-bold">draftcre_fcity</code>.</li>
                            <li>Selecione o Banco: <code class="text-amber-300 font-bold">draftcre_fotocidade</code>.</li>
                            <li>Clique em <strong>Adicionar</strong> (Add).</li>
                            <li>Marque a opção <strong>TODOS OS PRIVILÉGIOS (ALL PRIVILEGES)</strong> e clique em <strong>Fazer Alterações</strong> (Make Changes).</li>
                        </ol>
                    </div>
                </div>

                <a href="test_conexao.php" class="block w-full py-3 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl text-center shadow-lg transition-all">
                    Testar Conexão Novamente
                </a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
