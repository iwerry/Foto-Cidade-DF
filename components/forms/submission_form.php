<?php
/**
 * Componente Modal para Submissão de Missão da Trilha (PHP)
 */
?>
<div id="modal-submission-form" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-[#0D5BA8] rounded-2xl">
                    <i data-lucide="upload" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 font-heading">Enviar Trabalho Prático</h3>
                    <p class="text-xs text-slate-500">Submeta o resultado da sua missão formativa</p>
                </div>
            </div>
            <button onclick="closeModal('modal-submission-form')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="event.preventDefault(); alert('Trabalho enviado com sucesso! Ele aparecerá em breve na Vitrine do Território.'); closeModal('modal-submission-form');" class="py-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Título do Registro / Fotografia *</label>
                <input type="text" required placeholder="Ex: Abertura da Feira Comunitária da Fercal" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Região Administrativa / Bairro *</label>
                    <input type="text" required placeholder="Ex: Sobradinho II" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">URL da Imagem / Trabalho</label>
                    <input type="url" placeholder="https://..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descrição do Registro / Reflexão *</label>
                <textarea rows="3" required placeholder="Descreva o que motivou este registro e o impacto comunitário..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#0D5BA8] focus:ring-2 focus:ring-blue-100 transition-all"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('modal-submission-form')" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#00A7B5] hover:bg-[#008C99] text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-2">
                    <span>Publicar na Vitrine</span>
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>
