<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:00
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Fake\Ordering;

use DateTime;
use InvalidArgumentException;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use PHPUnit\Framework\TestCase;

class OrderingFakeTest extends TestCase
{
    public function testNormalize(): void
    {
        $this->assertSame([], OrderUtils::normalize(null));
        $this->assertSame([], OrderUtils::normalize([]));

        $input = ['id' => 'asc', 'name' => 'DESC', 'age' => 'Invalid'];
        $expected = ['id' => 'ASC', 'name' => 'DESC', 'age' => 'ASC'];

        $this->assertSame($expected, OrderUtils::normalize($input));
    }

    public function testNormalizeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid order direction: 'Invalid'. Must be 'ASC' or 'DESC'.");
        OrderUtils::normalize(['age' => 'Invalid'], true);
    }

    public function testBuildSqlOrderBy(): void
    {
        $this->assertSame('', OrderUtils::buildSqlOrderBy(null));

        $input = ['name' => 'asc', 'created_at' => 'desc', 'users.email' => 'asc'];
        // Note: The improved OrderUtils handles quoting and dot notation
        $expected = 'ORDER BY `name` ASC, `created_at` DESC, `users`.`email` ASC';

        $this->assertSame($expected, OrderUtils::buildSqlOrderBy($input));
    }

    public function testBuildJsonOrderBy(): void
    {
        $sql = OrderUtils::buildJsonOrderBy('meta', 'user.name', 'desc');
        $expected = "JSON_UNQUOTE(JSON_EXTRACT(`meta`, '$.user.name')) DESC";
        $this->assertSame($expected, $sql);
    }

    public function testBuildMongoSort(): void
    {
        $this->assertSame([], OrderUtils::buildMongoSort(null));

        $input = ['name' => 'asc', 'score' => 'desc'];
        $expected = ['name' => 1, 'score' => -1];

        $this->assertSame($expected, OrderUtils::buildMongoSort($input));
    }

    public function testSortArray(): void
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = [
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 20],
        ];

        // Sort by Name ASC
        $sorted = OrderUtils::sortArray($data, ['name' => 'asc']);
        /** @var array{name: string, age: int} $row0 */
        $row0 = $sorted[0];
        /** @var array{name: string, age: int} $row1 */
        $row1 = $sorted[1];
        /** @var array{name: string, age: int} $row2 */
        $row2 = $sorted[2];

        $this->assertSame('Alice', $row0['name']);
        $this->assertSame('Bob', $row1['name']);
        $this->assertSame('Bob', $row2['name']);

        // Sort by Name ASC, then Age ASC
        $sorted = OrderUtils::sortArray($data, ['name' => 'asc', 'age' => 'asc']);

        /** @var array{name: string, age: int} $row0 */
        $row0 = $sorted[0];
        /** @var array{name: string, age: int} $row1 */
        $row1 = $sorted[1];
        /** @var array{name: string, age: int} $row2 */
        $row2 = $sorted[2];

        $this->assertSame('Alice', $row0['name']);
        $this->assertSame('Bob', $row1['name']);
        $this->assertSame(20, $row1['age']);
        $this->assertSame('Bob', $row2['name']);
        $this->assertSame(30, $row2['age']);

        // Sort by Name ASC, then Age DESC
        $sorted = OrderUtils::sortArray($data, ['name' => 'asc', 'age' => 'desc']);

        /** @var array{name: string, age: int} $row0 */
        $row0 = $sorted[0];
        /** @var array{name: string, age: int} $row1 */
        $row1 = $sorted[1];
        /** @var array{name: string, age: int} $row2 */
        $row2 = $sorted[2];

        $this->assertSame('Alice', $row0['name']);
        $this->assertSame('Bob', $row1['name']);
        $this->assertSame(30, $row1['age']);
        $this->assertSame('Bob', $row2['name']);
        $this->assertSame(20, $row2['age']);
    }

    public function testSortArrayTypes(): void
    {
        $date1 = new DateTime('2023-01-01');
        $date2 = new DateTime('2023-02-01');

        /** @var array<int, array<string, mixed>> $data */
        $data = [
            ['val' => 10],
            ['val' => null],
            ['val' => 5],
        ];

        $sorted = OrderUtils::sortArray($data, ['val' => 'ASC']);
        // Nulls first check
        /** @var array{val: ?int} $row0 */
        $row0 = $sorted[0];
        /** @var array{val: ?int} $row1 */
        $row1 = $sorted[1];
        /** @var array{val: ?int} $row2 */
        $row2 = $sorted[2];

        $this->assertNull($row0['val']);
        $this->assertSame(5, $row1['val']);
        $this->assertSame(10, $row2['val']);

        /** @var array<int, array<string, mixed>> $dataDates */
        $dataDates = [
            ['date' => $date2],
            ['date' => $date1],
        ];
        $sortedDates = OrderUtils::sortArray($dataDates, ['date' => 'ASC']);

        /** @var array{date: DateTime} $dRow0 */
        $dRow0 = $sortedDates[0];
        /** @var array{date: DateTime} $dRow1 */
        $dRow1 = $sortedDates[1];

        $this->assertSame($date1, $dRow0['date']);
        $this->assertSame($date2, $dRow1['date']);
    }

    public function testIsValidDirection(): void
    {
        $this->assertTrue(OrderUtils::isValidDirection('asc'));
        $this->assertTrue(OrderUtils::isValidDirection('DESC'));
        $this->assertFalse(OrderUtils::isValidDirection('foo'));
    }

    public function testFromString(): void
    {
        $str = 'name:asc,age:desc, created:asc';
        $expected = ['name' => 'ASC', 'age' => 'DESC', 'created' => 'ASC'];
        $this->assertSame($expected, OrderUtils::fromString($str));
    }

    public function testReverse(): void
    {
        $input = ['name' => 'ASC', 'age' => 'DESC'];
        $expected = ['name' => 'DESC', 'age' => 'ASC'];
        $this->assertSame($expected, OrderUtils::reverse($input));
    }

    public function testMerge(): void
    {
        $o1 = ['name' => 'ASC'];
        $o2 = ['age' => 'DESC', 'name' => 'DESC'];

        // o2 overrides o1 for 'name'
        $expected = ['name' => 'DESC', 'age' => 'DESC'];

        $this->assertSame($expected, OrderUtils::merge($o1, $o2));
    }

    public function testCompareNulls(): void
    {
        $this->assertSame(0, OrderUtils::compareValues(null, null));
        $this->assertSame(-1, OrderUtils::compareValues(null, 5));
        $this->assertSame(1, OrderUtils::compareValues(10, null));
    }

    public function testCompareNumbers(): void
    {
        $this->assertSame(-1, OrderUtils::compareValues(1, 2));
        $this->assertSame(1, OrderUtils::compareValues(5, 3));
        $this->assertSame(0, OrderUtils::compareValues(10, 10));

        // Non-numeric — should fall back to 0
        $this->assertSame(0, OrderUtils::compareValues('a', 10));
        $this->assertSame(0, OrderUtils::compareValues(10, 'a'));
    }

    public function testCompareBooleans(): void
    {
        $this->assertSame(-1, OrderUtils::compareValues(false, true));
        $this->assertSame(1, OrderUtils::compareValues(true, false));
        $this->assertSame(0, OrderUtils::compareValues(true, true));

        // Non-bool fallback
        $this->assertSame(0, OrderUtils::compareValues(true, 1));
    }

    public function testCompareDates(): void
    {
        $d1 = new DateTime('2024-01-01');
        $d2 = new DateTime('2024-02-01');

        $this->assertSame(-1, OrderUtils::compareValues($d1, $d2));
        $this->assertSame(1, OrderUtils::compareValues($d2, $d1));
        $this->assertSame(0, OrderUtils::compareValues($d1, $d1));

        // Non-date fallback
        $this->assertSame(0, OrderUtils::compareValues($d1, '2024-01-01'));
    }

    public function testIsComparableScalar(): void
    {
        $ref = new \ReflectionMethod(OrderUtils::class, 'isComparableScalar');
        $ref->setAccessible(true);

        $this->assertTrue($ref->invoke(null, 'abc'));
        $this->assertTrue($ref->invoke(null, 123));
        $this->assertTrue($ref->invoke(null, 12.5));

        $this->assertFalse($ref->invoke(null, null));
        $this->assertFalse($ref->invoke(null, []));
        $this->assertFalse($ref->invoke(null, new \stdClass()));
    }

    public function testCompareValuesFull(): void
    {
        // Nulls
        $this->assertSame(-1, OrderUtils::compareValues(null, 1));

        // Numbers
        $this->assertSame(-1, OrderUtils::compareValues(1, 2));

        // Booleans
        $this->assertSame(-1, OrderUtils::compareValues(false, true));

        // Strings
        $this->assertSame(-1, OrderUtils::compareValues('a', 'b'));
        $this->assertSame(0, OrderUtils::compareValues('abc', 'abc'));

        // Incomparable
        $this->assertSame(0, OrderUtils::compareValues(['a'], ['b']));
    }

    public function testIsValidDirectionEdgeCases(): void
    {
        $this->assertFalse(OrderUtils::isValidDirection(''));
        $this->assertFalse(OrderUtils::isValidDirection('asc '));
        $this->assertFalse(OrderUtils::isValidDirection(' ASC '));

        // Lowercase mixed
        $this->assertTrue(OrderUtils::isValidDirection('aSc'));
    }

}
