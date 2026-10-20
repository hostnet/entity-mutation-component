<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Functional;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Hostnet\Component\DatabaseTest\MysqlPersistentConnection;
use Hostnet\Component\EntityMutation\Functional\Entity\Client;
use Hostnet\Component\EntityMutation\Functional\Entity\ContactInfo;
use Hostnet\Component\EntityMutation\Listener\MutationListener;
use Hostnet\Component\EntityMutation\Resolver\MutationResolver;
use Hostnet\Component\EntityTracker\Listener\EntityChangedListener;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;
use Hostnet\Component\EntityTracker\Provider\EntityMutationMetadataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class EmbeddableTest extends TestCase
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

    public static function setUpBeforeClass(): void
    {
        self::$connection = new MysqlPersistentConnection();
        $params           = self::$connection->getConnectionParams();
        $event_manager    = new EventManager();

        $configuration = ORMSetup::createConfiguration(true);
        $configuration->setMetadataDriverImpl(new AttributeDriver([__DIR__ . '/Entity'], true));

        $dbal_configuration = new DbalConfiguration();
        $dbal_configuration->setSchemaManagerFactory(new DefaultSchemaManagerFactory());

        $entity_metadata_provider   = new EntityMetadataProvider();
        $mutation_metadata_provider = new EntityMutationMetadataProvider();

        $entity_changed_listener = new EntityChangedListener(
            $entity_metadata_provider,
            $mutation_metadata_provider
        );

        $mutation_resolver = new MutationResolver($entity_metadata_provider);
        $mutation_listener = new MutationListener($mutation_resolver);

        $event_manager->addEventListener('preFlush', $entity_changed_listener);
        $event_manager->addEventListener('entityChanged', $mutation_listener);

        $dbal_connection = DriverManager::getConnection($params, $dbal_configuration, $event_manager);

        self::$entity_manager = new EntityManager($dbal_connection, $configuration, $event_manager);

        $metadata    = self::$entity_manager->getMetadataFactory()->getAllMetadata();
        $schema_tool = new SchemaTool(self::$entity_manager);
        $schema_tool->createSchema($metadata);
    }

    /**
     * @dataProvider embeddableProvider
     */
    public function testEmbeddable($mutate, $clear_after_insert, $clear_after_update): void
    {
        $info   = new ContactInfo('De Ruijterkade 6', 'Henk de Vries', new \DateTime('april 25th 10:50 2015'));
        $client = new Client($info);

        self::$entity_manager->persist($client);
        self::$entity_manager->flush();

        /** @var Client $client */
        if ($clear_after_insert) {
            self::$entity_manager->clear();
            $client = self::$entity_manager->find(Client::class, $client->getId());
            $info   = $client->getContactInfo();
        }

        if ($mutate) {
            $info->setAddressLine('Amsterdam Centraal');
            $info->setName('Henk de Vries');
            $info->setCreatedAt(new \DateTime('april 26th 10:45 2016'));
        } else {
            $info = new ContactInfo('Amsterdam Centraal', 'Henk de Vries', new \DateTime('april 26th 10:45 2016'));
            $client->setContactInfo($info);
        }

        self::$entity_manager->flush();
        unset($info);

        if ($clear_after_update) {
            self::$entity_manager->clear();
            $client = self::$entity_manager->find(Client::class, $client->getId());
        }

        $mutations = $client->getMutations();
        self::assertCount(2, $mutations);

        $latest_mutation = reset($mutations)->getContactInfo();
        $oldest_mutation = end($mutations)->getContactInfo();

        self::assertEquals('Amsterdam Centraal', $latest_mutation->getAddressLine());
        self::assertEquals('Henk de Vries', $latest_mutation->getName());
        self::assertEquals(new \DateTime('april 26th 10:45 2016'), $latest_mutation->getCreatedAt());

        self::assertEquals('De Ruijterkade 6', $oldest_mutation->getAddressLine());
        self::assertEquals('Henk de Vries', $oldest_mutation->getName());
        self::assertEquals(new \DateTime('april 25th 10:50 2015'), $oldest_mutation->getCreatedAt());
    }

    public function embeddableProvider()
    {
        return [
            [false, false, false], // Only passes if the collection is sorted DESC in memory
            [false, false, true],
            [false, true, false],
            [false, true, true],
            [true, false, false], // Only passes if the embeddable is cloned in the mutation constructor
            [true, false, true],
            [true, true, false],
            [true, true, true],
        ];
    }
}
