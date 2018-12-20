<?php declare(strict_types=1);

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
use Nfq\CmsPageBundle\Service\CmsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CmsPageController
 * @package Nfq\CmsPageBundle\Controller
 */
class CmsPageController extends AbstractController
{
    /** @var string */
    protected $defaultTemplate = '@NfqCmsPage/cms_page/view.html.twig';

    /** @var CmsManager */
    private $cmsManager;

    public function __construct(CmsManager $cmsManager)
    {
        $this->cmsManager = $cmsManager;
    }

    public function viewAction(Request $request, string $slug): Response
    {
        try {
            $entity = $this->cmsManager->getCmsPage($slug);

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
            throw $this->createNotFoundException($e->getMessage(), $e);
        }
    }

    protected function isSubRequest(): bool
    {
        return null !== $this->get('request_stack')->getParentRequest();
    }

    /**
     * This method can be used in order to append more parameters to response.
     */
    protected function appendResponseParameters(Request $request, CmsPage $entity, array &$responseParams): void
    {
    }

    protected function resolvePageTemplate(CmsPage $entity): string
    {
        $customTemplate = sprintf('@NfqCmsPage/cms_page/_custom:%s.html.twig', $entity->getIdentifier());
        $finalTemplate = $this->defaultTemplate;

        if ($this->l->exists($customTemplate)) {
            $finalTemplate = $customTemplate;
        }

        return $finalTemplate;
    }
}
