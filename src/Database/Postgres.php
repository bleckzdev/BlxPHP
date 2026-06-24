<?php

namespace BlxPHP\Database;

class Postgres extends AbstractDatabase
{
    private const DEFAULT_PORT = '5432';

    /**
     * Crea una nueva conexión a PostgreSQL.
     *
     * @param string $host     Servidor PostgreSQL
     * @param string $port     Puerto (por defecto 5432)
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
        return "pgsql:host={$this->host};port={$this->port};dbname={$this->database};options='--client_encoding=UTF8'";
    }

    /**
     * Establece el schema de búsqueda para la conexión actual.
     *
     * @param string $schema Nombre del schema de PostgreSQL
     */
    public function setSchema(string $schema): void
    {
        $this->connection->exec("SET search_path TO {$schema}");
    }
}
