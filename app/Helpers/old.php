<?php

function old(string $field, mixed $default = ''): mixed
{
    $old = $_SESSION['_old_input'][$field] ?? $default;
    
    if (is_array($old)) {
        return $old;
    }

    return htmlspecialchars((string)$old);
}

function flash_old(array $data): void
{
    $_SESSION['_old_input'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old_input']);
}