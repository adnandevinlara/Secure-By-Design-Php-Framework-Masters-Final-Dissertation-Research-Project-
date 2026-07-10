<?php

namespace Core\Database;

use PDO;

abstract class Model
{
    protected string $table;
    protected PDO $db;

    public function __construct()
    {
        // Automatically grab the secure singleton connection
        $this->db = Connection::getInstance();
    }

    // Securely find a single record by ID
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Securely retrieve all records
    public function all(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Securely insert an array of data, automatically generating parameterized bindings
    public function insert(array $data): bool
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        
        // Convert keys into PDO placeholders (e.g., :username, :email)
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
}