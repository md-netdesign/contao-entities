<?php

namespace MdNetdesign\ContaoEntities\Data\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

interface FilterableRepository extends FilterRepository
{

  public function getFilterType(): string;

}