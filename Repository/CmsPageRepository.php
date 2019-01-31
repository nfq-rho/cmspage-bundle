<?php declare(strict_types=1);

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
use Nfq\AdminBundle\Repository\TranslatableRepositoryTrait;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Entity\CmsPageTranslation;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CmsPageRepository
 * @package Nfq\CmsPageBundle\Repository
 */
class CmsPageRepository extends ServiceEntityRepository implements PlaceAwareRepositoryInterface
{
    use TranslatableRepositoryTrait;

    /** @var string */
    protected $entityClass = CmsPage::class;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, $this->entityClass);
    }

    public function getUsedPlaceSlots(string $placeId): int
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(cms.id)');

        $this->addCriteria($qb, ['cms.places' => '%' . $placeId . '%']);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get CMS page by id with translated content.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEditableEntity($id, ?string $locale): ?CmsPage
    {
        $query = $this->getTranslatableQueryByCriteria(['id' => $id], $locale, false);

        //This line fixes issue with same translation rendered for different locale in editing popup
        $query->useQueryCache(false);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    public function getTranslations(CmsPage $cmsPage): array
    {
        return $this
            ->getEntityManager()
            ->getRepository(CmsPageTranslation::class)
            ->findTranslations($cmsPage);
    }

    public function getAlias(): string
    {
        return 'cms';
    }
}
