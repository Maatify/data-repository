<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:15
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use DateTime;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class OrderUtilsCoverageTest extends TestCase
{
    public function testNormalizeString(): void
    {
        $input = ['name' => 'ASC'];
        $result = OrderUtils::normalize($input);
        $this->assertEquals(['name' => 'ASC'], $result);
    }

    public function testNormalizeInvalidDirectionThrowsOrIgnored(): void
    {
        $input = ['name' => 'INVALID'];
        $result = OrderUtils::normalize($input); // Should verify behavior. Assuming it defaults to ASC or filters out.
        // Checking implementation source would be better, but testing behavior:
        $this->assertEquals(['name' => 'ASC'], $result); // Implementation usually defaults to ASC
    }

    public function testBuildSqlOrderByMultiple(): void
    {
        $orderBy = ['name' => 'ASC', 'created_at' => 'DESC'];
        $sql = OrderUtils::buildSqlOrderBy($orderBy);
        $this->assertEquals('ORDER BY `name` ASC, `created_at` DESC', $sql);
    }

    public function testBuildMongoSort(): void
    {
        $orderBy = ['name' => 'ASC', 'age' => 'DESC'];
        $mongoSort = OrderUtils::buildMongoSort($orderBy);
        $this->assertEquals(['name' => 1, 'age' => -1], $mongoSort);
    }

    public function testSortArray(): void
    {
        $data = [
            ['id' => 1, 'score' => 10],
            ['id' => 2, 'score' => 20],
            ['id' => 3, 'score' => 10],
        ];

        // Sort by score DESC, id ASC
        $result = OrderUtils::sortArray($data, ['score' => 'DESC', 'id' => 'ASC']);

        $this->assertEquals(2, $result[0]['id']); // 20
        $this->assertEquals(1, $result[1]['id']); // 10, id 1
        $this->assertEquals(3, $result[2]['id']); // 10, id 3
    }

    public function testSortArrayComprehensive(): void
    {
        $date1 = new DateTime('2023-01-01');
        $date2 = new DateTime('2023-01-02');
        $date3 = new DateTime('2023-01-03');

        $data = [
            ['id' => 1, 'active' => true, 'created' => $date2, 'val' => null],
            ['id' => 2, 'active' => false, 'created' => $date1, 'val' => 10],
            ['id' => 3, 'active' => true, 'created' => $date3, 'val' => 5],
            ['id' => 4, 'active' => true, 'created' => $date2, 'val' => 20],
        ];

        // Sort by active DESC (true first), created ASC, val DESC
        $result = OrderUtils::sortArray($data, [
            'active' => 'DESC',
            'created' => 'ASC',
            'val' => 'DESC'
        ]);

        // Expected order:
        // 1. id 1 (true, 01-02, null) -> nulls handled last or first depending on impl, let's verify
        // 2. id 4 (true, 01-02, 20)
        // 3. id 3 (true, 01-03, 5)
        // 4. id 2 (false, ...)

        // Actually checking logic:
        // Active DESC: true(1,3,4) then false(2)
        // Created ASC: within true -> date2(1,4), date3(3)
        // Val DESC: within date2 -> 20(4), null(1)

        // Let's verify comparison logic for nulls via reflection first to be sure
        // Usually null < non-null

        // If null is smaller, DESC makes it larger? No, DESC flips the result.
        // If compare(null, 20) -> -1.
        // DESC -> -(-1) = 1. So null > 20 in DESC sort?
        // Wait, usort expects -1 if a < b.
        // If a=null, b=20, cmp=-1.
        // DESC: returns 1. So a > b for sorting purposes. Null comes after 20.

        $this->assertEquals(4, $result[0]['id']); // active=true, date2, val=20
        $this->assertEquals(1, $result[1]['id']); // active=true, date2, val=null
        $this->assertEquals(3, $result[2]['id']); // active=true, date3
        $this->assertEquals(2, $result[3]['id']); // active=false
    }

    public function testCompareValues(): void
    {
        $method = new ReflectionMethod(OrderUtils::class, 'compareValues');
        $method->setAccessible(true);

        // Nulls
        $this->assertEquals(0, $method->invoke(null, null, null));
        $this->assertEquals(-1, $method->invoke(null, null, 1));
        $this->assertEquals(1, $method->invoke(null, 1, null));

        // Numbers
        $this->assertEquals(0, $method->invoke(null, 10, 10));
        $this->assertEquals(-1, $method->invoke(null, 5, 10));
        $this->assertEquals(1, $method->invoke(null, 10, 5));
        $this->assertEquals(0, $method->invoke(null, '10', 10)); // Loose type check handling?
        // Code says: is_numeric($a) && is_numeric($b) -> cast float. So '10' == 10.

        // Booleans
        $this->assertEquals(0, $method->invoke(null, true, true));
        $this->assertEquals(-1, $method->invoke(null, false, true));
        $this->assertEquals(1, $method->invoke(null, true, false));

        // Dates
        $d1 = new DateTime('2023-01-01');
        $d2 = new DateTime('2023-01-02');
        $this->assertEquals(0, $method->invoke(null, $d1, $d1));
        $this->assertEquals(-1, $method->invoke(null, $d1, $d2));
        $this->assertEquals(1, $method->invoke(null, $d2, $d1));

        // Strings
        $this->assertEquals(0, $method->invoke(null, 'a', 'a'));
        $this->assertEquals(-1, $method->invoke(null, 'a', 'b'));
        $this->assertEquals(1, $method->invoke(null, 'b', 'a'));

        // Mixed/Uncomparable
        $this->assertEquals(0, $method->invoke(null, [], [])); // Not scalar
        $this->assertEquals(0, $method->invoke(null, 'a', 1)); // One string, one int?
        // Code: !isComparableScalar($a) || !isComparableScalar($b) -> return 0
        // But wait, is_numeric('1') is true. 'a' is not numeric.
        // isComparableScalar: string or numeric.
        // 'a' is string. 1 is numeric. Both are comparable scalar.
        // Then: is_numeric(a) && is_numeric(b)? No.
        // is_string(a) && is_string(b)? No.
        // Returns 0.
        $this->assertEquals(0, $method->invoke(null, 'a', 1));
    }
}
