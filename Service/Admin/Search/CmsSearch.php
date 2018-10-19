<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Service\Admin\Search;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Nfq\AdminBundle\Service\Generic\Search\GenericSearch;
use Nfq\AdminBundle\Service\Generic\Search\GenericSearchInterface;

/**
 * Class CmsSearch
 * @package Nfq\CmsPageBundle\Service\Admin\Search
 */
class CmsSearch extends GenericSearch implements GenericSearchInterface
{
    protected function extendQuery(Request $request, QueryBuilder $queryBuilder): void
    {
        if ($name = $request->query->get('search')) {
            $queryBuilder->andWhere('search.title LIKE :title');
            $queryBuilder->setParameter('title', '%' . $name . '%');
        }

        if ($contentType = $request->query->get('content_type')) {
            $queryBuilder->andWhere('search.contentType = :cType');
            $queryBuilder->setParameter('cType', $contentType);
        }

        $active = $request->query->get('active');
        if (!is_null($active) && (int)$active > -1) {
            $queryBuilder->andWhere('search.isActive = :active');
            $queryBuilder->setParameter('active', $active);
        }
    }

    public function getRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository('NfqCmsPageBundle:CmsPage');
    }
}
