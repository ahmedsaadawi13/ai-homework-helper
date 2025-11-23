<?php
// FILE: /app/helpers/Auth.php

/**
 * Authentication helper class
 * Handles user authentication and authorization
 */
class Auth
{
    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public static function id()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     *
     * @return array|null
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        if (!isset($_SESSION['user_data'])) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindValue(':id', self::id(), PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['user_data'] = $stmt->fetch();
        }

        return $_SESSION['user_data'];
    }

    /**
     * Get current user role
     *
     * @return string|null
     */
    public static function role()
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Get current tenant ID
     *
     * @return int|null
     */
    public static function tenantId()
    {
        return $_SESSION['tenant_id'] ?? null;
    }

    /**
     * Check if user has specific role
     *
     * @param string|array $roles Role or array of roles
     * @return bool
     */
    public static function hasRole($roles)
    {
        if (!self::check()) {
            return false;
        }

        $userRole = self::role();

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    /**
     * Check if user is platform admin
     *
     * @return bool
     */
    public static function isPlatformAdmin()
    {
        return self::hasRole('platform_admin');
    }

    /**
     * Check if user is tenant admin
     *
     * @return bool
     */
    public static function isTenantAdmin()
    {
        return self::hasRole('tenant_admin');
    }

    /**
     * Check if user is teacher
     *
     * @return bool
     */
    public static function isTeacher()
    {
        return self::hasRole('teacher');
    }

    /**
     * Check if user is student
     *
     * @return bool
     */
    public static function isStudent()
    {
        return self::hasRole('student');
    }

    /**
     * Login user
     *
     * @param array $user User data
     */
    public static function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = $user;
        $_SESSION['role'] = $user['role'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
    }

    /**
     * Logout user
     */
    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Attempt to authenticate user
     *
     * @param string $email Email
     * @param string $password Password
     * @return bool|array User data on success, false on failure
     */
    public static function attempt($email, $password)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
