<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:20:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Examples\Phase12;

require __DIR__ . '/../../../vendor/autoload.php'; // specific to project structure

use Maatify\DataRepository\Hydration\BaseHydrator;

// 1. Define a target DTO
class ProductDTO
{
    public int $id;
    public string $name;
    public float $price;
    public bool $in_stock;
}

// 2. Create a concrete Hydrator
class ProductHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new ProductDTO();
    }

    protected function onPrepare(array $data): array
    {
        // Normalize keys if necessary
        if (isset($data['product_name'])) {
            $data['name'] = $data['product_name'];
            unset($data['product_name']);
        }
        return $data;
    }

    protected function onCast(array $data): array
    {
        if (isset($data['price'])) {
            $data['price'] = (float)$data['price'];
        }
        if (isset($data['in_stock'])) {
            $data['in_stock'] = (bool)$data['in_stock'];
        }
        return $data;
    }
}

// 3. Usage
$hydrator = new ProductHydrator();

$rawData = [
    'id' => 101,
    'product_name' => 'Super Gadget',
    'price' => '199.99',
    'in_stock' => '1',
    'ignored_field' => 'junk'
];

$product = $hydrator->hydrate($rawData);

echo "Product: " . $product->name . "\n";
echo "Price: " . $product->price . "\n";
echo "In Stock: " . ($product->in_stock ? 'Yes' : 'No') . "\n";

/**
 * Output:
 * Product: Super Gadget
 * Price: 199.99
 * In Stock: Yes
 */
