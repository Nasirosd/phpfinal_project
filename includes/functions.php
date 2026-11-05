<?php
declare(strict_types=1);

function app_url(string $path = ''): string
{
    return APP_BASE_URL . '/' . ltrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_user(): array
{
    if (!current_user()) {
        flash('error', 'Please sign in to continue.');
        redirect('user/login.php');
    }
    return current_user();
}

function require_admin(): void
{
    if (empty($_SESSION['admin'])) {
        flash('error', 'Administrator sign-in is required.');
        redirect('admin/login.php');
    }
}

function valid_date_range(string $checkIn, string $checkOut): bool
{
    $start = DateTime::createFromFormat('!Y-m-d', $checkIn);
    $end = DateTime::createFromFormat('!Y-m-d', $checkOut);
    $today = new DateTimeImmutable('today');
    return $start && $end && $start->format('Y-m-d') === $checkIn
        && $end->format('Y-m-d') === $checkOut && $start >= $today && $end > $start;
}

function page_title(string $title): string
{
    return e($title) . ' | ' . APP_NAME;
}
