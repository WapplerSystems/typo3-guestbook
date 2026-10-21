<?php
declare(strict_types = 1);

namespace WapplerSystems\WsGuestbook\Form\Factory;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\AbstractFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\GridRow;
use TYPO3\CMS\Form\Domain\Model\FormElements\Section;
use TYPO3\CMS\Form\Domain\Renderer\FluidFormRenderer;
use WapplerSystems\WsGuestbook\Exception\MissingConfigurationException;

class GuestbookFormFactory extends AbstractFormFactory
{

    /**
     * @param array $configuration plugin settings, extended by "storagePid" and "pageUid" in GuestbookController::newAction()
     * @throws MissingConfigurationException
     */
    public function build(array $configuration, ?string $prototypeName = null, ?ServerRequestInterface $request = null): FormDefinition
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration('guestbook');

        $formDefinition = GeneralUtility::makeInstance(FormDefinition::class, 'guestbookEntryForm', $prototypeConfiguration);
        $formDefinition->setRendererClassName(FluidFormRenderer::class);
        $formDefinition->setRenderingOption('controllerAction', 'new');
        $formDefinition->setRenderingOption('submitButtonLabel', 'Submit');


        if (empty($configuration['storagePid'])) {
            throw new MissingConfigurationException('No storagePid set', 1627843908);
        }

        $actionKey = GeneralUtility::makeInstance(Random::class)->generateRandomHexString(30);

        $saveToDatabaseFinisher = $formDefinition->createFinisher('SaveToDatabase');
        $saveToDatabaseFinisher->setOptions([
            'table' => 'tx_wsguestbook_domain_model_entry',
            'mode' => 'insert',
            'databaseColumnMappings' => [
                'pid' => [
                    'value' => $configuration['storagePid'],
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
        $recipientsFlexform = $configuration['verification']['recipients'] ?? [];
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

        if (!empty($configuration['verification']['email']['senderEmailAddress'])) {
            $defaultFrom = [$configuration['verification']['email']['senderEmailAddress'] => $configuration['verification']['email']['senderName']];
        }

        $confirmationUrl = $this->buildActionUri($request, (int)$configuration['pageUid'], 'confirm', $actionKey);
        $declineUrl = $this->buildActionUri($request, (int)$configuration['pageUid'], 'decline', $actionKey);

        $emailFinisher = $formDefinition->createFinisher('EmailToReceiver');
        $emailFinisher->setOptions([
            'subject' => $configuration['verification']['email']['subject'],
            'recipients' => $recipients,
            'senderName' => $defaultFrom[array_key_first($defaultFrom)],
            'senderAddress' => array_key_first($defaultFrom),
            'templateName' => 'Notification',
            'templateRootPaths' => [
                50 => 'EXT:ws_guestbook/Resources/Private/Templates/Email/',
            ],
            'variables' => [
                'confirmationUrl' => $confirmationUrl,
                'declineUrl' => $declineUrl,
            ]
        ]);


        $redirectFinisher = $formDefinition->createFinisher('Redirect');
        $redirectFinisher->setOptions([
            'pageUid' => $configuration['pageUid'],
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
        $stringLengthValidator = GeneralUtility::makeInstance(StringLengthValidator::class);
        $stringLengthValidator->setOptions(['maximum' => 50]);
        $element->addValidator($stringLengthValidator);
        $element->addValidator(new NotEmptyValidator());

        if ((int)($configuration['fields']['email']['enable'] ?? 0) === 1) {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('email', 'Text');
            $element->setLabel('E-Mail');
            $element->setProperty('fluidAdditionalAttributes', ['placeholder' => 'mail@mail.de']);
            $element->addValidator(new EmailAddressValidator());
            if ((int)($configuration['fields']['email']['mandatory'] ?? 0) === 1) {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        if ((int)($configuration['fields']['website']['enable'] ?? 0) === 1) {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('website', 'Text');
            $element->setLabel('Website');
            $element->setProperty('fluidAdditionalAttributes', ['placeholder' => 'https://www.website.de']);
            $stringLengthValidator = GeneralUtility::makeInstance(StringLengthValidator::class);
            $stringLengthValidator->setOptions(['maximum' => 200]);
            $element->addValidator($stringLengthValidator);
            if ((int)($configuration['fields']['website']['mandatory'] ?? 0) === 1) {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        if ((int)($configuration['fields']['city']['enable'] ?? 0) === 1) {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('city', 'Text');
            $element->setLabel('City');
            $stringLengthValidator = GeneralUtility::makeInstance(StringLengthValidator::class);
            $stringLengthValidator->setOptions(['maximum' => 100]);
            $element->addValidator($stringLengthValidator);
            if ((int)($configuration['fields']['city']['mandatory'] ?? 0) === 1) {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('message', 'Textarea');
        $element->setLabel('Message');
        $element->setProperty('rows', '4');
        $element->setProperty('elementClassAttribute', 'form-control-bstextcounter');
        $element->setProperty('fluidAdditionalAttributes', ['data-maximum-chars' => (int)$configuration['fields']['message']['maxCharacters']]);
        $element->addValidator(new NotEmptyValidator());
        $stringLengthValidator = GeneralUtility::makeInstance(StringLengthValidator::class);
        $stringLengthValidator->setOptions(['minimum' => 50, 'maximum' => (int)$configuration['fields']['message']['maxCharacters']]);
        $element->addValidator($stringLengthValidator);

        if ((int)($configuration['fields']['privacyPolicy']['enable'] ?? 0) === 1) {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('privacyPolicy', 'PrivacyPolicyCheckbox');
            $element->setLabel('I agree to the privacy policy');
            $element->setProperty('privacyPolicyUid', $configuration['fields']['privacyPolicy']['page'] ?? '');
            $element->addValidator(new NotEmptyValidator());
        }

        return $this->triggerFormBuildingFinished($formDefinition);
    }

    protected function buildActionUri(?ServerRequestInterface $request, int $pageUid, string $action, string $actionKey): string
    {
        $site = $request?->getAttribute('site');
        if (!$site instanceof Site) {
            throw new MissingConfigurationException('No site found for the current request', 1761040000);
        }
        return (string)$site->getRouter()->generateUri($pageUid, [
            '_language' => $request->getAttribute('language'),
            'tx_wsguestbook_form' => [
                'action' => $action,
                'controller' => 'Guestbook',
                'action_key' => $actionKey,
            ],
        ]);
    }
}
