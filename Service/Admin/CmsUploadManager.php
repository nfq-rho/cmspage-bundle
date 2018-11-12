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

use Nfq\CmsPageBundle\Entity\CmsPage;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CmsUploadManager
 * @package Nfq\CmsPageBundle\Service\Admin
 */
class CmsUploadManager
{
    /** @var array */
    private $config;

    public function __construct(array $bundleConfig)
    {
        $this->resolveConfig($bundleConfig);
    }

    private function resolveConfig(array $config): void
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['upload_absolute', 'upload_relative']);

        $this->config = $resolver->resolve($config);
    }

    public function upload(CmsPage $entity): void
    {
        if (null === $entity->getFile()) {
            return;
        }

        $uploadDir = $this->getUploadPath($entity->getId());

        $entity->getFile()->move($uploadDir, $entity->getImage());
    }

    public function removeFile(int $id, string $tempImage, string $locale): void
    {
        //Do not delete temp image if locales are different
        if (strpos($tempImage, '_' . crc32($locale)) === false) {
            return;
        }

        $file = $this->getUploadPath($id, $tempImage);

        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function removeFiles($entityId): void
    {
        $filesDir = $this->getUploadPath($entityId);

        if (!file_exists($filesDir)) {
            return;
        }

        if (!is_dir($filesDir)) {
            $it = new \DirectoryIterator($filesDir);
            foreach ($it as $file) {
                if ($file->isDot()) {
                    continue;
                }

                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            rmdir($filesDir);
        }
    }

    /**
     * @param CmsPage|array $entity
     */
    public function getWebPathForEntity($entity): string
    {
        if ($entity instanceof CmsPage && $entity->getImage()) {
            return $this->getWebPath($entity->getId(), $entity->getImage()) ;
        }

        if (\is_array($entity)) {
            return $this->getWebPath($entity['id'], $entity['image']);
        }

        return '';
    }

    private function getWebPath($id, string $image = ''): string
    {
        return DIRECTORY_SEPARATOR . $this->config['upload_relative'] . md5((string)$id) . DIRECTORY_SEPARATOR . $image;
    }
    
    private function getUploadPath($id, string $image = ''): string
    {
        return $this->config['upload_absolute'] . md5((string)$id) . DIRECTORY_SEPARATOR . $image;
    }
}
