<?php

function avatarInitials(?string $name): string
{
    $name = trim((string)$name);

    if ($name === "") {
        return "?";
    }

    $parts = preg_split('/\s+/', $name);
    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : "";

    return mb_strtoupper($first . $last);
}

function avatarColor(?string $seed): string
{
    $colors = ["#0D3B36", "#C9974E", "#2E6F63", "#8C6A3F", "#1F5C52"];
    $index = crc32((string)$seed) % count($colors);

    return $colors[$index];
}

function avatarDefaultSvg(?string $name): string
{
    $initials = avatarInitials($name);
    $color = avatarColor($name);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
        . '<rect width="64" height="64" rx="32" fill="' . $color . '"/>'
        . '<text x="32" y="33" text-anchor="middle" dominant-baseline="central" '
        . 'font-family="Arial, sans-serif" font-size="24" fill="#FFFFFF">' . $initials . '</text>'
        . '</svg>';

    return "data:image/svg+xml;base64," . base64_encode($svg);
}

function avatarSrc(?string $avatar, ?string $name): string
{
    if (!$avatar) {
        return avatarDefaultSvg($name);
    }

    if (str_starts_with($avatar, "http://") || str_starts_with($avatar, "https://")) {
        return $avatar;
    }

    return assets($avatar);
}