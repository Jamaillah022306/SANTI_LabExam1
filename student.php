<?php
/**
 * student.php — Student Model (OOP)
 * Requires database.php in the same folder
 */

require_once __DIR__ . '/database.php';

class Student {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Get full name using string functions */
    public function getFullName(string $first, string $last): string {
        return ucwords(strtolower($first)) . ' ' . ucwords(strtolower($last));
    }

    /** READ - Get all students */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM students ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /** READ - Get one student by ID */
    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** CREATE - Insert new student (POST) */
    public function create(array $data): bool {
        $sql = "INSERT INTO students (id_number, first_name, last_name, email, course)
                VALUES (:id_number, :first_name, :last_name, :email, :course)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_number'  => trim($data['id_number']),
            ':first_name' => ucwords(strtolower(trim($data['first_name']))),
            ':last_name'  => ucwords(strtolower(trim($data['last_name']))),
            ':email'      => strtolower(trim($data['email'])),
            ':course'     => strtoupper(trim($data['course'])),
        ]);
    }

    /** UPDATE - Update student record (POST) */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE students
                SET id_number  = :id_number,
                    first_name = :first_name,
                    last_name  = :last_name,
                    email      = :email,
                    course     = :course
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'         => $id,
            ':id_number'  => trim($data['id_number']),
            ':first_name' => ucwords(strtolower(trim($data['first_name']))),
            ':last_name'  => ucwords(strtolower(trim($data['last_name']))),
            ':email'      => strtolower(trim($data['email'])),
            ':course'     => strtoupper(trim($data['course'])),
        ]);
    }

    /** DELETE - Remove student record */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** VALIDATE - Branching (if/else checks) */
    public function validate(array $data): array {
        $errors = [];

        if (empty(trim($data['id_number']))) {
            $errors[] = "ID Number is required.";
        } elseif (!preg_match('/^\d{5,10}$/', trim($data['id_number']))) {
            $errors[] = "ID Number must be 5–10 digits.";
        }

        if (empty(trim($data['first_name']))) {
            $errors[] = "First name is required.";
        }

        if (empty(trim($data['last_name']))) {
            $errors[] = "Last name is required.";
        }

        if (empty(trim($data['email']))) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if (empty(trim($data['course']))) {
            $errors[] = "Course is required.";
        }

        return $errors;
    }

    /** COUNT - Math function */
    public function countAll(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM students");
        $row  = $stmt->fetch();
        return (int) $row['total'];
    }
}
?>