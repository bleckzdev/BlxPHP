<?php

namespace BlxPHP\Database;

interface DatabaseInterface
{
    /**
     * Ejecuta una consulta que no retorna datos (UPDATE, DELETE, etc.)
     *
     * @param string $query SQL query con placeholders opcionales
     * @param array|null $params Parámetros para prepared statement
     * @return bool true si la consulta se ejecutó correctamente
     */
    public function Query(string $query, ?array $params = null): bool;

    /**
     * Ejecuta un INSERT y retorna el ID generado
     *
     * @param string $query SQL INSERT con placeholders opcionales
     * @param array|null $params Parámetros para prepared statement
     * @return int ID del último registro insertado
     */
    public function Insert(string $query, ?array $params = null): int;

    /**
     * Ejecuta una consulta y retorna un solo registro
     *
     * @param string $query SQL query con placeholders opcionales
     * @param array|null $params Parámetros para prepared statement
     * @return array|false Arreglo asociativo con el registro o false si no hay resultados
     */
    public function FetchOne(string $query, ?array $params = null): array|false;

    /**
     * Ejecuta una consulta y retorna todos los registros
     *
     * @param string $query SQL query con placeholders opcionales
     * @param array|null $params Parámetros para prepared statement
     * @return array Arreglo de registros como arreglos asociativos
     */
    public function FetchAll(string $query, ?array $params = null): array;

    /**
     * Inicia una transacción
     */
    public function beginTransaction(): void;

    /**
     * Confirma la transacción actual
     */
    public function commit(): void;

    /**
     * Revierte la transacción actual
     */
    public function rollBack(): void;

    /**
     * Retorna el ID del último registro insertado
     *
     * @param string|null $sequence Nombre de la secuencia (requerido en PostgreSQL)
     * @return int
     */
    public function lastInsertId(?string $sequence = null): int;

    /**
     * Cambia el schema/base de datos activo para la conexión.
     * En PostgreSQL ejecuta SET search_path TO.
     * En MySQL ejecuta USE.
     *
     * @param string $schema Nombre del schema
     */
    public function setSchema(string $schema): void;

    /**
     * Retorna la conexión PDO subyacente
     *
     * @return \PDO
     */
    public function getConnection(): \PDO;
}
