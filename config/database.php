<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $forceFailover = file_exists(__DIR__ . '/failover.flag');

    $primary = [
        'name' => 'hostinger',
        'host' => DB_HOST,
        'port' => DB_PORT,
        'user' => DB_USER,
        'pass' => DB_PASS,
    ];

    $secondary = [
        'name' => 'digitalocean',
        'host' => DB_FAILOVER_HOST,
        'port' => DB_FAILOVER_PORT,
        'user' => DB_FAILOVER_USER,
        'pass' => DB_FAILOVER_PASS,
    ];

    $servers = $forceFailover
        ? [$secondary]
        : [$primary, $secondary];

    foreach ($servers as $server) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $server['host'],
            $server['port'],
            DB_NAME,
            DB_CHARSET
        );

        try {
            $pdo = new PDO($dsn, $server['user'], $server['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 3,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
            ]);

            $pdo->exec("SET time_zone = '-06:00'");

            error_log('Database connection active: ' . $server['name']);

            return $pdo;
        } catch (PDOException $exception) {
            error_log(
                'Database connection failed [' .
                $server['name'] .
                ']: ' .
                $exception->getMessage()
            );

            $pdo = null;
        }
    }

    http_response_code(500);

    exit(
        APP_ENV === 'development'
            ? 'No fue posible conectar con ninguna base de datos disponible.'
            : 'Servicio temporalmente no disponible.'
    );
}
