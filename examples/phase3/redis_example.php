<?php

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericRedisRepository;

class CacheRepository extends GenericRedisRepository
{
    protected string $keyPrefix = 'cache:user:';
}

echo "Redis Generic Repository Example:\n";
echo "1. find(1) retrieves key 'cache:user:1'.\n";
echo "2. insert(['id' => 1, 'val' => 'test']) sets the key.\n";
