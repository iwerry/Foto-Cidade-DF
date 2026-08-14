import initialMapData from '../data/mapData.json';
import initialArtistas from '../data/artistas.json';
import initialTrilhas from '../data/trilhas.json';
import initialVitrine from '../data/vitrine.json';
import initialParceiros from '../data/parceiros.json';
import initialTalentos from '../data/talentos.json';
import { MapPoint, Artista, EixoTrilha, VitrineItem, Parceiro, TalentoLocal, SubmissaoMissao, PautaAgenda, UserProfile } from '../types';

const MAP_KEY = 'fotocidade_map_points';
const SUBMISSIONS_KEY = 'fotocidade_submissions';
const VITRINE_KEY = 'fotocidade_vitrine';
const PAUTAS_KEY = 'fotocidade_pautas';
const USER_KEY = 'fotocidade_user_profile';

export const defaultUser: UserProfile = {
  nome: "Ana Silva",
  email: "ana.silva@fotocidade.org",
  papel: "Pesquisadora Territorial & Agente Cultural",
  bairro: "Fercal - Bananal",
  avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80",
  bio: "Participante da 1ª Turma FotoCidade. Pesquisando a memória oral dos pioneiros e a produção de bordados tradicionais na Fercal.",
  progressoTrilha: 45,
  missoesConcluidas: 1,
  totalMissoes: 9,
  insignias: [
    { id: "ins-1", titulo: "Repórter Local", icone: "Camera", cor: "#00A7B5", data: "12/03/2026" },
    { id: "ins-2", titulo: "Articulador de Rede", icone: "Network", cor: "#FF8A00", data: "28/03/2026" }
  ],
  pontosCadastrados: 3
};

export const getStoredMapPoints = (): MapPoint[] => {
  try {
    const data = localStorage.getItem(MAP_KEY);
    if (data) return JSON.parse(data);
  } catch (e) {
    console.error("Error reading map storage", e);
  }
  return initialMapData as MapPoint[];
};

export const saveMapPoint = (point: Omit<MapPoint, 'id'>): MapPoint => {
  const points = getStoredMapPoints();
  const newPoint: MapPoint = {
    ...point,
    id: `custom-p-${Date.now()}`,
    dataCriacao: new Date().toLocaleDateString('pt-BR')
  };
  const updated = [newPoint, ...points];
  try {
    localStorage.setItem(MAP_KEY, JSON.stringify(updated));
  } catch (e) {
    console.error("Error saving map point", e);
  }
  return newPoint;
};

export const getStoredVitrine = (): VitrineItem[] => {
  try {
    const data = localStorage.getItem(VITRINE_KEY);
    if (data) return JSON.parse(data);
  } catch (e) {
    console.error("Error reading vitrine storage", e);
  }
  return initialVitrine as VitrineItem[];
};

export const toggleLikeVitrine = (id: string | number): VitrineItem[] => {
  const items = getStoredVitrine();
  const updated = items.map(item => {
    if (item.id === id) {
      const isLiked = !item.curtido;
      return {
        ...item,
        curtido: isLiked,
        likes: isLiked ? item.likes + 1 : Math.max(0, item.likes - 1)
      };
    }
    return item;
  });
  try {
    localStorage.setItem(VITRINE_KEY, JSON.stringify(updated));
  } catch (e) {
    console.error("Error saving vitrine", e);
  }
  return updated;
};

export const getStoredSubmissions = (): SubmissaoMissao[] => {
  try {
    const data = localStorage.getItem(SUBMISSIONS_KEY);
    if (data) return JSON.parse(data);
  } catch (e) {
    console.error("Error reading submissions", e);
  }
  return [
    {
      id: "sub-1",
      missaoId: "m-101",
      missaoTitulo: "Reconhecendo o Território",
      alunoNome: "Ana Silva",
      alunoEmail: "ana.silva@fotocidade.org",
      bairro: "Fercal",
      tituloTrabalho: "Meu Território em 10 Imagens: Cores da Bananal",
      descricao: "Ensaio fotográfico com 10 marcos históricos e pontos de afeto na comunidade Bananal, incluindo a Casa de Farinha e a ponte velha.",
      dataEnvio: "Hoje às 14:30",
      arquivos: [
        "https://images.unsplash.com/photo-1469488865564-c2de10f69f96?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80"
      ],
      status: "Em análise"
    }
  ];
};

export const saveSubmission = (submission: Omit<SubmissaoMissao, 'id' | 'dataEnvio' | 'status'>): SubmissaoMissao => {
  const list = getStoredSubmissions();
  const newSub: SubmissaoMissao = {
    ...submission,
    id: `sub-${Date.now()}`,
    dataEnvio: "Hoje às " + new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
    status: "Em análise"
  };
  const updated = [newSub, ...list];
  try {
    localStorage.setItem(SUBMISSIONS_KEY, JSON.stringify(updated));
  } catch (e) {
    console.error("Error saving submission", e);
  }
  return newSub;
};

export const getStoredPautas = (): PautaAgenda[] => {
  try {
    const data = localStorage.getItem(PAUTAS_KEY);
    if (data) return JSON.parse(data);
  } catch (e) {
    console.error("Error reading pautas", e);
  }
  return [
    {
      id: "pauta-1",
      titulo: "Cineclube Comunitário Especial: Memórias do Candango",
      setor: "Comunidade",
      organizacao: "Espaço Casa de Farinha",
      dataEvento: "2026-08-22",
      localizacao: "Rua da Mina, Chácara 12",
      bairro: "Fercal",
      descricao: "Exibição aberta de curtas-metragens produzidos por jovens de Sobradinho e debate com pipoca comunitária.",
      contatoResponsavel: "Ana Silva",
      email: "contato@casadefarinha.org",
      status: "Publicado"
    }
  ];
};

export const savePauta = (pauta: Omit<PautaAgenda, 'id' | 'status'>): PautaAgenda => {
  const pautas = getStoredPautas();
  const newPauta: PautaAgenda = {
    ...pauta,
    id: `pauta-${Date.now()}`,
    status: 'Publicado'
  };
  const updated = [newPauta, ...pautas];
  try {
    localStorage.setItem(PAUTAS_KEY, JSON.stringify(updated));
  } catch (e) {
    console.error("Error saving pauta", e);
  }
  return newPauta;
};

export const getUserProfile = (): UserProfile => {
  try {
    const data = localStorage.getItem(USER_KEY);
    if (data) return JSON.parse(data);
  } catch (e) {
    console.error("Error reading user profile", e);
  }
  return defaultUser;
};

export const updateUserProfile = (partial: Partial<UserProfile>): UserProfile => {
  const current = getUserProfile();
  const updated = { ...current, ...partial };
  try {
    localStorage.setItem(USER_KEY, JSON.stringify(updated));
  } catch (e) {
    console.error("Error saving user profile", e);
  }
  return updated;
};

export { initialArtistas, initialTrilhas, initialParceiros, initialTalentos };
