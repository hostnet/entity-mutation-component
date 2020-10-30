<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Functional\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityMutation\MutationAwareInterface;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn("type")
 * @ORM\DiscriminatorMap({
 *     1 = "HostingContract",
 *     2 = "DomainContract",
 *     3 = "Contract"
 * })
 * @Mutation(strategy="current")
 */
class Contract implements MutationAwareInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $identifier;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * The history of this object.
     *
     * @ORM\OneToMany(targetEntity="ContractMutation", mappedBy="contract")
     * @ORM\OrderBy(value={"id"="DESC"})
     * @var Collection
     */
    private $mutations;

    /**
     * @param string $identifier
     * @param int    $status
     */
    public function __construct($identifier, $status)
    {
        $this->identifier = $identifier;
        $this->status     = $status;
        $this->mutations  = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param DomainContractMutation $mutation
     */
    public function addMutation($mutation)
    {
        // $this->mutations is sorted by id descending, so we should add new
        // items at the start of the Collection. Doctrine collections don't
        // allow this right now, so we add it to the end. This is fixed in
        // getMutations.
        $this->mutations->add($mutation);
    }

    /**
     * @return ContractMutation[]
     */
    public function getMutations()
    {
        $mutations = $this->mutations->toArray();
        usort($mutations, function (ContractMutation $ma, ContractMutation $mb) {
            if ($ma->getId() === $mb->getId()) {
                return 0;
            }
            return ($ma->getId() > $mb->getId()) ? -1 : 1;
        });
        return $mutations;
    }

    /**
     * @return DomainContractMutation
     */
    public function getPreviousMutation()
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented.');
    }
}
