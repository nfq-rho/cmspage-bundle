<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service\Admin;

use Nfq\CmsPageBundle\Event\CmsPageEvents;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\AdminBundle\Service\Admin\AbstractAdminManager;
use Nfq\CmsPageBundle\Repository\CmsPageRepository;

/**
 * Class CmsManager
 * @package Nfq\CmsPageBundle\Service\Admin
 * @property CmsPageRepository $repository
 */
class CmsManager extends AbstractAdminManager
{
    public function __construct(CmsPageRepository $repository)
    {
        parent::__construct($repository);
    }

    public function delete(
        $entity,
        string $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_DELETE,
        string $afterEventName = CmsPageEvents::CMSPAGE_AFTER_DELETE
    ) {
        return parent::delete($entity, $beforeEventName, $afterEventName);
    }

    public function insert(
        $entity,
        string $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_INSERT,
        string $afterEventName = CmsPageEvents::CMSPAGE_AFTER_INSERT
    ) {
        return parent::insert($entity, $beforeEventName, $afterEventName);
    }

    public function save(
        $entity,
        string $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_SAVE,
        string $afterEventName = CmsPageEvents::CMSPAGE_AFTER_SAVE
    ) {
        return parent::save($entity, $beforeEventName, $afterEventName);
    }

    public function getEditableEntity($id, ?string $locale): CmsPage
    {
        return $this->repository->getEditableEntity($id, $locale);
    }
}
