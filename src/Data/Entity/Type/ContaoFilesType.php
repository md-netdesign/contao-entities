<?php

namespace MdNetdesign\ContaoEntities\Data\Entity\Type;

use Contao\FilesModel;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ContaoFilesType extends Type
{

  const NAME = "contao_files";

  public function getSQLDeclaration(array $column, AbstractPlatform $platform): string {
    return $platform->getBlobTypeDeclarationSQL($column);
  }

  public function convertToPHPValue($value, AbstractPlatform $platform) {
    if ($value === null)
      return null;

    if (str_contains($value, "\t"))
      return array_values(array_map(fn($entry) => FilesModel::findByUuid($entry), explode("\t", $value)));

    return array_values(array_map(fn($entry) => FilesModel::findByUuid(hex2bin($entry)), explode(":", $value)));
  }

  public function convertToDatabaseValue($value, AbstractPlatform $platform) {
    if (!is_array($value))
      return null;

    return join(":", array_map(fn($entry) => bin2hex($entry->uuid), $value));
  }

  public function requiresSQLCommentHint(AbstractPlatform $platform): bool {
    return true;
  }

  public function getName(): string {
    return self::NAME;
  }

}