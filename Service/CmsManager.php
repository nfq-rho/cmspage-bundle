<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service;

use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Repository\CmsPageRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class CmsManager
 * @package Nfq\CmsPageBundle\Service
 */
class CmsManager
{
    /**  @var CmsPageRepository */
    protected $repository;

    /** @var string */
    protected $defaultLocale;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    public function __construct(
        CmsPageRepository $repository,
        AuthorizationCheckerInterface $authChecker,
        string $defaultLocale
    ) {
        $this->repository = $repository;
        $this->authChecker = $authChecker;
        $this->defaultLocale = $defaultLocale;
    }

    public function getRepository(): CmsPageRepository

    {
        return $this->repository;
    }

    /**
     * @param string|CmsPage $target
     * @param string $locale
     * @param bool $raw
     * @return array
     */
    public function getCmsUrlParams($target, $locale, $raw = false): array
    {
        try {
            $entity = $target instanceof CmsPage ? $target : $this->getCmsPageByIdentifier($target, $locale);

            $slug = $entity->getSlug();
            $slug = !empty($slug) ? $slug : $entity->getIdentifier();

            $result = [
                '_locale' => $locale,
                'slug' => $slug,
            ];

            if ($raw) {
                $result['title'] = $entity->getTitle();
                $result['place_title'] = $entity->getPlaceTitleOverwrite();
            }
        } catch (\Exception $ex) {
            $result = ['slug' => '#'];

            if ($raw) {
                $result['title'] = '';
            }
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function getCmsPage(string $criteria, ?string $locale = null, bool $silent = false): CmsPage
    {
        $entity = null;

        try {
            try {
                //First try to find by slug
                $entity = $this->getCmsPageBySlug($criteria, $locale);
            } catch (\Exception $ex) {

                //If no page was found, try to find it by identifier
                //Identifier is not translated, so locale here is for other translatable fields
                $entity = $this->getCmsPageByIdentifier($criteria, $locale);
            }
        } catch (\Exception $ex) {
            //Page was not found by identifier, so just return null if $silent set to true
            //Otherwise exception should be handled outside this function
            if (!$silent) {
                throw $ex;
            }
        }

        return $entity;
    }

    /**
     * No need to set locale if it is current request locale
     * @throws \Exception
     */
    public function getCmsPageBySlug(string $slug, ?string $locale = null): CmsPage
    {
        if (empty($slug)) {
            throw new \Exception('cms.page_not_found');
        }

        $criteria = [
            'cms.slug' => $slug,
        ];

        $this->hideFromPublic($criteria);

        $entity = $this
            ->getRepository()
            ->setUseQueryCache(false)
            ->getOneTranslatableByCriteria($criteria, $locale, false);

        if (is_null($entity)) {
            throw new \Exception('cms.page_not_found');
        }

        return $entity;
    }

    /**
     * @return CmsPage[]
     */
    public function getCmsPagesByPlace(string $place, ?string $locale = null): array
    {
        $criteria = [
            'cms.place' => '%' . $place . '%',
        ];

        $this->hideFromPublic($criteria);

        $entities = $this->getRepository()
            ->setUseQueryCache(false)
            ->getTranslatableQueryByCriteria($criteria, $locale, false)
            ->getArrayResult();

        return $entities;
    }

    /**
     * No need to set locale if it is current request locale
     * @throws \Exception
     */
    public function getCmsPageByIdentifier(string $identifier, ?string $locale = null): CmsPage
    {
        if (empty($identifier)) {
            throw new \Exception('cms.page_not_found');
        }

        $criteria = [
            'cms.identifier' => $identifier,
        ];

        $this->hideFromPublic($criteria);

        $entity = $this->getRepository()
            ->setUseQueryCache(false)
            ->getOneTranslatableByCriteria($criteria, $locale, false);

        if (is_null($entity)) {
            throw new \Exception('cms.page_not_found');
        }

        return $entity;
    }

    public function getPagesByType(string $type): array
    {
        $criteria = ['cms.contentType' => $type];

        $qb = $this->getRepository()->getQueryBuilder();

        $this->hideFromPublic($criteria);

        $this->getRepository()->addArrayCriteria($qb, $criteria);

        return $qb->getQuery()->getArrayResult();
    }

    public function getCmsPageText(string $identifier, ?string $locale = null): string
    {
        if (empty($identifier)) {
            return '';
        }

        $criteria = [
            'cms.identifier' => $identifier,
        ];

        $this->hideFromPublic($criteria);

        $entity = $this->getRepository()->getOneTranslatableByCriteria($criteria, $locale, false);

        if (is_null($entity)) {
            return '';
        }

        $text = str_replace(['<p>', '</p>'], ['', ''], $entity->getText());

        return $text;
    }

    public function getCmsPageTranslations(CmsPage $cmsPage): array
    {
        //Get all entity translations
        $translations = $this->getRepository()->getTranslations($cmsPage);

        foreach ($translations as $locale => $translation) {
            $translations[$locale]['slug'] = !empty($translation['slug']) ? $translation['slug'] : $cmsPage->getIdentifier();
        }

        $this->addDefaultLocaleTranslation($cmsPage, $translations);

        return $translations;
    }

    private function hideFromPublic(array &$criteria): void
    {
        //Add isActive restriction for all other user than admin
        if ($this->authChecker->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'])) {
            $criteria['cms.isActive'] = true;
        }
    }

    private function addDefaultLocaleTranslation(CmsPage $cmsPage, array &$translations): void
    {
        //This is needed, because original entity data is not returned as translation
        $defaultEntity = $this->getRepository()->getEditableEntity($cmsPage->getId(), $this->defaultLocale);
        $defaultSlug = $defaultEntity->getSlug();

        $translations[$this->defaultLocale] = [
            'title' => $defaultEntity->getTitle(),
            'text' => $defaultEntity->getText(),
            'slug' => !empty($defaultSlug) ? $defaultSlug : $defaultEntity->getIdentifier(),
            'image' => $defaultEntity->getImage(),
        ];
    }
}
