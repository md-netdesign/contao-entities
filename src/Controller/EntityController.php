<?php

namespace MdNetdesign\ContaoEntities\Controller;

use Contao\BackendUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MdNetdesign\ContaoEntities\Data\Entity\Repository\FilterableRepository;
use MdNetdesign\ContaoEntities\Data\Entity\Repository\FilterRepository;
use MdNetdesign\ContaoEntities\Data\Form\Group;
use MdNetdesign\ContaoEntities\Form\Type\RequestTokenType;
use MdNetdesign\Form\AutoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class EntityController extends AbstractController
{

  protected EntityRepository $entityRepository;
  protected ?string $module = null;
  protected string $baseRoute;
  protected string $title;
  protected ?string $instanceTitle = null;
  protected string $dataClass;
  protected int $entitiesPerPage = 100;
  protected string $listTemplate = "@ContaoEntities/default.html.twig";
  protected bool $createDisabled = false;
  protected bool $editDisabled = false;
  protected bool $duplicateDisabled = false;
  protected bool $deleteDisabled = false;

  public function setEntityRepository(EntityRepository $entityRepository): EntityController {
    $this->entityRepository = $entityRepository;
    $this->dataClass = $this->entityRepository->getClassName();
    return $this;
  }

  public function setModule(?string $module): EntityController {
    $this->module = $module;
    return $this;
  }

  public function setBaseRoute(string $baseRoute): EntityController {
    $this->baseRoute = $baseRoute;
    return $this;
  }

  public function setTitle(string $title): EntityController {
    $this->title = $title;
    return $this;
  }

  public function setInstanceTitle(string $instanceTitle): EntityController {
    $this->instanceTitle = $instanceTitle;
    return $this;
  }

  public function setDataClass(string $dataClass): EntityController {
    $this->dataClass = $dataClass;
    return $this;
  }

  public function setEntitiesPerPage(int $entitiesPerPage): EntityController {
    $this->entitiesPerPage = $entitiesPerPage;
    return $this;
  }

  public function setListTemplate(string $listTemplate): EntityController {
    $this->listTemplate = $listTemplate;
    return $this;
  }

  public function disableCreate(): EntityController {
    $this->createDisabled = true;
    return $this;
  }

  public function disableEdit(): EntityController {
    $this->editDisabled = true;
    return $this;
  }

  public function disableDuplicate(): EntityController {
    $this->duplicateDisabled = true;
    return $this;
  }

  public function disableDelete(): EntityController {
    $this->deleteDisabled = true;
    return $this;
  }

  #[Route("/list/{!page}", name: "-list", requirements: ["page" => "\d+|\*"], defaults: ["page" => 1], methods: ["GET", "POST"])]
  public function list(Request $request, Security $security, FormFactoryInterface $formFactory, int $page): Response {
    $this->checkAuthentication($security);

    if ($page < 0)
      return $this->redirectToRoute($this->baseRoute."-list");

    $queryBuilder = $this->entityRepository->createQueryBuilder("entity");

    $locale = str_starts_with($request->getLocale(), "en") ? "en" : "de";
    $root = $queryBuilder->getRootAliases()[0];

    if ($this->entityRepository instanceof FilterableRepository) {
      if ($request->request->get("filter-reset") === "reset")
        return $this->redirectToRoute($this->baseRoute."-list");

      $filterForm = $formFactory->createNamed("filter", $this->entityRepository->getFilterType(), null, [
        "action" => $this->generateUrl($this->baseRoute."-list")])
        ->add(RequestTokenType::NAME, RequestTokenType::class);

      $filterForm->handleRequest($request);
      $this->entityRepository->applyFilter($queryBuilder, $filter = $filterForm->isSubmitted() && $filterForm->isValid() ? $filterForm->getData() : [], $root, $locale);
    } else {
      if ($this->entityRepository instanceof FilterRepository)
        $this->entityRepository->applyFilter($queryBuilder, [], $root, $locale);
      $filterForm = $filter = null;
    }

    try {
      $totalEntityCount = (clone $queryBuilder)
        ->select("count($root.id)")
        ->getQuery()->getSingleScalarResult();
    } catch (NoResultException | NonUniqueResultException) {
      $totalEntityCount = 0;
    }

    if ($page !== 0)
      $queryBuilder
        ->setMaxResults($this->entitiesPerPage)
        ->setFirstResult($this->entitiesPerPage * ($page - 1));

    $availablePages = ceil($totalEntityCount / $this->entitiesPerPage);

    return $this->renderForm("@ContaoEntities/list.html.twig", $this->getRenderParameters([
      "filterForm" => $filterForm,
      "filter" => $filter,
      "page" => $page,
      "availablePages" => $availablePages,
      "totalEntityCount" => $totalEntityCount,
      "entitiesPerPage" => $this->entitiesPerPage,
      "entities" => $queryBuilder->getQuery()->getResult(),
      "listTemplate" => $this->listTemplate]));
  }

  protected function checkAuthentication(Security $security): bool {
    /** @var BackendUser $user */
    $user = $security->getUser();

    if (!($user instanceof BackendUser))
      throw new AccessDeniedHttpException("Not authenticated in backend.");

    if ($this->module !== null)
      if (!in_array($this->module, $user->modules ?? []) && !$user->admin)
        throw new AccessDeniedHttpException("No access for module.");

    return $user->admin;
  }

  protected function getRenderParameters(array $source = []): array {
    return array_merge($source, [
      "baseRoute" => $this->baseRoute,
      "title" => $this->title,
      "instanceTitle" => $this->instanceTitle,
      "canCreate" => !$this->createDisabled,
      "canEdit" => !$this->editDisabled,
      "canDuplicate" => !$this->duplicateDisabled,
      "canDelete" => !$this->deleteDisabled]);
  }

  #[Route("/delete/{id}", name: "-delete", requirements: ["id" => "\d+"], methods: ["GET"])]
  public function delete(Security $security, EntityManagerInterface $entityManager, int $id): Response {
    $this->checkAuthentication($security);

    if (($instance = $this->entityRepository->find($id)) === null)
      throw new NotFoundHttpException();

    $entityManager->remove($instance);
    $entityManager->flush();

    return $this->redirectToRoute($this->baseRoute."-list");
  }

  #[Route("/create", name: "-create", methods: ["GET", "POST"])]
  #[Route("/edit/{id}", name: "-edit", requirements: ["id" => "\d+"], methods: ["GET", "POST"])]
  public function edit(Request $request, Security $security, FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, bool $duplicate = false, ?int $id = null): Response {
    $isUserAdmin = $this->checkAuthentication($security);

    if ($id === null)
      $instance = null;
    elseif (($instance = $this->entityRepository->find($id)) === null)
      throw new NotFoundHttpException();

    $form = $formFactory->createNamed("entity", AutoType::class, $instance, [
      "data_class" => $this->dataClass,
      "groups" => $isUserAdmin ? [Group::CONTAO, Group::CONTAO_ADMIN] : [Group::CONTAO],
      "action" => $id === null ?
        $this->generateUrl($this->baseRoute."-create") :
        $this->generateUrl($this->baseRoute."-edit", ["id" => $id])])
      ->add(RequestTokenType::NAME, RequestTokenType::class);

    $form->handleRequest($request);
    if ($form->isSubmitted())
      if ($form->isValid()) {
        $instance = $form->getData();

        $entityManager->persist($instance);
        $entityManager->flush();

        return match ($request->request->get("save")) {
          "close" => $this->redirectToRoute($this->baseRoute."-list"),
          default => $this->redirectToRoute($this->baseRoute."-edit", ["id" => $instance->getId()])
        };
      } elseif ($instance !== null && $entityManager->contains($instance))
        $entityManager->refresh($instance);


    $fieldsets = array_values(array_unique(array_map(fn(FormInterface $form) => $form->getConfig()->getOption("row_attr")["data-fieldset"] ?? null, array_filter($form->all(), fn(FormInterface $form) => $form->getName() !== "REQUEST_TOKEN" && !str_starts_with($form->getName(), "_")))));

    return $this->renderForm("@ContaoEntities/edit.html.twig", $this->getRenderParameters([
      "form" => $form,
      "entity" => $instance,
      "fieldsets" => $fieldsets
    ]));
  }

}