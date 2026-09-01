<?php
/**
 * Componente Modal para Mapear Novo Ponto Cultural (PHP)
 * Cadastro restrito para Administradores
 */
$categoriasModal = [
    'Patrimônio',
    'Espaço Cultural',
    'Coletivo',
    'Ponto de Memória',
    'Feira Cultural',
    'Órgão Público',
    'Hospital',
    'Escola',
    'Empresa',
    'ONG',
    'Instituto',
    'Associação',
    'Turismo'
];
?>
<div id="modal-add-point" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-[#0D5BA8] rounded-2xl">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 font-heading">Cadastrar Ponto no Mapa</h3>
                    <p class="text-xs text-slate-500">Mapeamento Territorial Comunitário</p>
                </div>
            </div>
            <button onclick="closeModal('modal-add-point')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <?php if (is_admin()): ?>
            <form method="POST" action="dashboard.php" class="py-4 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="action" value="salvar_ponto_mapa">
                <input type="hidden" name="redirect_to" value="mapa.php">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nome do Ponto / Espaço / Serviço *</label>
                    <input type="text" name="nome" required placeholder="Ex: C.E.M 01 de Sobradinho, Parque dos Jequitibás" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Categoria (13 Opções) *</label>
                        <select name="categoria" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all">
                            <?php foreach ($categoriasModal as $catItem): ?>
                                <option value="<?php echo htmlspecialchars($catItem); ?>"><?php echo htmlspecialchars($catItem); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Região Administrativa / Cidade *</label>
                        <input type="text" name="regiao" required placeholder="Ex: Sobradinho, Fercal, Ceilândia" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Bairro / Setor</label>
                        <input type="text" name="bairro" placeholder="Ex: Quadra 04, Setor de Mansões" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Endereço Completo</label>
                        <input type="text" name="endereco" placeholder="Ex: Quadra 02 Área Especial" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                    </div>
                </div>

                <!-- Coordenadas Geográficas GPS -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Coordenadas GPS (Latitude e Longitude)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <input type="number" step="any" name="lat" placeholder="Latitude ex: -15.6534" value="-15.6534" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-[#0D5BA8]" />
                        </div>
                        <div>
                            <input type="number" step="any" name="lng" placeholder="Longitude ex: -47.7891" value="-47.7891" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-[#0D5BA8]" />
                        </div>
                    </div>
                </div>

                <!-- Redes Sociais & Contato -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Instagram</label>
                        <input type="text" name="instagram" placeholder="@perfil" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Website</label>
                        <input type="text" name="website" placeholder="https://site.com" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">WhatsApp</label>
                        <input type="text" name="whatsapp" placeholder="(61) 98765-4321" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Descrição & Importância Cultural *</label>
                    <textarea name="descricao" rows="3" required placeholder="Descreva a história e atuação deste ponto no território..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('modal-add-point')" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Cadastrar no Mapa</span>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="py-8 text-center space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#FF8A00] flex items-center justify-center mx-auto">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </div>
                <h4 class="font-heading font-bold text-base text-slate-900">Acesso Restrito aos Administradores</h4>
                <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">
                    O cadastro, edição e exclusão de pontos no mapa territorial são de uso exclusivo dos Administradores do FotoCidade DF.
                </p>
                <div class="pt-2">
                    <button onclick="closeModal('modal-add-point')" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl">
                        Entendido
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
