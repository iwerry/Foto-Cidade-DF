<?php
/**
 * Componente Modal para Mapear Novo Ponto Cultural (PHP)
 */
?>
<div id="modal-add-point" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-[#0D5BA8] rounded-2xl">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 font-heading">Cadastrar Ponto Cultural</h3>
                    <p class="text-xs text-slate-500">Mapeamento Territorial Comunitário</p>
                </div>
            </div>
            <button onclick="closeModal('modal-add-point')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="event.preventDefault(); alert('Ponto cadastrado no mapeamento cultural com sucesso!'); closeModal('modal-add-point');" class="py-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nome do Ponto / Espaço / Coletivo *</label>
                <input type="text" required placeholder="Ex: Casa de Capoeira Angoleiros do Sertão" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Região Administrativa *</label>
                    <select required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="Sobradinho I">Sobradinho I</option>
                        <option value="Sobradinho II">Sobradinho II</option>
                        <option value="Fercal">Fercal</option>
                        <option value="Grande Colorado">Grande Colorado</option>
                        <option value="Outra RA">Outra RA do DF</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Categoria *</label>
                    <select required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="Patrimônio">Patrimônio</option>
                        <option value="Espaço Cultural">Espaço Cultural</option>
                        <option value="Coletivo">Coletivo</option>
                        <option value="Ponto de Memória">Ponto de Memória</option>
                        <option value="Feira Cultural">Feira Cultural</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Latitude (opcional)</label>
                    <input type="number" step="any" placeholder="-15.6500" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Longitude (opcional)</label>
                    <input type="number" step="any" placeholder="-47.7900" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descrição História / Importância *</label>
                <textarea rows="3" required placeholder="Descreva a história e a relação deste ponto com a comunidade..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('modal-add-point')" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#0D5BA8] hover:bg-[#0A4B8A] text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-2">
                    <span>Salvar Ponto</span>
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>
