<?php

namespace BlxPHP\Database;

use BlxPHP\Responser;

abstract class AbstractDatabase implements DatabaseInterface
{
    protected \PDO $connection;

    protected string $host;
    protected string $port;
    protected string $database;
    protected string $user;
    protected string $password;

    /**
     * Crea una nueva instancia de conexión a la base de datos.
     *
     * @param string $host     Servidor de la base de datos
     * @param string $port     Puerto de conexión
     * @param string $database Nombre de la base de datos
     * @param string $user     Usuario de la base de datos
     * @param string $password Contraseña del usuario
     * @param array  $options  Opciones adicionales para PDO
     */
    public function __construct(
        string $host,
        string $port,
        string $database,
        string $user,
        string $password,
        array  $options = []
    ) {
        $this->host     = $host;
        $this->port     = $port;
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;

        $defaultOptions = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $mergedOptions = $options + $defaultOptions;

        try {
            $this->connection = new \PDO(
                $this->buildDsn(),
                $this->user,
                $this->password,
                $mergedOptions
            );
        } catch (\PDOException $e) {
            Responser::error("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Construye el DSN específico del driver.
     * Cada implementación concreta define su formato.
     *
     * @return string DSN para PDO
     */
    abstract protected function buildDsn(): string;

    /**
     * {@inheritdoc}
     */
    public function Query(string $query, ?array $params = null): bool
    {
        if ($params === null) {
            $result = $this->connection->exec($query);
            if ($result === false) {
                Responser::error("Error de consulta a la base de datos");
            }
        } else {
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                Responser::error("Error de consulta a la base de datos");
            }
            $stmt->execute($params);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function Insert(string $query, ?array $params = null): int
    {
        if ($params === null) {
            $result = $this->connection->exec($query);
            if ($result === false) {
                Responser::error("Error de consulta a la base de datos");
            }
        } else {
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                Responser::error("Error de consulta a la base de datos");
            }
            $stmt->execute($params);
        }
        return (int) $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function FetchOne(string $query, ?array $params = null): array|false
    {
        if ($params === null) {
            $stmt = $this->connection->query($query);
        } else {
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                Responser::error("Error de consulta a la base de datos");
            }
            $stmt->execute($params);
        }
        return $stmt->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function FetchAll(string $query, ?array $params = null): array
    {
        if ($params === null) {
            $stmt = $this->connection->query($query);
        } else {
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                Responser::error("Error de consulta a la base de datos");
            }
            $stmt->execute($params);
        }
        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId(?string $sequence = null): int
    {
        return (int) $this->connection->lastInsertId($sequence);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}
