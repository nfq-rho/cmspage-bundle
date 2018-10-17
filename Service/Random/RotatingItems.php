<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service\Random;

use Doctrine\Common\Persistence\ObjectRepository;
use Nfq\CmsPageBundle\Repository\CmsPageRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Service\Admin\CmsUploadManager;

/**
 * Class RotatingItems
 * @package Nfq\CmsPageBundle\Service\Random
 */
abstract class RotatingItems
{
    /**
     * @var CmsUploadManager
     */
    protected $uploadManager;

    /**
     * @var CmsPageRepository
     */
    protected $repository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param CmsUploadManager $uploadManager
     * @param ObjectRepository $repository
     * @param Session $session
     */
    public function __construct(CmsUploadManager $uploadManager, CmsPageRepository $repository, Session $session)
    {
        $this->uploadManager = $uploadManager;
        $this->repository = $repository;
        $this->session = $session;
    }

    /**
     * @param string $locale
     * @return CmsPage|null
     */
    public function getRandomizedItem($locale)
    {
        $page = null;

        $alreadyDisplayed = $this->getAlreadyDisplayed();
        $items = $this->getItems($locale, $alreadyDisplayed);

        // Maybe there were no items because all items were already displayed at least once
        if (empty($items)) {
            $alreadyDisplayed = [];
            $this->setAlreadyDisplayed($alreadyDisplayed);
            $items = $this->getItems($locale);
        }

        if (!empty($items)) {
            $pageId = array_rand($items);
            $page = $items[$pageId];
            $alreadyDisplayed[] = $page->getId();
            $this->setAlreadyDisplayed($alreadyDisplayed);
        }

        return $page;
    }

    /**
     * @return string
     */
    abstract protected function getPageType();

    /**
     * @return string
     */
    abstract protected function getSessionVar();

    /**
     * @param string $locale
     * @param array $alreadyDisplayed
     * @return CmsPage[]
     */
    protected function getItems($locale, array $alreadyDisplayed = [])
    {
        $qb = $this->repository->getQueryBuilder();

        $this->repository->addArrayCriteria($qb, [
            'contentType' => $this->getPageType(),
            'isActive' => true,
        ]);

        if (!empty($alreadyDisplayed)) {
            $qb->andWhere('cms.id NOT IN (:alreadyDisplayed)');
            $qb->setParameter('alreadyDisplayed', $alreadyDisplayed);
        }

        $query = $qb->getQuery();
        $this->repository->setTranslatableHints($query, $locale, false);

        return $query->getResult();
    }

    /**
     * @param array $alreadyDisplayed
     */
    protected function setAlreadyDisplayed(array $alreadyDisplayed)
    {
        $sessionVar = $this->getSessionVar();

        $this->session->set($sessionVar, $alreadyDisplayed);
    }

    /**
     * @return array
     */
    private function getAlreadyDisplayed()
    {
        $sessionVar = $this->getSessionVar();

        return $this->session->get($sessionVar, []);
    }
}
