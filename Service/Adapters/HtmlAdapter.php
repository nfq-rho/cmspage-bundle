<?php

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
use Nfq\CmsPageBundle\Form\CmsPageType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CmsAdapter
 * @package Nfq\CmsPageBundle\Service\Adapters
 */
class HtmlAdapter extends AbstractAdapter
{
    /**
     * @cons string
     */
    const TYPE = 'html';

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return new CmsPageType();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyForm(FormBuilderInterface $builder)
    {
        parent::modifyForm($builder);

        $builder->remove('metaTitle');
        $builder->remove('metaDescription');
        $builder->remove('text_simple');
        $builder->remove('places_config');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        $entity = new CmsPage();

        $entity->setIsPublic($this->getIsPublic());
        $entity->setContentType(self::TYPE);

        return $entity;
    }
}
