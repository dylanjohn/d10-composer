<?php

namespace Drupal\graphql_compose\GraphQL;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * Provides a way for schemas to add query resolvers in a standardised format.
 */
trait StandardisedEntityQueryFieldsSchemaTrait {

  /**
   * Base entity type for this entity.
   *
   * @see StandardisedEntitySchemaTrait::getBaseEntityType()
   */
  abstract protected function getBaseEntityType();

  /**
   * Enabled single resolution query for type. Eg nodePage()
   *
   * @param mixed $definition
   *   Data Manager type definition being processed.
   *
   * @return bool
   *   If the query is enabled.
   */
  protected function isQuerySingleEnabled($definition = NULL) {
    return $this->querySingleEnabled ?? TRUE;
  }

  /**
   * Query producer for single.
   *
   * @param mixed $definition
   *   Data Manager type definition being processed.
   *
   * @return string
   *   Name of the producer.
   */
  protected function getQuerySingleProducer($definition = NULL) {
    return $this->querySingleProducer ?? 'entity_load_by_uuid';
  }

  /**
   * Enabled plural resolution query for type. Eg nodePages()
   *
   * @param mixed $definition
   *   Data Manager type definition being processed.
   *
   * @return bool
   *   If the query is enabled.
   */
  protected function isQueryPluralEnabled($definition = NULL) {
    return $this->queryPluralEnabled ?? TRUE;
  }

  /**
   * Query producer for plural.
   *
   * @param mixed $definition
   *   Data Manager type definition being processed.
   *
   * @return string
   *   Name of the producer.
   */
  protected function getQueryPluralProducer($definition = NULL) {
    return $this->queryPluralProducer ?? 'query_' . $this->getBaseEntityType() . '_type';
  }

  /**
   * Registers type and field resolvers in the query type.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   * @param array $definitions
   *   DataManager definitions for entity type.
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder, array $definitions = []) {

    $baseEntityType = $this->getBaseEntityType();

    foreach ($definitions as $definition) {
      if ($this->isQuerySingleEnabled($definition)) {
        $producer = $this->getQuerySingleProducer($definition);

        $registry->addFieldResolver(
          'Query',
          $definition['type'],
          $builder->produce($producer)
            ->map('type', $builder->fromValue($baseEntityType))
            ->map('bundles', $builder->fromValue([$definition['id']]))
            ->map('uuid', $builder->fromArgument('id'))
        );
      }

      if ($this->isQueryPluralEnabled($definition)) {
        $producer = $this->getQueryPluralProducer($definition);

        $registry->addFieldResolver(
          'Query',
          $definition['type_plural'],
          $builder->produce($producer . ':' . $definition['id'])
            ->map('after', $builder->fromArgument('after'))
            ->map('before', $builder->fromArgument('before'))
            ->map('first', $builder->fromArgument('first'))
            ->map('last', $builder->fromArgument('last'))
            ->map('reverse', $builder->fromArgument('reverse'))
            ->map('sortKey', $builder->fromArgument('sortKey'))
        );
      }
    }
  }

}
