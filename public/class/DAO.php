<?php
    class GenericDAO {
        private $db; // Database connection object
        private $tableName; // Name of the database table
        private $primaryKey; // Name of the primary key column
    
        public function __construct($db, $tableName, $primaryKey) {
            $this->db = $db;
            $this->tableName = $tableName;
            $this->primaryKey = $primaryKey;
        }
    
        // Update an object in the database
        public function update($object) {
            $class = get_class($object);
            $reflection = new ReflectionClass($class);
            $properties = $reflection->getProperties();
    
            $query = "UPDATE " . $this->tableName . " SET ";
            $params = [];
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if ($propertyName !== $this->primaryKey) {
                    $query .= $propertyName . " = ?, ";
                    $params[] = $reflection->getProperty($propertyName)->getValue($object);
                }
            }
            $query = rtrim($query, ", ");
            $query .= " WHERE " . $this->primaryKey . " = ?";
            $params[] = $reflection->getProperty($this->primaryKey)->getValue($object);
    
            $stmt = $this->db->prepare($query);
            $stmt->bind_param(str_repeat("s", count($params)), ...$params);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    
        // Save a new object to the database
        public function save($object) {
            $class = get_class($object);
            $reflection = new ReflectionClass($class);
            $properties = $reflection->getProperties();
    
            $query = "INSERT INTO " . $this->tableName . " (";
            $paramPlaceholders = "";
            $params = [];
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if ($propertyName !== $this->primaryKey) {
                    $query .= $propertyName . ", ";
                    $paramPlaceholders .= "?, ";
                    $params[] = $reflection->getProperty($propertyName)->getValue($object);
                }
            }
            $query = rtrim($query, ", ");
            $paramPlaceholders = rtrim($paramPlaceholders, ", ");
            $query .= ") VALUES (" . $paramPlaceholders . ")";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param(str_repeat("s", count($params)), ...$params);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    
        // Delete an object from the database by its primary key
        public function delete($id) {
            $query = "DELETE FROM " . $this->tableName . " WHERE " . $this->primaryKey . " = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("s", $id);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    }
    
?>