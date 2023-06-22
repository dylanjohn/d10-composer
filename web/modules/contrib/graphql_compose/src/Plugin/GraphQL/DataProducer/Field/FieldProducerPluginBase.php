<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns an file style derivative of an file.
 */
abstract class FieldProducerPluginBase extends DataProducerPluginBase implements FieldProducerPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('renderer'),
      $container->get('module_handler'),
    );
  }

  /**
   * ImageDerivative constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    RendererInterface $renderer,
    ModuleHandlerInterface $moduleHandler
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Resolve file field items.
   *
   * @param mixed $consumes
   *   Consumption options passed to the field.
   *
   * @return mixed
   *   Results from resolution. Array for multiple.
   */
  public function resolve(...$consumes) {
    $context = $this->getContextValues();
    $field = $context['field'] ?? NULL;

    if (!$field || !$field instanceof FieldItemListInterface) {
      return NULL;
    }

    $metadata = array_filter($consumes, function ($item) {
      return $item instanceof CacheableDependencyInterface;
    });

    $metadata = reset($metadata);

    $results = [];
    foreach ($field as $item) {
      if (!$item) {
        continue;
      }

      $result = $this->resolveFieldItem($item, $context, $metadata);

      $this->moduleHandler->invokeAll('graphql_compose_field_producer_alter', [
        $item,
        $context,
        $metadata,
        &$result,
      ]);

      if ($result) {
        $results[] = $result;
      }
    }

    if (empty($results)) {
      return NULL;
    }

    return $field
      ->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->isMultiple()
        ? $results
        : reset($results);
  }

}
