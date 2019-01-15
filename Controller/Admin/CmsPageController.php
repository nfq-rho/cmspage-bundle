<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Controller\Admin;

use Nfq\AdminBundle\Controller\Traits\ListControllerTrait;
use Nfq\AdminBundle\Controller\Traits\TranslatableCrudControllerTrait;
use Nfq\AdminBundle\PlaceManager\PlaceManagerInterface;
use Nfq\AdminBundle\Service\FormManager;
use Nfq\CmsPageBundle\Entity\CmsPage;
use Nfq\CmsPageBundle\Service\Adapters\CmsPageAdapterInterface;
use Nfq\CmsPageBundle\Service\Admin\CmsManager;
use Nfq\CmsPageBundle\Service\Admin\CmsSearch;
use Nfq\CmsPageBundle\Service\CmsManager as CmsManagerFront;
use Nfq\CmsPageBundle\Service\CmsTypeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CmsPageController
 * @package Nfq\CmsPageBundle\Controller\Admin
 */
class CmsPageController extends AbstractController
{
    use TranslatableCrudControllerTrait {
        newAction as traitNewAction;
        createAction as traitCreateAction;
        updateAction as traitUpdateAction;
    }
    use ListControllerTrait {
        indexAction as traitIndexAction;
    }

    /** @var CmsPageAdapterInterface */
    private $adapter;

    /** @var CmsManager */
    private $manager;

    /** @var CmsSearch */
    private $search;

    /** @var CmsManagerFront */
    private $frontManager;

    /** @var CmsTypeManager */
    private $typeManager;

    /** @var PlaceManagerInterface */
    private $placeManager;

    public function __construct(
        CmsManager $manager,
        CmsSearch $search,
        CmsManagerFront $frontManager,
        CmsTypeManager $typeManager,
        PlaceManagerInterface $placeManager
    ) {
        $this->manager = $manager;
        $this->search = $search;
        $this->frontManager = $frontManager;
        $this->typeManager = $typeManager;
        $this->placeManager = $placeManager;
    }

    /**
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $response = $this->traitIndexAction($request);

        return $response + [
                'contentTypes' => $this->getTypeManager()->getTypes()
            ];
    }

    private function getTypeManager(): CmsTypeManager
    {
        return $this->typeManager;
    }

    /**
     * @Template()
     */
    public function newAction(Request $request): array
    {
        $this->setAdapter($request);

        return $this->traitNewAction();
    }

    private function setAdapter(Request $request): void
    {
        if (null === $this->adapter) {
            $this->adapter = $this->getTypeManager()->getAdapterFromRequest($request);
        }
    }

    /**
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $this->setAdapter($request);

        return $this->traitCreateAction($request);
    }

    /**
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $this->setAdapter($request);

        return $this->traitUpdateAction($request, $id);
    }

    protected function getEntityForLocale($id, string $locale = null): ?CmsPage
    {
        return $this->manager->getEntity($id, $locale);
    }

    protected function getIndexActionResults(Request $request)
    {
        return $this->search->getResults($request);
    }

    protected function redirectToPreview(CmsPage $entity): RedirectResponse
    {
        $params = $this->frontManager->getCmsUrlParams($entity->getIdentifier(), $entity->getLocale());

        $params['_locale'] = $entity->getLocale();

        $url = $this->generateUrl('nfq_cmspage_view', $params);

        return new RedirectResponse($url);
    }

    protected function redirectToIndex(Request $request, CmsPage $entity = null): RedirectResponse
    {
        $redirectParams = $this->getRedirectToIndexParams($request, $entity);

        return $this->redirect($this->generateUrl('nfq_cmspage_list', $redirectParams->all()));
    }

    protected function getCreateFormAndEntity(string $locale): array
    {
        $formType = \get_class($this->adapter->getFormTypeInstance());
        $entity = $this->adapter->getEntityInstance();
        $entity->setLocale($locale);

        $uri = $this->generateUrl('nfq_cmspage_create', ['_type' => $this->adapter::getType()]);

        $formOptions = [
            'locale' => $locale,
            'places' => $this->placeManager->getPlaceChoices(),
        ];

        $submit = $entity->isPublic()
            ? FormManager::SUBMIT_STANDARD | FormManager::SUBMIT_CLOSE | FormManager::SUBMIT_PREVIEW
            : FormManager::SUBMIT_STANDARD | FormManager::SUBMIT_CLOSE;

        $formBuilder = $this
            ->getFormManager()
            ->getFormBuilder($uri, FormManager::CRUD_CREATE, $formType, $entity, $formOptions, $submit);

        $this->adapter->modifyForm($formBuilder);

        return [$entity, $formBuilder->getForm()];
    }

    protected function getEditDeleteForms(CmsPage $entity): array
    {
        $formType = \get_class($this->adapter->getFormTypeInstance());

        $id = $entity->getId();

        $formOptions = [
            'locale' => $entity->getLocale(),
            'places' => $this->placeManager->getPlaceChoices(),
        ];

        $uri = $this->generateUrl('nfq_cmspage_update', ['id' => $id, '_type' => $this->adapter::getType()]);

        $submit = $entity->isPublic()
            ? FormManager::SUBMIT_STANDARD | FormManager::SUBMIT_CLOSE | FormManager::SUBMIT_PREVIEW
            : FormManager::SUBMIT_STANDARD | FormManager::SUBMIT_CLOSE;

        $formBuilder = $this
            ->getFormManager()
            ->getFormBuilder($uri, FormManager::CRUD_UPDATE, $formType, $entity, $formOptions, $submit);

        $this->adapter->modifyForm($formBuilder);

        $deleteForm = $this->getDeleteForm($entity);

        return [$formBuilder->getForm(), $deleteForm];
    }

    protected function getDeleteForm($entity): FormInterface
    {
        $uri = $this->generateUrl('nfq_cmspage_delete', ['id' => $entity->getId()]);

        return $this->getFormManager()->getDeleteForm($uri);
    }

    protected function insertAfterCreateAction(CmsPage $entity): void
    {
        $this->manager->insert($entity);
    }

    protected function deleteAfterDeleteAction(CmsPage $entity): void
    {
        $this->manager->delete($entity);
    }

    protected function saveAfterUpdateAction(CmsPage $entity): void
    {
        $this->manager->save($entity);
    }
}
