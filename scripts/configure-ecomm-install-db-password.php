<?php
declare(strict_types=1);

$password = getenv('ECOMM_INSTALL_DB_PASSWORD');
$socket = getenv('MYSQL_SOCKET');
$marker = dirname(__DIR__) . '/.mysql-data/ecomm-installer-password-configured';

if ($password === false || $password === '') {
    fwrite(STDERR, "ECOMM_INSTALL_DB_PASSWORD must not be empty.\n");
    exit(1);
}

if ($socket === false || $socket === '' || !file_exists($socket)) {
    fwrite(STDERR, "The local MySQL socket is unavailable; installer DB password was not changed.\n");
    exit(1);
}

try {
    $db = new PDO(
        "mysql:unix_socket={$socket};dbname=mysql",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $quotedPassword = $db->quote($password);
    if ($quotedPassword === false) {
        throw new RuntimeException('Unable to quote the installer password.');
    }

    $db->exec("ALTER USER 'ecomm_installer'@'127.0.0.1' IDENTIFIED BY {$quotedPassword}");
    $db->exec('FLUSH PRIVILEGES');
    $applicationDb = new PDO(
        'mysql:host=127.0.0.1;dbname=ecomm_install;charset=utf8mb4',
        'ecomm_installer',
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $applicationDb->query('SELECT 1')->fetchColumn();

    if (file_put_contents($marker, "configured\n", LOCK_EX) === false) {
        throw new RuntimeException('Unable to save the configuration marker.');
    }
    chmod($marker, 0600);
} catch (Throwable $error) {
    fwrite(STDERR, "Unable to configure the local installer database account password.\n");
    exit(1);
}
