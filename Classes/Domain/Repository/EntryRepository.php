<?php

namespace WapplerSystems\WsGuestbook\Domain\Repository;


use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 *
 */
class EntryRepository extends Repository
{
    public function findSorted($settings)
    {
        $query = $this->createQuery();
        if ($settings['sorting'] === 'DESC') {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        } else {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        }
        return $query->execute();
    }

}
