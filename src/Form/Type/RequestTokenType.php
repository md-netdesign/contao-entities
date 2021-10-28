<?php

namespace MdNetdesign\ContaoEntities\Form\Type;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestTokenType extends AbstractType
{

  public const NAME = "REQUEST_TOKEN";

  public function __construct(
    private ContaoCsrfTokenManager $contaoCsrfTokenManager,
    private ContainerInterface     $container
  ) {
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefault("mapped", false);
  }

  public function buildView(FormView $view, FormInterface $form, array $options) {
    parent::buildView($view, $form, $options);
    $view->vars["full_name"] = self::NAME;
    $view->vars["value"] = $this->contaoCsrfTokenManager->getToken($this->container->getParameter("contao.csrf_token_name"))->getValue();
  }

  public function getParent(): string {
    return HiddenType::class;
  }


}