<?php
// /app/Models/EnrollmentModel.php

namespace App\Models;

use App\Core\Database;
use PDO;

class EnrollmentModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Enrolls a user in a course.
     * @param int $userId The ID of the user.
     * @param int $courseId The ID of the course.
     * @return bool
     */
    public function enroll(int $userId, int $courseId)
    {
        $sql = "INSERT INTO course_enrollments (user_id, course_id) VALUES (:user_id, :course_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
    }

    /**
     * Removes a user's enrollment from a course.
     * @param int $userId The ID of the user.
     * @param int $courseId The ID of the course.
     * @return bool
     */
    public function leave(int $userId, int $courseId)
    {
        $sql = "DELETE FROM course_enrollments WHERE user_id = :user_id AND course_id = :course_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
    }

    /**
     * Checks if a user is already enrolled in a course.
     * @param int $userId The ID of the user.
     * @param int $courseId The ID of the course.
     * @return bool
     */
    public function isEnrolled(int $userId, int $courseId)
    {
        $sql = "SELECT 1 FROM course_enrollments WHERE user_id = :user_id AND course_id = :course_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'course_id' => $courseId]);
        return $stmt->fetchColumn() !== false;
    }
}
