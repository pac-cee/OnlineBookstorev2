<?php
// File: models/User.php

class User {
    protected int $id;
    protected string $username;
    protected string $email;
    protected string $createdAt;

    public function __construct(int $id, string $username, string $email, string $createdAt) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->createdAt = $createdAt;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    /**
     * Indicates whether the user has admin privileges.
     */
    public function isAdmin(): bool {
        return false;
    }
}

?>

<?php
// File: models/AdminUser.php

require_once __DIR__ . '/User.php';

class AdminUser extends User {
    public function __construct(int $id, string $username, string $email, string $createdAt) {
        parent::__construct($id, $username, $email, $createdAt);
    }

    /**
     * Admin users can manage other users
     */
    public function canManageUsers(): bool {
        return true;
    }

    /**
     * Overrides base to indicate admin
     */
    public function isAdmin(): bool {
        return true;
    }
}

?>
