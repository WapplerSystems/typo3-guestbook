<?php

namespace WapplerSystems\WsGuestbook\Domain\Repository;


use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 *
 */
class EntryRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function findSorted($settings)
    {
        $query = $this->createQuery();
        if ($settings['sorting'] === 'DESCENDING') {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        } else {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        }
        if ($settings['totalnumber']) {
            $query->setLimit((int)$settings['totalnumber']);
        }
        $query = $query->execute();
        return $query;
    }

    public function findLatestSorted($settings)
    {
        $query = $this->createQuery();

        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        if ($settings['totalnumber']) {
            $query->setLimit((int)$settings['totalnumber']);
        }
        $query = $query->execute();
        return $query;
    }
}
