<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service;

use Nfq\CmsPageBundle\Service\Adapters\CmsPageAdapterInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CmsTypeManager
 * @package Nfq\CmsPageBundle\Service
 */
class CmsTypeManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    private $defaultType = 'cms';

    /**
     * @var string
     */
    private $defaultAdapterNs = 'Nfq\\CmsPageBundle\\Service\\Adapters\\%sAdapter';

    /**
     * @var array
     */
    private $adapters = [];

    /**
     * @param array $configuredTypes
     */
    public function setConfig(array $configuredTypes)
    {
        $this->resolveConfig($configuredTypes);
    }

    /**
     * @param Request $request
     * @return CmsPageAdapterInterface
     */
    public function getAdapterFromRequest(Request $request)
    {
        $type = $request->get('_type', $this->defaultType);
        $type = isset($this->adapters[$type]) ? $type : $this->defaultType;

        return $this->adapters[$type];
    }

    public function getTypes(): array
    {
        return array_combine(
            array_keys($this->adapters),
            array_map(function ($item) {
                return 'admin.cms.labels.adapter_' . $item;
            }, array_keys($this->adapters)));
    }

    /**
     * @return string
     */
    private function getDefaultAdapterClass()
    {
        return sprintf($this->defaultAdapterNs, $this->defaultType);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getCustomAdapterClass($name)
    {
        return sprintf($this->defaultAdapterNs, ucfirst($name));
    }

    /***
     * @param string $name
     * @param array $options
     * @return string
     */
    private function resolveAdapterClass($name, array &$options)
    {
        if (isset($options['class'])) {
            $class = $options['class'];

            unset($options['class']);
        } else {
            $class = $this->getCustomAdapterClass($name);
        }

        if (!class_exists($class)) {
            $class = $this->getDefaultAdapterClass();
        }

        return $class;
    }

    /**
     * @param array $configuredTypes
     */
    private function resolveConfig(array $configuredTypes)
    {
        foreach ($configuredTypes as $name => $options) {

            $class = $this->resolveAdapterClass($name, $options);

            /** @var CmsPageAdapterInterface $adapter */
            $adapter = new $class($options);

            if (isset($options['container_aware']) && $options['container_aware'] === true) {
                $adapter->setContainer($this->container);
            }

            $this->adapters[$name] = $adapter;
        }
    }
}
