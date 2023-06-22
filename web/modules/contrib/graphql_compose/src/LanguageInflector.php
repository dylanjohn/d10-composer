<?php

namespace Drupal\graphql_compose;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\String\Inflector\InflectorInterface;

class LanguageInflector {

  /**
   * Drupal module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Inflector interface.
   *
   * @var \Symfony\Component\String\Inflector\InflectorInterface
   */
  protected InflectorInterface $inflector;

  public function __construct(ModuleHandlerInterface $moduleHandler, InflectorInterface $inflector)
  {
    $this->moduleHandler = $moduleHandler;
    $this->inflector = $inflector;
  }

  /**
   * Returns the singular forms of a string.
   *
   * If the method can't determine the form with certainty, several possible singulars are returned.
   *
   * @return string[]
   */
  public function singularize(string $original): array {
    $singular = $this->inflector->singularize($original);
    $this->moduleHandler->invokeAll('graphql_compose_singularize_alter', [$original, &$singular]);
    return $singular;
  }

  /**
   * Returns the plural forms of a string.
   *
   * If the method can't determine the form with certainty, several possible plurals are returned.
   *
   * @return string[]
   */
  public function pluralize(string $original): array
  {
    $plural = $this->inflector->pluralize($original);
    $this->moduleHandler->invokeAll('graphql_compose_pluralize_alter', [$original, &$plural]);
    return $plural;
  }

}