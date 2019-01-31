<?php declare(strict_types=1);

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
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\AdminBundle\ImageUpload\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Class CmsPageType
 * @package Nfq\CmsPageBundle\Form
 */
class CmsPageType extends TranslatableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('isActive', CheckboxType::class, [
                'required' => false
            ])
            ->add('identifier', TextType::class)
            ->add('title', TextType::class)
            ->add('metaTitle', TextType::class, [
                'required' => false,
            ])
            ->add('metaDescription', TextareaType::class, [
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'required' => false,
            ])
            ->add('extra', CmsPageExtraType::class, [
                'label' => false,
                'allow_extra_fields' => true,
            ])
            ->add('text_mce', TextareaType::class, [
                'label' => false,
                'property_path' => 'text',
                'attr' => ['class' => 'tinymce']
            ])
            ->add('text_simple', TextareaType::class, [
                'label' => false,
                'property_path' => 'text'
            ])
            ->add('sortPosition', IntegerType::class, [
                'required' => false
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
                'image_uri' => true,
            ])
            ->add('imageAltText', TextType::class, [
                'required' => false,
                'property_path' => 'image.altText',
                'attr' => [
                    'placeholder' => 'admin.cms.labels.image.alt_text',
                ]
            ]);

        if (!empty($options['places'])) {
            $builder->add('places_config', PlaceType::class, [
                'inherit_data' => true,
                'places' => $options['places'],
                'label' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['places'])
            ->setAllowedTypes('places', 'array')
            ->setDefaults([
                'data_class' => CmsPage::class,
            ]);
    }
}
