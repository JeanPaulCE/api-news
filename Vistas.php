<?php

use hacienda\Hacienda;
use core\DB;
use core\Router;




class Vistas
{
    private static $instance;
    private DB $db;

    public function __construct()
    {
        require_once("{$_SERVER['DOCUMENT_ROOT']}/DB.php");
        $this->db = new DB();
        //inicializar herraminetas a usar
    }

    public function index(Router $router, array $vars){

            echo "hola usuario: ".$vars['a'];     
           
    }

    public function home(Router $router, array $vars)
    {
        $publicaciones  = $this->db->select('publicaciones', '*',);
        $respusta = json_decode("{}");
        for ($i = 0; $i < count($publicaciones); $i++) {
            $respusta->news[$i] = $publicaciones[$i];
        }
        var_dump(json_encode($respusta));
    }

    
}
