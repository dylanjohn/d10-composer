<?php

namespace Drupal\graphql_compose\GraphQL;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use function Symfony\Component\String\u;

/**
 * Provides a way for schemas to add type resolvers in a standardised format.
 */
trait StandardisedEntityQueryTypeFieldsSchemaTrait {

  /**
   * Registers type and field resolvers in the shared registry.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   * @param array $definitions
   *   DataManager definitions for entity type.
   */
  protected function addQueryTypeFields(ResolverRegistryInterface $registry, ResolverBuilder $builder, array $definitions = []) {

    foreach ($definitions as $definition) {
      foreach ($definition['fields'] as $field) {
        $builders = [];
        foreach ($field['producers'] as $producer) {
          if ($producer['type'] === 'dataProducer') {
            $customBuilder = $builder->produce($producer['id']);
            foreach ($producer['map'] as $map) {
              if ($map['id'] === 'fromParent') {
                $customBuilder->map($map['key'], $builder->fromParent());
              }
              if ($map['id'] === 'fromValue') {
                $mapValue = u($map['value'])->replace('{field_name}', $field['name'])->toString();
                $customBuilder->map($map['key'], $builder->fromValue($mapValue));
              }
            }
            $builders[] = $customBuilder;
          }
          if ($producer['type'] === 'fromPath') {
            $argsPath = u($producer['args']['path'])->replace('{field_name}', $field['name'])->toString();
            $builders[] = $builder->fromPath($producer['args']['type'], $argsPath);
          }
        }

        $registry->addFieldResolver(
          $definition['type_sdl'],
          $field['name_sdl'],
          $builder->compose(
            ...array_values($builders)
          )
        );
      }
    }
  }

}
