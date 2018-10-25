<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Service\Admin\CmsUploadManager;

/**
 * Class FileUploadListener
 * @package Nfq\CmsPageBundle\EventListener\Doctrine
 */
class FileUploadListener
{
    /** @var CmsUploadManager */
    private $uploadManager;

    /** @var int */
    private $entityId;

    public function __construct(CmsUploadManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    public function postPersist(CmsPage $entity, LifecycleEventArgs $event): void
    {
        $this->uploadManager->upload($entity);
    }

    public function postUpdate(CmsPage $entity, LifecycleEventArgs $event): void
    {
        $this->uploadManager->upload($entity);
    }

    public function preFlush(CmsPage $entity, PreFlushEventArgs $event): void
    {
        $this->setImage($entity);
        $this->removeExistingImage($entity);
    }

    public function preRemove(CmsPage $entity, LifecycleEventArgs $event): void
    {
        $this->entityId = $entity->getId();
    }

    public function postRemove(CmsPage $entity, LifecycleEventArgs $event): void
    {
        $this->uploadManager->removeFiles($this->entityId);
    }

    private function setImage(CmsPage $entity): void
    {
        if (null !== $uploadedFile = $entity->getFile()) {
            $filename = sha1(uniqid((string)mt_rand(), true)) . '_' . crc32($entity->getLocale());
            $entity->setImage($filename . '.' . $uploadedFile->guessExtension());
        }
    }

    private function removeExistingImage(CmsPage $entity): void
    {
        if (null !== $entity->getTempFile()) {
            $this->uploadManager->removeFile($entity->getId(), $entity->getTempFile(), $entity->getLocale());
            $entity->resetTempFile();
        }
    }
}
