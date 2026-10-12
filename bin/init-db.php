<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if (!is_dir(dirname(__DIR__).'/var')) {
    mkdir(dirname(__DIR__).'/var', 0775, true);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$connection = $kernel->getContainer()->get('doctrine')->getConnection();

try {
    if (!$connection->createSchemaManager()->tablesExist(['contacts'])) {
        $sql = file_get_contents(dirname(__DIR__).'/database/init.sql');

        if ($sql === false) {
            throw new RuntimeException('Cannot read database/init.sql.');
        }

        $connection->transactional(static function ($connection) use ($sql): void {
            $connection->executeStatement($sql);
        });

        echo "SQLite initialized.\n";
    } else {
        echo "Contacts table already exists.\n";
    }
} finally {
    $connection->close();
    $kernel->shutdown();
}
