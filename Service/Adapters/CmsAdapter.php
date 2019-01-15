<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service\Adapters;

use Nfq\CmsPageBundle\Entity\CmsPage;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CmsAdapter
 * @package Nfq\CmsPageBundle\Service\Adapters
 */
class CmsAdapter extends AbstractAdapter
{
    public const TYPE = 'cms';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function modifyForm(FormBuilderInterface $builder): void
    {
        parent::modifyForm($builder);

        $builder->remove('text_simple');
    }

    public function getEntity(): CmsPage
    {
        $entity = new CmsPage();

        $entity->setIsPublic($this->isPublic());
        $entity->setContentType(self::TYPE);

        return $entity;
    }
}
