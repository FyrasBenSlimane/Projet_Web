<?php
require_once __DIR__ . '/../../../config/database.php';

class JobOffersController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAllJobs() {
        $sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote,
                (SELECT COUNT(*) FROM job_applications WHERE job_id = jo.job_id) as applicant_count
                FROM job_offers jo
                LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
                LEFT JOIN locations l ON jo.location_id = l.location_id
                ORDER BY jo.created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $sql = "SELECT * FROM job_categories";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLocations() {
        $sql = "SELECT * FROM locations";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createJob($data) {
        $sql = "INSERT INTO job_offers (title, description, category_id, salary_min, salary_max, location_id, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['salary_min'],
            $data['salary_max'],
            $data['location'],
            $data['image_url']
        ]);
    }

    public function updateJob($data) {
        $sql = "UPDATE job_offers 
                SET title=?, description=?, category_id=?, salary_min=?, salary_max=?, location_id=?, image_url=? 
                WHERE job_id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['salary_min'],
            $data['salary_max'],
            $data['location'],
            $data['image_url'],
            $data['job_id']
        ]);
    }

    public function deleteJob($jobId) {
        $sql = "DELETE FROM job_offers WHERE job_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$jobId]);
    }
}