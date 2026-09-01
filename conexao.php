<?php
/**
 * FotoCidade DF - Conexão Central com Banco de Dados MySQL via PDO
 *
 * Configurações de conexão prontas para o servidor cPanel de produção.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Configurações do Banco de Dados de Produção (cPanel)
// O cPanel costuma usar o prefixo da conta (draftcre_)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'draftcre_fotocidade');
define('DB_USER', getenv('DB_USER') ?: 'draftcre_fcity');
define('DB_PASS', getenv('DB_PASS') ?: 'r.9~bT{AQYO!?Z66');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma instância singleton da conexão PDO com MySQL.
 * 
 * @return PDO|null Retorna a instância PDO ou null caso não consiga conectar
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    global $lastDbError;
    $lastDbError = null;

    $attempts = [
        sprintf("mysql:host=127.0.0.1;port=%s;dbname=%s;charset=%s", DB_PORT, DB_NAME, DB_CHARSET),
        sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", DB_HOST, DB_PORT, DB_NAME, DB_CHARSET),
        sprintf("mysql:host=localhost;dbname=%s;charset=%s", DB_NAME, DB_CHARSET),
        sprintf("mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=%s;charset=%s", DB_NAME, DB_CHARSET),
        sprintf("mysql:unix_socket=/tmp/mysql.sock;dbname=%s;charset=%s", DB_NAME, DB_CHARSET),
        sprintf("mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=%s;charset=%s", DB_NAME, DB_CHARSET),
    ];

    foreach ($attempts as $dsn) {
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            $lastDbError = $e->getMessage();
        }
    }

    error_log("FotoCidade DB Connection Failed: " . $lastDbError);
    return null;
}

/**
 * Testa se a conexão com o banco de dados MySQL está ativa
 * 
 * @return bool
 */
function is_db_connected() {
    $conn = get_db_connection();
    return $conn !== null;
}

/**
 * Retorna o primeiro nome de tabela existente no banco dentre os candidatos passados
 * Ex: get_existing_table_name($pdo, 'vitrine', 'posts')
 */
function get_existing_table_name($pdo, ...$candidates) {
    if (!$pdo) return $candidates[0] ?? '';
    
    static $existingTables = null;
    if ($existingTables === null) {
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $existingTables = [];
        }
    }

    foreach ($candidates as $cand) {
        if (in_array($cand, $existingTables)) {
            return $cand;
        }
    }

    return $candidates[0] ?? '';
}
