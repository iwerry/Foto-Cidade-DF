<?php
/**
 * Componente Modal de Notificações e Avisos da Trilha
 */
?>
<div id="modal-notifications" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-blue-50 text-[#0D5BA8] rounded-xl">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 font-heading">Notificações e Avisos</h3>
                    <p class="text-xs text-slate-500">Atualizações da Trilha e Chamadas Públicas</p>
                </div>
            </div>
            <button onclick="closeModal('modal-notifications')" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="py-4 space-y-3 max-h-96 overflow-y-auto pr-1">
            <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl relative">
                <span class="inline-block px-2 py-0.5 bg-[#0D5BA8] text-white text-[10px] font-bold rounded-md uppercase mb-1">Novo Módulo</span>
                <h4 class="text-xs font-bold text-slate-900">Oficina Prática de Cartografia Comunitária</h4>
                <p class="text-xs text-slate-600 mt-1">Neste sábado, às 09h no Galpão Cultural da Fercal. Traga seu smartphone!</p>
                <span class="text-[10px] text-slate-400 mt-2 block">Há 2 horas</span>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl relative">
                <span class="inline-block px-2 py-0.5 bg-[#FF8A00] text-white text-[10px] font-bold rounded-md uppercase mb-1">Vitrine Cultural</span>
                <h4 class="text-xs font-bold text-slate-900">Sua fotografia foi aprovada na Vitrine!</h4>
                <p class="text-xs text-slate-600 mt-1">O ensaio "Feira de Sobradinho" já está visível para toda a comunidade.</p>
                <span class="text-[10px] text-slate-400 mt-2 block">Ontem às 16:30</span>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl relative">
                <span class="inline-block px-2 py-0.5 bg-[#00A7B5] text-white text-[10px] font-bold rounded-md uppercase mb-1">Mapeamento</span>
                <h4 class="text-xs font-bold text-slate-900">5 novos pontos culturais adicionados</h4>
                <p class="text-xs text-slate-600 mt-1">Alunos da Fercal mapearam 3 nascentes preservadas e 2 espaços de capoeira.</p>
                <span class="text-[10px] text-slate-400 mt-2 block">Há 3 dias</span>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-end">
            <button onclick="closeModal('modal-notifications')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                Entendido
            </button>
        </div>
    </div>
</div>
