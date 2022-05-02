<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class ContactInfo
{
    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $address_line;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @param string    $address_line
     * @param string    $name
     * @param \DateTime $created_at
     */
    public function __construct($address_line, $name, \DateTime $created_at)
    {
        $this->address_line = $address_line;
        $this->name         = $name;
        $this->created_at   = $created_at;
    }

    public function getAddressLine(): string
    {
        return $this->address_line;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at): ContactInfo
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @param string $name
     */
    public function setName($name): ContactInfo
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $address_line
     */
    public function setAddressLine($address_line): ContactInfo
    {
        $this->address_line = $address_line;

        return $this;
    }
}
