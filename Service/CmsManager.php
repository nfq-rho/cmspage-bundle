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

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Query;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Entity\CmsPageRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Class CmsManager
 * @package Nfq\CmsPageBundle\Service
 */
class CmsManager
{
    /**
     * @var ObjectRepository|CmsPageRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @param ObjectRepository $repository
     * @param string $locale
     */
    public function __construct(ObjectRepository $repository, $locale)
    {
        $this->repository = $repository;
        $this->defaultLocale = $locale;
    }

    /**
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function setAuthChecker(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    /**
     * @return CmsPageRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string|CmsPage $target
     * @param string $locale
     * @param bool $raw
     * @return array
     */
    public function getCmsUrlParams($target, $locale, $raw = false)
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
                $result['name'] = $entity->getName();
                $result['place_name'] = $entity->getPlaceName();
            }
        } catch (\Exception $ex) {
            $result = ['slug' => '#'];

            if ($raw) {
                $result['name'] = '';
            }
        }

        return $result;
    }

    /**
     * @param string $criteria
     * @param string|null $locale
     * @param bool $silent
     * @return CmsPage
     * @throws \Exception
     */
    public function getCmsPage($criteria, $locale = null, $silent = false)
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
     * @param string $slug
     * @param string|null $locale - no need to set locale if it is current request locale
     * @return CmsPage
     * @throws \Exception
     */
    public function getCmsPageBySlug($slug, $locale = null)
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
     * @param string $place
     * @param string $locale
     * @return array
     */
    public function getCmsPagesByPlace($place, $locale = '')
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
     * @param string $identifier
     * @param string $locale - no need to set locale if it is current request locale
     * @return CmsPage
     * @throws \Exception
     */
    public function getCmsPageByIdentifier($identifier, $locale = '')
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

    /**
     * @param string $type
     * @return array
     */
    public function getPagesByType($type)
    {
        $criteria = ['cms.contentType' => $type];

        $qb = $this->getRepository()->getQueryBuilder();

        $this->hideFromPublic($criteria);

        $this->getRepository()->addArrayCriteria($qb, $criteria);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param string $identifier
     * @param string $locale
     * @return string
     */
    public function getCmsPageText($identifier, $locale)
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

    /**
     *
     * @param CmsPage $cmsPage
     *
     * @return array
     */
    public function getCmsPageTranslations(CmsPage $cmsPage)
    {
        //Get all entity translations
        $translations = $this->getRepository()->getTranslations($cmsPage);

        foreach ($translations as $locale => $translation) {
            $translations[$locale]['slug'] = !empty($translation['slug']) ? $translation['slug'] : $cmsPage->getIdentifier();
        }

        $this->addDefaultLocaleTranslation($cmsPage, $translations);

        return $translations;
    }

    /**
     * @param array $criteria
     */
    private function hideFromPublic(array &$criteria)
    {
        try {
            //Add isActive restriction for all other user than admin
            if (!$this->authChecker instanceof AuthorizationCheckerInterface
                || !$this->authChecker->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'])
            ) {
                $criteria['cms.isActive'] = true;
            }
        } catch (AuthenticationCredentialsNotFoundException $ex) {
            $criteria['cms.isActive'] = true;
        }
    }

    /**
     * @param CmsPage $cmsPage
     * @param array $translations
     *
     * @return mixed
     */
    private function addDefaultLocaleTranslation(CmsPage $cmsPage, &$translations)
    {
        //This is needed, because original entity data is not returned as translation
        $defaultEntity = $this->getRepository()->getEditableEntity($cmsPage->getId(), $this->defaultLocale);
        $defaultSlug = $defaultEntity->getSlug();

        $translations[$this->defaultLocale] = [
            'name' => $defaultEntity->getName(),
            'text' => $defaultEntity->getText(),
            'slug' => !empty($defaultSlug) ? $defaultSlug : $defaultEntity->getIdentifier(),
            'image' => $defaultEntity->getImage(),
        ];

        return $translations;
    }
}
