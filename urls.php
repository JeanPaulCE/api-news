<?php
use core\Router;
use core\DB;

/**clase de vistas por funciones */
require_once("{$_SERVER['DOCUMENT_ROOT']}/core/router.php");
/** base de datos */
require_once("{$_SERVER['DOCUMENT_ROOT']}/DB.php");
/**clase de vistas por funciones */
require_once("{$_SERVER['DOCUMENT_ROOT']}/Vistas.php");
/**inicia el enrutador */
$db = new DB();
$router = Router::start(new Vistas(),$db);
?>