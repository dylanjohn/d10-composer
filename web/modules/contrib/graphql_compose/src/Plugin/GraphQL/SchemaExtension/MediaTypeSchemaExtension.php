<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntitySchemaTrait;

/**
 * Adds Node data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "media_schema_extension",
 *   name = "Media Schema Extension",
 *   description = "Media GraphQL Schema Extension.",
 *   schema = "graphql_compose"
 * )
 */
class MediaTypeSchemaExtension extends SchemaExtensionPluginBase {

  use StandardisedEntitySchemaTrait;
  use StandardisedEntityQueryFieldsSchemaTrait;
  use StandardisedEntityQueryTypeFieldsSchemaTrait;

  /**
   * Base entity type for resolution.
   *
   * @var string
   */
  protected $baseEntityType = 'media';


  /**
   * {@inheritDoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    if (!$this->moduleHandler->moduleExists('media')) {
      return;
    }

    $definitions = $this->getDefinitions($this->getBaseEntityType());

    $this->addQueryFields($registry, $builder, $definitions);
    $this->addQueryTypeFields($registry, $builder, $definitions);
  }

}
