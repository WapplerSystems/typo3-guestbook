<?php

namespace WapplerSystems\WsGuestbook\Domain\Repository;


use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use WapplerSystems\WsGuestbook\Domain\Model\Entry;

/**
 *
 */
class EntryRepository extends Repository
{
    public function findSorted(array $settings)
    {
        $query = $this->createQuery();
        if ($settings['sorting'] === 'DESC') {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        } else {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        }
        return $query->execute();
    }

    /**
     * @param string $actionKey
     * @return Entry|null
     */
    public function findOneByActionKey(string $actionKey) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching($query->equals('action_key', $actionKey));
        return $query->execute()->getFirst();
    }

}
