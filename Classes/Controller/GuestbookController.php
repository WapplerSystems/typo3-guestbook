<?php

namespace WapplerSystems\WsGuestbook\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WapplerSystems\WsGuestbook\Domain\Repository\EntryRepository;


/**
 *
 */
class GuestbookController extends AbstractController
{


    public function __construct(readonly EntryRepository $entryRepository, EventDispatcherInterface $eventDispatcher)
    {
    }


    /**
     *
     * @param int $currentPage
     * @return ResponseInterface
     */
    public function listAction(int $currentPage = 1): ResponseInterface
    {
        $entries = $this->entryRepository->findSorted($this->settings);

        $assignedValues = [
            'settings' => $this->settings
        ];

        if ((int)($this->settings['hidePagination'] ?? 0) === 1) {
            $assignedValues['entries'] = $entries->toArray();
        } else {

            $paginator = new QueryResultPaginator($entries, $currentPage, (int)($this->settings['paginate']['itemsPerPage'] ?? 10));

            $pagination = new SimplePagination($paginator);
            $assignedValues = array_merge($assignedValues, [
                'paginator' => $paginator,
                'pagination' => $pagination,
                'entries' => $paginator->getPaginatedItems(),
            ]);
        }

        $this->view->assignMultiple($assignedValues);

        return $this->htmlResponse();
    }

    /**
     * action new
     *
     * @return ResponseInterface
     */
    public function newAction(): ResponseInterface
    {

        $configurationManager = GeneralUtility::makeInstance(FrontendConfigurationManager::class);
        $this->settings['frameworkConfiguration'] = $configurationManager->getConfiguration();
        $this->settings['pageUid'] = $this->getTypoScriptFrontendController()->id;

        $this->view->assignMultiple([
            'settings' => $this->settings,
        ]);

        return $this->htmlResponse();
    }


    public function doneAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @param string $action_key
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     */
    public function declineAction(string $action_key): ResponseInterface
    {
        $entry = $this->entryRepository->findOneByActionKey($action_key);

        if ($entry === null) {
            $this->forward('entryNotFound');
        } else {

            $this->entryRepository->remove($entry);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        }

        return $this->htmlResponse();
    }


    /**
     * @param string $action_key
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function confirmAction(string $action_key): ResponseInterface
    {
        $entry = $this->entryRepository->findOneByActionKey($action_key);

        if ($entry === null) {
            $this->forward('entryNotFound');
        } else {
            $entry->setHidden(0);
            $this->entryRepository->update($entry);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        }

        return $this->htmlResponse();
    }


    public function entryNotFoundAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

}
