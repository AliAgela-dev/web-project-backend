<?php
// /app/Controllers/CourseController.php

namespace App\Controllers;

use App\Core\Request;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Core\Database;
use PDO;

class CourseController
{
    private $request;
    private $courseModel;
    private $enrollmentModel;

    /**
     * The Request object is now "injected" into the controller.
     * @param Request $request The application's request object.
     */
    public function __construct(Request $request)
    {
        // Use the injected request object instead of creating a new one
        $this->request = $request;
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * [GET /api/courses] - Fetches a list of all available courses.
     */
    public function index()
    {
        $courses = $this->courseModel->findAll();
        echo json_encode($courses);
    }

    /**
     * [POST /api/courses] - Creates a new course. (Admin only)
     */
    public function create()
    {
        $user = $this->request->user;
        // Search the database for the user ID from $user->sub
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }
        if (!isset($userRecord['role']) || $userRecord['role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        $data = $this->request->getBody();

        if (empty($data['title']) || empty($data['speaker']) || !isset($data['rating']) || !isset($data['price'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Missing required fields: title, speaker, rating, price.']);
            return;
        }

        if ($this->courseModel->create($data)) {
            http_response_code(201); // Created
            echo json_encode(['message' => 'Course created successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create course.']);
        }
    }

    /**
     * [DELETE /api/courses/{id}] - Deletes a course. (Admin only)
     * @param int $courseId The ID of the course from the URL.
     */
    public function delete($courseId)
    {
        $user = $this->request->user;
        // Search the database for the user ID from $user->sub
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }
        if (!isset($userRecord['role']) || $userRecord['role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        if ($this->courseModel->delete($courseId)) {
            echo json_encode(['message' => 'Course deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete course.']);
        }
    }

    /**
     * [POST /api/courses/{id}/enroll] - Enrolls the current user in a course.
     * @param int $courseId The ID of the course from the URL.
     */
    public function enroll($courseId)
    {
        $userId = $this->request->user->sub; // Get user ID from the token

        if (!$this->courseModel->findById($courseId)) {
            http_response_code(404);
            echo json_encode(['error' => 'Course not found.']);
            return;
        }

        if ($this->enrollmentModel->isEnrolled($userId, $courseId)) {
            http_response_code(409); // Conflict
            echo json_encode(['error' => 'You are already enrolled in this course.']);
            return;
        }

        if ($this->enrollmentModel->enroll($userId, $courseId)) {
            http_response_code(201);
            echo json_encode(['message' => 'Successfully enrolled in the course.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to enroll in the course.']);
        }
    }

    /**
     * [DELETE /api/courses/{id}/leave] - User leaves a course.
     * @param int $courseId The ID of the course from the URL.
     */
    public function leave($courseId)
    {
        $userId = $this->request->user->sub; // Get user ID from the token

        if ($this->enrollmentModel->leave($userId, $courseId)) {
            echo json_encode(['message' => 'Successfully left the course.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to leave the course or you were not enrolled.']);
        }
    }

    /**
     * [GET /api/courses/enrolled] - Fetches a list of courses the user is enrolled in.
     */
    public function getEnrolledCourses()
    {
        $userId = $this->request->user->sub;

        // The database connection is handled by the model now, so this line is removed.
        // $db = \App\Core\Database::getInstance();

        // The SQL query is corrected to use 'course_enrollments'
        $sql = "SELECT c.* FROM courses c
                INNER JOIN course_enrollments e ON c.id = e.course_id
                WHERE e.user_id = :user_id";

        $db = Database::getInstance(); // Get DB instance
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // We use echo to send the response, not return.
        echo json_encode(['courses' => $courses]);
    }
}
