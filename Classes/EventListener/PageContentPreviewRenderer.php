<?php

declare(strict_types=1);

namespace WapplerSystems\WsGuestbook\EventListener;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

/**
 * Renders the backend page module preview of the guestbook content elements
 */
#[AsEventListener('ws-guestbook/page-content-preview')]
final readonly class PageContentPreviewRenderer
{
    private const TEMPLATES = [
        'wsguestbook_form' => 'FormPreview',
        'wsguestbook_list' => 'ListPreview',
    ];

    public function __construct(private ViewFactoryInterface $viewFactory) {}

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content' || !isset(self::TEMPLATES[$event->getRecordType()])) {
            return;
        }
        $view = $this->viewFactory->create(new ViewFactoryData(
            templatePathAndFilename: 'EXT:ws_guestbook/Resources/Private/Backend/' . self::TEMPLATES[$event->getRecordType()] . '.html',
            request: $event->getPageLayoutContext()->getCurrentRequest(),
        ));
        $view->assign('record', $event->getRecord());
        $event->setPreviewContent($view->render());
    }
}
