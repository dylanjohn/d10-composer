<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\text\Plugin\Field\FieldType\TextWithSummaryItem;

/**
 * Produces a field instance from an entity.
 *
 * Can be used instead of the property path when information about the field
 * item must be queryable. The property_path resolver always returns an array
 * which sometimes causes information loss.
 *
 * @DataProducer(
 *   id = "field_text",
 *   name = @Translation("Field Text"),
 *   description = @Translation("Selects a field from an entity."),
 *   produces = @ContextDefinition("mixed",
 *     label = @Translation("Field")
 *   ),
 *   consumes = {
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("Field list instance"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class FieldText extends FieldProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {

    $result = [
      'format' => $item->format,
      'value' => $item->value,
      'processed' => $item->processed,
    ];

    if ($item instanceof TextWithSummaryItem) {
      $result['summary'] = $item->summary;
    }

    return $result;
  }

}
