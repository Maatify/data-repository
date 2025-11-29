<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 17:00
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Pagination\PaginationResultDTO;

// Mock usage example
class UserRepository extends GenericMySQLRepository
{
    // ... setup
}

// $repo = new UserRepository($pdo);
// $result = $repo->paginate(1, 15);

// echo "Page: " . $result->pagination->page . "\n";
// echo "Total: " . $result->pagination->total . "\n";
// foreach ($result->data as $user) {
//     print_r($user);
// }
