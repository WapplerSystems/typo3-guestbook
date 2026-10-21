<?php

namespace WapplerSystems\WsGuestbook\ViewHelpers;

/**
 * This file is part of the "ws_guestbook" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to render data in <head> section of website
 *
 * # Example: Basic example
 * <code>
 * <wsgb:headerData>
 *        <link rel="alternate"
 *            type="application/rss+xml"
 *            title="RSS 2.0"
 *            href="<f:uri.page additionalParams="{type:9818}"/>" />
 * </wsgb:headerData>
 * </code>
 * <output>
 * Added to the header: <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="uri to this page and type 9818" />
 * </output>
 *
 */
class HeaderDataViewHelper extends AbstractViewHelper
{
    public function __construct(private readonly PageRenderer $pageRenderer) {}

    public function render(): string
    {
        $this->pageRenderer->addHeaderData((string)$this->renderChildren());
        return '';
    }
}
