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

use Nfq\AdminBundle\Service\Admin\AbstractAdminManager;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Event\CmsPageEvents;
use Nfq\CmsPageBundle\Repository\CmsPageRepository;

/**
 * Class CmsManager
 * @package Nfq\CmsPageBundle\Service\Admin
 */
class CmsManager extends AbstractAdminManager
{
    public function delete(
        object $entity,
        string $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_DELETE,
        string $afterEventName = CmsPageEvents::CMSPAGE_AFTER_DELETE
    ): object {
        return parent::delete($entity, $beforeEventName, $afterEventName);
    }

    public function insert(
        object $entity,
        string $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_INSERT,
        string $afterEventName = CmsPageEvents::CMSPAGE_AFTER_INSERT
    ): object {
        return parent::insert($entity, $beforeEventName, $afterEventName);
    }

    public function save(
        object $entity,
        string $beforeEventName = CmsPageEvents::CMSPAGE_BEFORE_SAVE,
        string $afterEventName = CmsPageEvents::CMSPAGE_AFTER_SAVE
    ): object {
        return parent::save($entity, $beforeEventName, $afterEventName);
    }

    public function getEntity($id, ?string $locale = null): ?CmsPage
    {
        return $this->getRepository()->getEditableEntity($id, $locale);
    }

    public function getRepository(): CmsPageRepository
    {
        return $this->entityManager->getRepository(CmsPage::class);
    }
}
