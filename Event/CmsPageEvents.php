<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Event;

/**
 * Class CmsPageEvents
 * @package Nfq\CmsPageBundle\Event
 */
final class CmsPageEvents
{
    public const CMSPAGE_BEFORE_INSERT = 'cmspage.before_insert';

    public const CMSPAGE_AFTER_INSERT = 'cmspage.after_insert';

    public const CMSPAGE_BEFORE_SAVE = 'cmspage.before_save';

    public const CMSPAGE_AFTER_SAVE = 'cmspage.after_save';

    public const CMSPAGE_BEFORE_DELETE = 'cmspage.before_delete';

    public const CMSPAGE_AFTER_DELETE = 'cmspage.after_delete';
}
