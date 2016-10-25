<?php
namespace Hostnet\Component\EntityMutation\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Functional\Entity\Contract;
use Functional\Entity\ContractMutation;
use Functional\Entity\DomainContract;
use Functional\Entity\DomainContractMutation;
use Functional\Entity\HostingContract;
use Functional\Entity\HostingContractMutation;
use Hostnet\Component\DatabaseTest\MysqlPersistentConnection;
use Hostnet\Component\EntityMutation\Listener\MutationListener;
use Hostnet\Component\EntityMutation\Resolver\MutationResolver;
use Hostnet\Component\EntityTracker\Listener\EntityChangedListener;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;
use Hostnet\Component\EntityTracker\Provider\EntityMutationMetadataProvider;

/**
 * @coversNothing
 */
class DiscriminatorMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private static $entity_manager;

    /**
     * Prevent destruction of the connection.
     *
     * @var MysqlPersistentConnection
     */
    private static $connection;

    public static function setUpBeforeClass()
    {
        self::$connection  = new MysqlPersistentConnection();
        $params            = self::$connection->getConnectionParams();
        $configuration     = Setup::createConfiguration(true);
        $event_manager     = new EventManager();
        $annotation_reader = new AnnotationReader();

        $configuration->setMetadataDriverImpl(new AnnotationDriver($annotation_reader, [__DIR__ . '/Entity']));

        $annotation_metadata_provider = new EntityAnnotationMetadataProvider($annotation_reader);
        $mutation_metadata_provider   = new EntityMutationMetadataProvider($annotation_reader);

        $entity_changed_listener = new EntityChangedListener(
            $annotation_metadata_provider,
            $mutation_metadata_provider
        );

        $mutation_resolver = new MutationResolver($annotation_metadata_provider);
        $mutation_listener = new MutationListener($mutation_resolver);

        $event_manager->addEventListener('prePersist', $entity_changed_listener);
        $event_manager->addEventListener('preFlush', $entity_changed_listener);
        $event_manager->addEventListener('entityChanged', $mutation_listener);

        self::$entity_manager = EntityManager::create($params, $configuration, $event_manager);

        $metadata    = self::$entity_manager->getMetadataFactory()->getAllMetadata();
        $schema_tool = new SchemaTool(self::$entity_manager);
        $schema_tool->createSchema($metadata);
    }

    /**
     * @dataProvider discriminatorMapProvider
     */
    public function testDiscriminatorMap($clear_after_update)
    {
        $hosting_contract = new HostingContract('foobar1.nl', 1, 'Hosting');
        $domain_contract  = new DomainContract('foobar2.nl', 2, 'www.foobar2.nl');
        $contract         = new Contract('foobar3.nl', 3);

        self::$entity_manager->persist($hosting_contract);
        self::$entity_manager->persist($domain_contract);
        self::$entity_manager->persist($contract);
        self::$entity_manager->flush();

        if ($clear_after_update) {
            self::$entity_manager->clear();

            $hosting_contract = self::$entity_manager->find(HostingContract::class, $hosting_contract->getId());
            $domain_contract  = self::$entity_manager->find(DomainContract::class, $domain_contract->getId());
            $contract         = self::$entity_manager->find(Contract::class, $contract->getId());
        }

        $hosting_contract_mutations = $hosting_contract->getMutations();
        $domain_contract_mutations  = $domain_contract->getMutations();
        $contract_mutations         = $contract->getMutations();

        self::assertCount(1, $hosting_contract_mutations);
        self::assertCount(1, $domain_contract_mutations);
        self::assertCount(1, $contract_mutations);

        $hosting_mutation = current($hosting_contract_mutations);

        self::assertInstanceOf(HostingContractMutation::class, $hosting_mutation);
        self::assertEquals($hosting_contract, $hosting_mutation->getContract());
        self::assertEquals('foobar1.nl', $hosting_mutation->getIdentifier());
        self::assertEquals(1, $hosting_mutation->getStatus());
        self::assertEquals('Hosting', $hosting_mutation->getService());

        $domain_mutation = current($domain_contract_mutations);

        self::assertInstanceOf(DomainContractMutation::class, $domain_mutation);
        self::assertEquals($domain_contract, $domain_mutation->getContract());
        self::assertEquals('foobar2.nl', $domain_mutation->getIdentifier());
        self::assertEquals(2, $domain_mutation->getStatus());
        self::assertEquals('www.foobar2.nl', $domain_mutation->getDomain());

        $mutation = current($contract_mutations);

        self::assertInstanceOf(ContractMutation::class, $mutation);
        self::assertEquals($contract, $mutation->getContract());
        self::assertEquals('foobar3.nl', $mutation->getIdentifier());
        self::assertEquals(3, $mutation->getStatus());
    }

    public function discriminatorMapProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
