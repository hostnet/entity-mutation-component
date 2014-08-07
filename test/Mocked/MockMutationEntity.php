<?php
namespace Hostnet\Component\EntityMutation\Mocked;

use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityMutation\MutationAwareInterface;

/**
 * @Mutation()
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
class MockMutationEntity implements MutationAwareInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id",type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="MockEntity", inversedBy="mutations")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    public $mutations = [];

    /**
     * @ORM\OneToOne(targetEntity="MockEntity")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     * @var unknown
     */
    public $parent;

    /**
     * @see \Hostnet\Component\EntityMutation\MutationAwareInterface::addMutation()
     */
    public function addMutation($mutation)
    {
        $this->mutations[] = $mutation;
    }

    /**
     * @see \Hostnet\Component\EntityMutation\MutationAwareInterface::getMutations()
     */
    public function getMutations()
    {
        return $this->mutations;
    }

    /**
     * @see \Hostnet\Component\EntityMutation\MutationAwareInterface::getPreviousMutation()
     */
    public function getPreviousMutation()
    {
        return current($this->mutations) ?: null;
    }
}
