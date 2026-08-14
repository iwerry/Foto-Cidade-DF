<?php
/**
 * FotoCidade DF - Arquivo de Configuração Central
 * Define diretórios, metadados do site e carrega o assistente de dados (db_helper).
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Configurações do Site
define('SITE_NAME', 'FotoCidade DF');
define('SITE_TITLE', 'FotoCidade - Olhar • Registrar • Revelar Talentos');
define('SITE_DESCRIPTION', 'Trilha de Formação em Mapeamento Territorial, Produção Cultural e Comunicação Comunitária no Distrito Federal e Entorno.');
define('SITE_URL', '');

// Caminhos dos Assets
define('ASSETS_PATH', 'assets');
define('CSS_PATH', 'assets/css');
define('JS_PATH', 'assets/js');
define('DATA_PATH', ROOT_PATH . '/data');

// Carrega o auxiliar de dados
require_once ROOT_PATH . '/db_helper.php';
