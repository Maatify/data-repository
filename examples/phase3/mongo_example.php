<?php

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMongoRepository;

class LogRepository extends GenericMongoRepository
{
    protected string $collectionName = 'app_logs';
}

echo "Mongo Generic Repository Example:\n";
echo "1. insert(['level' => 'info', 'msg' => 'Test']) adds a doc.\n";
echo "2. find('507f1f77bcf86cd799439011') retrieves by ObjectId.\n";
