<?php

namespace MdNetdesign\ContaoEntities\Data\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

class QueryHelper
{

  public static function search(QueryBuilder $queryBuilder, array $properties, string $query): void {
    $words = array_filter(array_map(fn(string $word) => trim($word), explode(" ", $query)), fn(string $word) => $word !== "");

    $queryParts = [];
    $index = 0;
    $base = "w".substr(md5(random_bytes(8)), 0, 6);
    foreach ($words as $word) {
      $index++;
      $queryBuilder->setParameter("$base$index", "%$word%");
      $propertyParts = [];
      foreach ($properties as $property)
        $propertyParts[] = "$property like :$base$index";
      $queryParts[] = "(".join(" or ", $propertyParts).")";
    }

    $queryBuilder->andWhere("(".join(" and ", $queryParts).")");
  }

}