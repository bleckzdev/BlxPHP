<?php

namespace BlxPHP\Database;

class MySQL extends AbstractDatabase
{
    private const DEFAULT_PORT = '3306';

    /**
     * Crea una nueva conexión a MySQL.
     *
     * @param string $host     Servidor MySQL
     * @param string $port     Puerto (por defecto 3306)
     * @param string $database Nombre de la base de datos
     * @param string $user     Usuario
     * @param string $password Contraseña
     * @param array  $options  Opciones adicionales para PDO
     */
    public function __construct(
        string $host,
        string $port = self::DEFAULT_PORT,
        string $database = '',
        string $user = '',
        string $password = '',
        array  $options = []
    ) {
        parent::__construct($host, $port, $database, $user, $password, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildDsn(): string
    {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
    }

    /**
     * {@inheritdoc}
     */
    public function setSchema(string $schema): void
    {
        $this->connection->exec("USE `{$schema}`");
    }
}

