<?php

namespace WapplerSystems\WsGuestbook\Controller;

use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SysLog\Action as SystemLogGenericAction;
use TYPO3\CMS\Core\SysLog\Error as SystemLogErrorClassification;
use TYPO3\CMS\Core\SysLog\Type as SystemLogType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use WapplerSystems\WsGuestbook\View\ErrorView;

class AbstractController extends ActionController implements \Psr\Log\LoggerAwareInterface
{

    use LoggerAwareTrait;

    protected function callActionMethod()
    {
        try {
            parent::callActionMethod();
        } catch (\Exception $exception) {

            /** @var ErrorView $view */
            $view = $this->objectManager->get(ErrorView::class);
            $view->assignMultiple(['errorCode' => $exception->getCode(), 'errorMessage' => $exception->getMessage()]);
            $view->setControllerContext($this->controllerContext);
            if (method_exists($view, 'injectSettings')) {
                $view->injectSettings($this->settings);
            }
            $this->response->appendContent($view->render());
            $this->response->setStatus(500);

            $errorMessage = "ws_guestbook; ".$exception->getCode()."; ".$exception->getMessage()."; ".$this->request->getRequestUri();
            $this->logger->error($errorMessage);

            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['belogErrorReporting']) {
                try {
                    $this->writeLog($errorMessage, 2);
                } catch (\Exception $e) {
                }
            }

        }
    }


    /**
     * Writes an error in the sys_log table
     *
     * @param string $logMessage Default text that follows the message (in english!).
     * @param int $severity The error level of the message (0 = OK, 1 = warning, 2 = error)
     */
    protected function writeLog($logMessage, $severity)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_log');
        if ($connection->isConnected()) {
            $userId = 0;
            $workspace = 0;
            $data = [];
            $backendUser = $this->getBackendUser();
            if (is_object($backendUser)) {
                if (isset($backendUser->user['uid'])) {
                    $userId = $backendUser->user['uid'];
                }
                if (isset($backendUser->workspace)) {
                    $workspace = $backendUser->workspace;
                }
                if (!empty($backendUser->user['ses_backuserid'])) {
                    $data['originalUser'] = $backendUser->user['ses_backuserid'];
                }
            }

            $connection->insert(
                'sys_log',
                [
                    'userid' => $userId,
                    'type' => SystemLogType::ERROR,
                    'action' => SystemLogGenericAction::UNDEFINED,
                    'error' => SystemLogErrorClassification::SYSTEM_ERROR,
                    'level' => $severity,
                    'details_nr' => 0,
                    'details' => str_replace('%', '%%', $logMessage),
                    'log_data' => empty($data) ? '' : serialize($data),
                    'IP' => (string)GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                    'tstamp' => $GLOBALS['EXEC_TIME'],
                    'workspace' => $workspace
                ]
            );
        }
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

}
