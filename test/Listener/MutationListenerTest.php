<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
namespace Hostnet\Component\EntityMutation\Listener;

use Hostnet\Component\EntityMutation\Mocked\MockMutationEntity;
use Hostnet\Component\EntityMutation\Mocked\MockMutationEntityMutation;
use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;

/**
 * @covers Hostnet\Component\EntityMutation\Listener\MutationListener
 */
class MutationListenerTest extends \PHPUnit_Framework_TestCase
{
    private $resolver;
    private $listener;
    private $em;

    public function setUp()
    {
        $this->resolver = $this->createMock('Hostnet\Component\EntityMutation\Resolver\MutationResolverInterface');
        $this->listener = new MutationListener($this->resolver);
        $this->em       = $this
            ->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testOnEntityChanged()
    {
        $current_entity  = new MockMutationEntity();
        $original_entity = new MockMutationEntity();

        $current_entity->id  = 2;
        $original_entity->id = 1;

        $mutated_fields = ['id'];

        $this->resolver
            ->expects($this->once())
            ->method('getMutatableFields')
            ->with($this->em, $current_entity)
            ->willReturn(['id']);

        $annotation = new Mutation();

        $this->resolver
            ->expects($this->once())
            ->method('getMutationAnnotation')
            ->with($this->em, $current_entity)
            ->willReturn($annotation);

        $this->resolver
            ->expects($this->once())
            ->method('getMutationClassName')
            ->with($this->em, $current_entity)
            ->willReturn(get_class($current_entity) . 'Mutation');

        $mutation_meta = $this->createMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $mutation_meta
            ->expects($this->any())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(get_class($current_entity) . 'Mutation'));

        $this->em
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($current_entity) . 'Mutation')
            ->willReturn($mutation_meta);

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(get_class($current_entity) . 'Mutation'));

        $event = new EntityChangedEvent($this->em, $current_entity, $original_entity, $mutated_fields);
        $this->listener->entityChanged($event);

        $this->assertTrue(current($current_entity->getMutations()) instanceof MockMutationEntityMutation);
    }

    public function testOnEntityChangedCopyCurrent()
    {
        $current_entity  = new MockMutationEntity();
        $original_entity = new MockMutationEntity();

        $current_entity->id  = 2;
        $original_entity->id = 1;

        $mutated_fields = ['id'];

        $this->resolver
            ->expects($this->once())
            ->method('getMutatableFields')
            ->with($this->em, $current_entity)
            ->willReturn(['id']);

        $annotation           = new Mutation();
        $annotation->strategy = Mutation::STRATEGY_COPY_CURRENT;

        $this->resolver
            ->expects($this->once())
            ->method('getMutationAnnotation')
            ->with($this->em, $current_entity)
            ->willReturn($annotation);

        $this->resolver
            ->expects($this->once())
            ->method('getMutationClassName')
            ->with($this->em, $current_entity)
            ->willReturn(get_class($current_entity) . 'Mutation');

        $mutation_meta = $this->createMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $mutation_meta
            ->expects($this->any())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(get_class($current_entity) . 'Mutation'));

        $this->em
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($current_entity) . 'Mutation')
            ->willReturn($mutation_meta);

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(get_class($current_entity) . 'Mutation'));

        $event = new EntityChangedEvent($this->em, $current_entity, $original_entity, $mutated_fields);
        $this->listener->entityChanged($event);

        $this->assertTrue(current($current_entity->getMutations()) instanceof MockMutationEntityMutation);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testOnEntityChangedUnknownStrategy()
    {
        $current_entity  = new MockMutationEntity();
        $original_entity = new MockMutationEntity();

        $current_entity->id  = 2;
        $original_entity->id = 1;

        $mutated_fields = ['id'];

        $this->resolver
            ->expects($this->once())
            ->method('getMutatableFields')
            ->with($this->em, $current_entity)
            ->willReturn(['id']);

        $annotation = $this->createMock('Hostnet\Component\EntityMutation\Mutation');
        $annotation
            ->expects($this->once())
            ->method('getStrategy')
            ->willReturn('phpunit');

        $this->resolver
            ->expects($this->once())
            ->method('getMutationAnnotation')
            ->with($this->em, $current_entity)
            ->willReturn($annotation);

        $event = new EntityChangedEvent($this->em, $current_entity, $original_entity, $mutated_fields);
        $this->listener->entityChanged($event);
    }

    public function testOnEntityChangedInsertedWithPreviousStrategy()
    {
        $current_entity  = new MockMutationEntity();
        $original_entity = null;

        $current_entity->id = 2;

        $mutated_fields = ['id'];

        $this->resolver
            ->expects($this->once())
            ->method('getMutatableFields')
            ->with($this->em, $current_entity)
            ->willReturn(['id']);

        $annotation = $this->createMock('Hostnet\Component\EntityMutation\Mutation');
        $annotation
            ->expects($this->once())
            ->method('getStrategy')
            ->willReturn('previous');

        $this->resolver
            ->expects($this->once())
            ->method('getMutationAnnotation')
            ->with($this->em, $current_entity)
            ->willReturn($annotation);

        $this->em
            ->expects($this->never())
            ->method('persist');

        $event = new EntityChangedEvent($this->em, $current_entity, $original_entity, $mutated_fields);
        $this->listener->entityChanged($event);
    }

    public function testOnEntityChangedEmptyChanges()
    {
        $current_entity  = new MockMutationEntity();
        $original_entity = new MockMutationEntity();

        $current_entity->id  = 2;
        $original_entity->id = 1;
        $mutated_fields      = ['id'];

        $this->resolver
            ->expects($this->once())
            ->method('getMutatableFields')
            ->with($this->em, $current_entity)
            ->willReturn([]);

        $annotation           = new Mutation();
        $annotation->strategy = Mutation::STRATEGY_COPY_CURRENT;

        $this->resolver
            ->expects($this->once())
            ->method('getMutationAnnotation')
            ->with($this->em, $current_entity)
            ->willReturn($annotation);

        $this->em
            ->expects($this->never())
            ->method('persist')
            ->with($this->isInstanceOf(get_class($current_entity) . 'Mutation'));

        $event = new EntityChangedEvent($this->em, $current_entity, $original_entity, $mutated_fields);
        $this->listener->entityChanged($event);

        $this->assertEquals(0, count($current_entity->getMutations()));
    }

    public function testOnEntityChangedNoAnnotation()
    {
        $current_entity  = new MockMutationEntity();
        $original_entity = new MockMutationEntity();

        $current_entity->id  = 2;
        $original_entity->id = 1;
        $mutated_fields      = ['id'];

        $this->resolver
            ->expects($this->once())
            ->method('getMutationAnnotation')
            ->with($this->em, $current_entity)
            ->willReturn(null);

        $this->em
            ->expects($this->never())
            ->method('persist')
            ->with($this->isInstanceOf(get_class($current_entity) . 'Mutation'));

        $event = new EntityChangedEvent($this->em, $current_entity, $original_entity, $mutated_fields);
        $this->listener->entityChanged($event);

        $this->assertEquals(0, count($current_entity->getMutations()));
    }
}
