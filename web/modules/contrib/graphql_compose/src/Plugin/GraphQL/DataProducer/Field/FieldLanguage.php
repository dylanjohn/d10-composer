<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Returns the language of an entity.
 *
 * @DataProducer(
 *   id = "field_language",
 *   name = @Translation("Entity language"),
 *   description = @Translation("Returns the entity language."),
 *   produces = @ContextDefinition("language",
 *     label = @Translation("Language")
 *   ),
 *   consumes = {
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("Field list instance"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class FieldLanguage extends FieldProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {
    return [
      'id' => $item->language->getId(),
      'name' => $item->language->getName(),
      'direction' => $item->language->getDirection(),
    ];
  }

}
