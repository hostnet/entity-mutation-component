<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class DomainContractMutation extends ContractMutation
{
    /**
     * @ORM\Column(type="string")
     */
    private $domain;

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param Contract $original
     */
    protected function absorb(Contract $original)
    {
        if (!($original instanceof DomainContract)) {
            throw new \InvalidArgumentException(sprintf(
                'DomainContractMutation can only be created from DomainContract entities, "%s" given',
                get_class($original)
            ));
        }

        parent::absorb($original);

        $this->domain = $original->getDomain();
    }
}
