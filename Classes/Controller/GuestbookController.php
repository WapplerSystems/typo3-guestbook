<?php

namespace WapplerSystems\WsGuestbook\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use WapplerSystems\WsGuestbook\Domain\Repository\EntryRepository;


/**
 *
 */
class GuestbookController extends AbstractController
{

    /**
     * Pages showing the guestbook list are tagged with the table name; TYPO3 flushes this tag
     * on every backend change of an entry, the frontend actions below flush it as well.
     */
    protected const CACHE_TAG = 'tx_wsguestbook_domain_model_entry';

    public function __construct(
        protected readonly EntryRepository $entryRepository,
        protected readonly PersistenceManagerInterface $persistenceManager,
        protected readonly CacheManager $cacheManager,
    ) {
    }


    /**
     *
     * @param int $currentPage
     * @return ResponseInterface
     */
    public function listAction(int $currentPage = 1): ResponseInterface
    {
        $this->request->getAttribute('frontend.cache.collector')?->addCacheTags(new CacheTag(self::CACHE_TAG));
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
        $frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        // storagePid may be a comma separated list, new entries go to the first page
        $this->settings['storagePid'] = (int)($frameworkConfiguration['persistence']['storagePid'] ?? 0);
        $this->settings['pageUid'] = $this->request->getAttribute('frontend.page.information')->getId();

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
            return new ForwardResponse('entryNotFound');
        }

        $this->entryRepository->remove($entry);
        $this->persistenceManager->persistAll();
        $this->cacheManager->flushCachesInGroupByTags('pages', [self::CACHE_TAG]);

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
            return new ForwardResponse('entryNotFound');
        }

        $entry->setHidden(false);
        $this->entryRepository->update($entry);
        $this->persistenceManager->persistAll();
        $this->cacheManager->flushCachesInGroupByTags('pages', [self::CACHE_TAG]);

        return $this->htmlResponse();
    }


    public function entryNotFoundAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

}
