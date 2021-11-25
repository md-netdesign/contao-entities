<?php

namespace MdNetdesign\ContaoEntities\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitySelectType extends AbstractType
{

  public function __construct(
    private EntityManagerInterface $entityManager
  ) {
  }

  public function buildForm(FormBuilderInterface $builder, array $options) {
    ["entities" => $entities] = $options;

    $classMetadata = $this->entityManager->getClassMetadata(get_class(reset($entities)));

    foreach ($entities as $entity)
      $builder->add("entity".($entityId = array_values($classMetadata->getIdentifierValues($entity))[0]), CheckboxType::class, ["label" => false, "required" => false, "property_path" => "[$entityId]", "attr" => ["class" => "tl_tree_checkbox"]]);
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefault("entities", null);
    $resolver->setAllowedTypes("entities", "array");
  }

}