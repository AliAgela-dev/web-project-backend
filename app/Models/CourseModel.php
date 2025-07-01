<?php
// /app/Models/CourseModel.php

namespace App\Models;

use App\Core\Database;
use PDO;

class CourseModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new course. (Admin only)
     * @param array $data Contains title, speaker, rating, price.
     * @return bool
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO courses (title, speaker, rating, price) VALUES (:title, :speaker, :rating, :price)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'title' => $data['title'],
            'speaker' => $data['speaker'],
            'rating' => $data['rating'],
            'price' => $data['price']
        ]);
    }

    /**
     * Fetches all courses from the database.
     * @return array
     */
    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a course. (Admin only)
     * @param int $id The ID of the course to delete.
     * @return bool
     */
    public function delete(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Finds a single course by its ID.
     * @param int $id The course ID.
     * @return mixed
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params[':title'] = $data['title'];
        }
        if (isset($data['speaker'])) {
            $fields[] = 'speaker = :speaker';
            $params[':speaker'] = $data['speaker'];
        }
        if (isset($data['rating'])) {
            $fields[] = 'rating = :rating';
            $params[':rating'] = $data['rating'];
        }
        if (isset($data['price'])) {
            $fields[] = 'price = :price';
            $params[':price'] = $data['price'];
        }
        if (isset($data['image_url'])) {
            $fields[] = 'image_url = :image_url';
            $params[':image_url'] = $data['image_url'];
        }
        if (empty($fields))
            return false;
        $sql = 'UPDATE courses SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    /**
     * Updates a course by ID.
     * @param int $id The course ID.
     * @param array $data The data to update (title, speaker, rating, price, etc).
     * @return bool True on success, false on failure.
     */
}
