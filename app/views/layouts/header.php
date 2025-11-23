<!-- FILE: /app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'AI Homework Helper'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php if (Auth::check()): ?>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/dashboard" class="nav-brand">AI Homework Helper</a>
            <ul class="nav-menu">
                <li><a href="/dashboard">Dashboard</a></li>

                <?php if (Auth::hasRole(['tenant_admin', 'teacher'])): ?>
                    <li><a href="/students">Students</a></li>
                    <li><a href="/classes">Classes</a></li>
                    <li><a href="/subjects">Subjects</a></li>
                <?php endif; ?>

                <li><a href="/homework">Homework</a></li>

                <?php if (Auth::hasRole(['tenant_admin', 'teacher'])): ?>
                    <li><a href="/ai/sessions">AI Sessions</a></li>
                    <li><a href="/reports/homework">Reports</a></li>
                <?php endif; ?>

                <?php if (Auth::isTenantAdmin()): ?>
                    <li><a href="/admin/subscription">Subscription</a></li>
                <?php endif; ?>

                <?php if (Auth::isPlatformAdmin()): ?>
                    <li><a href="/admin/tenants">Tenants</a></li>
                <?php endif; ?>

                <li class="nav-user">
                    <span><?php echo htmlspecialchars(Auth::user()['name']); ?></span>
                    <ul class="dropdown">
                        <li><a href="/logout">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <main class="main-content">
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
