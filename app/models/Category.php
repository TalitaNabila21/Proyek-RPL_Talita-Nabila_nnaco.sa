<?php

class Category {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAll() {
        $this->db->query("SELECT * FROM categories ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function getActive() {
        $this->db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM categories WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function insert($data) {
        $this->db->query("INSERT INTO categories (name, slug, description, status) VALUES (:name, :slug, :description, :status)");
        $this->db->bind('name', $data['name']);
        $this->db->bind('slug', $data['slug']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('status', $data['status']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function update($data) {
        $this->db->query("UPDATE categories SET name = :name, slug = :slug, description = :description, status = :status WHERE id = :id");
        $this->db->bind('id', $data['id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('slug', $data['slug']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('status', $data['status']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM categories WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
