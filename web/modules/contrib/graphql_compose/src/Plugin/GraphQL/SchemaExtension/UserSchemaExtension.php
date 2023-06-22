<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntityQueryTypeFieldsSchemaTrait;
use Drupal\graphql_compose\GraphQL\StandardisedEntitySchemaTrait;
use Drupal\graphql_compose\GraphQL\UserActorTypeResolver;

/**
 * Adds user data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "user_schema_extension",
 *   name = "GraphQL Compose - User Schema Extension",
 *   description = "GraphQL schema extension for GraphQL Compose user data.",
 *   schema = "graphql_compose"
 * )
 */
class UserSchemaExtension extends SchemaExtensionPluginBase {

  use StandardisedEntitySchemaTrait;
  use StandardisedEntityQueryFieldsSchemaTrait;
  use StandardisedEntityQueryTypeFieldsSchemaTrait;

  /**
   * Base entity type for resolution.
   *
   * @var string
   */
  protected $baseEntityType = 'user';

  /**
   * Replace the plural query producer.
   *
   * @var string
   */
  protected $queryPluralProducer = 'query_user';

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $definitions = $this->getDefinitions($this->getBaseEntityType());

    $this->addQueryFields($registry, $builder, $definitions);
    $this->addQueryTypeFields($registry, $builder, $definitions);

    // Type resolvers.
    $registry->addTypeResolver('Actor', new UserActorTypeResolver($registry->getTypeResolver('Actor')));

    // Root Query fields.
    $registry->addFieldResolver('Query', 'viewer',
      $builder->produce('viewer')
    );
  }

}
