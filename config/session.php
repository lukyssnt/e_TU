<?php
/**
 * Session Management
 * E-ADMIN TU MA AL IHSAN
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes

    session_start();
}

class Session
{

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }

    /**
     * Set user session data
     */
    public static function setUser($userId, $username, $fullName, $role, $permissions = [])
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['role'] = $role;
        $_SESSION['permissions'] = $permissions;
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get session data
     */
    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission($module)
    {
        $permissions = self::get('permissions') ?? [];
        return in_array($module, $permissions) || in_array('all', $permissions);
    }

    /**
     * Check session timeout
     */
    public static function checkTimeout()
    {
        if (isset($_SESSION['last_activity'])) {
            $timeout = 1800; // 30 minutes
            if (time() - $_SESSION['last_activity'] > $timeout) {
                self::destroy();
                return false;
            }
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Destroy session
     */
    public static function destroy()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Set flash message
     */
    public static function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear flash message
     */
    public static function getFlash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
