<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Functional\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityMutation\Mutation;

/**
 * @ORM\Entity()
 * @Mutation(strategy="current")
 */
class HostingContract extends Contract
{
    /**
     * @ORM\Column(type="string")
     */
    private $service;

    /**
     * @param string $identifier
     * @param int    $status
     * @param string $service
     */
    public function __construct($identifier, $status, $service)
    {
        parent::__construct($identifier, $status);

        $this->service = $service;
    }

    public function getService(): string
    {
        return $this->service;
    }
}
