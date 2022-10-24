<?php

namespace core;

use core\DataBase;

require_once("{$_SERVER['DOCUMENT_ROOT']}/core/DataBase.php");

class DB extends DataBase //escribe consultas personalizadas
{
    public function __construct()
    {
        parent::__construct();
    }
    public function test()
    {
        $res = parent::select('Permiso', "*");
        var_dump($res);
    }
}
