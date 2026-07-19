<?php

namespace Core\Database;

use PDO;

class ORM
{
    private PDO $db;
    private string $table;
    private array $wheres = [];
    private array $params = [];

    public function __construct(string $table)
    {
        // Encapsulate the PDO instance privately
        $this->db = Connection::getInstance();
        $this->table = $table;
    }

    public function where(string $column, mixed $value): self
    {
        // Build the parameterized WHERE clause securely
        $this->wheres[] = "$column = :$column";
        $this->params[":$column"] = $value;
        
        return $this; // Return self to allow method chaining
    }

    public function first(): array|false
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->params);
        
        // Reset state for the next query
        $this->wheres = [];
        $this->params = [];
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }
}