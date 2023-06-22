<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Return only a prop from a Field.
 *
 * Can be used instead of the property path to return
 * fields as singuilar value or multiple values.
 *
 * @DataProducer(
 *   id = "field_property",
 *   name = @Translation("Field Properties"),
 *   description = @Translation("Selects props from a field."),
 *   produces = @ContextDefinition("mixed",
 *     label = @Translation("Field")
 *   ),
 *   consumes = {
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("FieldItemListInterface")
 *     ),
 *     "value" = @ContextDefinition("string",
 *       label = @Translation("Property value to fetch"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class FieldProperty extends FieldProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {
    return $item->{$context['value']};
  }

}
