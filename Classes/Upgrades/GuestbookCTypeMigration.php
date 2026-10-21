<?php

declare(strict_types=1);

namespace WapplerSystems\WsGuestbook\Upgrades;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('wsGuestbookCTypeMigration')]
final class GuestbookCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "ws_guestbook" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The guestbook plugins are now registered as content element types. This updates existing content '
            . 'elements and backend user permissions from "list_type" to the new "CType" values.';
    }

    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'wsguestbook_list' => 'wsguestbook_list',
            'wsguestbook_form' => 'wsguestbook_form',
        ];
    }
}
