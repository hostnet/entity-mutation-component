<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Hostnet\Component\DatabaseTest\MysqlPersistentConnection;
use Hostnet\Component\EntityMutation\Functional\Entity\Client;
use Hostnet\Component\EntityMutation\Functional\Entity\ContactInfo;
use Hostnet\Component\EntityMutation\Listener\MutationListener;
use Hostnet\Component\EntityMutation\Resolver\MutationResolver;
use Hostnet\Component\EntityTracker\Listener\EntityChangedListener;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;
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
     * @dataProvider embeddableProvider
     */
    public function testEmbeddable($mutate, $clear_after_insert, $clear_after_update): void
    {
        $info   = new ContactInfo('De Ruijterkade 6', 'Henk de Vries', new \DateTime('april 25th 10:50 2015'));
        $client = new Client($info);

        self::$entity_manager->persist($client);
        self::$entity_manager->flush($client);

        /** @var $client Client */
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

        self::$entity_manager->flush($client);
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
