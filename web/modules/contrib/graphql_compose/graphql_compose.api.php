<?php

/**
 * @file
 * Hooks provided by GraphQL Compose module.
 */

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Alter the result from language singularize.
 *
 * @param string $original
 *   Original string to be converted.
 * @param array $singular
 *   Result from the language interface.
 */
function hook_graphql_compose_singularize_alter($original, array &$singular): void {
  if (preg_match('/media$/i', $original)) {
    $singular = [$original];
  }
}

/**
 * Alter the result from language pluralize.
 *
 * @param string $original
 *   Original string to be converted.
 * @param array $plural
 *   Result from the language interface.
 */
function hook_graphql_compose_pluralize_alter($original, array &$plural): void {
  if (preg_match('/media$/i', $original)) {
    $plural = [$original . 'Items'];
  }
}

/**
 * Alter results for producers which extend FieldProducerPluginBase.
 *
 * @param \Drupal\Core\Field\FieldItemInterface $item
 *   Field item being processed.
 * @param array $context
 *   Context Passed to resolver. Eg $context['field'].
 * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
 *   Context for metadata expansion.
 * @param mixed $result
 *   The result being returned.
 */
function hook_graphql_compose_field_producer_alter(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata, &$result) {
  $field = $context['field'];
  if ($field->getName() === 'field_banana' && $item->value === 'A') {
    $result['something'] = 'here';
  }
}
