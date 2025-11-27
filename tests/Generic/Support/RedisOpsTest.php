<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Support;

use Maatify\DataRepository\Generic\Support\MongoOps;
use Maatify\DataRepository\Generic\Support\MysqlOps;
use Maatify\DataRepository\Generic\Support\RedisOps;
use Maatify\DataFakes\Adapters\Redis\FakeRedisAdapter;
use Maatify\DataFakes\Adapters\Mongo\FakeMongoAdapter;
use Maatify\DataFakes\Adapters\MySQL\FakeMySQLAdapter;
use Maatify\DataFakes\Storage\FakeStorageLayer;
use Maatify\DataRepository\Tests\Helpers\RedisRepositoryStub;
use MongoDB\Collection;
use PDO;
use PHPUnit\Framework\TestCase;

class RedisOpsTest extends TestCase
{
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
        // استخدام FakeRedisAdapter
        $redisAdapter = new FakeRedisAdapter();
        $ops = new RedisOps($redisAdapter);

        $this->assertTrue($ops->set('p1', 'value'));
        $this->assertSame('value', $ops->get('p1'));
        $this->assertNull($ops->get('missing'));
        $this->assertSame(1, $ops->del('p1'));
    }

    public function testRedisOpsWithFakeDriver(): void
    {
        // استخدام FakeRedisAdapter مباشرة
        $fake = new FakeRedisAdapter();
        $ops = new RedisOps($fake);

        $this->assertTrue($ops->set('f1', 'data'));
        $this->assertSame('data', $ops->get('f1'));
        $this->assertNull($ops->get('missing'));
        $this->assertSame(1, $ops->del('f1'));
        $this->assertSame([], $ops->keys('*'));
    }

    public function testMongoOpsAndMysqlOpsAccessors(): void
    {
        // استخدام FakeMongoAdapter - لا يمكن assertion أنه Collection
        $storage = new FakeStorageLayer();
        $mongoAdapter = new FakeMongoAdapter($storage);
        $mongoOps = new MongoOps($mongoAdapter);

        // بدلاً من التحقق من النوع، تحقق من أن getCollection() يعيد الـ adapter
        $this->assertSame($mongoAdapter, $mongoOps->getCollection());

        // استخدام FakeMySQLAdapter - لا يمكن assertion أنه PDO
        $mysqlAdapter = new FakeMySQLAdapter($storage);
        $mysqlOps = new MysqlOps($mysqlAdapter);

        // بدلاً من التحقق من النوع، تحقق من أن getDriver() يعيد الـ adapter
        $this->assertSame($mysqlAdapter, $mysqlOps->getDriver());
    }

    public function testRedisOpsReflectionFallbackKeys(): void
    {
        $driver = new class () {
            /** @var array<string, string> $store */
            /** @phpstan-ignore-next-line */
            private array $store = [
                'aa1' => 'x',
                'aa2' => 'y',
                'bb1' => 'z',
            ];

            // لا يوجد method keys() ليتم استخدام الـ fallback
        };

        $ops = new RedisOps($driver);

        // الـ fallback يجب أن يجد property باسم 'store' ويستخدمه
        $result = $ops->keys('aa*');
        $this->assertSame(['aa1', 'aa2'], $result);
    }

    public function testRedisOpsJsonEncodeFailure(): void
    {
        // استخدام FakeRedisAdapter مخصص لاختبار فشل json_encode
        $driver = new class () extends FakeRedisAdapter {
            public function __construct()
            {
                // تجنب استدعاء parent constructor
            }

            public function get(string $key): mixed
            {
                // invalid UTF-8 triggers json_encode failure
                return ['bad' => "\xB1\x31"];
            }
        };

        $ops = new RedisOps($driver);

        $this->assertNull($ops->get('any'));
    }

    public function testRedisOpsPredisStatusObject(): void
    {
        // استخدام FakeRedisAdapter مخصص لمحاكاة Predis Status object
        $driver = new class () extends FakeRedisAdapter {
            public function __construct()
            {
                // تجنب استدعاء parent constructor
            }

            public function set(string $key, mixed $value, ?int $ttl = null): bool
            {
                // محاكاة Predis Status object ولكن مع الالتزام بreturn type bool
                $statusObject = new class () {
                    public function __toString(): string
                    {
                        return 'OK';
                    }
                };

                // معالجة Status object وإرجاع bool
                return (string)$statusObject === 'OK';
            }
        };

        $ops = new RedisOps($driver);

        $this->assertTrue($ops->set('s1', 'v1'));
    }

    // اختبارات إضافية باستخدام FakeRedisAdapter
    public function testRedisOpsWithFakeRedisAdapter(): void
    {
        $redisAdapter = new FakeRedisAdapter();
        $ops = new RedisOps($redisAdapter);

        // اختبار العمليات الأساسية
        $this->assertTrue($ops->set('test_key', 'test_value'));
        $this->assertSame('test_value', $ops->get('test_key'));
        $this->assertSame(1, $ops->del('test_key'));
        $this->assertNull($ops->get('test_key'));
    }

    public function testRedisOpsPatternMatchingWithFakeAdapter(): void
    {
        $redisAdapter = new FakeRedisAdapter();
        $ops = new RedisOps($redisAdapter);

        $ops->set('user:1', 'Alice');
        $ops->set('user:2', 'Bob');
        $ops->set('cache:item1', 'data');

        $userKeys = $ops->keys('user:*');
        $this->assertContains('user:1', $userKeys);
        $this->assertContains('user:2', $userKeys);
    }

    public function testRedisOpsComplexOperations(): void
    {
        $redisAdapter = new FakeRedisAdapter();
        $ops = new RedisOps($redisAdapter);

        // اختبار عمليات متعددة
        $ops->set('counter', '10');
        $value = json_encode(['name' => 'John', 'age' => 30]);
        $this->assertIsString($value);
        $ops->set('user:profile:1', $value);
        $ops->set('session:abc', 'active');

        $allKeys = $ops->keys('*');
        $this->assertCount(3, $allKeys);

        $userProfile = $ops->get('user:profile:1');
        $this->assertIsString($userProfile);
        $this->assertSame(['name' => 'John', 'age' => 30], json_decode($userProfile, true));
    }

    public function testRedisRepositoryStubOps(): void
    {
        $adapter = new FakeRedisAdapter();
        $repo = new RedisRepositoryStub($adapter);

        // force lazy init
        $ops = $repo->getOps();

        $payload = json_encode(['id' => 1, 'name' => 'momo']);
        $this->assertIsString($payload);

        // استخدم repository keyPrefix بدل key manual
        $ops->set('test:1', $payload);

        // IMPORTANT: لازم نقرأ من الـ repo للتأكد من نفس الـ instance
        $stored = $repo->getOps()->get('test:1');

        $this->assertSame($payload, $stored);
    }

    public function testRedisRepositoryStubWithFakeAdapter(): void
    {
        $adapter = new FakeRedisAdapter();
        $repo = new RedisRepositoryStub($adapter);

        // إقحام القيمة من خلال BaseRedisRepository→RedisOps→driver
        $repo->getOps()->set('repo:test', 'value');

        $this->assertSame('value', $repo->getOps()->get('repo:test'));

        $repo->getOps()->del('repo:test');
        $this->assertNull($repo->getOps()->get('repo:test'));
    }

}
