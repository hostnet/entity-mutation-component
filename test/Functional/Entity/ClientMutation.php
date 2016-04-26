<?php
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ContactInfo
     */
    public function getContactInfo()
    {
        return $this->contact_info;
    }
}
