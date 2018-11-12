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
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Interface CmsPageAdapterInterface
 * @package Nfq\CmsPageBundle\Service\Adapters
 */
interface CmsPageAdapterInterface
{
    public static function getType(): string;

    public function getFormType(): CmsPageType;

    public function modifyForm(FormBuilderInterface $builder): void;

    public function getFormTypeInstance(): CmsPageType;

    public function getEntity(): CmsPage;

    public function getEntityInstance(): CmsPage;
}
