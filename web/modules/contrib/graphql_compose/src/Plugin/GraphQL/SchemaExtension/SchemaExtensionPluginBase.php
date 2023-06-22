<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql_compose\DataManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_compose\GraphQL\StandardisedMutationSchemaTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class that can be used for GraphQL Compose schema extension plugins.
 */
abstract class SchemaExtensionPluginBase extends SdlSchemaExtensionPluginBase implements ContainerFactoryPluginInterface {

  use StandardisedMutationSchemaTrait;

  /**
   * GraphQL Compose Data Manager.
   *
   * @var \Drupal\graphql_compose\DataManager
   */
  protected $dataManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('graphql_compose.datamanager')
    );
  }

  /**
   * SdlSchemaExtensionPluginBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\graphql_compose\DataManager $dataManager
   *   GraphQL Compose Data Manager.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    DataManager $dataManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler);
    $this->dataManager = $dataManager;
  }

  /**
   * Return the GraphQL Compose data manager.
   *
   * @return \Drupal\graphql_compose\DataManager
   *   GraphQL Compose Data Manager.
   */
  public function getDataManager() {
    return $this->dataManager;
  }

  /**
   * Get Data Manager definitions for base type.
   *
   * @param string $type
   *   Entity type to getch definition for, Eg node.
   *
   * @return array
   *   Definitions from the data manager.
   */
  protected function getDefinitions($type) {
    return $this->getDataManager()->getDefinitions($type);
  }

  /**
   * Register the resolvers in a standardised way.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   GraphQL Registry.
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
  }

}
