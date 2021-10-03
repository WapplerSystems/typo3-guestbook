<?php

namespace WapplerSystems\WsGuestbook\Controller;

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\GridRow;
use TYPO3\CMS\Form\Domain\Model\FormElements\Section;
use TYPO3\CMS\Form\Domain\Renderer\FluidFormRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WapplerSystems\WsGuestbook\Domain\Repository\EntryRepository;
use WapplerSystems\WsGuestbook\Exception\MissingConfigurationException;


/**
 *
 */
class GuestbookController extends AbstractController
{

    /**
     *
     * @var \WapplerSystems\WsGuestbook\Domain\Repository\EntryRepository
     */
    protected $entryRepository;


    /**
     * @param \WapplerSystems\WsGuestbook\Domain\Repository\EntryRepository $entryRepository
     * @internal
     */
    public function injectEntryRepository(EntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }


    /**
     *
     * @param int $currentPage
     * @return void
     */
    public function listAction(int $currentPage = 1)
    {
        $entries = $this->entryRepository->findSorted($this->settings);

        $assignedValues = [
            'settings' => $this->settings
        ];

        if ((int)$this->settings['hidePagination'] === 1) {
            $assignedValues['entries'] = $entries->toArray();
        } else {

            $paginator = new QueryResultPaginator($entries, $currentPage, $this->settings['paginate']['itemsPerPage'] ?? 10);

            $pagination = new SimplePagination($paginator);
            $assignedValues = array_merge($assignedValues,[
                'paginator' => $paginator,
                'pagination' => $pagination,
                'entries' => $paginator->getPaginatedItems(),
            ]);
        }


        $assignedValues = $this->emitActionSignal(self::class, __FUNCTION__, $assignedValues);

        $this->view->assignMultiple($assignedValues);
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
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration('guestbook');

        /** @var FormDefinition $formDefinition */
        $formDefinition = $this->objectManager->get(FormDefinition::class, 'guestbookEntryForm', $prototypeConfiguration);
        $formDefinition->setRendererClassName(FluidFormRenderer::class);
        $formDefinition->setRenderingOption('controllerAction', 'new');
        $formDefinition->setRenderingOption('submitButtonLabel', 'Submit');


        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $this->objectManager->get(
            ConfigurationManagerInterface::class
        );
        $frameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (empty($frameworkConfiguration['persistence']['storagePid'])) {
            throw new MissingConfigurationException('No storagePid set', 1627843908);
        }

        $actionKey = GeneralUtility::makeInstance(Random::class)->generateRandomHexString(30);

        $saveToDatabaseFinisher = $formDefinition->createFinisher('SaveToDatabase');
        $saveToDatabaseFinisher->setOptions([
            'table' => 'tx_wsguestbook_domain_model_entry',
            'mode' => 'insert',
            'databaseColumnMappings' => [
                'pid' => [
                    'value' => $frameworkConfiguration['persistence']['storagePid'],
                ],
                'tstamp' => [
                    'value' => time(),
                ],
                'crdate' => [
                    'value' => time(),
                ],
                'action_key' => [
                    'value' => $actionKey,
                ],
                'hidden' => [
                    'value' => 1,
                ],
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


        $recipients = [];
        $recipientsFlexform = $this->settings['verification']['recipients'];
        foreach ($recipientsFlexform as $recipient) {
            $recipients[$recipient['container']['address']] = $recipient['container']['name'];
        }

        if (count($recipients) === 0) {
            throw new MissingConfigurationException('No recipients set', 1627843942);
        }

        $defaultFrom = MailUtility::getSystemFrom();
        if (isset($defaultFrom[0])) {
            $defaultFrom = [$defaultFrom[0] => 'no sendername'];
        }

        if (!empty($this->settings['verification']['email']['senderEmailAddress'])) {
            $defaultFrom = [$this->settings['verification']['email']['senderEmailAddress'] => $this->settings['verification']['email']['senderName']];
        }

        $confirmationLink = $this->uriBuilder->reset()->setTargetPageUid($this->getTypoScriptFrontendController()->id)->setArgumentPrefix('tx_wsguestbook_form')->setArguments([
            'action' => 'confirm',
            'action_key' => $actionKey,
        ])->buildFrontendUri();

        $declineLink = $this->uriBuilder->reset()->setTargetPageUid($this->getTypoScriptFrontendController()->id)->setArguments([

        ])->buildFrontendUri();

        $emailFinisher = $formDefinition->createFinisher('EmailToReceiver');
        $emailFinisher->setOptions([
            'subject' => $this->settings['verification']['email']['subject'],
            'recipients' => $recipients,
            'senderName' => $defaultFrom[array_key_first($defaultFrom)],
            'senderAddress' => array_key_first($defaultFrom),
            'useFluidEmail' => true,
            'templateName' => 'Notification',
            'templateRootPaths' => [
                50 => 'EXT:ws_guestbook/Resources/Private/Templates/Email/',
            ],
            'variables' => [
                'confirmationLink' => $confirmationLink,
                'declineLink' => $declineLink,
            ]
        ]);


        $redirectFinisher = $formDefinition->createFinisher('Redirect');
        $redirectFinisher->setOptions([
            'pageUid' => $this->getTypoScriptFrontendController()->id,
            'additionalParameters' => 'tx_wsguestbook_form[action]=done',
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

        if ($this->settings['fields']['email']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('email', 'Text');
            $element->setLabel('E-Mail');
            $element->setProperty('fluidAdditionalAttributes', ['placeholder' => 'mail@mail.de']);
            $element->addValidator(new EmailAddressValidator());
            if ($this->settings['fields']['email']['mandatory'] === '1') {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        if ($this->settings['fields']['website']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('website', 'Text');
            $element->setLabel('Website');
            $element->setProperty('fluidAdditionalAttributes', ['placeholder' => 'https://www.website.de']);
            $element->addValidator(new StringLengthValidator(['maximum' => 200]));
            if ($this->settings['fields']['website']['mandatory'] === '1') {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        if ($this->settings['fields']['city']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('city', 'Text');
            $element->setLabel('City');
            $element->addValidator(new StringLengthValidator(['maximum' => 100]));
            if ($this->settings['fields']['city']['mandatory'] === '1') {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('message', 'Textarea');
        $element->setLabel('Message');
        $element->setProperty('rows', '4');
        $element->setProperty('elementClassAttribute', 'form-control-bstextcounter');
        $element->setProperty('fluidAdditionalAttributes',['data-maximum-chars' => (int)$this->settings['fields']['message']['maxCharacters']]);
        $element->addValidator(new NotEmptyValidator());
        $element->addValidator(new StringLengthValidator(['minimum' => 50, 'maximum' => (int)$this->settings['fields']['message']['maxCharacters']]));

        if ($this->settings['fields']['captcha']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('captcha', 'Captcha');
            $element->setLabel('Captcha');
        }

        if ($this->settings['fields']['privacyPolicy']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('privacyPolicy', 'PrivacyPolicyCheckbox');
            $element->setLabel('I agree to the privacy policy');
            $element->setProperty('privacyPolicyUid',$this->settings['fields']['privacyPolicy']['page'] ?? '');
            $element->addValidator(new NotEmptyValidator());
        }

        return $formDefinition;
    }

    public function doneAction() {

    }

    public function reviewAction() {

    }

    public function declineAction() {

    }

    public function confirmAction() {

    }


    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
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

        /** @var StandaloneView $emailView */
        $emailView = $this->objectManager->get(StandaloneView::class);

        /*For use of Localize value */
        $extensionName = $this->request->getControllerExtensionName();
        $emailView->getRequest()->setControllerExtensionName($extensionName);

        /*For use of Localize value */
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $templateRootPath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPaths']['0']);
        $templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . '.html';
        $emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $emailView->assignMultiple($variables);
        $emailBody = $emailView->render();
        /** @var $message MailMessage */
        $message = $this->objectManager->get(MailMessage::class);
        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);
        $message->html($emailBody);

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
        $errormsg = LocalizationUtility::translate(
            'controller.insertError.msg',
            'ws_guestbook'
        );
        return $errormsg;
    }



    /**
     * Emits signal for various actions
     *
     * @param string $class the class name
     * @param string $signalName name of the signal slot
     * @param array $signalArguments arguments for the signal slot
     *
     * @return array
     */
    protected function emitActionSignal($class, $signalName, array $signalArguments)
    {
        $signalArguments['extendedVariables'] = [];
        return $this->signalSlotDispatcher->dispatch($class, $signalName, $signalArguments);
    }


}
