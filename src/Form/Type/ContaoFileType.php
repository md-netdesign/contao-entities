<?php

namespace MdNetdesign\ContaoEntities\Form\Type;

use Contao\FilesModel;
use Contao\StringUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContaoFileType extends AbstractType
{

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefault("extensions", "jpg,jpeg,png,svg,webp");
    $resolver->setAllowedTypes("extensions", "string");
  }

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder->addModelTransformer(new CallbackTransformer(function (FilesModel|null $value) use ($options) {
      if ($value === null)
        return null;

      $options["file"] = $value;

      return StringUtil::binToUuid($value->uuid);
    }, function ($value) {
      if ($value === null)
        return null;

      return FilesModel::findByUuid(StringUtil::uuidToBin($value));
    }));
  }

  public function buildView(FormView $view, FormInterface $form, array $options) {
    parent::buildView($view, $form, $options);
    $view->vars["extensions"] = $options["extensions"];
    $view->vars["file"] = FilesModel::findByUuid(StringUtil::uuidToBin($view->vars["value"]));
  }

  public function getParent(): string {
    return TextType::class;
  }

  public function getBlockPrefix(): string {
    return "contao_file";
  }

}