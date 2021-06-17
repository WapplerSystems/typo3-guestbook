<?php

namespace WapplerSystems\WsGuestbook\Controller;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Annotation\Inject as inject;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Renderer\FluidFormRenderer;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

use GuzzleHttp\Exception\ConnectException;
use LiNear\LinearDownloadManager\Domain\Model\FrontendUser;
use LiNear\LinearDownloadManager\Service\KeycloakConnector;
use LiNear\LinearDownloadManager\Validation\Validator\UsernameAlreadyTakenValidator;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\GridRow;
use TYPO3\CMS\Form\Domain\Model\FormElements\Section;

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
 * WsguestbookController
 */
class WsguestbookController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * wsguestbookRepository
     *
     * @var \WapplerSystems\WsGuestbook\Domain\Repository\WsguestbookRepository
     * @inject
     */
    protected $wsguestbookRepository = null;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $wsguestbooks = $this->wsguestbookRepository->findSorted($this->settings);
        $this->view->assign('wsguestbooks', $wsguestbooks);
        $this->view->assign('settings', $this->settings);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $formDefinition = $this->buildGuestbookEntryForm();
        $form = $formDefinition->bind($this->request, $this->response);

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'form' => $form->render(),
        ]);
    }

    public function buildGuestbookEntryForm()
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = $this->objectManager->get(ConfigurationService::class);
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration('standard');

        /** @var FormDefinition $formDefinition */
        $formDefinition = $this->objectManager->get(FormDefinition::class, 'guestbookEntryForm', $prototypeConfiguration);
        $formDefinition->setRendererClassName(FluidFormRenderer::class);
        $formDefinition->setRenderingOption('controllerAction', 'new');
        $formDefinition->setRenderingOption('submitButtonLabel', 'Submit');

        $saveToDatabaseFinisher = $formDefinition->createFinisher('SaveToDatabase');
        $saveToDatabaseFinisher->setOptions([
            'table' => 'tx_wsguestbook_domain_model_wsguestbook',
            'mode' => 'insert',
            'databaseColumnMappings' => [
                'pid' => [
                    'value' => $this->settings['storagePid'],
                ],
                'tstamp' => [
                    'value' => time(),
                ]
            ],

            'elements' => [
                'name' => [
                    'mapOnDatabaseColumn' => 'name',
                ],
                'email' => [
                    'mapOnDatabaseColumn' => 'email',
                ],
                'city' => [
                    'mapOnDatabaseColumn' => 'city',
                ],
                'website' => [
                    'mapOnDatabaseColumn' => 'website',
                ],
                'message' => [
                    'mapOnDatabaseColumn' => 'message',
                ],
            ]
        ]);


        $confirmationFinisher = $formDefinition->createFinisher('Confirmation');
        $confirmationFinisher->setOptions([
            'message' => LocalizationUtility::translate('msg.pleaseConfirmEmailAddress', 'ws_guestbook'),
            'templateName' => 'Confirmation',
            'templateRootPaths' => [
                10 => 'EXT:ws_guestbook/Resources/Private/Templates/Wsguestbook/',
            ]
        ]);

        $emailFinisher = $formDefinition->createFinisher('EmailToReceiver');
        $emailFinisher->setOptions([
            'subject' => $this->settings['emailSubject'],
            'recipientAddress' => $this->settings['adminEmail'],
            'recipientName' => $this->settings['adminName'],
            'senderAddress' => $this->settings['adminEmail'],
            'templateName' => 'MailTemplate',
            'templateRootPaths' => [
                10 => 'EXT:ws_guestbook/Resources/Private/Templates/Email/',
            ]


        ]);

        $page = $formDefinition->createPage('page1');


        /** @var GridRow $row */
        $row = $page->createElement('row1', 'GridRow');


        /** @var Section $fieldset */
        $fieldset = $row->createElement('fieldsetEntry', 'Fieldset');
        $fieldset->setLabel('New Guestbook Entry');
        $fieldset->setOptions(['properties' => [
            'gridColumnClassAutoConfiguration' => [
                'viewPorts' => [
                    'md' => 12
                ]
            ]
        ]]);

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('name', 'Text');
        $element->setLabel('Name');
        $element->setProperty('required', true);
        $element->addValidator(new StringLengthValidator(['maximum' => 50]));
        $element->addValidator(new NotEmptyValidator());

        $element = $fieldset->createElement('email', 'Text');
        $element->setLabel('E-Mail');
        $element->addValidator(new EmailAddressValidator());

        $element = $fieldset->createElement('website', 'Text');
        $element->setLabel('Website');
        $element->addValidator(new StringLengthValidator(['maximum' => 100]));

        $element = $fieldset->createElement('city', 'Text');
        $element->setLabel('City');
        $element->addValidator(new StringLengthValidator(['maximum' => 100]));

        $element = $fieldset->createElement('message', 'Textarea');
        $element->setLabel('Message');
        $element->setProperty('required', true);
        $element->setProperty('rows', '4');
        $element->addValidator(new NotEmptyValidator());

        return $formDefinition;

    }

    public function reviewAction() {

    }

    public function declineAction() {

    }

    public function confirmAction() {

    }

    /**
     * action create
     *
     * @param \WapplerSystems\WsGuestbook\Domain\Model\Wsguestbook $newWsguestbook
     * @return void
     */
    public function createAction(\WapplerSystems\WsGuestbook\Domain\Model\Wsguestbook $newWsguestbook)
    {

        $settings = $this->settings;
        $error = 0;
        $mailerror = 0;

        if ($newWsguestbook->getName() == '' || $newWsguestbook->getEmail() == '') {
            $error = 1;
        }
        if ($newWsguestbook->getEmail() != '') {
            if (filter_var($newWsguestbook->getEmail(), FILTER_VALIDATE_EMAIL)) {
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
                'ws_guestbook'
            );
            $this->addFlashMessage($checkcaptchamsg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new', 'Wsguestbook', 'ws_guestbook', $_REQUEST);
        } else {
            $secretkey = $settings['secretkey'];
            $response = json_decode(
                file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretkey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']),
                true
            );
            if ($response['success'] == false && $settings['captcha'] == 0) {
                $wrongcaptcha = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'controller.wrongcaptcha.msg',
                    'ws_guestbook'
                );
                $this->addFlashMessage($wrongcaptcha, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            } else {
                if ($error == 1) {
                    $requireFields = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'controller.requireFields',
                        'ws_guestbook'
                    );

                    $mailfrmt = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'controller.mailfrmt',
                        'ws_guestbook'
                    );

                    $this->addFlashMessage($requireFields, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

                    if ($mailerror == 1) {
                        $this->addFlashMessage($mailfrmt, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                    }

                    $this->redirect('new', 'Wsguestbook', 'ws_guestbook', $_REQUEST);
                }

                if ($mailerror == 1) {
                    $mailfrmt = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'controller.mailfrmt',
                        'ws_guestbook'
                    );
                    $this->addFlashMessage($mailfrmt, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                    $this->redirect('new', 'Wsguestbook', 'ws_guestbook', $_REQUEST);
                }

                $thanksmsg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'controller.thanks.msg',
                    'ws_guestbook'
                );

                $this->addFlashMessage($thanksmsg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                if ($this->settings['autoaprrove']) {
                } else {
                    $newWsguestbook->setHidden('1');
                }
                $this->wsguestbookRepository->add($newWsguestbook);

                // User name and mail
                if (!empty($this->settings['adminEmail'])) {
                    $adminName = $this->settings['adminName'];
                    $adminEmail = $this->settings['adminEmail'];

                    $confirmationContent = [
                        'adminName' => $adminName,
                        'name' => $newWsguestbook->getName(),
                        'city' => $newWsguestbook->getCity(),
                        'email' => $newWsguestbook->getEmail(),
                        'website' => $newWsguestbook->getWebsite(),
                        'message' => $newWsguestbook->getMessage(),
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
    )
    {

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
            'ws_guestbook'
        );
        return $errormsg;
    }



}
