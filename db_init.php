<?php
/**
 * FotoCidade DF - Script de Instalação e Migração de Banco de Dados
 *
 * Cria as tabelas do MySQL automaticamente e importa todos os registros
 * dos arquivos JSON locais para o banco de dados.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/conexao.php';

// Cria diretório de uploads locais se não existir
$uploadsDir = ROOT_PATH . '/assets/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
    // Cria proteção para impedir execução de scripts em uploads
    $htaccessUploads = $uploadsDir . '/.htaccess';
    if (!file_exists($htaccessUploads)) {
        file_put_contents($htaccessUploads, "Options -Indexes -ExecCGI\nAddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|phps|cgi|pl|py)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>");
    }
}

/**
 * Executa a migração e inicialização completa
 */
function run_database_migration() {
    $pdo = get_db_connection();
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Não foi possível conectar ao servidor MySQL. Verifique os dados em conexao.php.'
        ];
    }

    try {
        // 1. Executa a criação das tabelas
        $sqlPath = ROOT_PATH . '/database.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            $pdo->exec($sqlContent);
        }

        // 2. Garante que o Admin 'Daniel' e Aluna 'Ana Silva' existam com senhas criptografadas válidas
        $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        
        // Admin Daniel
        $stmtUser->execute(['daniel@fotocidade.org']);
        if (!$stmtUser->fetch()) {
            $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
            $insAdmin = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel, cidade, avatar, biografia) VALUES (?, ?, ?, 'admin', 'Sobradinho I', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=300&q=80', 'Coordenador Geral e Administrador da Plataforma FotoCidade DF.')");
            $insAdmin->execute(['Daniel', 'daniel@fotocidade.org', $adminPass]);
        }

        // Aluna Ana Silva
        $stmtUser->execute(['ana.silva@fotocidade.org']);
        if (!$stmtUser->fetch()) {
            $alunoPass = password_hash('aluno123', PASSWORD_BCRYPT);
            $insAluno = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel, cidade, avatar, biografia) VALUES (?, ?, ?, 'aluno', 'Ceilândia', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80', 'Fotógrafa comunitária e estudante de comunicação em Ceilândia.')");
            $insAluno->execute(['Ana Silva', 'ana.silva@fotocidade.org', $alunoPass]);
        }

        // 3. Migra Vitrine se estiver vazia
        $countVitrine = $pdo->query("SELECT COUNT(*) FROM vitrine")->fetchColumn();
        if ($countVitrine == 0) {
            $vitrineJson = get_json_data('vitrine');
            $insVitrine = $pdo->prepare("INSERT INTO vitrine (tipo, titulo, subtitulo, descricao, autor_nome, autor_avatar, autor_role, bairro, media_url, thumbnail_url, likes, compartilhamentos, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($vitrineJson as $v) {
                $tagsStr = is_array($v['tags'] ?? null) ? implode(', ', $v['tags']) : ($v['tags'] ?? '');
                $insVitrine->execute([
                    $v['tipo'] ?? 'Foto',
                    $v['titulo'] ?? '',
                    $v['subtitulo'] ?? '',
                    $v['descricao'] ?? '',
                    $v['autorNome'] ?? 'Aluno FotoCidade',
                    $v['autorAvatar'] ?? '',
                    $v['autorRole'] ?? 'Aluno',
                    $v['bairro'] ?? 'Sobradinho',
                    $v['mediaUrl'] ?? '',
                    $v['thumbnailUrl'] ?? $v['mediaUrl'] ?? '',
                    $v['likes'] ?? 0,
                    $v['compartilhamentos'] ?? 0,
                    $tagsStr
                ]);
            }
        }

        // 4. Migra Pontos do Mapa se estiver vazio
        $countMapa = $pdo->query("SELECT COUNT(*) FROM pontos_mapa")->fetchColumn();
        if ($countMapa == 0) {
            $mapJson = get_json_data('dados_mapa');
            $insMap = $pdo->prepare("INSERT INTO pontos_mapa (nome, categoria, bairro, regiao, lat, lng, descricao, endereco, contato, foto, autor_nome, destaque) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($mapJson as $m) {
                $bairro = $m['bairro'] ?? 'Sobradinho I';
                $regiao = 'Sobradinho I';
                if (stripos($bairro, 'Fercal') !== false) {
                    $regiao = 'Fercal';
                } elseif (stripos($bairro, 'Sobradinho II') !== false) {
                    $regiao = 'Sobradinho II';
                } elseif (stripos($bairro, 'Colorado') !== false) {
                    $regiao = 'Grande Colorado';
                }

                $insMap->execute([
                    $m['nome'] ?? 'Ponto Cultural',
                    $m['categoria'] ?? 'Espaço',
                    $bairro,
                    $regiao,
                    $m['lat'] ?? -15.6534,
                    $m['lng'] ?? -47.7891,
                    $m['descricao'] ?? '',
                    $m['endereco'] ?? '',
                    $m['contato'] ?? '',
                    $m['foto'] ?? '',
                    $m['autorRegistro'] ?? 'Alunos FotoCidade',
                    isset($m['destaque']) && $m['destaque'] ? 1 : 0
                ]);
            }
        }

        // 5. Migra Parceiros se estiver vazio
        $countParceiros = $pdo->query("SELECT COUNT(*) FROM parceiros")->fetchColumn();
        if ($countParceiros == 0) {
            $parcJson = get_json_data('parceiros');
            $insParc = $pdo->prepare("INSERT INTO parceiros (nome, setor, tipo, bairro, descricao, contribuicao, logo, site, contato_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($parcJson as $p) {
                $insParc->execute([
                    $p['nome'] ?? '',
                    $p['setor'] ?? '1º Setor (Poder Público)',
                    $p['tipo'] ?? 'Parceiro',
                    $p['bairro'] ?? 'Sobradinho',
                    $p['descricao'] ?? '',
                    $p['contribuicao'] ?? '',
                    $p['logo'] ?? '',
                    $p['site'] ?? '',
                    $p['contatoEmail'] ?? ''
                ]);
            }
        }

        // 6. Migra Talentos se estiver vazio
        $countTalentos = $pdo->query("SELECT COUNT(*) FROM talentos")->fetchColumn();
        if ($countTalentos == 0) {
            $talJson = get_json_data('talentos');
            $insTal = $pdo->prepare("INSERT INTO talentos (nome, funcao, habilidades, bairro, bio, disponibilidade, avatar, whatsapp, email, portfolio, verificado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($talJson as $t) {
                $habStr = is_array($t['habilidades'] ?? null) ? implode(', ', $t['habilidades']) : ($t['habilidades'] ?? '');
                $insTal->execute([
                    $t['nome'] ?? '',
                    $t['funcao'] ?? 'Artista',
                    $habStr,
                    $t['bairro'] ?? 'Sobradinho',
                    $t['bio'] ?? '',
                    $t['disponibilidade'] ?? 'Disponível para projetos',
                    $t['avatar'] ?? '',
                    $t['whatsapp'] ?? '',
                    $t['email'] ?? '',
                    $t['portfolio'] ?? '',
                    isset($t['verificado']) && $t['verificado'] ? 1 : 0
                ]);
            }
        }

        // 7. Migra Trilhas / Eixos e Missões se estiver vazio
        $countTrilhas = $pdo->query("SELECT COUNT(*) FROM trilhas_eixos")->fetchColumn();
        if ($countTrilhas == 0) {
            $trilhasJson = get_json_data('trilhas');
            $insEixo = $pdo->prepare("INSERT INTO trilhas_eixos (numero_eixo, titulo, subtitulo, carga_horaria, cor, icone, descricao, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insMissao = $pdo->prepare("INSERT INTO trilhas_missoes (eixo_id, numero_missao, titulo, descricao_curta, descricao_completa, duracao_horas, prazo, entregas_requeridas, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $ordemEixo = 1;
            foreach ($trilhasJson as $e) {
                $insEixo->execute([
                    $e['id'] ?? $ordemEixo,
                    $e['titulo'] ?? 'Eixo ' . $ordemEixo,
                    $e['subtitulo'] ?? '',
                    $e['cargaHoraria'] ?? '20 Horas',
                    $e['cor'] ?? '#0D5BA8',
                    $e['icone'] ?? 'compass',
                    $e['descricao'] ?? '',
                    $ordemEixo
                ]);
                $eixoDbId = $pdo->lastInsertId();

                if (!empty($e['missoes']) && is_array($e['missoes'])) {
                    $ordemMissao = 1;
                    foreach ($e['missoes'] as $m) {
                        $insMissao->execute([
                            $eixoDbId,
                            $m['numero'] ?? $ordemMissao,
                            $m['titulo'] ?? 'Missão ' . $ordemMissao,
                            $m['descricaoCurta'] ?? '',
                            $m['descricaoCompleta'] ?? ($m['descricaoCurta'] ?? ''),
                            $m['duracaoHoras'] ?? 4,
                            $m['prazo'] ?? '7 dias',
                            $m['entregasRequeridas'] ?? 'Relatório de campo',
                            $ordemMissao
                        ]);
                        $ordemMissao++;
                    }
                }
                $ordemEixo++;
            }
        }

        return [
            'success' => true,
            'message' => 'Migração do banco de dados concluída com sucesso!'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro durante a migração: ' . $e->getMessage()
        ];
    }
}

