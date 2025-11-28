<?php

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Resolver\RepositoryResolver;

// Mocking a concrete class for the example
class UserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';
}

// In a real app, Resolver would provide the adapter
// $resolver = new RepositoryResolver();
// $repo = new UserRepository($resolver->getAdapter('mysql'));

echo "MySQL Generic Repository Example:\n";
echo "1. find(1) retrieves a user by ID.\n";
echo "2. findBy(['status' => 'active']) retrieves active users.\n";
echo "3. insert(['name' => 'John']) adds a user.\n";
