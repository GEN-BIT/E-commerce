<?php
/**
 * Authentication / authorization helpers
 * Stage: 3 - Authentication
 */

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === ROLE_ADMIN;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('auth/signin.php');
    }
}

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}
