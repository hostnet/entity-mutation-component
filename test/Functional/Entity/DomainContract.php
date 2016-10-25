<?php
namespace Functional\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityMutation\Mutation;

/**
 * @ORM\Entity()
 * @Mutation(strategy="current")
 */
class DomainContract extends Contract
{
    /**
     * @ORM\Column(type="string")
     */
    private $domain;

    /**
     * @param string $identifier
     * @param int    $status
     * @param string $domain
     */
    public function __construct($identifier, $status, $domain)
    {
        parent::__construct($identifier, $status);

        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
