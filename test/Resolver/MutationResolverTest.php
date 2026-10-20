<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Hostnet\Component\EntityMutation\Attributes\Mutation;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\EntityMutation\Resolver\MutationResolver
 */
class MutationResolverTest extends TestCase
{
    private EntityMetadataProvider&MockObject $provider;
    private MutationResolver $resolver;
    private EntityManagerInterface&MockObject $em;

    public function setUp(): void
    {
        $this->provider = $this
            ->getMockBuilder(EntityMetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new MutationResolver($this->provider);
    }

    public function testGetMutationClassName(): void
    {
        $entity = new \stdClass();
        $this->assertEquals('stdClassMutation', $this->resolver->getMutationClassName($this->em, $entity));
    }

    public function testGetMutatedFields(): void
    {
        $entity        = new \stdClass();
        $metadata      = $this->createMock(ClassMetadata::class);
        $metadata_meta = $this->createMock(ClassMetadata::class);

        $metadata->expects($this->once())->method('getFieldNames')->willReturn(['id']);
        $metadata_meta->expects($this->once())->method('getFieldNames')->willReturn(['id']);
        $metadata->expects($this->once())->method('getAssociationNames')->willReturn(['test']);
        $metadata_meta->expects($this->once())->method('getAssociationNames')->willReturn(['test']);

        $this->em
            ->expects($this->exactly(2))
            ->method('getClassMetadata')
            ->withConsecutive([get_class($entity)], [get_class($entity) . 'Mutation'])
            ->willReturnOnConsecutiveCalls($metadata, $metadata_meta);

        $this->assertEquals(['id', 'test'], $this->resolver->getMutatableFields($this->em, $entity));
    }

    public function testGetMutationAttribute(): void
    {
        $entity = new \stdClass();

        $attribute = new Mutation(Mutation::STRATEGY_COPY_CURRENT);

        $this->provider
            ->expects($this->once())
            ->method('getAttributeFromEntity')
            ->with(Mutation::class, $this->em, $entity)
            ->willReturn($attribute);

        self::assertSame($attribute, $this->resolver->getMutationAttribute($this->em, $entity));
    }
}
