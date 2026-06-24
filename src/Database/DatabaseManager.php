<?php

namespace BlxPHP\Database;

use BlxPHP\Responser;

class DatabaseManager
{
    /** @var array<string, DatabaseInterface> */
    private static array $connections = [];

    /**
     * Registra una conexión con un nombre identificador.
     *
     * @param string            $name       Nombre único de la conexión
     * @param DatabaseInterface $connection Instancia de conexión a la base de datos
     */
    public static function add(string $name, DatabaseInterface $connection): void
    {
        self::$connections[$name] = $connection;
    }

    /**
     * Obtiene una conexión registrada por su nombre.
     *
     * @param string $name Nombre de la conexión
     * @return DatabaseInterface
     */
    public static function get(string $name): DatabaseInterface
    {
        if (!isset(self::$connections[$name])) {
            Responser::error("La conexión '{$name}' no está registrada en el DatabaseManager");
        }
        return self::$connections[$name];
    }

    /**
     * Verifica si una conexión existe en el registro.
     *
     * @param string $name Nombre de la conexión
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(self::$connections[$name]);
    }

    /**
     * Elimina una conexión del registro.
     *
     * @param string $name Nombre de la conexión
     */
    public static function remove(string $name): void
    {
        unset(self::$connections[$name]);
    }

    /**
     * Retorna los nombres de todas las conexiones registradas.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return array_keys(self::$connections);
    }
}
