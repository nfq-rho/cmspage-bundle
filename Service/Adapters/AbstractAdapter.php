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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractAdapter
 * @package Nfq\CmsPageBundle\Service\Adapters
 */
abstract class AbstractAdapter implements CmsPageAdapterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var CmsPage
     */
    protected $entity;

    /**
     * @var CmsPageType
     */
    protected $formType;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return bool
     */
    protected function getIsPublic()
    {
        return $this->options['public'];
    }

    /**
     * @return bool
     */
    protected function hasFeaturedImage()
    {
        return $this->options['has_featured_image'];
    }

    /**
     * @param FormBuilderInterface $builder
     */
    public function modifyForm(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();

            if (!$this->hasFeaturedImage()) {
                $form->remove('file');
                $form->remove('imageAlt');
            }

            if (!$this->getIsPublic()) {
                $form->remove('slug');
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTypeInstance()
    {
        is_null($this->formType) && $this->formType = $this->getFormType();
        return $this->formType;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityInstance()
    {
        is_null($this->entity) && $this->entity = $this->getEntity();
        return $this->entity;
    }
}
