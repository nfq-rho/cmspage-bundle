<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle;

/**
 * Class CmsPageEvents
 * @package Nfq\CmsPageBundle
 */
final class CmsPageEvents
{
    const CMSPAGE_BEFORE_INSERT = 'cmspage.before_insert';
    const CMSPAGE_AFTER_INSERT = 'cmspage.after_insert';
    const CMSPAGE_BEFORE_SAVE = 'cmspage.before_save';
    const CMSPAGE_AFTER_SAVE = 'cmspage.after_save';
    const CMSPAGE_BEFORE_DELETE = 'cmspage.before_delete';
    const CMSPAGE_AFTER_DELETE = 'cmspage.after_delete';
}
