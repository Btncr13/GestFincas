<?php

class Database
{
    private $host;
    private $db;
    private $user;
    private $pass;
    private $charset;

    public function __construct($config)
    {
        $this->host    = $config['host'];
        $this->db      = $config['db'];
        $this->user    = $config['user'];
        $this->pass    = $config['pass'];
        $this->charset = $config['charset'];
    }

    public function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            return new PDO($dsn, $this->user, $this->pass, $options);

        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>