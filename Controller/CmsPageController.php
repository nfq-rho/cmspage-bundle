<?php

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Controller;

use Nfq\CmsPageBundle\Entity\CmsPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CmsPageController
 * @package Nfq\CmsPageBundle\Controller
 */
class CmsPageController extends Controller
{
    /**
     * @var string
     */
    protected $defaultTemplate = 'NfqCmsPageBundle:CmsPage:view.html.twig';

    /**
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function viewAction(Request $request, $slug)
    {
        try {
            $entity = $this->getCmsPageManager()->getCmsPage($slug);

            $responseParams = [
                'entity' => clone $entity,
                'isModal' => $this->isSubRequest(),
            ];

            $this->appendResponseParameters($request, $entity, $responseParams);

            return $this->render(
                $this->resolvePageTemplate($entity),
                $responseParams
                );
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }

    /**
     * Returns true if this is a sub-request
     * @return bool
     */
    protected function isSubRequest()
    {
        return null !== $this->get('request_stack')->getParentRequest();
    }

    /**
     * This method can be used in order to append more parameters to response.
     *
     * @param Request $request
     * @param CmsPage $entity
     * @param array $responseParams
     */
    protected function appendResponseParameters(Request $request, $entity, array &$responseParams)
    {
    }

    /**
     * @return \Nfq\CmsPageBundle\Service\CmsManager
     */
    protected function getCmsPageManager()
    {
        return $this->get('nfq_cmspage.cms_manager');
    }

    /**
     * @param CmsPage $entity
     * @return string
     */
    protected function resolvePageTemplate(CmsPage $entity)
    {
        $twigTemplateLoader = $this->get('twig.loader');

        $customTemplate = sprintf('NfqCmsPageBundle:CmsPage/_custom:%s.html.twig', $entity->getIdentifier());
        $finalTemplate = $this->defaultTemplate;

        if ($twigTemplateLoader->exists($customTemplate)) {
            $finalTemplate = $customTemplate;
        }

        return $finalTemplate;
    }
}
