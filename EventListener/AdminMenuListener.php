<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\EventListener;

use Knp\Menu\ItemInterface;
use Nfq\AdminBundle\Event\ConfigureMenuEvent;
use Nfq\AdminBundle\Menu\AdminMenuListener as AdminMenuListenerBase;

/**
 * Class AdminMenuListener
 * @package Nfq\CmsPageBundle\EventListener
 */
class AdminMenuListener extends AdminMenuListenerBase
{
    private const CMS_ROUTES = [
        'nfq_cmspage_list',
        'nfq_cmspage_new',
        'nfq_cmspage_create',
        'nfq_cmspage_update',
        'nfq_cmspage_delete',
    ];

    protected function doMenuConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $node = $this->getCmsPageNode();

        $menu->addChild($node);
    }

    private function getCmsPageNode(): ItemInterface
    {
        return $this
            ->getFactory()
            ->createItem('admin.side_menu.cms_pages', ['route' => self::CMS_ROUTES[0]])
            ->setLabelAttributes([
                'icon' => 'fa fa-newspaper',
            ])
            ->setExtras(
                [
                    'translation_domain' => 'adminInterface',
                    'routes' => self::CMS_ROUTES
                ]
            );
    }
}
