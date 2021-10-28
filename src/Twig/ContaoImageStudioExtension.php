<?php

namespace MdNetdesign\ContaoEntities\Twig;

use Contao\CoreBundle\File\Metadata;
use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FilesModel;
use JetBrains\PhpStorm\Pure;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContaoImageStudioExtension extends AbstractExtension
{

  public function __construct(
    private Studio $imageStudio
  ) {
  }

  public function getFunctions(): iterable {
    yield new TwigFunction("createContaoImageStudioFigureBuilder", [$this, "createContaoImageStudioFigureBuilder"]);
    yield new TwigFunction("createContaoImageStudioFigureBuilderByUuid", [$this, "createContaoImageStudioFigureBuilderByUuid"]);
    yield new TwigFunction("createContaoImageStudioFigureBuilderByPath", [$this, "createContaoImageStudioFigureBuilderByPath"]);
    yield new TwigFunction("createContaoImageStudioMetadata", [$this, "createContaoImageStudioMetadata"]);
    yield new TwigFunction("createContaoImageStudioMetadataAlt", [$this, "createContaoImageStudioMetadataAlt"]);
    yield new TwigFunction("createContaoImageStudioMetadataAltCaption", [$this, "createContaoImageStudioMetadataAltCaption"]);
  }

  public function createContaoImageStudioFigureBuilderByUuid(string $uuid): FigureBuilder {
    return $this->imageStudio->createFigureBuilder()
      ->fromUuid($uuid);
  }

  public function createContaoImageStudioFigureBuilderByPath(string $path): FigureBuilder {
    return $this->imageStudio->createFigureBuilder()
      ->fromPath($path);
  }

  public function createContaoImageStudioFigureBuilder(FilesModel $file): FigureBuilder {
    return $this->imageStudio->createFigureBuilder()
      ->fromFilesModel($file);
  }

  #[Pure] public function createContaoImageStudioMetadata(array $values): Metadata {
    return new Metadata($values);
  }

  #[Pure] public function createContaoImageStudioMetadataAlt(string $alt): Metadata {
    return new Metadata([Metadata::VALUE_ALT => $alt]);
  }

  #[Pure] public function createContaoImageStudioMetadataAltCaption(string $alt, string $caption = null): Metadata {
    return new Metadata([Metadata::VALUE_ALT => $alt, Metadata::VALUE_CAPTION => $caption ?? $alt]);
  }

}