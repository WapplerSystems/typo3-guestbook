<?php

namespace WapplerSystems\WsGuestbook\Controller;

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
        /** @var ConfigurationService $configurationService */
        $configurationService = $this->objectManager->get(ConfigurationService::class);
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration('standard');

        /** @var FormDefinition $formDefinition */
        $formDefinition = $this->objectManager->get(FormDefinition::class, 'registrationForm', $prototypeConfiguration);
        $formDefinition->setRendererClassName(FluidFormRenderer::class);
        $formDefinition->setRenderingOption('controllerAction', 'registration');
        $formDefinition->setRenderingOption('submitButtonLabel', 'Submit registration');

        $saveToDatabaseFinisher = $formDefinition->createFinisher('SaveToDatabase');
        $saveToDatabaseFinisher->setOptions([
            'table' => 'fe_users',
            'mode' => 'insert',
            'databaseColumnMappings' => [
                'pid' => [
                    'value' => 14,
                ],
                'password' => [
                    'value' => bin2hex(random_bytes(10)),
                ],
                'tx_extbase_type' => [
                    'value' => 'Tx_Extbase_Domain_Model_FrontendUser',
                ],
                'usergroup' => [
                    'value' => '1',
                ],
                'tstamp' => [
                    'value' => time(),
                ]
            ],
            'elements' => [
                'email' => [
                    'mapOnDatabaseColumn' => 'username',
                ],
                'firstName' => [
                    'mapOnDatabaseColumn' => 'first_name',
                ],
                'lastName' => [
                    'mapOnDatabaseColumn' => 'last_name',
                ],
                'telephone' => [
                    'mapOnDatabaseColumn' => 'telephone',
                ],
                'postalcode' => [
                    'mapOnDatabaseColumn' => 'zip',
                ],
                'city' => [
                    'mapOnDatabaseColumn' => 'city',
                ],
                'street' => [
                    'mapOnDatabaseColumn' => 'address',
                ],
                'company' => [
                    'mapOnDatabaseColumn' => 'company',
                ],
                'country' => [
                    'mapOnDatabaseColumn' => 'country',
                ],
                'privacyStatement' => [
                    'mapOnDatabaseColumn' => 'accept_privacy_policy',
                    'skipIfValueIsEmpty' => true
                ],
                'dataTransfer' => [
                    'mapOnDatabaseColumn' => 'accept_data_transfer',
                    'skipIfValueIsEmpty' => true
                ]
            ]
        ]);




        /*
        $mailchimpFinisher = $formDefinition->createFinisher('MailChimp');
        $mailchimpFinisher->setOption('apiKey',$this->settings['mailchimp']['apiKey']);
        $mailchimpFinisher->setOption('listId',$this->settings['mailchimp']['listId']);


        if (isset($this->settings['keycloak']['activate']) && (int)$this->settings['keycloak']['activate'] === 1) {
            $keycloakCreateUserFinisher = $formDefinition->createFinisher('KeycloakCreateUser');
            $link = $this->uriBuilder->reset()->setTargetPageUid(293)->setCreateAbsoluteUri(true)->buildFrontendUri();
            $keycloakCreateUserFinisher->setOption('redirectUrl', $link);
        }


        $doubleOptInFormFinisher = $formDefinition->createFinisher('DoubleOptIn');
        $doubleOptInFormFinisher->setOption('validationPid', $this->settings['validationPid']);
        $doubleOptInFormFinisher->setOption('subject','Please confirm your email address');
        $doubleOptInFormFinisher->setOption('recipientAddress', '{email}');
        $doubleOptInFormFinisher->setOption('recipientName', '{firstName} {lastName}');
        $doubleOptInFormFinisher->setOption('senderAddress', 'info@linear.eu');
        $doubleOptInFormFinisher->setOption('senderName', 'liNear');
        $doubleOptInFormFinisher->setOption('templatePathAndFilename', 'EXT:linear_download_manager/Resources/Private/Templates/Email/DoubleOptIn.html');
        $doubleOptInFormFinisher->setOption('variables',[
            'uri' => [
                'icon' => $this->getControllerContext()->getRequest()->getBaseUri() .'typo3conf/ext/linear_download_manager/Resources/Public/Icons/Email/',
                'images' => $this->getControllerContext()->getRequest()->getBaseUri() .'typo3conf/ext/linear_download_manager/Resources/Public/Images/',
                'facebook' => LocalizationUtility::translate('uri.facebook', 'linear_download_manager'),
                'instagram' => LocalizationUtility::translate('uri.instagram', 'linear_download_manager'),
                'linkedin' => LocalizationUtility::translate('uri.linkedin', 'linear_download_manager'),
                'youtube' => LocalizationUtility::translate('uri.youtube', 'linear_download_manager'),
                'website' => LocalizationUtility::translate('uri.website', 'linear_download_manager'),
                'legalNotice' => LocalizationUtility::translate('uri.legalNotice', 'linear_download_manager'),
                'dataPrivacyStatement' => LocalizationUtility::translate('uri.dataPrivacyStatement', 'linear_download_manager')
            ]
        ]);
        $doubleOptInFormFinisher->setOption('payloadElements', [
                'subscribeToNewsletter',
                'applyForActivation',
            ]
        );
        if (isset($GLOBALS['TSFE']->config['config']['language'])) {
            $doubleOptInFormFinisher->setOption('translation',['language' => $GLOBALS['TSFE']->config['config']['language']]);
        }


        $confirmationFinisher = $formDefinition->createFinisher('Confirmation');
        $confirmationFinisher->setOptions([
            'message' => LocalizationUtility::translate('msg.pleaseConfirmEmailAddress', 'linear_download_manager'),
            'templateName' => 'Confirmation',
            'templateRootPaths' => [
                10 => 'EXT:linear_download_manager/Resources/Private/Templates/Form/Finisher/Confirmation/',
            ]
        ]);
*/

        $page = $formDefinition->createPage('page1');


        /** @var GridRow $row */
        $row = $page->createElement('row1', 'GridRow');


        /** @var Section $fieldset */
        $fieldset = $row->createElement('fieldsetPerson', 'Fieldset');
        $fieldset->setLabel('Personal data');
        $fieldset->setOptions(['properties' => [
            'gridColumnClassAutoConfiguration' => [
                'viewPorts' => [
                    'md' => 6
                ]
            ]
        ]]);

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('firstName', 'Text');
        $element->setLabel('firstName');
        //$element->addValidator(new StringLengthValidator(['maximum' => 50]));
        $element->addValidator(new NotEmptyValidator());

        $element = $fieldset->createElement('lastName', 'Text');
        $element->setLabel('lastName');
        $element->setProperty('required', true);
        $element->addValidator(new NotEmptyValidator());
        //$element->addValidator(new StringLengthValidator(['minimum' => 2]));

        $element = $fieldset->createElement('email', 'Text');
        $element->setLabel('email');
        //$element->addValidator(new StringLengthValidator(['maximum' => 50]));
        $element->addValidator(new NotEmptyValidator());
        $element->addValidator(new EmailAddressValidator());
        if (isset($this->settings['keycloak']['activate']) && (int)$this->settings['keycloak']['activate'] === 1) {
            $element->addValidator(new UsernameAlreadyTakenValidator());
        }

        $element = $fieldset->createElement('telephone', 'Text');
        $element->setLabel('telephone');
        $element->addValidator(new StringLengthValidator(['maximum' => 60]));

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('password', 'AdvancedPassword');
        $element->setLabel('password');
        $element->setProperty('confirmationLabel', 'confirm password');
        $element->addValidator(new StringLengthValidator(['minimum' => 8, 'maximum' => 40]));
        $element->addValidator(new NotEmptyValidator());
        $element->setProperty('passwordDescription', 'At least 8 characters. Numbers, letters and special characters are recommended.');



        /** @var Section $fieldset */
        $fieldset = $row->createElement('fieldsetCompany', 'Fieldset');
        $fieldset->setLabel('Company');
        $fieldset->setOptions(['properties' => [
            'gridColumnClassAutoConfiguration' => [
                'viewPorts' => [
                    'md' => 6
                ]
            ]
        ]]);


        $element = $fieldset->createElement('company', 'Text');
        $element->setLabel('company');
        $element->addValidator(new StringLengthValidator(['maximum' => 60]));
        $element->addValidator(new NotEmptyValidator());

        $element = $fieldset->createElement('street', 'Text');
        $element->setLabel('street');
        $element->addValidator(new StringLengthValidator(['maximum' => 60]));

        $element = $fieldset->createElement('postalcode', 'Text');
        //$element->setLabel('postalcode');
        $element->setLabel('postalcode');
        $element->addValidator(new StringLengthValidator(['maximum' => 8]));

        $element = $fieldset->createElement('city', 'Text');
        $element->setLabel('city');
        $element->addValidator(new StringLengthValidator(['maximum' => 60]));

        $element = $fieldset->createElement('country', 'Text');
        $element->setLabel('country');
        $element->addValidator(new StringLengthValidator(['maximum' => 60]));



        $element = $page->createElement('privacyStatement', 'PrivacyStatementCheckbox');
        $element->setLabel('privacyStatement');
        $element->setProperty('privacyUid', 127);
        $element->setProperty('value', 1);
        $element->setProperty('containerClassAttribute', 'checkbox single-checkbox');
        $element->addValidator(new NotEmptyValidator());

        $element = $page->createElement('dataTransfer', 'DataTransferCheckbox');
        $element->setLabel('dataTransfer');
        $element->setProperty('datatransferUid', 517);
        $element->setProperty('value', 1);
        $element->setProperty('containerClassAttribute', 'checkbox single-checkbox');

        $element = $page->createElement( 'subscribeToNewsletter', 'Checkbox');
        $element->setLabel('subscribeToNewsletter');
        $element->setProperty('value', 1);
        $element->setProperty('containerClassAttribute', 'checkbox single-checkbox');

        $element = $page->createElement( 'applyForActivation', 'Checkbox');
        $element->setLabel('applyForActivation');
        $element->setProperty('value', 1);
        $element->setProperty('containerClassAttribute', 'checkbox single-checkbox');

        return $formDefinition;

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

    public function guestbookformAction()
    {
        $page = $formDefinition->createPage('formPage');

        $row = $page->createElement('row1', 'GridRow');

        $fieldset = $row->createElement('fieldsetEntry', 'Fieldset');
        $fieldset->setLabel('New Entry');
        $fieldset->setOptions(['properties' => [
            'gridColumnClassAutoConfiguration' => [
                'viewPorts' => [
                    'md' => 6
                ]
            ]
        ]]);

        $element = $fieldset->createElement('firstName', 'Text');
        $element->setLabel('firstName');
        $element->addValidator(new NotEmptyValidator());
        $element->addValidator(newStringLengthValidator(['maximum' => 50]));

        $element = $fieldset->createElement('lastName', 'Text');
        $element->setLabel('lastName');
        $element->addValidator(newStringLengthValidator(['maximum' => 50]));

        $element = $fieldset->createElement('email', 'Text');
        $element->setLabel('email');
        $element->addValidator(new NotEmptyValidator());
        $element->addValidator(new EmailAddressValidator());
        if (isset($this->settings['keycloak']['activate']) && (int)$this->settings['keycloak']['activate'] === 1) {
            $element->addValidator(new UsernameAlreadyTakenValidator());
        }

    }

}
