<?php

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
    /**
     * @var CmsUploadManager
     */
    private $uploadManager;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @param CmsUploadManager $uploadManager
     */
    public function __construct(CmsUploadManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    /**
     * @param CmsPage $entity
     * @param LifecycleEventArgs $event
     */
    public function postPersist(CmsPage $entity, LifecycleEventArgs $event)
    {
        $this->uploadManager->upload($entity);
    }

    /**
     * @param CmsPage $entity
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(CmsPage $entity, LifecycleEventArgs $event)
    {
        $this->uploadManager->upload($entity);
    }

    /**
     * @param CmsPage $entity
     * @param PreFlushEventArgs $event
     */
    public function preFlush(CmsPage $entity, PreFlushEventArgs $event)
    {
        $this->setImage($entity);
        $this->removeExistingImage($entity);
    }

    /**
     * @param CmsPage $entity
     * @param LifecycleEventArgs $event
     */
    public function preRemove(CmsPage $entity, LifecycleEventArgs $event)
    {
        $this->entityId = $entity->getId();
    }

    /**
     * @param CmsPage $entity
     * @param LifecycleEventArgs $event
     */
    public function postRemove(CmsPage $entity, LifecycleEventArgs $event)
    {
        $this->uploadManager->removeFiles($this->entityId);
    }

    /**
     * @param CmsPage $entity
     */
    private function setImage(CmsPage $entity)
    {
        if (null !== $entity->getFile()) {
            $filename = sha1(uniqid(mt_rand(), true)) . '_' . crc32($entity->getLocale());
            $entity->setImage($filename . '.' . $entity->getFile()->guessExtension());
        }
    }

    /**
     * @param CmsPage $entity
     */
    private function removeExistingImage(CmsPage $entity)
    {
        if (null !== $entity->getTempFile()) {
            $this->uploadManager->removeFile($entity->getId(), $entity->getTempFile(), $entity->getLocale());
            $entity->resetTempFile();
        }
    }
}
