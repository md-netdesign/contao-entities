<?php

namespace MdNetdesign\ContaoEntities\Twig;

use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

class ContaoFileTypeExtension extends AbstractExtension
{

  public function getFunctions(): iterable {
    yield new TwigFunction("contaoFileType_getFileTree", [$this, "getFileTree"]);
  }

  public function getTests(): iterable {
    yield new TwigTest("contaoFileType_formView", [$this, "isFormView"]);
  }

  public function isFormView(mixed $candidate): bool {
    return $candidate instanceof FormView;
  }

  public function getFileTree(array $forms): array {
    $tree = [];

    /** @var FormView $form */
    foreach ($forms as $form) {
      $parts = array_slice(explode("/", $form->vars["label"]), 1, -1);
      $node = &$tree;
      foreach ($parts as $level) {
        if (!array_key_exists($level, $node))
          $node[$level] = [];
        $node = &$node[$level];
      }

      $node[] = $form;
    }

    return $tree;
  }

}