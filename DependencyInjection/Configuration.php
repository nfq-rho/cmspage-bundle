<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Nfq\CmsPageBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nfq_cms_page');
        $rootNode = method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('nfq_cms_page');

        $rootNode
            ->children()
                ->arrayNode('types')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')
                                ->info('Custom class for type')
                            ->end()
                            ->booleanNode('has_featured_image')
                                ->info('Set to true if this type has featured image. Image input will be displayed')
                                ->isRequired()
                            ->end()
                            ->booleanNode('container_aware')
                                ->info('If custom adapter need container, set this to true')
                                ->isRequired()
                            ->end()
                            ->booleanNode('public')
                                ->info('Set to true if this type should be available via public url. If set to false,
                                    slug will not be generated for this type')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('places')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->isRequired()->end()
                            ->integerNode('limit')->isRequired()->end()
                        ->end()
                    ->end()
                ->end() // places
            ->end();

        return $treeBuilder;
    }
}
