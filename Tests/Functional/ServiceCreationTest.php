<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ServiceCreationTest
 * @package Nfq\CmsPageBundle\Tests\Functional
 */
class ServiceCreationTest extends WebTestCase
{
    /**
     * Tests if container is returned.
     */
    public function testGetContainer()
    {
        $container = self::createClient()->getKernel()->getContainer();
        $this->assertNotNull($container);
    }

    /**
     * Tests if service are created correctly.
     *
     * @param string $serviceId
     * @param string $instance
     *
     * @dataProvider getTestServiceCreateData
     */
    public function testServiceCreate($serviceId, $instance)
    {
        $container = self::createClient()->getKernel()->getContainer();
        $this->assertTrue($container->has($serviceId), sprintf('Service `%s` was not found in container', $serviceId));

        $service = $container->get($serviceId);
        $this->assertInstanceOf($instance, $service,
            sprintf('Invalid instance `%s` for service `%s`', $instance, $serviceId));
    }

    /**
     * Data provider for testServiceCreate().
     *
     * @return array[]
     */
    public function getTestServiceCreateData()
    {
        return [
            [
                'nfq_cmspage.generic_search',
                'Nfq\\CmsPageBundle\\Service\\Admin\\Search\\CmsSearch',
            ],
            [
                'nfq_cmspage.admin.service.cms_manager',
                'Nfq\\CmsPageBundle\\Service\\Admin\\CmsManager',
            ],
            [
                'nfq_cmspage.admin.service.cms_upload_manager',
                'Nfq\\CmsPageBundle\\Service\\Admin\\CmsUploadManager',
            ],
            [
                'nfq_cmspage.notice_listener',
                'Nfq\\AdminBundle\\EventListener\\NoticeListener',
            ],
            [
                'nfq_cmspage.cms_manager',
                'Nfq\\CmsPageBundle\\Service\\CmsManager',
            ],
            [
                'nfq_cmspage.twig.cmspage_extension',
                'Nfq\\CmsPageBundle\\Twig\\CmsPageExtension',
            ],
            [
                'nfq_cmspage.entity_listener.file_upload',
                'Nfq\\CmsPageBundle\\EventListener\\Doctrine\\FileUploadListener',
            ],
            [
                'nfq_cmspage.entity_listener.generate_slug',
                'Nfq\\CmsPageBundle\\EventListener\\Doctrine\\SlugListener',
            ],
            [
                'nfq_cmspage.admin_configure_menu_listener',
                'Nfq\\CmsPageBundle\\EventListener\\AdminMenuListener',
            ],
        ];
    }
}