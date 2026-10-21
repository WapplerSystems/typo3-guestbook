<?php

namespace WapplerSystems\WsGuestbook\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use WapplerSystems\WsGuestbook\Exception\MissingConfigurationException;
use WapplerSystems\WsGuestbook\View\ErrorView;

class AbstractController extends ActionController implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    protected function callActionMethod(RequestInterface $request): ResponseInterface
    {
        try {
            return parent::callActionMethod($request);
        } catch (MissingConfigurationException $exception) {

            /** @var ErrorView $view */
            $view = GeneralUtility::makeInstance(ErrorView::class);
            $view->assignMultiple(['errorCode' => $exception->getCode(), 'errorMessage' => $exception->getMessage()]);

            $this->logger->error('ws_guestbook; ' . $exception->getCode() . '; ' . $exception->getMessage() . '; ' . $request->getUri());

            return $this->htmlResponse($view->render())->withStatus(500);

        }
    }

}
