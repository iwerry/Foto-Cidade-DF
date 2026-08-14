<?php
/**
 * Componente Modal de Detalhes do Ponto Cultural (PHP)
 */
?>
<div id="modal-point-detail" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs modal-container">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl relative border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="h-48 bg-slate-200 relative">
            <img id="detail-point-img" src="assets/images/oficina-olhar-fercal.jpg" class="w-full h-full object-cover" />
            <button onclick="closeModal('modal-point-detail')" class="absolute top-3 right-3 p-2 bg-white/80 backdrop-blur-md text-slate-700 hover:text-slate-900 rounded-full shadow-md">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <span id="detail-point-categoria" class="absolute bottom-3 left-3 px-3 py-1 bg-[#0D5BA8] text-white text-xs font-bold rounded-lg shadow-md uppercase">
                Patrimônio
            </span>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <h3 id="detail-point-nome" class="text-xl font-bold text-slate-900 font-heading">Nome do Ponto</h3>
                <p id="detail-point-ra" class="text-xs text-[#00A7B5] font-semibold flex items-center gap-1 mt-1">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#FF8A00]"></i>
                    Região Administrativa
                </p>
            </div>

            <p id="detail-point-descricao" class="text-xs text-slate-600 leading-relaxed">
                Descrição detalhada do espaço de memória ou patrimônio mapeado pela comunidade.
            </p>

            <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="font-bold">Endereço / Referência:</span>
                    <span id="detail-point-endereco" class="text-slate-800">Centro Histórico</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="font-bold">Mapeado Por:</span>
                    <span id="detail-point-responsavel" class="text-[#0D5BA8] font-bold">Alunos da Trilha</span>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button onclick="closeModal('modal-point-detail')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
