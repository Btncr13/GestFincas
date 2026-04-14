<?php
class ReunionController {
    protected $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function reuniones() {
        // Aquí cargas la vista que has creado en la carpeta reunion
        require "src/views/reunion/reuniones.php";
    }
}
?>