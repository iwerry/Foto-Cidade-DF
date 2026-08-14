<?php
/**
 * FotoCidade DF - DB Helper (Simulador de Banco de Dados com JSON)
 * Prepara o projeto para que, no futuro, a leitura de JSON seja substituída
 * por conexões e queries nativas como mysqli_query().
 */

/**
 * Lê e decodifica um arquivo JSON localizado na pasta /data
 *
 * @param string $filename Nome do arquivo (sem extensão)
 * @return array
 */
function get_json_data($filename) {
    $primaryPath = ROOT_PATH . '/data/' . $filename . '.json';
    $fallbackPath = ROOT_PATH . '/src/data/' . $filename . '.json';

    if (file_exists($primaryPath)) {
        $content = file_get_contents($primaryPath);
        return json_decode($content, true) ?? [];
    } elseif (file_exists($fallbackPath)) {
        $content = file_get_contents($fallbackPath);
        return json_decode($content, true) ?? [];
    }

    return [];
}

/**
 * Obtém os dados do perfil do aluno/usuário ativo
 */
function get_user_profile() {
    return [
        'id' => 'usr-1',
        'nome' => 'Ana Silva',
        'email' => 'ana.silva@fotocidade.org',
        'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
        'biografia' => 'Fotógrafa comunitária e estudante de comunicação em Ceilândia. Apaixonada por registrar a arquitetura popular e as histórias vivas da periferia do DF.',
        'cidade' => 'Ceilândia',
        'eixoPrincipal' => 'Mapeamento & Fotografia',
        'pontosCadastrados' => 7,
        'oficinasConcluidas' => 5,
        'totalOficinas' => 8,
        'progressoTrilha' => 62,
        'interesses' => ['Fotografia de Rua', 'Mapeamento Territorial', 'Design Gráfico', 'Comunicação Comunitária'],
        'selos' => [
            ['id' => 's1', 'titulo' => 'Olhar Territorial', 'descricao' => 'Concluiu o módulo de Cartografia Afetiva', 'icone' => 'map-pin', 'cor' => 'bg-cyan-500'],
            ['id' => 's2', 'titulo' => 'Mestre da Lente', 'descricao' => 'Publicou 5 ensaios na Vitrine Cultural', 'icone' => 'camera', 'cor' => 'bg-amber-500'],
            ['id' => 's3', 'titulo' => 'Voz Comunitária', 'descricao' => 'Participou de 3 pautas da cidade', 'icone' => 'award', 'cor' => 'bg-indigo-500']
        ]
    ];
}

/**
 * Funções específicas de acesso aos módulos
 */
function get_artistas() {
    return get_json_data('artistas');
}

function get_map_data() {
    return get_json_data('mapData');
}

function get_parceiros() {
    return get_json_data('parceiros');
}

function get_talentos() {
    return get_json_data('talentos');
}

function get_trilhas() {
    return get_json_data('trilhas');
}

function get_vitrine() {
    return get_json_data('vitrine');
}
