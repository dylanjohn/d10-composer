<?php

namespace Drupal\graphql_compose\GraphQL;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * Provides a way for schemas to add type unions in a standardised format.
 */
trait StandardisedEntityQueryTypeUnionsSchemaTrait {

  /**
   * Get known entity schema types for unions.
   *
   * @return array
   *   All union entity types available to GraphQL.
   */
  protected function getDefaultUnionTypes() {
    $typeDefinitions = \Drupal::entityTypeManager()->getDefinitions();
    $schemaDefinitions = \Drupal::service('plugin.manager.graphql.schema_extension')->getDefinitions();

    $unions = [];
    foreach ($schemaDefinitions as $schemaDefinition) {
      $schemaReflection = new \ReflectionClass($schemaDefinition['class']);
      $schemaProperties = $schemaReflection->getDefaultProperties();
      $schemaBaseEntityType = $schemaProperties['baseEntityType'] ?? NULL;

      if (array_key_exists($schemaBaseEntityType, $typeDefinitions)) {
        $unions[$schemaBaseEntityType] = $typeDefinitions[$schemaBaseEntityType]->getClass();
      }
    }

    return $unions;
  }

  /**
   * Get the types of unions to resolve for this entity.
   *
   * @return array
   *   Array of unions to use.
   */
  protected function getQueryTypeUnionTypes() {
    return array_merge($this->getDefaultUnionTypes(), ($this->queryTypeUnionTypes ?? []));
  }

  /**
   * Registers type and field resolvers in the shared registry.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   * @param array $definitions
   *   DataManager definitions for entity type.
   *
   * @throws \Exception
   */
  protected function addQueryTypeUnions(ResolverRegistryInterface $registry, ResolverBuilder $builder, array $definitions = []) {

    $unionTypes = $this->getQueryTypeUnionTypes();
    if (empty($unionTypes)) {
      return;
    }

    foreach ($definitions as $definition) {
      foreach ($definition['unions'] as $unionType => $union) {
        $mapping = $union['mapping'];

        if (!$mappingType = $unionTypes[$union['type']] ?? NULL) {
          throw new \Exception('Unknown entity union type: ' . $union['type']);
        }

        $registry->addTypeResolver(
          $unionType,
          function ($value) use ($mappingType, $mapping) {
            if ($value instanceof $mappingType) {
              return $mapping[$value->bundle()];
            }

            throw new \Exception('Union for ' . $value->bundle() . 'not found.');
          }
        );
      }
    }
  }

}
