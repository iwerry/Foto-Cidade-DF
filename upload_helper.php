<?php
/**
 * FotoCidade DF - Helper de Upload Local Seguro
 *
 * Gerencia o upload de imagens, fotos de pontos e mídias salvando
 * exclusivamente no diretório local /assets/uploads/ do servidor.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

/**
 * Processa o upload de um arquivo e salva em /assets/uploads/
 *
 * @param array $fileArray Array do $_FILES['campo']
 * @param string $subFolder Subpasta opcional dentro de uploads (ex: 'vitrine', 'mapa', 'talentos')
 * @param array $allowedMimes Tipos MIME permitidos
 * @param int $maxSize Tamanho máximo em bytes (padrão 10MB)
 * @return array ['success' => bool, 'path' => string, 'message' => string]
 */
function upload_local_file($fileArray, $subFolder = '', $allowedMimes = null, $maxSize = 10485760) {
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.'];
    }

    if ($allowedMimes === null) {
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'video/mp4' => 'mp4',
            'application/pdf' => 'pdf'
        ];
    }

    // Verifica tamanho
    if ($fileArray['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Arquivo muito grande. Limite máximo de ' . ($maxSize / 1024 / 1024) . 'MB.'];
    }

    // Validação real do tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileArray['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mimeType, $allowedMimes)) {
        return ['success' => false, 'message' => 'Tipo de arquivo não permitido (' . htmlspecialchars($mimeType) . '). Apenas imagens e documentos autorizados.'];
    }

    $extension = $allowedMimes[$mimeType];

    // Diretório de destino
    $baseDir = ROOT_PATH . '/assets/uploads';
    if (!empty($subFolder)) {
        $baseDir .= '/' . trim($subFolder, '/');
    }

    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }

    // Gera nome único seguro
    $filename = 'fc_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
    $destination = $baseDir . '/' . $filename;

    if (move_uploaded_file($fileArray['tmp_name'], $destination)) {
        $relativePath = 'assets/uploads/' . (!empty($subFolder) ? trim($subFolder, '/') . '/' : '') . $filename;
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
            'message' => 'Arquivo enviado com sucesso!'
        ];
    }

    return ['success' => false, 'message' => 'Falha ao salvar arquivo no servidor.'];
}

/**
 * Processa upload de foto de perfil redimensionando para 1080x1080
 * e salvando em /public/perfil/{NomeDoUsuario_ID}.jpg
 *
 * @param array $fileArray $_FILES['avatar_arquivo']
 * @param string $nome Nome do usuário
 * @param int|string $userId ID do usuário
 * @return array ['success' => bool, 'path' => string, 'message' => string]
 */
function upload_user_profile_photo($fileArray, $nome, $userId = 0) {
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.'];
    }

    $targetDir = ROOT_PATH . '/public/perfil';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Sanitiza o nome para o arquivo (ex: "Daniel Rodrigues" -> "Daniel_Rodrigues")
    $nomeSanitizado = preg_replace('/[^A-Za-z0-9_-]/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome));
    $nomeSanitizado = trim(preg_replace('/_+/', '_', $nomeSanitizado), '_');
    if (empty($nomeSanitizado)) {
        $nomeSanitizado = 'user';
    }

    $filename = $nomeSanitizado . '_' . ($userId ?: time()) . '.jpg';
    $targetPath = $targetDir . '/' . $filename;
    $relativePath = 'public/perfil/' . $filename;

    $tmpPath = $fileArray['tmp_name'];

    // Se GD estiver disponível, faz o crop central quadrado e redimensiona para 1080x1080
    if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
        try {
            $imageContent = file_get_contents($tmpPath);
            $srcImage = @imagecreatefromstring($imageContent);

            if ($srcImage) {
                $srcWidth = imagesx($srcImage);
                $srcHeight = imagesy($srcImage);

                // Determina área quadrada central para o crop
                $cropSize = min($srcWidth, $srcHeight);
                $cropX = (int)(($srcWidth - $cropSize) / 2);
                $cropY = (int)(($srcHeight - $cropSize) / 2);

                // Cria tela 1080x1080
                $dstImage = imagecreatetruecolor(1080, 1080);

                // Preserva cores verdadeiras
                imagealphablending($dstImage, true);

                // Redimensiona com interpolação de alta qualidade
                imagecopyresampled(
                    $dstImage,
                    $srcImage,
                    0, 0,
                    $cropX, $cropY,
                    1080, 1080,
                    $cropSize, $cropSize
                );

                // Salva em JPG com 90% de qualidade
                imagejpeg($dstImage, $targetPath, 90);

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                return [
                    'success' => true,
                    'path' => $relativePath,
                    'filename' => $filename,
                    'message' => 'Foto de perfil salva com sucesso (1080x1080)!'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro GD resize avatar: " . $e->getMessage());
        }
    }

    // Fallback caso GD falhe
    if (move_uploaded_file($tmpPath, $targetPath)) {
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
            'message' => 'Foto de perfil salva com sucesso!'
        ];
    }

    return ['success' => false, 'message' => 'Falha ao salvar foto no servidor.'];
}

/**
 * Sanitiza texto para nome de arquivo sem espaços, acentos ou caracteres especiais
 */
function sanitize_file_name($text) {
    if (function_exists('iconv')) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9_-]/', '_', $text);
    $text = trim(preg_replace('/_+/', '_', $text), '_');
    return !empty($text) ? $text : 'item';
}

/**
 * Processa upload de foto/logo de parceiro salvando em /public/parceiros/{nome_sanitizado}_{id}.ext
 */
function upload_parceiro_logo($fileArray, $nomeLocal, $id = 0) {
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.'];
    }

    $targetDir = ROOT_PATH . '/public/parceiros';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $ext = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
    $ext = strtolower($ext ?: 'jpg');
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
        $ext = 'jpg';
    }

    $nomeSanitizado = sanitize_file_name($nomeLocal);
    $filename = $nomeSanitizado . '_' . ($id ?: time()) . '.' . $ext;
    $targetPath = $targetDir . '/' . $filename;
    $relativePath = 'public/parceiros/' . $filename;

    if (move_uploaded_file($fileArray['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
            'message' => 'Logo da organização salva em /public/parceiros/ com sucesso!'
        ];
    }

    return ['success' => false, 'message' => 'Falha ao mover arquivo enviado para /public/parceiros/'];
}

/**
 * Processa upload de foto de ponto no mapa salvando em /public/mapa/{nome_sanitizado}_{id}.ext
 */
function upload_mapa_photo($fileArray, $nomeLocal, $id = 0) {
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.'];
    }

    $targetDir = ROOT_PATH . '/public/mapa';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $ext = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
    $ext = strtolower($ext ?: 'jpg');
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        $ext = 'jpg';
    }

    $nomeSanitizado = sanitize_file_name($nomeLocal);
    $filename = $nomeSanitizado . '_' . ($id ?: time()) . '.' . $ext;
    $targetPath = $targetDir . '/' . $filename;
    $relativePath = 'public/mapa/' . $filename;

    if (move_uploaded_file($fileArray['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
            'message' => 'Foto do ponto salva em /public/mapa/ com sucesso!'
        ];
    }

    return ['success' => false, 'message' => 'Falha ao mover arquivo enviado para /public/mapa/'];
}


