<?php declare(strict_types=1);

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
use Nfq\CmsPageBundle\Service\CmsManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CmsPageExtension
 * @package Nfq\CmsPageBundle\Twig
 */
class CmsPageExtension extends \Twig_Extension
{
    /** @var string */
    private $defaultRouteName = 'nfq_cmspage_view';

    /** @var CmsManager */
    private $manager;

    /** @var PlaceManagerInterface */
    private $placeManager;

    /** @var RouterInterface */
    private $router;

    /** @var RequestStack */
    private $requestStack;

    /** @var string */
    private $defaultLocale;

    public function __construct(
        CmsManager $manager,
        PlaceManagerInterface $placeManager,
        RouterInterface $router,
        RequestStack $requestStack,
        string $defaultLocale
    ) {
        $this->router = $router;
        $this->manager = $manager;
        $this->placeManager = $placeManager;
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction(
                'cmspage',
                [$this, 'getPage'],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new \Twig_SimpleFunction(
                'cms_url',
                [$this, 'getPageUrl'],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new \Twig_SimpleFunction(
                'cms_urls_in_place',
                [$this, 'getPageUrlsInPlace']
            ),
            new \Twig_SimpleFunction(
                'cms_in_place',
                [$this, 'getPagesInPlace']
            ),
            new \Twig_SimpleFunction(
                'cms_urls_raw',
                [$this, 'getPageUrlsRaw'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    /**
     * Returns CMS Page text by its identifier.
     *
     * @throws \Exception
     */
    public function getPage(
        string $identifier,
        string $field = 'text',
        bool $raw = true
    ): string {
        $locale = $this->getLocale();

        try {
            $cmsPage = $this->manager->getCmsPageByIdentifier($identifier, $locale);

            $propAccessor = new PropertyAccessor();
            if (!$cmsPage || !$propAccessor->isReadable($cmsPage, $field)) {
                return '';
            }

            $value = $propAccessor->getValue($cmsPage, $field);

            if ($field === 'text' && $raw) {
                $value = str_replace(['<p>', '</p>'], '', $value);
            }
        } catch (\Exception $ex) {
            //Cms page was not found so just return empty string
            $value = '';
        }

        return $value;
    }

    public function getPageUrlsInPlace(string $placeId, bool $raw = false, string $sortOrder = 'ASC'): array
    {
        $locale = $this->getLocale();

        try {
            $cmsPages = $this->placeManager->getItemsInPlace($placeId, $locale, $sortOrder);

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

    public function getPagesInPlace(string $placeId): array
    {
        $locale = $this->getLocale();

        return $this->placeManager->getItemsInPlace($placeId, $locale);
    }

    public function getPageUrl(
        string $identifier,
        string $locale = null,
        string $routeName = null,
        bool $raw = false
    ): string {
        $locale = $locale ?: $this->getLocale();
        $urlParams = $this->manager->getCmsUrlParams($identifier, $locale, $raw);

        return $this->getUrl($urlParams, $routeName, $raw);
    }

    /**
     * @throws \Exception
     */
    public function getPageUrlsRaw(): array
    {
        $routes = [];
        //Get entity by locale and slug/identifier
        $cmsPage = $this->manager->getCmsPage($this->getSlug(), $this->getLocale());

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

    private function getLocale(): string
    {
        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            return $request->getLocale();
        }

        return $this->defaultLocale;
    }

    private function getSlug(): ?string
    {
        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            return $request->attributes->get('slug');
        }

        return null;
    }

    private function getUrl(array $urlParams, ?string $routeName = null, bool $raw = false): string
    {
        $link = '';
        if ($raw) {
            $link = empty($urlParams['place_title']) ? $urlParams['title'] : $urlParams['place_title'];

            if (empty($link)) {
                return '';
            }

            unset($urlParams['title']);
            unset($urlParams['place_title']);
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
