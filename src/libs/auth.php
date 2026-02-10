<?php

declare(strict_types=1);

// ============================================================
// ADMIN AUTHENTICATION
// ============================================================

if (!function_exists('isAdminAuthenticated')) {
    function isAdminAuthenticated(): bool
    {
        return !empty($_SESSION['admin_authenticated'])
            && !empty($_SESSION['admin_user_id']);
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(): void
    {
        if (!isAdminAuthenticated()) {
            if (isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Neautorizovan pristup.']);
                exit;
            }
            header('Location: /admin/login.php');
            exit;
        }
    }
}

if (!function_exists('attemptAdminLogin')) {
    function attemptAdminLogin(string $username, string $password): bool
    {
        $stmt = db()->prepare('SELECT id, username, password_hash, is_active FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin || !$admin['is_active']) {
            return false;
        }

        if (password_verify($password, $admin['password_hash'])) {
            // Regenerate session to prevent fixation
            session_regenerate_id(true);

            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_user_id']       = (int) $admin['id'];
            $_SESSION['admin_username']      = $admin['username'];
            $_SESSION['admin_logged_in_at']  = time();

            // Update last login
            $update = db()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
            $update->execute([$admin['id']]);

            return true;
        }

        return false;
    }
}

if (!function_exists('logoutAdmin')) {
    function logoutAdmin(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}

// ============================================================
// USER AUTHENTICATION
// ============================================================

if (!function_exists('isUserAuthenticated')) {
    function isUserAuthenticated(): bool
    {
        return !empty($_SESSION['user_authenticated'])
            && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('currentUserId')) {
    function currentUserId(): ?int
    {
        return isUserAuthenticated() ? (int) $_SESSION['user_id'] : null;
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?array
    {
        $userId = currentUserId();
        if (!$userId) {
            return null;
        }

        static $user = null;
        if ($user === null) {
            $stmt = db()->prepare('SELECT id, first_name, last_name, email, phone, status, email_verified, marketing_optin, created_at FROM users WHERE id = ? AND status = ?');
            $stmt->execute([$userId, 'active']);
            $user = $stmt->fetch() ?: null;
        }

        return $user;
    }
}

if (!function_exists('requireUser')) {
    function requireUser(): void
    {
        if (!isUserAuthenticated()) {
            if (isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Morate biti prijavljeni.']);
                exit;
            }
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
}

if (!function_exists('registerUser')) {
    function registerUser(string $firstName, string $lastName, string $email, string $password, bool $marketingOptin = false): array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        // Check existing
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email adresa je već registrovana.'];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $verifyToken = bin2hex(random_bytes(32));

        $conn = db();
        $conn->beginTransaction();

        try {
            $insert = $conn->prepare('
                INSERT INTO users (first_name, last_name, email, password_hash, marketing_optin, verify_token)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $insert->execute([
                sanitize($firstName),
                sanitize($lastName),
                $email,
                $passwordHash,
                $marketingOptin ? 1 : 0,
                $verifyToken,
            ]);

            $userId = (int) $conn->lastInsertId();

            // Initialize loyalty record
            $conn->prepare('INSERT INTO user_loyalty (user_id) VALUES (?)')->execute([$userId]);

            // Add to email subscribers if opted in
            if ($marketingOptin) {
                $unsubToken = bin2hex(random_bytes(16));
                $conn->prepare('
                    INSERT IGNORE INTO email_subscribers (user_id, name, email, source, unsubscribe_token)
                    VALUES (?, ?, ?, ?, ?)
                ')->execute([$userId, sanitize("$firstName $lastName"), $email, 'registration', $unsubToken]);
            }

            // Merge guest cart to user
            mergeGuestCartToUser($userId);

            $conn->commit();

            return ['success' => true, 'user_id' => $userId, 'verify_token' => $verifyToken];
        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Greška pri registraciji. Pokušajte ponovo.'];
        }
    }
}

if (!function_exists('attemptUserLogin')) {
    function attemptUserLogin(string $email, string $password): array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        $stmt = db()->prepare('SELECT id, first_name, last_name, email, password_hash, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Pogrešan email ili lozinka.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Vaš nalog je blokiran. Kontaktirajte podršku.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Pogrešan email ili lozinka.'];
        }

        // Regenerate session
        session_regenerate_id(true);

        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id']            = (int) $user['id'];
        $_SESSION['user_name']          = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']         = $user['email'];

        // Merge guest cart
        mergeGuestCartToUser((int) $user['id']);

        // Rehash password if needed
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$newHash, $user['id']]);
        }

        return ['success' => true, 'user' => $user];
    }
}

if (!function_exists('logoutUser')) {
    function logoutUser(): void
    {
        unset(
            $_SESSION['user_authenticated'],
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_email']
        );
    }
}

if (!function_exists('mergeGuestCartToUser')) {
    function mergeGuestCartToUser(int $userId): void
    {
        $sessionId = session_id();
        if (!$sessionId) {
            return;
        }

        try {
            db()->prepare('UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL')
                ->execute([$userId, $sessionId]);
        } catch (\Throwable $e) {
            error_log('Cart merge error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('requestPasswordReset')) {
    function requestPasswordReset(string $email): bool
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        $stmt = db()->prepare('SELECT id, first_name FROM users WHERE email = ? AND status = ?');
        $stmt->execute([$email, 'active']);
        $user = $stmt->fetch();

        if (!$user) {
            return true; // Don't reveal user existence
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        db()->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?')
            ->execute([$token, $expires, $user['id']]);

        $resetUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' .
                    ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/reset-password?token=' . $token;

        $body = "<p>Poštovani {$user['first_name']},</p>
                 <p>Zatražili ste resetovanje lozinke. Kliknite na link ispod:</p>
                 <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
                 <p>Link važi 1 sat. Ako niste zatražili ovu promenu, ignorišite ovaj email.</p>";

        sendEmail($email, 'Resetovanje lozinke - Egoire', $body);

        return true;
    }
}

if (!function_exists('resetPassword')) {
    function resetPassword(string $token, string $newPassword): array
    {
        $stmt = db()->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() AND status = ?');
        $stmt->execute([$token, 'active']);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Nevažeći ili istekli link za resetovanje.'];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        db()->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?')
            ->execute([$hash, $user['id']]);

        return ['success' => true];
    }
}

