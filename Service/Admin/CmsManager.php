<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service\Admin;

use Nfq\CmsPageBundle\CmsPageEvents;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\AdminBundle\Service\Admin\AbstractAdminManager;
use Nfq\CmsPageBundle\Repository\CmsPageRepository;

/**
 * Class CmsManager
 * @package Nfq\CmsPageBundle\Service\Admin
 */
class CmsManager extends AbstractAdminManager
{
    public function __construct(CmsPageRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @param $entity
     * @param string $beforeEventName
     * @param string $afterEventName
     * @return mixed
     */
    public function delete(
        $entity,
        $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_DELETE,
        $afterEventName = CmsPageEvents::CMSPAGE_AFTER_DELETE
    ) {
        return parent::delete($entity, $beforeEventName, $afterEventName);
    }

    /**
     * @param $entity
     * @param string $beforeEventName
     * @param string $afterEventName
     * @return mixed
     */
    public function insert(
        $entity,
        $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_INSERT,
        $afterEventName = CmsPageEvents::CMSPAGE_AFTER_INSERT
    ) {
        return parent::insert($entity, $beforeEventName, $afterEventName);
    }

    /**
     * @param $entity
     * @param string $beforeEventName
     * @param string $afterEventName
     * @return mixed
     */
    public function save(
        $entity,
        $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_SAVE,
        $afterEventName = CmsPageEvents::CMSPAGE_AFTER_SAVE
    ) {
        return parent::save($entity, $beforeEventName, $afterEventName);
    }

    public function getEditableEntity($id, ?string $locale): CmsPage
    {
        return $this->repository->getEditableEntity($id, $locale);
    }
}
