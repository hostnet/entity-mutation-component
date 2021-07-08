<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\EntityMutation\Resolver\MutationResolver
 */
class MutationResolverTest extends TestCase
{
    private $provider;
    private $resolver;
    private $em;

    public function setUp(): void
    {
        $this->provider = $this
            ->getMockBuilder(EntityAnnotationMetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new MutationResolver($this->provider);
    }

    public function testGetMutationAnnotation(): void
    {
        $entity = new \stdClass();

        $this->provider
            ->expects($this->once())
            ->method('getAnnotationFromEntity')
            ->with($this->em, $entity, Mutation::class);

        $this->resolver->getMutationAnnotation($this->em, $entity);
    }

    public function testGetMutationClassName(): void
    {
        $entity            = new \stdClass();
        $annotation        = new Mutation();
        $annotation->class = 'Phpunit';

        $this->provider
            ->expects($this->exactly(3))
            ->method('getAnnotationFromEntity')
            ->with($this->em, $entity, 'Hostnet\Component\EntityMutation\Mutation')
            ->willReturnOnConsecutiveCalls(null, new Mutation(), $annotation);

        $this->assertEquals("", $this->resolver->getMutationClassName($this->em, $entity));
        $this->assertEquals("stdClassMutation", $this->resolver->getMutationClassName($this->em, $entity));
        $this->assertEquals("Phpunit", $this->resolver->getMutationClassName($this->em, $entity));
    }

    public function testGetMutatedFields(): void
    {
        $entity        = new \stdClass();
        $metadata      = $this->createMock(ClassMetadata::class);
        $metadata_meta = $this->createMock(ClassMetadata::class);

        $this->provider
            ->expects($this->once())
            ->method('getAnnotationFromEntity')
            ->willReturnOnConsecutiveCalls(new Mutation());

        $metadata->expects($this->once())->method('getFieldNames')->willReturn(['id']);
        $metadata_meta->expects($this->once())->method('getFieldNames')->willReturn(['id']);
        $metadata->expects($this->once())->method('getAssociationNames')->willReturn(['test']);
        $metadata_meta->expects($this->once())->method('getAssociationNames')->willReturn(['test']);

        $this->em
            ->expects($this->exactly(2))
            ->method('getClassMetadata')
            ->withConsecutive([get_class($entity)], [get_class($entity) . "Mutation"])
            ->willReturnOnConsecutiveCalls($metadata, $metadata_meta);

        $this->assertEquals(["id", "test"], $this->resolver->getMutatableFields($this->em, $entity));
    }
}
