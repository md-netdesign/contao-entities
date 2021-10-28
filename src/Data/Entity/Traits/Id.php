<?php

namespace MdNetdesign\ContaoEntities\Data\Entity\Traits;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

trait Id
{

  #[Column(type: "integer")]
  #[\Doctrine\ORM\Mapping\Id]
  #[GeneratedValue(strategy: "AUTO")]
  private ?int $id = null;

  public function getId(): ?int {
    return $this->id;
  }

}