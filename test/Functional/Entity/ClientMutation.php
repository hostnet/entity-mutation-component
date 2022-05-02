<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ClientMutation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Embedded(class="ContactInfo")
     *
     * @var ContactInfo
     */
    private $contact_info;

    /**
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="mutations")
     * @ORM\JoinColumn()
     */
    private $client;

    /**
     * @param Client $client
     * @param Client $orginal
     */
    public function __construct(Client $client, Client $orginal)
    {
        $this->client = $client;

        // Clone the embeddable so we keep the original state in case it mutates.
        // Note: This *must* be a deep clone.
        $this->contact_info = clone $orginal->getContactInfo();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContactInfo(): ContactInfo
    {
        return $this->contact_info;
    }
}
