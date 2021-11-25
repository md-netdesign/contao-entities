<?php

namespace MdNetdesign\ContaoEntities\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\FilesModel;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/file-helper", name: "file-helper", defaults: ["_scope" => "backend"])]
class FileHelperController extends AbstractController
{

  #[Route("/update", name: "-update", methods: ["GET"])]
  public function update(Request $request): Response {
    $query = $request->query;
    $path = $query->get("path");
    $id = $query->get("id");

    $files = array_values(array_filter(array_map(fn($entry) => FilesModel::findByPath($entry), explode("\t", $path))));

    return $this->render("@ContaoEntities/file-helper/update.html.twig", [
      "id" => $id,
      "uuids" => array_values(array_map(fn($file) => StringUtil::binToUuid($file->uuid), $files)),
      "files" => $files
    ]);
  }

}