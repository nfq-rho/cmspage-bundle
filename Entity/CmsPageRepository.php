<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Nfq\AdminBundle\Doctrine\ORM\EntityRepository;
use Nfq\AdminBundle\PlaceManager\Repository\PlaceAwareRepositoryInterface;

/**
 * Class CmsPageRepository
 * @package Nfq\CmsPageBundle\Entity
 */
class CmsPageRepository extends EntityRepository implements PlaceAwareRepositoryInterface
{
    /**
     * @param string $idf
     * @param array $criteria
     * @param string $locale
     * @return CmsPage
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCmsPage($idf, array $criteria = [], $locale = null)
    {
        $qb = $this->getQueryBuilder();
        $this->addArrayCriteria($qb, $criteria);

        $qb
            ->select('cms')
            ->andWhere($qb->expr()->orX('cms.identifier = :idf', 'cms.slug = :idf'))
            ->setMaxResults(1)
            ->setParameter('idf', $idf);

        $query = $qb->getQuery();
        $this->setTranslatableHints($query, $locale, false);

        return $query->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedPlaceSlots($placeId)
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(cms.id)');

        $this->addArrayCriteria($qb, ['cms.places' => '%' . $placeId . '%']);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get CMS page by id with translated content.
     *
     * @param int $id
     * @param string $locale
     * @return null|CmsPage
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEditableEntity($id, $locale)
    {
        $query = $this->getTranslatableQueryByCriteria(['id' => $id], $locale, false);

        //This line fixes issue with same translation rendered for different locale in editing popup
        $query->useQueryCache(false);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @param CmsPage $cmsPage
     *
     * @return array
     */
    public function getTranslations(CmsPage $cmsPage)
    {
        return $this
            ->getEntityManager()
            ->getRepository('NfqCmsPageBundle:CmsPageTranslation')
            ->findTranslations($cmsPage);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'cms';
    }
}
