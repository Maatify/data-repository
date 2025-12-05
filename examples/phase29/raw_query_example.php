<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev DataRepository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-05 11:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase29;

use Maatify\DataRepository\Generic\GenericMySQLRepository;

/**
 * @template T of object
 * @extends GenericMySQLRepository<T>
 */
abstract class AdvancedRepository extends GenericMySQLRepository
{
    /**
     * While Generic Repositories abstract most SQL, sometimes raw queries are needed.
     * The underlying adapter is accessible via the protected property $this->adapter.
     */
    public function getComplexReport(): array
    {
        // Access raw PDO connection
        // We access the protected $adapter property directly as this class extends BaseRepository
        $pdo = $this->adapter->getConnection();

        $sql = '
            SELECT u.status, COUNT(*) as count
            FROM users u
            LEFT JOIN orders o ON o.user_id = u.id
            WHERE o.created_at > :date
            GROUP BY u.status
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['date' => '2024-01-01']);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Using Filters with manual SQL construction (using public builders)
     * Note: This usually requires accessing protected helpers or builders manually
     * if extending BaseRepository directly.
     */
}
