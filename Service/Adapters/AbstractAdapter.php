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
use Nfq\CmsPageBundle\Form\CmsPageType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AbstractAdapter
 * @package Nfq\CmsPageBundle\Service\Adapters
 */
abstract class AbstractAdapter implements CmsPageAdapterInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var array */
    protected $options;

    /** @var CmsPage */
    protected $entity;

    /** @var CmsPageType */
    protected $formType;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    protected function getIsPublic(): bool
    {
        return $this->options['public'];
    }

    protected function hasFeaturedImage(): bool
    {
        return $this->options['has_featured_image'];
    }

    public function modifyForm(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
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

    public function getFormType(): CmsPageType
    {
        return new CmsPageType();
    }

    public function getFormTypeInstance(): CmsPageType
    {
        null === $this->formType && $this->formType = $this->getFormType();
        return $this->formType;
    }

    public function getEntityInstance(): CmsPage
    {
        null === $this->entity && $this->entity = $this->getEntity();
        return $this->entity;
    }
}
