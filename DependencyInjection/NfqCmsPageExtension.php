<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NfqCmsPageExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->mapConfig($container, $configs[0]);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function mapConfig(ContainerBuilder $container, array $config)
    {
        $uploadDir = ltrim($config['upload_dir'], DIRECTORY_SEPARATOR);

        $_config = [
            'upload_absolute' => $container->getParameter('kernel.root_dir') . '/../web/' . $uploadDir ,
            'upload_relative' => $uploadDir,
        ];

        $container->setParameter('nfq_cmspage.config', $_config);
        $container->setParameter('nfq_cmspage.types', $config['types']);

        $places = isset($config['places']) ? (array)$config['places'] : [];
        $container->setParameter('nfq_cmspage.places', $places);
    }
}
