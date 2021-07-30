<?php

namespace WapplerSystems\WsGuestbook\Validation\Validator;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CaptchaValidator extends AbstractValidator
{

    protected $supportedOptions = [
        'phrase' => ['', 'The phrase of the captcha', 'string']
    ];

    protected function isValid($value)
    {
        $cacheIdentifier = $GLOBALS['TSFE']->fe_user->getKey('ses', 'captchaId');

        if (!$cacheIdentifier) {
            $this->displayError();
            return;
        }

        // get captcha secret from cache and compare
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('wsguestbookcaptcha');
        $phrase = $cache->get($cacheIdentifier);

        if (!$phrase || !is_string($value) || $phrase !== $value) {
            $this->displayError();
        }
    }

    protected function displayError()
    {
        $this->addError(
            $this->translateErrorMessage(
                'validator.captcha.notvalid',
                'ws_guestbook'
            ) ?? '',
            1623240740
        );
    }
}
