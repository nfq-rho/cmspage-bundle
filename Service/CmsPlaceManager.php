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

use Doctrine\ORM\EntityManagerInterface;
use Nfq\AdminBundle\PlaceManager\PlaceManager;
use Nfq\AdminBundle\PlaceManager\Repository\PlaceAwareRepositoryInterface;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Repository\CmsPageRepository;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CmsPlaceManager
 * @package Nfq\CmsPageBundle\Service
 */
class CmsPlaceManager extends PlaceManager
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function formatPlaceChoice(array &$item, string $key): void
    {
        $item = sprintf(
            '%s (%d/%d)',
            $this->translator->trans($item['title']),
            $this->getUsedPlaceSlots($key),
            $item['limit']
        );
    }

    /**
     * @return CmsPage[]
     */
    public function getItemsInPlace(string $placeId, string $locale, string $sortOrder = 'ASC'): array
    {
        $criteria = [
            'places' => '%' . $placeId . '%',
            'isActive' => true,
        ];

        $query = $this->getPlaceAwareRepository()
            ->getTranslatableQueryByCriteriaSorted(
                $criteria,
                $locale,
                true,
                'sortPosition',
                $sortOrder
            );

        $query
            ->expireQueryCache(true)
            ->expireResultCache(true)
            ->setMaxResults($this->getPlaceLimit($placeId));

        return $query->getResult();
    }

    /**
     * Get cms pages in given place sorted by sort position.
     *
     * @return CmsPage[]
     */
    public function getItemsInPlaceSorted(string $placeId, string $locale, string $sortOrder): array
    {
        $criteria = [
            'places' => '%' . $placeId . '%',
            'isActive' => true,
        ];

        $query = $this->getPlaceAwareRepository()
            ->getTranslatableQueryByCriteriaSorted($criteria, $locale, true, $sortOrder);

        $query
            ->expireQueryCache(true)
            ->expireResultCache(true)
            ->setMaxResults($this->getPlaceLimit($placeId));

        return $query->getResult();
    }

    /**
     * @return CmsPageRepository
     */
    protected function getPlaceAwareRepository(): PlaceAwareRepositoryInterface
    {
        return $this->em->getRepository('NfqCmsPageBundle:CmsPage');
    }
}
