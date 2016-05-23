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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Interface CmsPageAdapterInterface
 * @package Nfq\CmsPageBundle\Service\Adapters
 */
interface CmsPageAdapterInterface extends ContainerAwareInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return Form
     */
    public function getFormType();

    /**
     * @param FormBuilderInterface $builder
     * @return void
     */
    public function modifyForm(FormBuilderInterface $builder);

    /**
     * @return Form
     */
    public function getFormTypeInstance();

    /**
     * @return CmsPage
     */
    public function getEntity();

    /**
     * @return CmsPage
     */
    public function getEntityInstance();
}
