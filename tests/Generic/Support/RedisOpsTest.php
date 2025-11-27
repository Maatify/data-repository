<?php

declare(strict_types=1);

namespace {
    if (! class_exists(\Redis::class)) {
        class Redis
        {
            /**
             * @var array<string, string>
             */
            private array $store = [];

            public function get(string $key): string|false
            {
                return $this->store[$key] ?? false;
            }

            public function set(string $key, string $value): bool
            {
                $this->store[$key] = $value;

                return true;
            }

            public function del(string $key): int
            {
                unset($this->store[$key]);

                return 1;
            }

            /**
             * @return array<int, string>
             */
            public function keys(string $pattern): array
            {
                return array_keys($this->store);
            }
        }
    }
}

namespace Maatify\DataRepository\Tests\Generic\Support {

    use Maatify\DataRepository\Generic\Support\MongoOps;
    use Maatify\DataRepository\Generic\Support\MysqlOps;
    use Maatify\DataRepository\Generic\Support\RedisOps;
    use MongoDB\Collection;
    use PDO;
    use PHPUnit\Framework\TestCase;

    class RedisOpsTest extends TestCase
    {
        private static function predisStub(): \Predis\Client
        {
            return new class () extends \Predis\Client {
                /**
                 * @var array<string, mixed>
                 */
                private array $store = [];

                public function __construct()
                {
                    // Avoid parent connection logic
                }

                public function get(string $key): mixed
                {
                    return $this->store[$key] ?? ['not-string'];
                }

                public function set(string $key, mixed $value): mixed
                {
                    $this->store[$key] = $value;

                    return 'OK';
                }

                /**
                 * @param array<int, string> $keys
                 */
                public function del(array $keys): mixed
                {
                    foreach ($keys as $key) {
                        unset($this->store[$key]);
                    }

                    return count($keys);
                }

                /**
                 * @return array<int, string>
                 */
                public function keys(string $pattern): array
                {
                    return array_keys($this->store);
                }
            };
        }

        public function testRedisOpsWithRedisDriver(): void
        {
            try {
                $redis = new \Redis();
                $ops   = new RedisOps($redis);

                $this->assertTrue($ops->set('key1', 'value1'));
                $this->assertSame('value1', $ops->get('key1'));
                $this->assertNull($ops->get('missing'));
                $this->assertSame(1, $ops->del('key1'));
                $this->assertSame([], $ops->keys('*'));
            } catch (\RedisException $e) {
                $this->markTestSkipped('Redis extension not usable in this environment: ' . $e->getMessage());
            }
        }

        public function testRedisOpsWithPredisDriver(): void
        {
            $predis = self::predisStub();
            $ops = new RedisOps($predis);

            $this->assertTrue($ops->set('p1', 'value'));
            $this->assertSame('value', $ops->get('p1'));
            // missing key returns a non-string from stub, which should normalize to null
            $this->assertNull($ops->get('missing'));
            $this->assertSame(1, $ops->del('p1'));
        }

        public function testRedisOpsWithFakeDriver(): void
        {
            $fake = new class () {
                /**
                 * @var array<string, mixed>
                 */
                public array $store = [];

                public function get(string $key): mixed
                {
                    return $this->store[$key] ?? ['arr' => 'val'];
                }

                public function set(string $key, string $value): bool
                {
                    $this->store[$key] = $value;

                    return true;
                }

                public function del(string $key): mixed
                {
                    unset($this->store[$key]);

                    return 0;
                }

                /**
                 * @return array<int, string|int>
                 */
                public function keys(string $pattern): array
                {
                    return ['k1', 5];
                }
            };

            $ops = new RedisOps($fake);

            $this->assertTrue($ops->set('f1', 'data'));
            $this->assertSame('data', $ops->get('f1'));

            // array return should be json-encoded
            $encoded = $ops->get('missing');
            $this->assertIsString($encoded);
            $this->assertSame(['arr' => 'val'], json_decode($encoded, true));

            $this->assertSame(0, $ops->del('f1'));
            $this->assertSame(['k1'], $ops->keys('*'));
        }

        public function testMongoOpsAndMysqlOpsAccessors(): void
        {
            $collection = $this->createMock(Collection::class);
            $mongoOps = new MongoOps($collection);
            $this->assertSame($collection, $mongoOps->getCollection());

            $pdo = new PDO('sqlite::memory:');
            $mysqlOps = new MysqlOps($pdo);
            $this->assertSame($pdo, $mysqlOps->getDriver());
        }
    }
}
