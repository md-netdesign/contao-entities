<?php

namespace MdNetdesign\ContaoEntities\Data\Entity\Type;

use Contao\FilesModel;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ContaoFileType extends Type
{

  const NAME = "contao_file";

  public function getSQLDeclaration(array $column, AbstractPlatform $platform): string {
    $column["length"] = 16;
    return $platform->getBinaryTypeDeclarationSQL($column);
  }

  public function convertToPHPValue($value, AbstractPlatform $platform) {
    if ($value === null || ($file = FilesModel::findByUuid($value)) === null)
      return null;

    return $file;
  }

  public function convertToDatabaseValue($value, AbstractPlatform $platform) {
    if (!($value instanceof FilesModel))
      return null;

    return $value->uuid;
  }

  public function requiresSQLCommentHint(AbstractPlatform $platform): bool {
    return true;
  }

  public function getName(): string {
    return self::NAME;
  }

}