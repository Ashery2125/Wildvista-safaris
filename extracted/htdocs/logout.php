<?php

require_once __DIR__ . '/app/core/bootstrap.php';

try {
    logout_user();
} catch (Throwable $e) {
    /*
     * Even if something goes wrong while destroying
     * the session, make sure the session is cleared.
     */
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

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

redirect('index.php');
exit;