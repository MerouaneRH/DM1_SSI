<?php

declare(strict_types=1);

function get_pdo(): PDO
{
    # Option securite (A02)
    $host = getenv("DB_HOST");
    $db = getenv("DB_NAME");
    $user = getenv("DB_USER");
    $pass = getenv("DB_PASS") ?: "";
    $charset = getenv("DB_CHARSET");

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}
