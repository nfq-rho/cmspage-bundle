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

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Query;
use Mapping\Fixture\Xml\Uploadable;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Entity\CmsPageRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $this->file = null;
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

    public function removeFiles($entityId)
    {
        $filesDir = $this->getUploadPath($entityId);

        if (is_dir($filesDir)) {
            $it = new \DirectoryIterator($filesDir);
            foreach($it as $file) {
                if ($file->isDot()) {
                    continue;
                }

                if ($file->isDir()){
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
        } elseif (is_array($entity)) {
            return $this->getWebPath($entity['id'], $entity['image']);
        }

        return '';
    }

    private function getWebPath($id, string $image = ''): string
    {
        $_id = md5($id);

        return DIRECTORY_SEPARATOR . $this->config['upload_relative'] . $_id . DIRECTORY_SEPARATOR . $image;
    }
    
    private function getUploadPath($id, string $image = ''): string
    {
        $_id = md5($id);

        return $this->config['upload_absolute'] . $_id . DIRECTORY_SEPARATOR . $image;
    }
}
