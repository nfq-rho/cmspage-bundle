<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Form;

use Nfq\AdminBundle\Form\TranslatableType;
use Nfq\AdminBundle\PlaceManager\Form\PlaceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CmsPageType
 * @package Nfq\CmsPageBundle\Form
 */
class CmsPageType extends TranslatableType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function callBuildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', 'checkbox', [
                'required' => false
            ])
            ->add('identifier')
            ->add('name')
            ->add('metaTitle', 'text', [
                'required' => false,
            ])
            ->add('metaDescription', 'textarea', [
                'required' => false,
            ])
            ->add('slug', 'text', [
                'required' => false,
            ])
            ->add('file')
            ->add('imageAlt', 'text', [
                'required' => false
            ])
            ->add('extra', new CmsPageExtraType(), [
                'mapped' => false,
                'label' => false,
                'allow_extra_fields' => true,
            ])
            ->add('text_mce', 'textarea', [
                'label' => false,
                'property_path' => 'text',
                'attr' => ['class' => 'tinymce']
            ])
            ->add('text_simple', 'textarea', [
                'label' => false, 'property_path' => 'text'
            ]);

            if (!empty($options['places'])) {
                $builder->add('places_config', new PlaceType(), [
                    'virtual' => true,
                    'places' => $options['places'],
                    'label' => false
                ]);
            }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function callSetDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['places'])
            ->setAllowedTypes(['places' => 'array'])
            ->setDefaults([
                'data_class' => 'Nfq\\CmsPageBundle\\Entity\\CmsPage'
            ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmspage';
    }
}
