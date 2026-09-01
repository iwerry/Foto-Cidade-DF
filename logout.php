<?php
/**
 * FotoCidade DF - Logout (logout.php)
 */
require_once __DIR__ . '/auth_helper.php';

logout_user();
header("Location: index.php?msg=desconectado");
exit;
