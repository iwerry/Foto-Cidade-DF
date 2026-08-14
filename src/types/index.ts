export type CategoryType = 'Artista' | 'Espaço' | 'Informação' | 'Liderança' | 'Patrimônio';

export interface MapPoint {
  id: string | number;
  nome: string;
  categoria: CategoryType;
  bairro: string;
  lat: number;
  lng: number;
  descricao: string;
  endereco: string;
  contato?: string;
  redesSociais?: string;
  foto?: string;
  autorRegistro?: string;
  dataCriacao?: string;
  tags?: string[];
  destaque?: boolean;
}

export interface Artista {
  id: string | number;
  nome: string;
  categoria: string;
  linguagem: string; // ex: "Música", "Artes Visuais", "Teatro", "Artesanato", "Audiovisual"
  bairro: string;
  lat: number;
  lng: number;
  bio: string;
  foto: string;
  instagram?: string;
  telefone?: string;
  portfolioUrl?: string;
  destaque?: boolean;
  projetos?: string[];
}

export interface VitrineItem {
  id: string | number;
  tipo: 'Foto' | 'Vídeo' | 'Perfil' | 'Espaço';
  titulo: string;
  subtitulo: string;
  descricao: string;
  autorNome: string;
  autorAvatar: string;
  autorRole?: string;
  bairro: string;
  mediaUrl: string;
  thumbnailUrl?: string;
  dataPublicacao: string;
  likes: number;
  curtido?: boolean;
  compartilhamentos: number;
  tags: string[];
  comentariosCount?: number;
}

export interface Missao {
  id: string;
  eixoId: number;
  numero: number;
  titulo: string;
  descricaoCurta: string;
  descricaoCompleta: string;
  status: 'concluido' | 'em_andamento' | 'bloqueado';
  duracaoHoras: number;
  prazo?: string;
  entregasRequeridas: string;
  exercicioAtivo?: boolean;
  arquivosExemplo?: { titulo: string; tipo: string; url: string }[];
  criteriosAvaliacao?: string[];
}

export interface EixoTrilha {
  id: number;
  titulo: string;
  subtitulo: string;
  cargaHoraria: string;
  cor: string;
  icone: string;
  descricao: string;
  objetivos: string[];
  missoes: Missao[];
}

export interface SubmissaoMissao {
  id: string;
  missaoId: string;
  missaoTitulo: string;
  alunoNome: string;
  alunoEmail: string;
  bairro: string;
  tituloTrabalho: string;
  descricao: string;
  dataEnvio: string;
  arquivos: string[];
  status: 'Em análise' | 'Aprovado' | 'Destaque';
  feedback?: string;
}

export interface Parceiro {
  id: string;
  nome: string;
  setor: '1º Setor (Poder Público)' | '2º Setor (Empresas/Comércio)' | '3º Setor (OSCs/Coletivos)' | 'Comunidade';
  tipo: 'Escola' | 'Empresa' | 'ONG' | 'Coletivo' | 'Órgão Público';
  bairro: string;
  descricao: string;
  contribuicao: string;
  logo: string;
  site?: string;
  contatoEmail?: string;
}

export interface TalentoLocal {
  id: string;
  nome: string;
  funcao: string;
  habilidades: string[];
  bairro: string;
  bio: string;
  disponibilidade: 'Disponível para projetos' | 'Em formação' | 'Oficineiro';
  avatar: string;
  whatsapp?: string;
  email?: string;
  portfolio?: string;
  verificado: boolean;
}

export interface PautaAgenda {
  id: string;
  titulo: string;
  setor: string;
  organizacao: string;
  dataEvento: string;
  localizacao: string;
  bairro: string;
  descricao: string;
  contatoResponsavel: string;
  email: string;
  status: 'Pendente' | 'Publicado';
}

export interface UserProfile {
  nome: string;
  email: string;
  papel: string;
  bairro: string;
  avatar: string;
  bio: string;
  progressoTrilha: number;
  missoesConcluidas: number;
  totalMissoes: number;
  insignias: { id: string; titulo: string; icone: string; cor: string; data: string }[];
  pontosCadastrados: number;
}
