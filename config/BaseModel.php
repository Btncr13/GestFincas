<?php

class BaseModel
{
    /**
     * @var PDO
     */
    protected $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }
}
?>