<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Nfq\AdminBundle\PlaceManager\Repository\PlaceAwareRepositoryInterface;
use Nfq\AdminBundle\Repository\ServiceEntityRepository;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CmsPageRepository
 * @package Nfq\CmsPageBundle\Repository
 */
class CmsPageRepository extends ServiceEntityRepository implements PlaceAwareRepositoryInterface
{
    protected $entityClass = CmsPage::class;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, $this->entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedPlaceSlots(string $placeId): int
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(cms.id)');

        $this->addArrayCriteria($qb, ['cms.places' => '%' . $placeId . '%']);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $idf
     * @param array $criteria
     * @param string $locale
     * @return CmsPage
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCmsPage(string $idf, array $criteria = [], string $locale = null): ?CmsPage
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
     * Get CMS page by id with translated content.
     *
     * @param int $id
     * @param string $locale
     * @return null|CmsPage
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEditableEntity($id, ?string $locale): ?CmsPage
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
    public function getTranslations(CmsPage $cmsPage): array
    {
        return $this
            ->getEntityManager()
            ->getRepository('NfqCmsPageBundle:CmsPageTranslation')
            ->findTranslations($cmsPage);
    }

    public function getTranslatableQueryByCriteriaSorted(
        array $criteria,
        ?string $locale,
        bool $fallback = true,
        string $sortOrder = 'ASC'
    ) {
        $qb = $this->getQueryBuilder();
        $this->addArrayCriteria($qb, $criteria);
        $qb->orderBy('cms.sortPosition', $sortOrder);

        $query = $qb->getQuery();

        $this->setTranslatableHints($query, $locale, $fallback);

        return $query;
    }

    public function getAlias(): string
    {
        return 'cms';
    }
}
