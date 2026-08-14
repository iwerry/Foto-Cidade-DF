import React, { useState } from 'react';
import { Missao, SubmissaoMissao } from '../../types';
import { Upload, CheckCircle2, Image as ImageIcon, X, AlertCircle } from 'lucide-react';

interface SubmissionFormProps {
  missao: Missao;
  onSuccess: (sub: Omit<SubmissaoMissao, 'id' | 'dataEnvio' | 'status'>) => void;
  onCancel?: () => void;
}

export const SubmissionForm: React.FC<SubmissionFormProps> = ({
  missao,
  onSuccess,
  onCancel
}) => {
  const [tituloTrabalho, setTituloTrabalho] = useState('Meu Território em 10 Imagens: ');
  const [descricao, setDescricao] = useState('');
  const [bairro, setBairro] = useState('Fercal');
  const [sampleImages, setSampleImages] = useState<string[]>([
    'https://images.unsplash.com/photo-1469488865564-c2de10f69f96?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=800&q=80'
  ]);
  const [imageUrlInput, setImageUrlInput] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  const handleAddImageUrl = () => {
    if (imageUrlInput.trim() && !sampleImages.includes(imageUrlInput.trim())) {
      setSampleImages([...sampleImages, imageUrlInput.trim()]);
      setImageUrlInput('');
    }
  };

  const handleRemoveImage = (index: number) => {
    setSampleImages(sampleImages.filter((_, i) => i !== index));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!tituloTrabalho.trim() || !descricao.trim()) return;

    setIsSubmitting(true);
    setTimeout(() => {
      onSuccess({
        missaoId: missao.id,
        missaoTitulo: missao.titulo,
        alunoNome: 'Ana Silva',
        alunoEmail: 'ana.silva@fotocidade.org',
        bairro,
        tituloTrabalho,
        descricao,
        arquivos: sampleImages
      });
      setIsSubmitting(false);
      setSubmitted(true);
    }, 800);
  };

  if (submitted) {
    return (
      <div className="bg-white rounded-2xl border border-emerald-200 p-8 text-center space-y-4 shadow-sm animate-in zoom-in-95">
        <div className="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
          <CheckCircle2 className="w-10 h-10" />
        </div>
        <h3 className="font-heading font-bold text-xl text-slate-900">
          Exercício Enviado com Sucesso!
        </h3>
        <p className="text-sm text-slate-600 max-w-md mx-auto">
          Seus registros foram enviados para a curadoria da Trilha FotoCidade. Eles serão catalogados no acervo territorial.
        </p>
        <button
          onClick={() => setSubmitted(false)}
          className="mt-4 px-6 py-2.5 bg-[#0D5BA8] text-white font-bold text-xs rounded-xl shadow-xs hover:bg-[#09427D]"
        >
          Enviar Nova Atualização
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
      <div className="border-b border-slate-100 pb-3">
        <span className="text-[11px] font-bold uppercase tracking-wider text-[#FF8A00] block mb-1">
          Exercício Ativo da Trilha
        </span>
        <h3 className="font-heading font-bold text-lg text-slate-900">
          {missao.titulo}: Envio de Atividade Prática
        </h3>
        <p className="text-xs text-slate-500 mt-1">
          {missao.entregasRequeridas}
        </p>
      </div>

      <div>
        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          Título do Trabalho *
        </label>
        <input
          type="text"
          required
          value={tituloTrabalho}
          onChange={(e) => setTituloTrabalho(e.target.value)}
          placeholder="Ex: Meu Território em 10 Imagens - Caminhos da Fercal"
          className="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
        />
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Bairro / Local de Coleta *
          </label>
          <select
            value={bairro}
            onChange={(e) => setBairro(e.target.value)}
            className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
          >
            <option value="Fercal">Fercal</option>
            <option value="Sobradinho I">Sobradinho I</option>
            <option value="Sobradinho II">Sobradinho II</option>
            <option value="Grande Colorado">Grande Colorado</option>
          </select>
        </div>

        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Responsável pelo Envio
          </label>
          <input
            type="text"
            disabled
            value="Ana Silva (ana.silva@fotocidade.org)"
            className="w-full px-3 py-2 text-xs bg-slate-100 text-slate-600 border border-slate-200 rounded-lg cursor-not-allowed"
          />
        </div>
      </div>

      <div>
        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
          Relato do Percurso e Observações de Campo *
        </label>
        <textarea
          required
          rows={4}
          value={descricao}
          onChange={(e) => setDescricao(e.target.value)}
          placeholder="Descreva o trajeto realizado, as pessoas que encontrou no caminho, aspectos visuais marcantes e memórias contadas pelos moradores..."
          className="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
        />
      </div>

      {/* Image Upload Gallery Box */}
      <div>
        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          Arquivos e Registros Fotográficos ({sampleImages.length} selecionados)
        </label>

        {/* Drag / Upload Box */}
        <div className="border-2 border-dashed border-slate-300 rounded-xl p-4 bg-slate-50 text-center hover:bg-slate-100 transition-colors">
          <Upload className="w-6 h-6 text-[#00A7B5] mx-auto mb-1.5" />
          <p className="text-xs font-semibold text-slate-700">
            Arraste imagens do seu trabalho ou adicione links de fotos
          </p>
          <p className="text-[10px] text-slate-500 mt-0.5">Suporta JPG, PNG ou links diretos</p>

          <div className="mt-3 flex gap-2 max-w-md mx-auto">
            <input
              type="url"
              value={imageUrlInput}
              onChange={(e) => setImageUrlInput(e.target.value)}
              placeholder="Cole o link de uma imagem da internet..."
              className="flex-1 px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00A7B5]"
            />
            <button
              type="button"
              onClick={handleAddImageUrl}
              className="px-3 py-1.5 bg-[#00A7B5] text-white text-xs font-bold rounded-lg hover:bg-[#008f9c] transition-colors"
            >
              Adicionar
            </button>
          </div>
        </div>

        {/* Thumbnails grid */}
        {sampleImages.length > 0 && (
          <div className="grid grid-cols-3 sm:grid-cols-4 gap-2.5 mt-3">
            {sampleImages.map((img, index) => (
              <div key={index} className="relative group rounded-lg overflow-hidden h-20 bg-slate-200 border border-slate-300">
                <img src={img} alt={`Registro ${index + 1}`} className="w-full h-full object-cover" />
                <button
                  type="button"
                  onClick={() => handleRemoveImage(index)}
                  className="absolute top-1 right-1 w-5 h-5 bg-red-600/90 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <X className="w-3 h-3" />
                </button>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
        {onCancel && (
          <button
            type="button"
            onClick={onCancel}
            className="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900"
          >
            Cancelar
          </button>
        )}
        <button
          type="submit"
          id="btn-submit-mission"
          disabled={isSubmitting}
          className="w-full sm:w-auto px-8 py-3 bg-[#0D5BA8] hover:bg-[#09427D] text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-2"
        >
          {isSubmitting ? (
            <span>Enviando arquivos...</span>
          ) : (
            <>
              <CheckCircle2 className="w-4 h-4 text-[#FFC107]" />
              <span>Enviar Missão</span>
            </>
          )}
        </button>
      </div>
    </form>
  );
};
