<?php

/** 
 * enrutador por templates o funciones
 * @version 1.0.0 
 * @author Jean Paul Carvajal Elizondo. 
 */

namespace core;

use core\DataBase;

session_start();
if (!isset($_SESSION["id"])) {
  $_SESSION["id"] = 0;
}


require_once("{$_SERVER['DOCUMENT_ROOT']}/core/DataBase.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/core/log.php");


class Router
{
  private static $instance = null;
  private $funciones;
  private $setings;
  private bool $isF;
  private $DB;
  private $res = false;

  private function __construct($funciones, $db = null)
  {
    if ($db == null) {
      $db = new DataBase();
    }

    $this->DB = $db;
    $this->isF = false;
    $this->funciones = $funciones;
    $this->setings = json_decode(file_get_contents("urls.json"), true)['urls'];

    foreach ($this->setings as $key => $value) {
      if ($this->res) {
        break;
      }
      switch ($value["method"]) {
        case 'get':
          if (isset($value["function"])) {
            $this->isF = true;
            $this->get($value);
          } else $this->get($value);
          break;
        case 'post':
          if (isset($value["function"])) {
            $this->isF = true;
            $this->post($value);
          } else $this->post($value);
          break;
        case 'delete':
          if (isset($value["function"])) {
            $this->isF = true;
            $this->delete($value);
          } else $this->delete($value);
          break;
        case 'put':
          if (isset($value["function"])) {
            $this->isF = true;
            $this->put($value);
          } else $this->put($value);
          break;
        case 'patch':
          if (isset($value["function"])) {
            $this->isF = true;
            $this->patch($value);
          } else $this->patch($value);
          break;
        default:

          break;
      }
      $this->isF = false;
    }
    if (!$this->res) {
      if (isset($this->setings["404"]["function"])) {
        $this->isF = true;
        $this->any($this->setings["404"]);
      } else $this->any($this->setings["404"]);
    }
  }

  private function get($path)
  {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $this->route($path);
    }
  }

  private function post($path)
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $this->route($path);
    }
  }

  private function put($path)
  {
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      $this->route($path);
    }
  }

  private function patch($path)
  {
    if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
      $this->route($path);
    }
  }

  private function delete($path)
  {
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
      $this->route($path);
    }
  }

  private function any($path)
  {
    $this->route($path);
  }

  public function url($name = "index")
  {
    return $this->setings[$name]["url"];
  }

  private function route($path)
  {
    $vars = [];
    $perm = null;
    $route = $path["url"];
    $ROOT = $_SERVER['DOCUMENT_ROOT'];
    if (isset($path["perm"])) {
      $perm = $path["perm"];
    }
    if ($this->isF) {
      $path_to_include = $path["function"];
    } else {
      $path_to_include = $path["template"];
    }

    if ($route == "/404") { // que mas va ser
      $this->rPath($ROOT, $path_to_include, $vars, $perm);
      return;
    }

    //filtrado de urls por seguridad
    $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $request_url = rtrim($request_url, '/');
    $request_url = strtok($request_url, '?');
    $route_parts = explode('/', $route);
    $request_url_parts = explode('/', $request_url);

    array_shift($route_parts);
    array_shift($request_url_parts);

    if ($route_parts[0] == '' && count($request_url_parts) == 0) { //si es simple
      $this->rPath($ROOT, $path_to_include, $vars, $perm);
      return;
    }
    if (count($route_parts) != count($request_url_parts)) {
      return;
    }
    $parameters = [];

    for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) { //urls dinamcias
      $route_part = $route_parts[$__i__];
      if (preg_match("/^[$]/", $route_part)) {
        $route_part = ltrim($route_part, '$');
        array_push($parameters, $request_url_parts[$__i__]);

        $$route_part = $request_url_parts[$__i__];
        $vars[$route_part] = $request_url_parts[$__i__];
      } else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
        return;
      }
    }

    $this->rPath($ROOT, $path_to_include, $vars, $perm);
    return;
  }



  private function rPath($ROOT, $path_to_include, $vars, $perm = null)
  {
    if ($perm != null) {
      if (!$this->DB->can($perm)) {
        header("Location: /login");
        exit();
      }
    }
    
    if ($this->isF) {
      $this->funciones->$path_to_include($this, $vars);
    } else {
      include_once("$ROOT/$path_to_include");
    }
    $this->res = true;
  }

  /** prevencion de inyecciones de codigo */
  public static function out($text)
  {
    echo htmlspecialchars($text);
  }

  public static function set_csrf()
  {
    if (!isset($_SESSION["csrf"])) {
      $_SESSION["csrf"] = bin2hex(random_bytes(50));
    }
    echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
  }

  public static function is_csrf_valid()
  {
    if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) {
      return false;
    }
    if ($_SESSION['csrf'] != $_POST['csrf']) {
      return false;
    }
    return true;
  }


  public static function start($funciones, $db)
  {

    if (self::$instance == null) {
      self::$instance = new Router($funciones, $db);
    }
    return self::$instance;
  }
}
