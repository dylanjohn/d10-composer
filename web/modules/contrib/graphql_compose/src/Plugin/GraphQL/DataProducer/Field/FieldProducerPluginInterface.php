<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Field producer interface.
 */
interface FieldProducerPluginInterface {

  /**
   * Resolve a field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   Field value to process.
   * @param array $context
   *   Contextual consumes passed to the parent resolve().
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Cache helper.
   *
   * @return mixed|void
   *   Result to pass to producer base.
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata);

}
