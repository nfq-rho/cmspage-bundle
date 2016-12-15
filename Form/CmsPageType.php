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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('isActive', CheckboxType::class, [
                'required' => false
            ])
            ->add('identifier')
            ->add('name')
            ->add('metaTitle', TextType::class, [
                'required' => false,
            ])
            ->add('metaDescription', TextareaType::class, [
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'required' => false,
            ])
            ->add('file')
            ->add('imageAlt', TextType::class, [
                'required' => false
            ])
            ->add('extra', CmsPageExtraType::class, [
                'mapped' => false,
                'label' => false,
                'allow_extra_fields' => true,
            ])
            ->add('text_mce', TextareaType::class, [
                'label' => false,
                'property_path' => 'text',
                'attr' => ['class' => 'tinymce']
            ])
            ->add('text_simple', TextareaType::class, [
                'label' => false, 'property_path' => 'text'
            ]);

            if (!empty($options['places'])) {
                $builder->add('places_config', PlaceType::class, [
                    'inherit_data' => true,
                    'places' => $options['places'],
                    'label' => false
                ]);
            }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function callSetDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['places'])
            ->setAllowedTypes('places', 'array')
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
