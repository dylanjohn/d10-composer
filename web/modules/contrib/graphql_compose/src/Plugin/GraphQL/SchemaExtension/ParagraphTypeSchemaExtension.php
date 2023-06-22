<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeUnionsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntitySchemaTrait;

/**
 * Adds Node data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "paragraph_schema_extension",
 *   name = "Paragraph Schema Extension",
 *   description = "Paragraph GraphQL Schema Extension.",
 *   schema = "graphql_compose"
 * )
 */
class ParagraphTypeSchemaExtension extends SchemaExtensionPluginBase {

  use StandardisedEntitySchemaTrait;
  use StandardisedEntityQueryFieldsSchemaTrait;
  use StandardisedEntityQueryTypeFieldsSchemaTrait;
  use StandardisedEntityQueryTypeUnionsSchemaTrait;

  /**
   * Base entity type for resolution.
   *
   * @var string
   */
  protected $baseEntityType = 'paragraph';

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    if (!$this->moduleHandler->moduleExists('paragraphs')) {
      return;
    }

    $definitions = $this->getDefinitions($this->getBaseEntityType());

    $this->addQueryFields($registry, $builder, $definitions);
    $this->addQueryTypeFields($registry, $builder, $definitions);
    $this->addQueryTypeUnions($registry, $builder, $definitions);
  }

}
