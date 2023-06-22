<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Returns an file style derivative of an file.
 *
 * @DataProducer(
 *   id = "field_file",
 *   name = @Translation("File"),
 *   description = @Translation("Returns file and derivatives."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("File properties")
 *   ),
 *   consumes = {
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("Field list instance"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class FieldFile extends FieldProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {
    if (!$item->entity) {
      return;
    }

    $access = $item->entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);

    if ($access->isAllowed()) {
      return [
        'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($item->entity->getFileUri()),
        'name' => $item->entity->getFilename(),
        'size' => (int) $item->entity->getSize(),
        'mime' => $item->entity->getMimeType(),
      ];
    }
  }

}
