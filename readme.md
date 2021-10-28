# Contao Entities

> Mapped doctrine entities inside the Contao backend.

## Config

You'll have to add the following minimal configuration after installing this bundle to use
it.

### `config/config.yml`

```yaml
doctrine:
  orm:
    auto_generate_proxy_classes: '%kernel.debug%'
    entity_managers:
      default:
        mappings:
          App\Entity:
            type: attribute
            dir: '%kernel.project_dir%/src/Entity'
            is_bundle: false
            prefix: App\Entity
            alias: App
  dbal:
    types:
      contao_file: MdNetdesign\ContaoEntities\Data\Entity\Type\ContaoFileType
```

```yaml
twig:
  globals:
    contaoEntitiesFormTheme: 'form/custom.html.twig'
```

### `config/routes.yaml`

```yaml
contao_entities.controller:
  resource: '@ContaoEntities/Resources/config/routes.yaml'
```