<?php
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

    /**
     * @return string
     */
    public function getAddressLine()
    {
        return $this->address_line;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     * @return ContactInfo
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @param string $name
     * @return ContactInfo
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $address_line
     * @return ContactInfo
     */
    public function setAddressLine($address_line)
    {
        $this->address_line = $address_line;

        return $this;
    }
}
