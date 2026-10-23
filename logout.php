<?php
require_once __DIR__ . '/auth.php';
fazer_logout();
header('Location: index.php?msg=desconectado');
exit;
