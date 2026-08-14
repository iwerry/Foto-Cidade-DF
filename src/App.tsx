import React, { useState, useEffect } from 'react';
import { 
  getStoredMapPoints, 
  saveMapPoint, 
  getStoredVitrine, 
  toggleLikeVitrine, 
  getStoredSubmissions, 
  saveSubmission, 
  getStoredPautas, 
  savePauta, 
  getUserProfile, 
  updateUserProfile,
  initialArtistas,
  initialTrilhas,
  initialParceiros,
  initialTalentos
} from './utils/storage';
import { MapPoint, VitrineItem, SubmissaoMissao, PautaAgenda, UserProfile } from './types';
import { Navbar } from './components/common/Navbar';
import { Footer } from './components/common/Footer';
import { NotificationsModal } from './components/common/NotificationsModal';
import { InscriptionModal } from './components/forms/InscriptionModal';

import { Home } from './pages/Home';
import { Trilha } from './pages/Trilha';
import { Vitrine } from './pages/Vitrine';
import { Mapa } from './pages/Mapa';
import { Parceiros } from './pages/Parceiros';
import { Perfil } from './pages/Perfil';

export default function App() {
  const [activeTab, setActiveTab] = useState<string>('inicio');
  const [user, setUser] = useState<UserProfile>(getUserProfile());
  const [mapPoints, setMapPoints] = useState<MapPoint[]>(getStoredMapPoints());
  const [vitrineItems, setVitrineItems] = useState<VitrineItem[]>(getStoredVitrine());
  const [submissions, setSubmissions] = useState<SubmissaoMissao[]>(getStoredSubmissions());
  const [pautas, setPautas] = useState<PautaAgenda[]>(getStoredPautas());
  
  const [selectedVitrineItem, setSelectedVitrineItem] = useState<VitrineItem | null>(null);
  const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
  const [isInscriptionOpen, setIsInscriptionOpen] = useState(false);

  // Handle like toggle in vitrine
  const handleLikeVitrine = (id: string | number) => {
    const updated = toggleLikeVitrine(id);
    setVitrineItems(updated);
  };

  // Handle adding new map point
  const handleSaveMapPoint = (point: Omit<MapPoint, 'id'>) => {
    const newPoint = saveMapPoint(point);
    const updatedPoints = [newPoint, ...mapPoints];
    setMapPoints(updatedPoints);
    
    // Update user stats
    const updatedUser = updateUserProfile({
      pontosCadastrados: user.pontosCadastrados + 1
    });
    setUser(updatedUser);
  };

  // Handle mission submission
  const handleSubmitMission = (sub: Omit<SubmissaoMissao, 'id' | 'dataEnvio' | 'status'>) => {
    const newSub = saveSubmission(sub);
    setSubmissions([newSub, ...submissions]);
    
    // Add to vitrine too so it immediately showcases
    const newVitrineItem: VitrineItem = {
      id: `v-${Date.now()}`,
      tipo: 'Foto',
      titulo: sub.tituloTrabalho,
      subtitulo: `Registro da ${sub.missaoTitulo} por ${sub.alunoNome}`,
      descricao: sub.descricao,
      autorNome: sub.alunoNome,
      autorAvatar: user.avatar,
      autorRole: 'Aluna - Eixo Mapeamento',
      bairro: sub.bairro,
      mediaUrl: sub.arquivos[0] || 'https://images.unsplash.com/photo-1469488865564-c2de10f69f96?auto=format&fit=crop&w=800&q=80',
      dataPublicacao: 'Hoje',
      likes: 1,
      curtido: true,
      compartilhamentos: 0,
      tags: ['Caminhada', 'Mapeamento', sub.bairro]
    };
    
    setVitrineItems([newVitrineItem, ...vitrineItems]);
  };

  // Handle saving pauta
  const handleSavePauta = (pauta: Omit<PautaAgenda, 'id' | 'status'>) => {
    const newPauta = savePauta(pauta);
    setPautas([newPauta, ...pautas]);
  };

  const handleNavigate = (tab: string) => {
    setActiveTab(tab);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#F8FAFC] text-[#333333] selection:bg-[#00A7B5] selection:text-white">
      {/* Navbar */}
      <Navbar
        activeTab={activeTab}
        setActiveTab={handleNavigate}
        user={user}
        onOpenNotifications={() => setIsNotificationsOpen(true)}
        onOpenInscription={() => setIsInscriptionOpen(true)}
      />

      {/* Main Page Content */}
      <main className="flex-1">
        {activeTab === 'inicio' && (
          <Home
            onNavigate={handleNavigate}
            onOpenInscription={() => setIsInscriptionOpen(true)}
            vitrineItems={vitrineItems}
            mapPoints={mapPoints}
            artistas={initialArtistas}
            onLikeVitrine={handleLikeVitrine}
            onSelectVitrineItem={(item) => setSelectedVitrineItem(item)}
          />
        )}

        {activeTab === 'trilha' && (
          <Trilha
            trilhas={initialTrilhas}
            user={user}
            submissions={submissions}
            onSubmitMission={handleSubmitMission}
            onNavigate={handleNavigate}
          />
        )}

        {activeTab === 'vitrine' && (
          <Vitrine
            items={vitrineItems}
            onLike={handleLikeVitrine}
            selectedItem={selectedVitrineItem}
            onSelectItem={setSelectedVitrineItem}
          />
        )}

        {activeTab === 'mapa' && (
          <Mapa
            points={mapPoints}
            onSavePoint={handleSaveMapPoint}
          />
        )}

        {activeTab === 'parceiros' && (
          <Parceiros
            parceiros={initialParceiros}
            talentos={initialTalentos}
            pautas={pautas}
            onSavePauta={handleSavePauta}
          />
        )}

        {activeTab === 'perfil' && (
          <Perfil
            user={user}
            submissions={submissions}
            vitrine={vitrineItems}
            onNavigate={handleNavigate}
          />
        )}
      </main>

      {/* Footer */}
      <Footer onNavigate={handleNavigate} />

      {/* Global Modals */}
      <NotificationsModal
        isOpen={isNotificationsOpen}
        onClose={() => setIsNotificationsOpen(false)}
        onNavigate={handleNavigate}
      />

      <InscriptionModal
        isOpen={isInscriptionOpen}
        onClose={() => setIsInscriptionOpen(false)}
      />
    </div>
  );
}
