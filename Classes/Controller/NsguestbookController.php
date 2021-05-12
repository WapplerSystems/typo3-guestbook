<?php

namespace Nitsan\NsGuestbook\Controller;

use TYPO3\CMS\Extbase\Annotation\Inject as inject;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * NsguestbookController
 */
class NsguestbookController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * nsguestbookRepository
     *
     * @var \Nitsan\NsGuestbook\Domain\Repository\NsguestbookRepository
     * @inject
     */
    protected $nsguestbookRepository = null;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $nsguestbooks = $this->nsguestbookRepository->findSorted($this->settings);
        $this->view->assign('nsguestbooks', $nsguestbooks);
        $this->view->assign('settings', $this->settings);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_ns_guestbook_form'); // extname
        $this->view->assign('nsguestbookdata', $request['tx_nsguestbook_form']['newNsguestbook']);
    }

    /**
     * action create
     *
     * @param \Nitsan\NsGuestbook\Domain\Model\Nsguestbook $newNsguestbook
     * @return void
     */
    public function createAction(\Nitsan\NsGuestbook\Domain\Model\Nsguestbook $newNsguestbook)
    {
        $settings = $this->settings;
        $error = 0;
        $mailerror = 0;

        if ($newNsguestbook->getName() == '' || $newNsguestbook->getEmail() == '') {
            $error = 1;
        }
        if ($newNsguestbook->getEmail() != '') {
            if (filter_var($newNsguestbook->getEmail(), FILTER_VALIDATE_EMAIL)) {
            } else {
                $mailerror = 1;
            }
        }

        if (isset($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
        }
        if (!$captcha && $settings['captcha'] == 0) {
            $checkcaptchamsg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'controller.checkcaptcha.msg',
                'ns_guestbook'
            );
            $this->addFlashMessage($checkcaptchamsg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new', 'Nsguestbook', 'ns_guestbook', $_REQUEST);
        } else {
            $secretkey = $settings['secretkey'];
            $response = json_decode(
                file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretkey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']),
                true
            );
            if ($response['success'] == false && $settings['captcha'] == 0) {
                $wrongcaptcha = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'controller.wrongcaptcha.msg',
                    'ns_guestbook'
                );
                $this->addFlashMessage($wrongcaptcha, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            } else {
                if ($error == 1) {
                    $requireFields = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'controller.requireFields',
                        'ns_guestbook'
                    );

                    $mailfrmt = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'controller.mailfrmt',
                        'ns_guestbook'
                    );

                    $this->addFlashMessage($requireFields, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

                    if ($mailerror == 1) {
                        $this->addFlashMessage($mailfrmt, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                    }

                    $this->redirect('new', 'Nsguestbook', 'ns_guestbook', $_REQUEST);
                }

                if ($mailerror == 1) {
                    $mailfrmt = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'controller.mailfrmt',
                        'ns_guestbook'
                    );
                    $this->addFlashMessage($mailfrmt, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                    $this->redirect('new', 'Nsguestbook', 'ns_guestbook', $_REQUEST);
                }

                $thanksmsg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'controller.thanks.msg',
                    'ns_guestbook'
                );

                $this->addFlashMessage($thanksmsg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                if ($this->settings['autoaprrove']) {
                } else {
                    $newNsguestbook->setHidden('1');
                }
                $this->nsguestbookRepository->add($newNsguestbook);

                // User name and mail
                if (!empty($this->settings['adminEmail'])) {
                    $adminName = $this->settings['adminName'];
                    $adminEmail = $this->settings['adminEmail'];

                    $confirmationContent = [
                        'adminName' => $adminName,
                        'name' => $newNsguestbook->getName(),
                        'city' => $newNsguestbook->getCity(),
                        'email' => $newNsguestbook->getEmail(),
                        'website' => $newNsguestbook->getWebsite(),
                        'message' => $newNsguestbook->getMessage(),
                    ];
                    $emailSubject = $this->settings['emailSubject'];

                    $confirmationVariables = ['guest' => $confirmationContent];
                    $sendSenderMail = $this->sendTemplateEmail(
                        [$adminEmail => $adminName],
                        [$adminEmail => $adminName],
                        $emailSubject,
                        'MailTemplate',
                        $confirmationVariables
                    );
                }
            }
        }
        $this->redirect('new');
    }

    /**
     * @param array $recipient recipient of the email in the format array('recipient@domain.tld' => 'Recipient Name')
     * @param array $sender sender of the email in the format array('sender@domain.tld' => 'Sender Name')
     * @param string $subject subject of the email
     * @param string $templateName template name (UpperCamelCase)
     * @param array $variables variables to be passed to the Fluid view
     */
    protected function sendTemplateEmail(
        array $recipient,
        array $sender,
        $subject,
        $templateName,
        array $variables = []
    ) {

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
        $emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

        /*For use of Localize value */
        $extensionName = $this->request->getControllerExtensionName();
        $emailView->getRequest()->setControllerExtensionName($extensionName);

        /*For use of Localize value */
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPaths']['0']);
        $templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . '.html';
        $emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $emailView->assignMultiple($variables);
        $emailBody = $emailView->render();
        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);
        // HTML Email
        if (version_compare(TYPO3_branch, '10.0', '>')) {
            $message->html($emailBody);
        } else {
            $message->setBody($emailBody, 'text/html');
        }

        $status = 0;
        $message->send();
        $status = $message->isSent();

        return $status;
    }

    /**
     * A template method for displaying custom error flash messages, or to
     * display no flash message at all on errors. Override this to customize
     * the flash message in your action controller.
     *
     * @return string|bool The flash message or FALSE if no flash message should be set
     * @api
     */
    protected function getErrorFlashMessage()
    {
        $errormsg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'controller.insertError.msg',
            'ns_guestbook'
        );
        return $errormsg;
    }
}
