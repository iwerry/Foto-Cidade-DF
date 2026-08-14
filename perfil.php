<?php
/**
 * FotoCidade DF - Painel do Aluno / Perfil (perfil.php)
 */
require_once __DIR__ . '/config.php';

$activeTab = 'perfil';
$pageTitle = 'Painel do Aluno • Meu Perfil e Insígnias';

$user = get_user_profile();

require_once ROOT_PATH . '/components/common/head.php';
require_once ROOT_PATH . '/components/common/navbar.php';
?>

<main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-[#0D5BA8] via-[#09427D] to-[#00A7B5] rounded-3xl p-6 sm:p-10 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                     alt="<?php echo htmlspecialchars($user['nome']); ?>"
                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-white/80 shadow-md" />

                <div class="text-center sm:text-left space-y-2 flex-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 text-xs font-semibold text-teal-200">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#FFC107]"></i>
                        <span>Agente Cultural Formada</span>
                    </div>

                    <h1 class="font-heading font-black text-2xl sm:text-4xl">
                        Bem-vinda, <?php echo htmlspecialchars($user['nome']); ?>
                    </h1>

                    <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                        <?php echo htmlspecialchars($user['biografia']); ?>
                    </p>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 pt-2 text-xs">
                        <span class="flex items-center gap-1 text-slate-200">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FFC107]"></i>
                            <?php echo htmlspecialchars($user['cidade']); ?>
                        </span>
                        <span>•</span>
                        <span class="text-emerald-300 font-semibold">
                            <?php echo $user['pontosCadastrados']; ?> Pontos Mapeados
                        </span>
                        <span>•</span>
                        <span class="text-orange-300 font-semibold">
                            <?php echo $user['oficinasConcluidas']; ?>/<?php echo $user['totalOficinas']; ?> Oficinas Concluídas
                        </span>
                    </div>
                </div>

                <a href="trilha.php"
                   class="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 shrink-0 flex items-center gap-2">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                    <span>Continuar Trilha</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Col: Progress & Badges -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Progress Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-heading font-bold text-lg text-slate-900">
                                Meu Progresso: Mapeamento Territorial
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Eixo I em andamento</p>
                        </div>
                        <span class="text-2xl font-black font-heading text-[#0D5BA8]"><?php echo $user['progressoTrilha']; ?>%</span>
                    </div>

                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#0D5BA8] to-[#00A7B5] rounded-full"
                             style="width: <?php echo $user['progressoTrilha']; ?>%"></div>
                    </div>

                    <!-- Next Step Callout -->
                    <div class="bg-blue-50/70 rounded-xl p-4 border border-blue-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div>
                            <h4 class="font-heading font-bold text-xs text-[#0D5BA8]">
                                Próximo Desafio: Descubra um Espaço Cultural
                            </h4>
                            <p class="text-[11px] text-slate-600">
                                Eixo I • Etapa 3/9: Mapeie e entreviste um ponto de cultura em seu bairro.
                            </p>
                        </div>
                        <a href="trilha.php" class="px-4 py-2 bg-[#0D5BA8] text-white text-xs font-bold rounded-lg shadow-xs hover:bg-[#09427D] shrink-0">
                            Ir para Missão
                        </a>
                    </div>
                </div>

                <!-- Insígnias Conquistadas -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-heading font-bold text-lg text-slate-900 flex items-center gap-2">
                            <i data-lucide="award" class="w-5 h-5 text-[#FF8A00]"></i>
                            Minhas Insígnias e Selos
                        </h3>
                        <span class="text-xs text-slate-500 font-semibold"><?php echo count($user['selos']); ?> desbloqueados</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <?php foreach ($user['selos'] as $selo): ?>
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center gap-3.5 shadow-2xs">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-xs shrink-0 <?php echo $selo['cor']; ?>">
                                    <i data-lucide="<?php echo $selo['icone']; ?>" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-heading font-bold text-xs text-slate-900"><?php echo htmlspecialchars($selo['titulo']); ?></h4>
                                    <p class="text-[10px] text-slate-500 mt-0.5"><?php echo htmlspecialchars($selo['descricao']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Right Col: Certificação & Atendimento -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Certificação -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-[#FF8A00] flex items-center justify-center mb-2">
                        <i data-lucide="award" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-heading font-bold text-base text-slate-900">
                        Certificação em Andamento
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Complete todos os 4 eixos para obter seu certificado digital oficial de 56 horas de Agente Territorial FotoCidade.
                    </p>
                    <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1.5 font-medium text-slate-700">
                        <div class="flex items-center justify-between">
                            <span>Eixo I - Mapeamento:</span>
                            <span class="text-emerald-600 font-bold">1/3 Concluído</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Eixo II - Fotografia:</span>
                            <span class="text-slate-400">Em Breve</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Eixo III - Comunicação:</span>
                            <span class="text-slate-400">Em Breve</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Eixo IV - Produção:</span>
                            <span class="text-slate-400">Em Breve</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<?php
require_once ROOT_PATH . '/components/common/notifications_modal.php';
require_once ROOT_PATH . '/components/common/footer.php';
?>
