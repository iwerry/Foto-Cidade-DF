<?php
/**
 * Componente Modal de Inscrição na Trilha Formativa (PHP)
 */
?>
<div id="modal-inscription" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-cyan-50 text-[#00A7B5] rounded-2xl">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 font-heading">Inscrição na Trilha Formativa</h3>
                    <p class="text-xs text-slate-500">Sobradinho, Fercal e Grande Colorado - DF</p>
                </div>
            </div>
            <button onclick="closeModal('modal-inscription')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="event.preventDefault(); alert('Inscrição enviada com sucesso! Em breve a coordenação entrará em contato via WhatsApp.'); closeModal('modal-inscription');" class="py-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nome Completo *</label>
                <input type="text" required placeholder="Digite seu nome completo" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">E-mail *</label>
                    <input type="email" required placeholder="seu@email.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">WhatsApp / Celular *</label>
                    <input type="tel" required placeholder="(61) 99999-9999" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Região Administrativa / Cidade *</label>
                    <select required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">Selecione sua RA...</option>
                        <option value="Sobradinho I">Sobradinho I</option>
                        <option value="Sobradinho II">Sobradinho II</option>
                        <option value="Fercal">Fercal</option>
                        <option value="Grande Colorado">Grande Colorado</option>
                        <option value="Outra RA do DF">Outra RA do DF</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Eixo de Interesse Principal</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="Mapeamento Territorial">Mapeamento Territorial</option>
                        <option value="Fotografia e Audiovisual">Fotografia e Audiovisual</option>
                        <option value="Comunicação Comunitária">Comunicação Comunitária</option>
                        <option value="Produção Cultural">Produção Cultural</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Qual o seu objetivo com a trilha?</label>
                <textarea rows="3" placeholder="Conte brevemente o que você espera aprender..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('modal-inscription')" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-2">
                    <span>Confirmar Inscrição</span>
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>
