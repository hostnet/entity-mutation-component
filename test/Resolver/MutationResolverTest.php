<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
namespace Hostnet\Component\EntityMutation\Resolver;

use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityMutation\Resolver\MutationResolver;

/**
 * @covers Hostnet\Component\EntityMutation\Resolver\MutationResolver
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
class MutationResolverTest extends \PHPUnit_Framework_TestCase
{
    private $provider;
    private $resolver;
    private $em;

    public function setUp()
    {
        $this->provider = $this
            ->getMockBuilder('Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new MutationResolver($this->provider);
    }

    public function testGetMutationAnnotation()
    {
        $entity = new \stdClass();

        $this->provider
            ->expects($this->once())
            ->method('getAnnotationFromEntity')
            ->with($this->em, $entity, 'Hostnet\Component\EntityMutation\Mutation');

        $this->resolver->getMutationAnnotation($this->em, $entity);
    }

    public function testGetMutationClassName()
    {
        $entity            = new \stdClass();
        $annotation        = new Mutation();
        $annotation->class = "Phpunit";

        $this->provider
            ->expects($this->exactly(3))
            ->method('getAnnotationFromEntity')
            ->with($this->em, $entity, 'Hostnet\Component\EntityMutation\Mutation')
            ->willReturnOnConsecutiveCalls(null, new Mutation(), $annotation);

        $this->assertEquals("", $this->resolver->getMutationClassName($this->em, $entity));
        $this->assertEquals("stdClassMutation", $this->resolver->getMutationClassName($this->em, $entity));
        $this->assertEquals("Phpunit", $this->resolver->getMutationClassName($this->em, $entity));
    }

    public function testGetMutatedFields()
    {
        $entity        = new \stdClass();
        $metadata      = $this->createMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata_meta = $this->createMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');

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
