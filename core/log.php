<?php
    /**log::add("Archivo.extension-linea:descripcion Fecha: ".date('Y-m-d H:i:s')); */
   namespace core;
    class log
    {
        public static function system_report(array $data)
        {
           $date = date("Y/m/d");
           $file = fopen("{$_SERVER['DOCUMENT_ROOT']}/logs/sys-$date.txt","w");
           foreach ($data as $key => $value) {
               $string = $key.":/n".$value."/n/n";
               fwrite($file,$string); 
           }
           fclose($file);
        }
        /**
        *@param String $string Formato: "Archivo.extension-linea:descripcion Fecha: ".date('Y-m-d H:i:s')  */
        public static function add($string)
        {
           $date = date("Y/m/d");
           $string = $string."/n";
           $file = fopen("{$_SERVER['DOCUMENT_ROOT']}/logs/log-$date.txt","w");
           fwrite($file,$string);
           fclose($file);

        }
    }

?>