<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:48
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Helpers;

use Exception;
use Maatify\DataAdapters\Core\EnvironmentConfig;

trait RealAdapterTrait
{
    /**
     * @throws Exception
     */
    protected function getRealConfig(): EnvironmentConfig
    {
        // Pass the current directory as root for config/registry lookup
        // In CI/Test env, we rely on ENV vars mostly, but this satisfies the constructor.
        return new EnvironmentConfig(__DIR__ . '/../../');
    }
}
