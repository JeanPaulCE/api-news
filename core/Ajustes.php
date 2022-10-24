<?php

namespace core;

use mysqli;

class Ajustes
{
    private $json;

    public function __construct()
    {
        $this->json = json_decode(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/setings.json"), true);
    }

    public function getDb()
    {
        return ['type' => $this->json['DB']['type'], 'host' => $this->json['DB']['host'], 'database' => $this->json['DB']['database'], 'username' => $this->json['DB']['username'], 'password' => $this->json['DB']['password']];
    }

    public function save()
    {
        unlink("{$_SERVER['DOCUMENT_ROOT']}/setings.json");
        fclose(fopen("{$_SERVER['DOCUMENT_ROOT']}/setings.json", "w"));
        file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/setings.json", json_encode($this->json));
    }
}
