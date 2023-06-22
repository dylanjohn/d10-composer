<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\node\NodeInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeUnionsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntitySchemaTrait;

use function Symfony\Component\String\u;

/**
 * Adds Node data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "node_schema_extension",
 *   name = "Node Schema Extension",
 *   description = "Node GraphQL Schema Extension.",
 *   schema = "graphql_compose"
 * )
 */
class ContentTypeSchemaExtension extends SchemaExtensionPluginBase {

  use StandardisedEntitySchemaTrait;
  use StandardisedEntityQueryFieldsSchemaTrait;
  use StandardisedEntityQueryTypeFieldsSchemaTrait;
  use StandardisedEntityQueryTypeUnionsSchemaTrait;

  /**
   * Base entity type for resolution.
   *
   * @var string
   */
  protected $baseEntityType = 'node';

  /**
   * Replace the plural query producer.
   *
   * @var string
   */
  protected $queryPluralProducer = 'query_content_type';

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $definitions = $this->getDefinitions($this->getBaseEntityType());

    $this->addQueryFields($registry, $builder, $definitions);
    $this->addQueryTypeFields($registry, $builder, $definitions);
    $this->addQueryTypeUnions($registry, $builder, $definitions);

    $registry->addTypeResolver('NodeContentUnion', function ($value) {
      if ($value instanceof NodeInterface) {
        return u($value->bundle())->title()->prepend('Node')->toString();
      }

      throw new \Error('Could not resolve content type.');
    });

    $registry->addFieldResolver(
      'Query',
      'nodeByPath',
      $builder->compose(
        $builder->produce('route_load')
          ->map('path', $builder->fromArgument('path')),
        $builder->produce('route_entity')
          ->map('url', $builder->fromParent())
      )
    );
  }

}
