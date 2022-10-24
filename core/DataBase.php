<?php

/**
 *@author Jean Paul.
 */

namespace core;

use core\Ajustes;
use Medoo\Medoo;

require_once("{$_SERVER['DOCUMENT_ROOT']}/core/Ajustes.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/core/Medoo.php");

date_default_timezone_set("America/Costa_Rica");
$date = date('Y-m-d H:i:s');

/**
 * Conexion a base de datos y analisis de permisos
 */
class DataBase
{
    protected Medoo $database;
    private $setings;
    /**
     * singelton 
     */
    public function __construct()
    {

        $this->setings = new Ajustes();
        $this->database = new Medoo($this->setings->getDb());
    }
    /**
     * 
     * @param String $table Usuario del trabajador
     * @param array|String $columns columnas a seleccionar
     * @param array $where Condicion de la peticion
     */
    public function select(String $table, $columns, array $where = [])
    {
        if ($this->can("select_" . $table)) {
            return $this->database->select($table, $columns, $where);
        }
        return false;
    }

    public function insert(String $table, array $values)
    {
        if ($this->can("insert_" . $table)) {
            $this->database->insert($table, $values);
        }
    }
    /**
     * 
     * @param String $table Usuario del trabajador
     * @param  array $data datos a actualizar ["col"=>"new value",]
     * @param array $where Condicion de la peticion
     */
    public function update(String $table, array $data, array $where)
    {
        if ($this->can("update_" . $table)) {
            return $this->database->update($table, $data, $where);
        }
        return false;
    }
    /**
     * 
     * @param String $table Usuario del trabajador
     * @param array $where Condicion de la peticion
     */
    public function delete(String $table, array $where)
    {
        if ($this->can("delete_" . $table)) {
            return $this->database->delete($table, $where);
        }
    }
    /**
     * 
     * @param String $table Usuario del trabajador
     * @param array|Strings $columns Columna a remplasar ["column" => ["old_value" => "new_value"]]
     * @param array $where Condicion de la peticion
     */
    public function replace(String $table,  $columns, array $where)
    {
        if ($this->can("replace_" . $table)) {
            return  $this->database->replace($table, $columns, $where);
        }
    }

    /**
     * consulta si el usuario actual tiene permiso
     * @param string $perm Permiso a evaluar
     */
    public function can($perm)
    {
        return $this->ask($_SESSION["id"], $perm);
    }
    /**
     * consulta la DB si tiene permiso
     * @param string $user Cedula trabajador
     * @param string $perm Permiso a evaluar
     */
    private function ask($user, $perm)
    {
        if (!isset($_SESSION["cache_prem"])) {
            $db = $this->setings->getDb()["database"];

            $_SESSION["cache_prem"] = $this->database->query("SELECT * FROM  $db.Permiso where idPermiso in (SELECT Permiso_idPermiso FROM $db.`P-G` where Grupo_idGrupo in (SELECT Grupo FROM $db.`Colaboradores` where id = $user)) or idPermiso in (SELECT Permiso_idPermiso FROM $db.`C-P` where Colaboradores_id = $user)")->fetchAll();
        }
        $Res = $_SESSION["cache_prem"];
        if (count($Res) > 0) {
            foreach ($Res as $key => $value) {
                if ($value['valor'] == $perm) {
                    return true;
                }
                if ($value['valor'] == "admin") {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * efectua el login creando una secion en DB
     * @param string $user Usuario del trabajador
     * @param string $pass Contraseña del trabajador
     */
    public function login($user, $pass)
    {
        $user = filter_var($user, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH);
        $usuario = $this->database->select("Usuario", "*", [
            "usuario" => $user
        ]);

        if (count($usuario) > 0) {
            if (hash("sha256", $pass) == $usuario[0]["password"]) {
                $_SESSION["id"] = $usuario[0]["cedula"];
                $_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
                $_SESSION["token"] = hash(
                    "sha256",
                    $_SERVER["REMOTE_ADDR"] . $_SERVER["REMOTE_PORT"] . random_int(0, 100)
                );

                $this->database->insert("logSESSION", [
                    "token" => $_SESSION["token"],
                    "ip" => $_SESSION["ip"],
                    "cedula" => $_SESSION["id"],
                    "fecha" => date('Y-m-d H:i:s')

                ]);
                return true;
            }
        }
        return false;
    }

    public function logOut()
    {
        session_destroy();
    }
}
