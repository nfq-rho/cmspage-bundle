<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Twig;

use Nfq\AdminBundle\PlaceManager\PlaceManagerInterface;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Service\Admin\CmsUploadManager;
use Nfq\CmsPageBundle\Service\CmsManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class CmsPageExtension
 * @package Nfq\CmsPageBundle\Twig
 */
class CmsPageExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $defaultRouteName = 'nfq_cmspage_view';

    /**
     * @var CmsManager
     */
    private $manager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var CmsUploadManager
     */
    private $uploadManager;

    /**
     * @var PlaceManagerInterface
     */
    private $placeManager;

    /**
     * CmsPageExtension constructor.
     * @param CmsManager $manager
     * @param CmsUploadManager $uploadManager
     * @param PlaceManagerInterface $placeManager
     * @param RouterInterface $router
     */
    public function __construct(
        CmsManager $manager,
        CmsUploadManager $uploadManager,
        PlaceManagerInterface $placeManager,
        RouterInterface $router
    ) {
        $this->router = $router;
        $this->manager = $manager;
        $this->placeManager = $placeManager;
        $this->uploadManager = $uploadManager;
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cmspage', [$this, 'getPage'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new \Twig_SimpleFunction('cms_url', [$this, 'getPageUrl'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new \Twig_SimpleFunction('cms_urls_in_place', [$this, 'getPageUrlsInPlace'],
                [
                    'needs_environment' => true,
                ]
            ),
            new \Twig_SimpleFunction('cms_in_place', [$this, 'getPagesInPlace'],
                [
                    'needs_environment' => true,
                ]
            ),
            new \Twig_SimpleFunction('cms_image_src', [$this, 'getCmsImageSrc']),
            new \Twig_SimpleFunction('cms_urls_raw', [$this, 'getPageUrlsRaw'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            )
        ];
    }

    /**
     * @param CmsPage|array $entity
     * @return string
     */
    public function getCmsImageSrc($entity)
    {
        return $this->uploadManager->getWebPathForEntity($entity);
    }

    /**
     * Returns CMS Page text by its identifier.
     *
     * @param \Twig_Environment $environment
     * @param string $identifier
     * @param string $field
     * @param bool $raw
     * @return string
     * @throws \Exception
     */
    public function getPage(\Twig_Environment $environment, $identifier, $field = 'text', $raw = true)
    {
        $locale = $this->getLocale($environment);

        try {
            $cmsPage = $this->manager->getCmsPageByIdentifier($identifier, $locale);

            $propAccessor = new PropertyAccessor();
            if (!$cmsPage || !$propAccessor->isReadable($cmsPage, $field)) {
                return '';
            }

            $value = $propAccessor->getValue($cmsPage, $field);

            if ($field == 'text' && $raw) {
                $value = str_replace(['<p>', '</p>'], ['', ''], $value);
            }
        } catch (\Exception $ex) {
            //Cms page was not found so just return empty string
            $value = '';
        }

        return $value;
    }

    /**
     * @param \Twig_Environment $environment
     * @param string $placeId
     * @param bool|false $raw
     * @return array
     */
    public function getPageUrlsInPlace(\Twig_Environment $environment, $placeId, $raw = false)
    {
        $locale = $this->getLocale($environment);

        try {
            $cmsPages = $this->placeManager->getItemsInPlace($placeId, $locale);

            $result = [];
            foreach ($cmsPages as $groupPage) {
                $urlParams = $this->manager->getCmsUrlParams($groupPage, $locale, $raw);

                $result[] = $this->getUrl($urlParams, '', $raw);
            }
        } catch (\Exception $ex) {
            //Cms page was not found so just return empty string
            $result = [];
        }

        return $result;
    }

    /**
     * @param \Twig_Environment $environment
     * @param string $placeId
     * @return array
     */
    public function getPagesInPlace(\Twig_Environment $environment, $placeId)
    {
        $locale = $this->getLocale($environment);

        return $this->placeManager->getItemsInPlace($placeId, $locale);
    }

    /**
     * @param \Twig_Environment $environment
     * @param string $identifier
     * @param string $locale
     * @param string $routeName
     * @param bool $raw
     * @return string
     */
    public function getPageUrl(\Twig_Environment $environment, $identifier, $locale = '', $routeName = '', $raw = false)
    {
        if (empty($locale)) {
            $locale = $this->getLocale($environment);
        }

        $urlParams = $this->manager->getCmsUrlParams($identifier, $locale, $raw);

        return $this->getUrl($urlParams, $routeName, $raw);
    }

    /**
     * @param \Twig_Environment $environment
     *
     * @return array
     * @throws \Exception
     */
    public function getPageUrlsRaw(\Twig_Environment $environment)
    {
        $routes = [];
        //Get entity by locale and slug/identifier
        $cmsPage = $this->manager->getCmsPage($this->getSlug($environment), $this->getLocale($environment));

        foreach ($this->manager->getCmsPageTranslations($cmsPage) as $locale => $translation) {
            $urlParams['_locale'] = $locale;
            $urlParams['slug'] = $translation['slug'];

            try {
                $routes[$locale] = $this->getUrl($urlParams);
            } catch (RouteNotFoundException $e) {
                //Exception can be thrown if JMS I18 does not have cms page with this locale.
            }
        }

        return $routes;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmspage_extension';
    }

    /**
     * @param \Twig_Environment $environment
     * @return string
     */
    private function getLocale(\Twig_Environment $environment)
    {
        if ($this->session && $this->session->has('_locale')) {
            return $this->session->get('_locale');
        }

        return $environment->getGlobals()['app']->getRequest()->getLocale();
    }

    /**
     * @param $environment
     *
     * @return string
     */
    private function getSlug($environment)
    {
        return $environment->getGlobals()['app']->getRequest()->attributes->get('slug');
    }

    /**
     * @param array $urlParams
     * @param string $routeName
     * @param bool $raw
     * @return string
     */
    private function getUrl(array $urlParams, $routeName = '', $raw = false)
    {
        $link = '';
        if ($raw) {
            $link = empty($urlParams['place_name']) ? $urlParams['name'] : $urlParams['place_name'];

            if (empty($link)) {
                return '';
            }

            unset($urlParams['name']);
            unset($urlParams['place_name']);
        }

        if (empty($routeName)) {
            $routeName = $this->defaultRouteName;
        }

        $href = $this->router->generate($routeName, $urlParams, true);

        return $raw
            ? sprintf('<a href="%s">%s</a>', $href, $link)
            : $href;
    }
}
