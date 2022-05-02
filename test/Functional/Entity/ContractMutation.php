<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn("type")
 * @ORM\DiscriminatorMap({
 *     1 = "HostingContractMutation",
 *     2 = "DomainContractMutation",
 *     3 = "ContractMutation"
 * })
 */
class ContractMutation
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
     * @ORM\ManyToOne(targetEntity="Contract", inversedBy="mutations")
     * @ORM\JoinColumn()
     */
    private $contract;

    /**
     * @ORM\Column(type="string")
     */
    private $identifier;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $status;

    /**
     * @param Contract $contract
     * @param Contract $original
     */
    public function __construct(Contract $contract, Contract $original)
    {
        $this->contract = $contract;
        $this->absorb($original);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContract(): Contract
    {
        return $this->contract;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param Contract $original
     */
    protected function absorb(Contract $original): void
    {
        $this->identifier = $original->getIdentifier();
        $this->status     = $original->getStatus();
    }
}
