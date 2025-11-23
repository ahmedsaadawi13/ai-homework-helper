<!-- FILE: /app/views/auth/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | AI Homework Helper</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1>AI Homework Helper</h1>
            <h2>Create Account</h2>

            <?php
            $flash = View::flash();
            if (!empty($flash)):
                foreach ($flash as $type => $message):
            ?>
                <div class="alert alert-<?php echo $type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php
                endforeach;
            endif;
            ?>

            <form method="POST" action="/register">
                <?php echo View::csrfField(); ?>

                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <hr>

                <div class="form-group">
                    <label for="tenant_name">Organization/Account Name</label>
                    <input type="text" id="tenant_name" name="tenant_name" class="form-control" required placeholder="e.g., Smith Family, ABC Tutoring">
                </div>

                <div class="form-group">
                    <label for="tenant_type">Account Type</label>
                    <select id="tenant_type" name="tenant_type" class="form-control">
                        <option value="family">Family Account</option>
                        <option value="tutoring_center">Tutoring Center</option>
                        <option value="school">School</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-footer">
                Already have an account? <a href="/login">Login</a>
            </p>
        </div>
    </div>
</body>
</html>
