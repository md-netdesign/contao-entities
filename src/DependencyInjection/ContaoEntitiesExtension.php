<?php

namespace MdNetdesign\ContaoEntities\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoEntitiesExtension extends Extension
{

  public function load(array $configs, ContainerBuilder $container) {
    (new YamlFileLoader($container, new FileLocator(__DIR__."/../Resources/config")))
      ->load("services.yaml");
  }

}