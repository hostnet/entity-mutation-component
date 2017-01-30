<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
namespace Hostnet\Component\EntityMutation\Functional\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityMutation\MutationAwareInterface;

/**
 * @ORM\Entity()
 * @Mutation(strategy="current")
 */
class Client implements MutationAwareInterface
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
     * The history of this object.
     *
     * @ORM\OneToMany(targetEntity="ClientMutation", mappedBy="client")
     * @ORM\OrderBy(value={"id"="DESC"})
     * @var Collection
     */
    private $mutations;

    /**
     * @param ContactInfo $contact_info
     */
    public function __construct(ContactInfo $contact_info)
    {
        $this->contact_info = $contact_info;
        $this->mutations    = new ArrayCollection();
    }

    /**
     * @return ContactInfo
     */
    public function getContactInfo()
    {
        return $this->contact_info;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ContactInfo $contact_info
     * @return $this
     */
    public function setContactInfo(ContactInfo $contact_info)
    {
        $this->contact_info = $contact_info;

        return $this;
    }

    /**
     * @param ClientMutation $mutation
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
     * @return ClientMutation[]
     */
    public function getMutations()
    {
        $mutations = $this->mutations->toArray();
        usort($mutations, function (ClientMutation $ma, ClientMutation $mb) {
            if ($ma->getId() === $mb->getId()) {
                return 0;
            }
            return ($ma->getId() > $mb->getId()) ? -1 : 1;
        });
        return $mutations;
    }

    /**
     * @return ClientMutation
     */
    public function getPreviousMutation()
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented.');
    }
}
