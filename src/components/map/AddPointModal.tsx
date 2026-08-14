import React, { useState } from 'react';
import { MapPoint, CategoryType } from '../../types';
import { X, MapPin, Plus, Sparkles, Upload, CheckCircle2 } from 'lucide-react';

interface AddPointModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (point: Omit<MapPoint, 'id'>) => void;
}

export const AddPointModal: React.FC<AddPointModalProps> = ({
  isOpen,
  onClose,
  onSave
}) => {
  const [nome, setNome] = useState('');
  const [categoria, setCategoria] = useState<CategoryType>('Artista');
  const [bairro, setBairro] = useState('Sobradinho I');
  const [descricao, setDescricao] = useState('');
  const [endereco, setEndereco] = useState('');
  const [contato, setContato] = useState('');
  const [fotoUrl, setFotoUrl] = useState('');
  const [tagsInput, setTagsInput] = useState('');
  const [lat, setLat] = useState<number>(-15.6500);
  const [lng, setLng] = useState<number>(-47.7950);
  const [submittedSuccess, setSubmittedSuccess] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!nome.trim() || !descricao.trim()) return;

    // Split tags
    const tags = tagsInput
      .split(',')
      .map(t => t.trim().replace(/^#/, ''))
      .filter(Boolean);

    // Randomize slightly around selected neighborhood if default
    const finalLat = lat || -15.65 + (Math.random() - 0.5) * 0.04;
    const finalLng = lng || -47.81 + (Math.random() - 0.5) * 0.05;

    const defaultImages: Record<string, string> = {
      'Artista': 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
      'Espaço': 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80',
      'Informação': 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=800&q=80',
      'Liderança': 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80',
      'Patrimônio': 'https://images.unsplash.com/photo-1526478806334-5fd488fcaabc?auto=format&fit=crop&w=800&q=80'
    };

    onSave({
      nome,
      categoria,
      bairro,
      descricao,
      endereco: endereco || `${bairro} - DF`,
      contato,
      foto: fotoUrl.trim() || defaultImages[categoria],
      tags: tags.length ? tags : ['Cultura Local', 'Mapeamento'],
      autorRegistro: 'Agente Comunitário FotoCidade',
      lat: finalLat,
      lng: finalLng,
      destaque: false
    });

    setSubmittedSuccess(true);
    setTimeout(() => {
      setSubmittedSuccess(false);
      onClose();
    }, 1200);
  };

  const handleBairroChange = (b: string) => {
    setBairro(b);
    if (b.includes('Fercal')) {
      setLat(-15.6040 + (Math.random() - 0.5) * 0.01);
      setLng(-47.8720 + (Math.random() - 0.5) * 0.01);
    } else if (b.includes('Colorado')) {
      setLat(-15.6740 + (Math.random() - 0.5) * 0.01);
      setLng(-47.8380 + (Math.random() - 0.5) * 0.01);
    } else if (b.includes('Sobradinho II')) {
      setLat(-15.6380 + (Math.random() - 0.5) * 0.01);
      setLng(-47.8130 + (Math.random() - 0.5) * 0.01);
    } else {
      setLat(-15.6510 + (Math.random() - 0.5) * 0.01);
      setLng(-47.7940 + (Math.random() - 0.5) * 0.01);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="bg-[#0D5BA8] text-white p-5 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
              <Plus className="w-5 h-5 text-[#FF8A00]" />
            </div>
            <div>
              <h3 className="font-heading font-bold text-lg">Mapear Novo Ponto Cultural</h3>
              <p className="text-xs text-blue-100">Colabore com a cartografia do território</p>
            </div>
          </div>

          <button
            onClick={onClose}
            className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {submittedSuccess ? (
          <div className="p-10 text-center space-y-3">
            <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
              <CheckCircle2 className="w-10 h-10" />
            </div>
            <h4 className="font-heading font-bold text-xl text-slate-800">Ponto Mapeado com Sucesso!</h4>
            <p className="text-sm text-slate-600">O novo ponto já está visível e disponível no mapa cultural do FotoCidade.</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="p-6 overflow-y-auto space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Nome do Artista, Espaço ou Ponto *
              </label>
              <input
                type="text"
                required
                value={nome}
                onChange={(e) => setNome(e.target.value)}
                placeholder="Ex: Roda de Samba da Quadra 3, Ateliê do Mestre Chico..."
                className="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Categoria *
                </label>
                <select
                  value={categoria}
                  onChange={(e) => setCategoria(e.target.value as CategoryType)}
                  className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                >
                  <option value="Artista">Artista / Músico / Artesão</option>
                  <option value="Espaço">Espaço Cultural / Ponto</option>
                  <option value="Informação">Ponto de Informação</option>
                  <option value="Liderança">Liderança Comunitária</option>
                  <option value="Patrimônio">Patrimônio / Memória</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Bairro / Região *
                </label>
                <select
                  value={bairro}
                  onChange={(e) => handleBairroChange(e.target.value)}
                  className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                >
                  <option value="Sobradinho I">Sobradinho I</option>
                  <option value="Sobradinho II">Sobradinho II</option>
                  <option value="Fercal - Bananal">Fercal - Bananal</option>
                  <option value="Fercal - Boa Vista">Fercal - Boa Vista</option>
                  <option value="Fercal - Rua da Mina">Fercal - Rua da Mina</option>
                  <option value="Grande Colorado">Grande Colorado</option>
                  <option value="Nova Colina">Nova Colina</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Descrição e História do Local *
              </label>
              <textarea
                required
                rows={3}
                value={descricao}
                onChange={(e) => setDescricao(e.target.value)}
                placeholder="Conte quem é a pessoa ou o que acontece nesse espaço, suas atividades culturais e relevância para o bairro..."
                className="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Endereço / Referência
                </label>
                <input
                  type="text"
                  value={endereco}
                  onChange={(e) => setEndereco(e.target.value)}
                  placeholder="Ex: Próximo à praça central"
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                  Contato / WhatsApp
                </label>
                <input
                  type="text"
                  value={contato}
                  onChange={(e) => setContato(e.target.value)}
                  placeholder="(61) 9...."
                  className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Foto ou Imagem (URL)
              </label>
              <input
                type="url"
                value={fotoUrl}
                onChange={(e) => setFotoUrl(e.target.value)}
                placeholder="https://exemplo.com/foto.jpg (opcional - usará imagem padrão se vazio)"
                className="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tags (separadas por vírgula)
              </label>
              <input
                type="text"
                value={tagsInput}
                onChange={(e) => setTagsInput(e.target.value)}
                placeholder="Ex: Música, Samba, Feira, Fotografia"
                className="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D5BA8]"
              />
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
              <button
                type="button"
                onClick={onClose}
                className="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900"
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="px-5 py-2.5 bg-[#FF8A00] hover:bg-[#E67A00] text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
              >
                <Plus className="w-4 h-4" />
                Cadastrar no Mapa
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};
