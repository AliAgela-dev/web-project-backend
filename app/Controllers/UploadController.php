<?php
// /app/Controllers/UploadController.php

namespace App\Controllers;

class UploadController
{
    public function uploadCourseImage()
    {
        if (!isset($_FILES['image']) || !isset($_POST['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No image or course_id provided.']);
            return;
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid image type.']);
            return;
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            http_response_code(400);
            echo json_encode(['error' => 'File too large.']);
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/courses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $url = '/uploads/courses/' . $filename;
            // Update the course record in the database
            $courseId = (int) $_POST['course_id'];
            $courseModel = new \App\Models\CourseModel();
            $courseModel->update($courseId, ['image_url' => $url]);
            echo json_encode(['message' => 'Image uploaded and course updated successfully.', 'image_url' => $url]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to upload image.']);
        }
    }
}
