<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Hydration;

use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HydrationContext::class)]
class HydrationContextCoverageTest extends TestCase
{
    private HydrationContext $context;

    protected function setUp(): void
    {
        $this->context = new HydrationContext();
    }

    public function testConstructor(): void
    {
        $expectedStages = [
            HydrationContext::STAGE_PREPARE,
            HydrationContext::STAGE_CAST,
            HydrationContext::STAGE_MAP,
            HydrationContext::STAGE_VALIDATE,
            HydrationContext::STAGE_COMPLETE,
        ];
        $this->assertEquals($expectedStages, $this->context->getStages());
    }

    public function testGetAndSetStages(): void
    {
        $stages = ['test_stage_1', 'test_stage_2'];
        $this->context->setStages($stages);
        $this->assertEquals($stages, $this->context->getStages());
    }

    public function testAddAndGetMeta(): void
    {
        $this->context->addMeta('key1', 'value1');
        $this->assertEquals('value1', $this->context->getMeta('key1'));
        $this->assertNull($this->context->getMeta('non_existent_key'));
    }

    public function testSetAndGetProfile(): void
    {
        $profile = new MappingProfile([]);
        $this->context->setProfile($profile);
        $this->assertSame($profile, $this->context->getProfile());
    }
}
