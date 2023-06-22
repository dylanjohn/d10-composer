<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeUnionsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntitySchemaTrait;

/**
 * Adds Taxonomy data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "taxonomy_schema_extension",
 *   name = "Taxonomy Schema Extension",
 *   description = "Taxonomy GraphQL Schema Extension.",
 *   schema = "graphql_compose"
 * )
 */
class TaxonomySchemaExtension extends SchemaExtensionPluginBase {

  use StandardisedEntitySchemaTrait;
  use StandardisedEntityQueryFieldsSchemaTrait;
  use StandardisedEntityQueryTypeFieldsSchemaTrait;
  use StandardisedEntityQueryTypeUnionsSchemaTrait;

  /**
   * Base entity type for resolution.
   *
   * @var string
   */
  protected $baseEntityType = 'taxonomy_term';

  /**
   * Replace the plural query producer.
   *
   * @var string
   */
  protected $queryPluralProducer = 'query_taxonomy_vocabulary';

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    if (!$this->moduleHandler->moduleExists('taxonomy_term')) {
      return;
    }

    $definitions = $this->getDefinitions($this->getBaseEntityType());

    $this->addQueryFields($registry, $builder, $definitions);
    $this->addQueryTypeFields($registry, $builder, $definitions);
    $this->addQueryTypeUnions($registry, $builder, $definitions);
  }

}
