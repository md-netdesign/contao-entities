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

    $resolver->setDefault("filesOnly", true);
    $resolver->setAllowedTypes("filesOnly", "bool");

    $resolver->setDefault("filesOnly", true);
    $resolver->setAllowedTypes("filesOnly", "bool");

    $resolver->setDefault("fieldType", "radio");
    $resolver->setAllowedValues("fieldType", ["radio", "checkbox"]);
  }

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder->addModelTransformer(new CallbackTransformer(function (FilesModel|array|null $value) use ($options) {
      if ($value === null)
        return null;

      if (is_array($value))
        return join(",", array_map(fn($entry) => StringUtil::binToUuid($entry->uuid), $value));

      return StringUtil::binToUuid($value->uuid);
    }, fn($value) => $this->reverseTransform($value, $options["fieldType"] === "checkbox")));
  }

  private function reverseTransform($value, bool $multiple): FilesModel|array|null {
    if ($value === null)
      return null;

    $value = explode(",", $value);

    if (($sizeOfValue = sizeof($value)) === 0)
      return null;

    if ($sizeOfValue > 1 || $multiple)
      return array_map(fn($entry) => FilesModel::findByUuid(StringUtil::uuidToBin($entry)), $value);

    return FilesModel::findByUuid(StringUtil::uuidToBin(reset($value)));
  }

  public function buildView(FormView $view, FormInterface $form, array $options) {
    parent::buildView($view, $form, $options);
    $view->vars["extensions"] = $options["extensions"];
    $view->vars["filesOnly"] = $options["filesOnly"];
    $view->vars["fieldType"] = $options["fieldType"];
    $view->vars["files"] = $files = $this->reverseTransform($view->vars["value"], $options["fieldType"] === "checkbox");
    $view->vars["uuids"] = $files === null ? [] : array_values(array_map(fn($file) => StringUtil::binToUuid($file->uuid), is_array($files) ? $files : [$files]));
  }

  public function getParent(): string {
    return TextType::class;
  }

  public function getBlockPrefix(): string {
    return "contao_file";
  }

}