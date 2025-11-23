<!-- FILE: /app/views/auth/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AI Homework Helper</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1>AI Homework Helper</h1>
            <h2>Login</h2>

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

            <form method="POST" action="/login">
                <?php echo View::csrfField(); ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="/register">Sign up</a>
            </p>

            <div class="demo-credentials">
                <h4>Demo Credentials:</h4>
                <p><strong>Platform Admin:</strong> admin@aihomework.com / password123</p>
                <p><strong>School Admin:</strong> principal@greenwood-high.edu / password123</p>
                <p><strong>Teacher:</strong> emily.davis@greenwood-high.edu / password123</p>
                <p><strong>Student:</strong> alex.thompson@student.greenwood.edu / password123</p>
            </div>
        </div>
    </div>
</body>
</html>
