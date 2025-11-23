<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository Tests Bootstrap
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-21 20:00
 */

use Maatify\Bootstrap\Core\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

// 🔥 Initialize repository environment before any tests run
Bootstrap::init(dirname(__DIR__));
