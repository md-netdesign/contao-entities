<?php

namespace MdNetdesign\ContaoEntities\Data\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

interface FilterRepository
{

  public function applyFilter(QueryBuilder $queryBuilder, array $filter, string $root, string $locale): void;

}