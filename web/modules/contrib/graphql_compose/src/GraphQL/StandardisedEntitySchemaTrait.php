<?php

namespace Drupal\graphql_compose\GraphQL;

use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * Provides a way for schemas to add resolvers in a standardised format.
 */
trait StandardisedEntitySchemaTrait {

  /**
   * Return the GraphQL Compose data manager.
   *
   * @return \Drupal\graphql_compose\DataManager
   *   GraphQL Data Manager.
   */
  abstract protected function getDataManager();

  /**
   * Base entity type for resolution.
   *
   * @return string
   *   Base type id. Eg node, media.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getBaseEntityType() {
    if ($this->baseEntityType) {
      return $this->baseEntityType;
    }

    throw new MissingDataException('baseEntityType is not set');
  }

  /**
   * Loads a schema definition file.
   *
   * @param string $type
   *   The type of the definition file to load.
   *
   * @return string|null
   *   The definition based on Drupal ParagraphTypes or NULL if it was empty.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function loadDefinitionFile($type) {
    return $this->getDataManager()->getSdlByStorage($this->getBaseEntityType(), $type);
  }

}
