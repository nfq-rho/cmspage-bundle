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

use Doctrine\ORM\EntityManagerInterface;
use Nfq\AdminBundle\PlaceManager\PlaceManagerInterface;
use Nfq\AdminBundle\PlaceManager\PlaceManager;
use Nfq\CmsPageBundle\Entity\CmsPageRepository;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CmsPlaceManager
 * @package Nfq\CmsPageBundle\Service
 */
class CmsPlaceManager extends PlaceManager implements PlaceManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function formatPlaceChoice(&$item, $key)
    {
        $item = sprintf('%s (%d/%d)',
            $this->translator->trans($item['title']),
            $this->getUsedPlaceSlots($key),
            $item['limit']);
    }

    /**
     * Get categories in given place
     *
     * @param string $placeId
     * @param string $locale
     * @return array
     */
    public function getItemsInPlace($placeId, $locale)
    {
        $criteria = [
            'places' => '%' . $placeId . '%',
            'isActive' => true,
        ];

        $orderBy = [];

        /** @var CmsPageRepository $repo */
        $repo = $this->getPlaceAwareRepository();
        $query = $repo->getTranslatableQueryByCriteria($criteria, $locale);

        $query
            ->expireQueryCache(true)
            ->expireResultCache(true)
            ->setMaxResults($this->getPlaceLimit($placeId));

        return $query->getResult();
    }

    /**
     * Get cms pages in given place sorted by sort position.
     *
     * @param string $placeId
     * @param string $locale
     * @param string $sortOrder
     * @return array
     */
    public function getItemsInPlaceSorted($placeId, $locale, $sortOrder)
    {
        $criteria = [
            'places' => '%' . $placeId . '%',
            'isActive' => true,
        ];

        /** @var CmsPageRepository $repo */
        $repo = $this->getPlaceAwareRepository();
        $query = $repo->getTranslatableQueryByCriteriaSorted($criteria, $locale, $sortOrder);

        $query
            ->expireQueryCache(true)
            ->expireResultCache(true)
            ->setMaxResults($this->getPlaceLimit($placeId));

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPlaceAwareRepository()
    {
        return $this->em->getRepository('NfqCmsPageBundle:CmsPage');
    }
}
