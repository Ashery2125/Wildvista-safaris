<?php

require_once __DIR__ . '/app/core/bootstrap.php';


/*
|--------------------------------------------------------------------------
| Already logged in?
|--------------------------------------------------------------------------
| Do NOT send the user to public index.php.
| Send them to their own portal.
|--------------------------------------------------------------------------
*/

$u = current_user();

if ($u) {

    $role = $u['role'] ?? 'customer';

    if ($role === 'admin') {
        redirect('admin/index.php');
        exit;
    }

    if ($role === 'operator') {
        redirect('operator/index.php');
        exit;
    }

    redirect('customer/index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

$title = 'Login';
$error = '';

if (is_post()) {

    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {

        $error = 'Please enter your email and password.';

    } else {

        $st = db()->prepare(
            'SELECT * FROM users WHERE email=? LIMIT 1'
        );

        if (!$st) {

            $error = 'Unable to process your login right now. Please try again.';

        } else {

            $st->bind_param('s', $email);
            $st->execute();

            $user = $st->get_result()->fetch_assoc();

            if (
                $user &&
                !empty($user['password_hash']) &&
                password_verify($pass, $user['password_hash']) &&
                ($user['status'] ?? '') === 'active'
            ) {

                /*
                 * Create authenticated session
                 */
                login_user($user);


                /*
                 * Login logging must never break authentication.
                 */
                try {

                    if (!empty($user['id'])) {

                        @safe_log(
                            'login',
                            'user',
                            (int)$user['id']
                        );
                    }

                } catch (Throwable $e) {

                    // Ignore logging failure.
                }


                /*
                 * Redirect according to account role.
                 */
                $role = $user['role'] ?? 'customer';

                if ($role === 'admin') {

                    redirect('admin/index.php');
                    exit;
                }

                if ($role === 'operator') {

                    redirect('operator/index.php');
                    exit;
                }

                redirect('customer/index.php');
                exit;

            } else {

                $error = 'Invalid login details or account not active.';
            }
        }
    }
}


require __DIR__ . '/app/views/header.php';

?>

<div class="form-shell">

    <div class="form-card">

        <div
            class="eyebrow"
            style="color:var(--green)"
        >
            Welcome back
        </div>


        <h1
            style="
                font-family:Georgia,serif;
                font-size:42px
            "
        >
            Enter Wildvista
        </h1>


        <?php if ($error): ?>

            <div
                class="toast error"
                style="
                    position:relative;
                    top:auto;
                    right:auto;
                    margin:15px 0;
                    animation:none
                "
            >
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="post"
            data-loader
        >

            <?= csrf_field() ?>


            <label for="email">
                Email
            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="<?= e($_POST['email'] ?? '') ?>"
                required
                autocomplete="email"
                placeholder="you@example.com"
            >


            <label for="password">
                Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            >


            <button
                type="submit"
                class="btn btn-dark"
                style="
                    margin-top:18px;
                    width:100%
                "
            >
                Login
            </button>

        </form>


        <p
            class="muted"
            style="margin-top:18px"
        >
            New to Wildvista?

            <a href="<?= url('signup.php') ?>">
                Create a customer account
            </a>
        </p>

    </div>

</div>


<?php require __DIR__ . '/app/views/footer.php'; ?>