// Se for chamado diretamente via navegador
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $result = run_database_migration();
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>FotoCidade - Instalador MySQL</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4 font-sans">
        <div class="max-w-md w-full bg-slate-800 p-8 rounded-3xl border border-slate-700 shadow-2xl space-y-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl <?php echo $result['success'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'; ?> flex items-center justify-center text-3xl font-bold">
                <?php echo $result['success'] ? '✓' : '✗'; ?>
            </div>
            
            <h1 class="text-2xl font-black font-heading text-white">Instalação do Banco de Dados</h1>
            
            <div class="p-4 rounded-xl <?php echo $result['success'] ? 'bg-emerald-950/60 border border-emerald-800 text-emerald-200' : 'bg-rose-950/60 border border-rose-800 text-rose-200'; ?> text-xs leading-relaxed">
                <?php echo htmlspecialchars($result['message']); ?>
            </div>

            <?php if ($result['success']): ?>
                <div class="bg-slate-900/80 p-4 rounded-xl text-left text-xs space-y-2 border border-slate-700">
                    <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Credenciais Pré-configuradas:</p>
                    <p><strong class="text-amber-400">Admin (Daniel):</strong> <code>daniel@fotocidade.org</code> / <code>admin123</code></p>
                    <p><strong class="text-cyan-400">Aluno (Ana Silva):</strong> <code>ana.silva@fotocidade.org</code> / <code>aluno123</code></p>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="login.php" class="flex-1 py-3 bg-[#FF8A00] hover:bg-[#e67a00] font-bold rounded-xl text-white text-xs transition-colors">
                        Ir para Login
                    </a>
                    <a href="index.php" class="flex-1 py-3 bg-slate-700 hover:bg-slate-600 font-bold rounded-xl text-white text-xs transition-colors">
                        Ver Site Público
                    </a>
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400">Verifique as configurações de host, usuário e senha em <code>conexao.php</code>.</p>
                <a href="index.php" class="inline-block py-2.5 px-6 bg-slate-700 hover:bg-slate-600 font-bold rounded-xl text-white text-xs">Voltar</a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}
