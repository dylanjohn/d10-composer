<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Url;

/**
 * Produces a field instance from an entity.
 *
 * Can be used instead of the property path when information about the field
 * item must be queryable. The property_path resolver always returns an array
 * which sometimes causes information loss.
 *
 * @DataProducer(
 *   id = "field_link",
 *   name = @Translation("Field Link"),
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
class FieldLink extends FieldProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {
    if (!$item->uri) {
      return;
    }

    $context = new RenderContext();
    return $this->renderer->executeInRenderContext($context, function () use ($item): array {
      return [
        'uri' => $item->uri,
        'link' => $item->uri ? Url::fromUri($item->uri)->toString() : NULL,
        'title' => $item->title,
      ];
    });
  }

}
