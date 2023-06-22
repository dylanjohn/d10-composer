<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\image\Entity\ImageStyle;

/**
 * Returns an image style derivative of an image.
 *
 * @DataProducer(
 *   id = "field_image",
 *   name = @Translation("Image"),
 *   description = @Translation("Returns image and derivatives."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Image properties")
 *   ),
 *   consumes = {
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("Field list instance"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class FieldImage extends FieldProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {

    // Return if we dont have an entity.
    if (!$item->entity) {
      return;
    }

    $access = $item->entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed()) {
      $width = $item->entity->width;
      $height = $item->entity->height;

      // @todo Not sure why PHPStan complains here, this should be refactored to
      // check the entity properties first.
      // @phpstan-ignore-next-line
      if (empty($width) || empty($height)) {
        /** @var \Drupal\Core\Image\ImageInterface $image */
        $image = \Drupal::service('image.factory')->get($item->entity->getFileUri());
        if ($image->isValid()) {
          $width = $image->getWidth();
          $height = $image->getHeight();
        }
      }

      $styles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple();
      $derivatives = [];
      foreach ($styles as $key => $style) {
        $image_style = ImageStyle::load($key);
        // @todo Not sure why PHPStan complains here, this should be refactored to
        // check the entity properties first.
        // @phpstan-ignore-next-line
        if (empty($width) || empty($height)) {
          /** @var \Drupal\Core\Image\ImageInterface $image */
          $image = \Drupal::service('image.factory')->get($item->entity->getFileUri());
          if ($image->isValid()) {
            $width = $image->getWidth();
            $height = $image->getHeight();
          }
        }

        // Determine the dimensions of the styled image.
        $dimensions = [
          'width' => $width,
          'height' => $height,
        ];

        $image_style->transformDimensions($dimensions, $item->entity->getFileUri());
        $metadata->addCacheableDependency($image_style);

        // The underlying URL generator that will be invoked will leak cache
        // metadata, resulting in an exception. By wrapping within a new render
        // context, we can capture the leaked metadata and make sure it gets
        // incorporated into the response.
        $context = new RenderContext();
        $url = $this->renderer->executeInRenderContext($context, function () use ($image_style, $item) {
          return $image_style->buildUrl($item->entity->getFileUri());
        });

        if (!$context->isEmpty()) {
          $metadata->addCacheableDependency($context->pop());
        }

        $derivatives[] = [
          'style' => $key,
          'url' => $url,
          'width' => $dimensions['width'],
          'height' => $dimensions['height'],
        ];
      }

      return [
        'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($item->entity->getFileUri()),
        'width' => $width,
        'height' => $height,
        'styles' => $derivatives,
      ];
    }
  }

}
