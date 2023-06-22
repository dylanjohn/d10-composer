<?php

namespace Drupal\graphql_compose;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Template\TwigEnvironment;

use function Symfony\Component\String\u;

/**
 * GraphQL Compose Data Manager.
 */
class DataManager {

  /**
   * Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager = NULL;

  /**
   * Drupal entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager = NULL;

  /**
   * Drupal renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer = NULL;

  /**
   * Drupal twig env.
   *
   * @var Drupal\Core\Template\TwigEnvironment
   */
  protected $twig = NULL;

  /**
   * Drupal module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler = NULL;

  /**
   * Langhuage inflector service.
   *
   * @var LanguageInflector
   */
  protected $inflector = NULL;

  /**
   * Setting storage property.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * SDL Storage property.
   *
   * @var array
   */
  protected $sdl = [];

  /**
   * Definition storage property.
   *
   * @var array
   */
  protected $definitions = [];

  /**
   * Construct DataManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Drupal entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Drupal entity field manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal renderer service.
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   *   Drupal twig service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Drupal module handler service.
   * @param LanguageInflector $inflector
   *   Language inflector service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    RendererInterface $renderer,
    TwigEnvironment $twig,
    ModuleHandlerInterface $moduleHandler,
    LanguageInflector $inflector
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->renderer = $renderer;
    $this->twig = $twig;
    $this->moduleHandler = $moduleHandler;
    $this->inflector = $inflector;

    // @todo Define config schemas and GUI to allow user select which data to expose
    $this->settings = [
      'generate' => [
        'queryies' => TRUE,
        'mutations' => FALSE,
      ],
      'user' => [
        // 'User'
        'interface' => ['Node', 'Actor'],
        'prefix' => '',
        'storage_type' => 'user',
        // 'none' | 'camel' | 'snake'
        'case' => 'camel',
        'defaults' => [
          'isUnion' => FALSE,
          'isMultiple' => FALSE,
          'isRequired' => FALSE,
        ],
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'path' => [
            'type' => 'path',
          ],
          'created' => [
            'type' => 'created',
          ],
          'changed' => [
            'type' => 'changed',
          ],
          'name' => [
            'type' => 'entity_label',
            'label' => 'The display name of the user.',
            'description' => 'The specific format of the display name could depend on permissions of the requesting user or application.',
            'name_sdl' => 'displayName',
          ],
          'mail' => [
            'type' => 'email',
            'label' => 'The e-mail of the user.',
            'description' => 'Can be null if the user has not filled in an e-mail or if the user/application making the request is not allowed to view this user\'s e-mail.',
            'name_sdl' => 'mail',
          ],
          'status' => [
            'type' => 'user_status',
            'label' => 'The status of the user account.',
          ],
          'roles' => [
            'type' => 'user_roles',
            'label' => 'The roles that the user has.',
          ],

        ],
      ],
      'taxonomy_term' => [
        'interface' => ['Node'],
        'prefix' => 'TaxonomyTerm',
        'storage_type' => 'taxonomy_vocabulary',
        // 'none' | 'camel' | 'snake'
        'case' => 'camel',
        'defaults' => [
          'isUnion' => FALSE,
          'isMultiple' => FALSE,
          'isRequired' => FALSE,
        ],
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'langcode' => [
            'type' => 'field_language',
            'description' => 'The {entity_type} language.',
          ],
          'name' => [
            'type' => 'entity_label',
            'label' => '{field_name}',
            'description' => 'The display name of the {entity_type} term.',
          ],
          'description' => [
            'type' => 'text_with_summary',
            'label' => 'Description',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'path' => [
            'type' => 'path',
          ],
          'changed' => [
            'type' => 'changed',
          ],
        ],
      ],
      'node' => [
        'interface' => ['Node'],
        'prefix' => 'Node',
        'storage_type' => 'node_type',
        // 'none' | 'camel' | 'snake'
        'case' => 'camel',
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'uid' => [
            'type' => 'entity_owner',
            'description' => 'The author of the {entity_type}.',
            'name_sdl' => 'author',
          ],
          'langcode' => [
            'type' => 'field_language',
            'description' => 'The {entity_type} language.',
          ],
          'title' => [
            'type' => 'entity_label',
            'description' => 'The display title of the {entity_type}.',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'promote' => [
            'type' => 'boolean',
            'label' => 'Promoted to front page',
          ],
          'sticky' => [
            'type' => 'boolean',
            'label' => 'Sticky at top of lists',
          ],
          'body' => [
            'type' => 'text_with_summary',
          ],
          'path' => [
            'type' => 'path',
          ],
          'created' => [
            'type' => 'created',
          ],
          'changed' => [
            'type' => 'changed',
          ],
          'metatag' => [
            'type' => 'metatag',
          ],
        ],
      ],
      'media' => [
        'interface' => ['Node'],
        'prefix' => 'Media',
        'storage_type' => 'media_type',
        // 'none' | 'camel' | 'snake'
        'case' => 'camel',
        'defaults' => [
          'isUnion' => FALSE,
          'isMultiple' => FALSE,
          'isRequired' => FALSE,
        ],
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'created' => [
            'type' => 'created',
          ],
          'changed' => [
            'type' => 'changed',
          ],
        ],
      ],
      'paragraph'  => [
        'interface' => ['Node'],
        'prefix' => 'Paragraph',
        'storage_type' => 'paragraphs_type',
        // 'none' | 'camel' | 'snake'
        'case' => 'camel',
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'langcode' => [
            'type' => 'field_language',
            'description' => 'The {entity_type} language.',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'created' => [
            'type' => 'created',
          ],
        ],
      ],
      // @todo implement as formatters
      // Fields types
      'fields' => [
        // 'field_'
        'prefix' => '',
        // 'none' | 'camel' | 'snake'
        'case' => 'camel',
        // @todo rename as formatters
        'types' => [
          'uuid' => [
            'type' => 'uuid',
            'description' => 'The unique identifier for the {entity_type}.',
            'name_sdl' => 'id',
            'type_sdl' => 'ID',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_uuid',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'entity_owner' => [
            'type' => 'entity_reference',
            'description' => 'The author of the {entity_type}.',
            'name_sdl' => 'author',
            'type_sdl' => 'User',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_owner',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'user_roles' => [
            'type' => 'entity_reference',
            'label' => 'The roles that the user has.',
            'description' => '',
            'name_sdl' => 'roles',
            'type_sdl' => 'UserRole',
            'isUnion' => FALSE,
            'isMultiple' => TRUE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'user_roles',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'user',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'user_status' => [
            'type' => 'boolean',
            'label' => 'The status of the user account.',
            'description' => '',
            'name_sdl' => 'status',
            'type_sdl' => 'UserStatus',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'user_status',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'user',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          // @todo validate implementation is correct
          'field_language' => [
            'type' => 'field_language',
            'description' => 'The the {entity_type} language.',
            'name_sdl' => 'langcode',
            'type_sdl' => 'Language',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => $this->getFieldPluginProducer('field_language'),
          ],
          'entity_label' => [
            'type' => 'string',
            'description' => 'The display name of the {entity_type} term.',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_label',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'path' => [
            'type' => 'path',
            'label' => 'URL alias',
            'description' => '',
            'name_sdl' => 'path',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_url',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
              [
                'type' => 'dataProducer',
                'id' => 'url_path',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'url',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'string' => [
            'type' => 'string',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'string_long' => [
            'type' => 'string_long',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'list_string' => [
            'type' => 'list_string',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'boolean' => [
            'type' => 'boolean',
            'type_sdl' => 'Boolean',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'integer' => [
            'type' => 'integer',
            'type_sdl' => 'Int',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'float' => [
            'type' => 'float',
            'type_sdl' => 'Float',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          // @todo Validate if a new Decimal Scalar type is needed.
          'decimal' => [
            'type' => 'decimal',
            'type_sdl' => 'Float',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'list_integer' => [
            'type' => 'list_integer',
            'type_sdl' => 'Int',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'list_float' => [
            'type' => 'list_float',
            'type_sdl' => 'Float',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'link' => [
            'type' => 'link',
            'name_sdl' => 'link',
            'type_sdl' => 'Link',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPluginProducer('field_link'),
          ],
          // @todo fix error when adding text field and there is a paragraph or block_content with the same name
          'text' => [
            'type' => 'text',
            'type_sdl' => 'Text',
            'producers' => $this->getFieldPluginProducer('field_text'),
          ],
          'text_long' => [
            'type' => 'text',
            'type_sdl' => 'Text',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPluginProducer('field_text'),
          ],
          'text_with_summary' => [
            'type' => 'text',
            'type_sdl' => 'TextSummary',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPluginProducer('field_text'),
          ],
          // @todo provide date formatters using directives
          'datetime' => [
            'type' => 'string',
            'name_sdl' => 'datetime',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'email' => [
            'type' => 'string',
            'name_sdl' => 'email',
            'type_sdl' => 'Email',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'telephone' => [
            'type' => 'telephone',
            'name_sdl' => 'telephone',
            'type_sdl' => 'PhoneNumber',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          'password' => [
            'type' => 'string',
            'name_sdl' => 'password',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => $this->getFieldPropertyProducer(),
          ],
          // @todo provide date formatters using directives
          'created' => [
            'label' => 'Entity Authored on',
            'description' => 'An entity field containing a UNIX timestamp of when the entity has been created.',
            'name_sdl' => 'created',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_created',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          // @todo provide date formatters using directives
          'changed' => [
            'label' => 'Entity Changed on',
            'description' => 'An entity field containing a UNIX timestamp of when the entity has been changed.',
            'name_sdl' => 'changed',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_changed',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'entity_reference' => [
            // This should be defined by target bundle(s) as Bundle Type or Union.
            'type_sdl' => '',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer('entity'),
          ],
          // Paragraphs.
          'entity_reference_revisions' => [
            // This should be defined by target bundle(s) as Bundle Type or Union.
            'type_sdl' => '',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPropertyProducer('entity'),
          ],
          'image' => [
            'type_sdl' => 'Image',
            'name_sdl' => 'image',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPluginProducer('field_image'),
          ],
          'file' => [
            'type_sdl' => 'File',
            'name_sdl' => 'file',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => $this->getFieldPluginProducer('field_file'),
          ],
          // Metatags module.
          'metatag' => [
            'type' => 'metatag',
            'type_sdl' => 'MetaTagUnion',
            'isUnion' => TRUE,
            'isMultiple' => TRUE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'meta_tag',
                'map' => [
                  [
                    'id' => 'fromValue',
                    'key' => 'type',
                    'value' => 'entity:node',
                  ],
                  [
                    'id' => 'fromParent',
                    'key' => 'value',
                    'value' => NULL,
                  ],
                ]
              ],
            ],
          ],
        ],
      ],
          // @todo extract from schema definitions)
      'types' => [
        'Actor' => [
          'type_sdl' => 'Actor',
          'isFielddable' => TRUE,
          'fields' => [
            'id' => [
              'name_sdl' => 'id',
              'type_sdl' => 'ID',
            ],
            'displayName' => [
              'name_sdl' => 'displayName',
              'type_sdl' => 'String',
            ],
          ],
        ],

        'Language' => [
          'type_sdl' => 'Language',
          'isFielddable' => TRUE,
          'fields' => [
            'id' => [
              'name_sdl' => 'id',
              'type_sdl' => 'String',
            ],
            'name' => [
              'name_sdl' => 'name',
              'type_sdl' => 'String',
            ],
            'direction' => [
              'name_sdl' => 'direction',
              'type_sdl' => 'String',
            ],
          ],
        ],
        'Link' => [
          'type_sdl' => 'Link',
          'isFielddable' => TRUE,
          'fields' => [
            'uri' => [
              'name_sdl' => 'uri',
              'type_sdl' => 'String',
            ],
            'link' => [
              'name_sdl' => 'link',
              'type_sdl' => 'String',
            ],
            'title' => [
              'name_sdl' => 'title',
              'type_sdl' => 'String',
            ],
          ],
        ],
        'Image' => [
          'type_sdl' => 'Image',
          'isFielddable' => TRUE,
          'fields' => [
            'url' => [
              'name_sdl' => 'url',
              'type_sdl' => 'String',
            ],
            'width' => [
              'name_sdl' => 'width',
              'type_sdl' => 'Int',
            ],
            'height' => [
              'name_sdl' => 'height',
              'type_sdl' => 'Int',
            ],
          ],
        ],
        'File' => [
          'type_sdl' => 'File',
          'isFielddable' => TRUE,
          'fields' => [
            'url' => [
              'name_sdl' => 'url',
              'type_sdl' => 'String',
            ],
            'name' => [
              'name_sdl' => 'name',
              'type_sdl' => 'String',
            ],
            'size' => [
              'name_sdl' => 'size',
              'type_sdl' => 'Int',
            ],
            'mime' => [
              'name_sdl' => 'mime',
              'type_sdl' => 'String',
            ],
          ],
        ],
        'Text' => [
          'type_sdl' => 'Text',
          'isFielddable' => TRUE,
          'fields' => [
            'format' => [
              'name_sdl' => 'format',
              'type_sdl' => 'String',
            ],
            'value' => [
              'name_sdl' => 'value',
              'type_sdl' => 'String',
            ],
            'processed' => [
              'name_sdl' => 'processed',
              'type_sdl' => 'String',
            ],
          ],
        ],
        'TextSummary' => [
          'type_sdl' => 'TextSummary',
          'isFielddable' => TRUE,
          'fields' => [
            'format' => [
              'name_sdl' => 'format',
              'type_sdl' => 'String',
            ],
            'value' => [
              'name_sdl' => 'value',
              'type_sdl' => 'String',
            ],
            'summary' => [
              'name_sdl' => 'summary',
              'type_sdl' => 'String',
            ],
            'processed' => [
              'name_sdl' => 'processed',
              'type_sdl' => 'String',
            ],
          ],
        ],
      ],
    ];

    if ($this->moduleHandler->moduleExists('media')) {
      $this->calculateEntity('media');
      $this->calculateSdl('media');
    }

    $this->calculateEntity('user');
    $this->calculateSdl('user');

    $this->calculateEntity('taxonomy_term');
    $this->calculateSdl('taxonomy_term');

    if ($this->moduleHandler->moduleExists('paragraphs')) {
      $this->calculateEntity('paragraph');
      $this->calculateSdl('paragraph');
    }

    $this->calculateEntity('node');
    $this->calculateSdl('node');
  }

  /**
   * Return producers for fetching simple values from a field.
   *
   * @param string $property
   *   Propety to fetch from the field item.
   *
   * @return array
   *   Producers to pass to GraphQL.
   */
  protected function getFieldPropertyProducer($property = 'value') {
    return [
      [
        'type' => 'dataProducer',
        'id' => 'field',
        'map' => [
          [
            'id' => 'fromParent',
            'key' => 'entity',
            'value' => NULL,
          ],
          [
            'id' => 'fromValue',
            'key' => 'field',
            'value' => '{field_name}',
          ],
        ],
      ], [
        'type' => 'dataProducer',
        'id' => 'field_property',
        'map' => [
          [
            'id' => 'fromParent',
            'key' => 'field',
            'value' => NULL,
          ],
          [
            'id' => 'fromValue',
            'key' => 'value',
            'value' => $property,
          ],
        ],
      ],
    ];
  }

  /**
   * Return producers for fetching plugin decorated values from a field.
   *
   * @param string $plugin_id
   *   Propety to fetch from the field item.
   *
   * @return array
   *   Producers to pass to GraphQL.
   */
  protected function getFieldPluginProducer($plugin_id) {
    return [
      [
        'type' => 'dataProducer',
        'id' => 'field',
        'map' => [
          [
            'id' => 'fromParent',
            'key' => 'entity',
            'value' => NULL,
          ],
          [
            'id' => 'fromValue',
            'key' => 'field',
            'value' => '{field_name}',
          ],
        ],
      ], [
        'type' => 'dataProducer',
        'id' => $plugin_id,
        'map' => [
          [
            'id' => 'fromParent',
            'key' => 'field',
            'value' => NULL,
          ],
        ],
      ],
    ];
  }

  /**
   * Get settings for type.
   *
   * @param string $type
   *   Type to get settings for. Eg 'media'.
   *
   * @return array
   *   Settings defined by data manager.
   */
  public function getSettings($type) {
    if (!array_key_exists($type, $this->settings)) {
      return [];
    }

    return $this->settings[$type];
  }

  /**
   * Get definitions for type.
   *
   * @param string $type
   *   Type to get settings for. Eg 'media'.
   *
   * @return array
   *   Definitions calculated by data manager.
   */
  public function getDefinitions($type) {
    if (!array_key_exists($type, $this->definitions)) {
      return [];
    }

    return $this->definitions[$type];
  }

  /**
   *
   */
  public function getSdlByStorage($storage, $type) {
    if (!array_key_exists($storage, $this->sdl)) {
      return NULL;
    }

    if (!array_key_exists($type, $this->sdl[$storage])) {
      return NULL;
    }

    return $this->sdl[$storage][$type];
  }

  /**
   *
   */
  protected function getEntitiesFromStorageType($type) {
    $entities = [];

    if ($type === 'user') {
      $entities[] = [
        'id' => 'user',
        'label' => 'Users',
        'description' => 'The GraphQL API users.',
      ];

      return $entities;
    }

    $storage_type = $this->settings[$type]['storage_type'];
    $types = $this->entityTypeManager->getStorage($storage_type)->loadMultiple();

    foreach ($types as $entity) {
      $entities[] = [
        'id' => $entity->id(),
        'label' => $entity->label(),
        'description' => $entity->getDescription(),
      ];
    }

    return $entities;
  }

  /**
   *
   */
  protected function calculateEntity($type) {
    $entities = $this->getEntitiesFromStorageType($type);
    $this->calculateFields($type, $entities);
  }

  /**
   *
   */
  protected function calculateSdl($type) {
    if (!array_key_exists($type, $this->definitions)) {
      return;
    }

    $base = '';
    $extension = '';
    foreach ($this->definitions[$type] as $entity) {
      $renderableBase = [
        '#theme' => 'entity_base',
        '#entity' => $entity,
      ];

      $base .= $this->renderGraphqls($renderableBase);

      $renderableExtension = [
        '#theme' => 'entity_extension',
        '#entity' => $entity,
      ];

      $extension .= $this->renderGraphqls($renderableExtension);
    }

    if ($type === 'node') {
      $renderableBase = [
        '#theme' => 'entity_base_content',
        '#entities' => $this->definitions[$type],
      ];

      $base .= $this->renderGraphqls($renderableBase);

      $renderableExtension = [
        '#theme' => 'entity_extension_content',
        '#entities' => $this->definitions[$type],
      ];

      $extension .= $this->renderGraphqls($renderableExtension);
    }

    $this->sdl[$type] = [
      'base' => $base,
      'extension' => $extension,
    ];
  }

  /**
   *
   */
  protected function calculateFields($entityType, $entities) {
    $fieldNames = $this->settings[$entityType]['fields'] ?: [];
    $fieldTypes = $this->settings['fields']['types'] ?: [];

    // @todo provide a GUI to skip types from singularize
    foreach ($entities as ['id' => $entityId, 'label' => $entityLabel, 'description' => $entityDescription]) {
      $prefix = $this->settings[$entityType]['prefix'];
      $type = u($entityId)->title()->prepend($prefix)->camel()->toString();
      $fields = [];
      $unions = [];

      foreach ($this->entityFieldManager->getFieldDefinitions($entityType, $entityId) as $field) {
        $fieldInfo = [];

        // Core fields.
        if (!u($field->getName())->startsWith('field_')) {

          if (!in_array($field->getName(), array_keys($fieldNames))) {
            continue;
          }

          $entitySDL = u($entityLabel)->camel()->title()->toString();
          if (!in_array($field->getName(), array_keys($fieldNames))) {
            continue;
          }

          $fieldInfo = $fieldNames[$field->getName()];
          $fieldInfo = array_merge($fieldTypes[$fieldInfo['type']], $fieldInfo);

          if (!array_key_exists('label', $fieldInfo)) {
            $fieldInfo['label'] = $field->getLabel();
          }
          else {
            $fieldInfo['label'] = u($fieldInfo['label'])->replace('{field_name}', $field->getName())->toString();
          }

          if (!array_key_exists('name', $fieldInfo)) {
            $fieldInfo['name'] = $field->getName();
          }
          if (!array_key_exists('description', $fieldInfo)) {
            $fieldInfo['description'] = $field->getDescription();
          }
          else {
            $fieldInfo['description'] = u($fieldInfo['description'])->replace('{entity_type}', $entitySDL)->toString();
          }
          if (!array_key_exists('type', $fieldInfo)) {
            $fieldInfo['type'] = $field->getType();
          }
          if (!array_key_exists('name_sdl', $fieldInfo)) {
            $fieldInfo['name_sdl'] = u($field->getName())->camel()->toString();
          }
        }

        // Custom fields.
        if (u($field->getName())->startsWith('field_')) {
          if (!in_array($field->getType(), array_keys($fieldTypes))) {
            continue;
          }

          $fieldName = u($field->getName())->trimPrefix('field_')->camel()->title()->toString();
          $fieldInfo = $fieldTypes[$field->getType()];

          if ($field->getType() === 'entity_reference' || $field->getType() === 'entity_reference_revisions') {

            $handlerSettings = $field->getSetting('handler_settings');
            // @todo fix ReusableParagraph that contains target_bundles with NULL value
            if (!$handlerSettings || $handlerSettings['target_bundles'] === NULL) {
              continue;
            }

            $targetBundles = array_keys($handlerSettings['target_bundles']);
            $targetType = $field->getSetting('target_type');

            $referenceTargetBundles = [];
            $targetBundleMapping = [];
            foreach ($targetBundles as $targetBundle) {
              $targetBundleSingular = u($targetBundle)->title()->prepend($targetType)->camel()->title()->toString();
              $targetBundleSingular = $this->inflector->singularize($targetBundleSingular)[0];
              $referenceTargetBundles[] = u($targetBundleSingular)->camel()->title()->toString();
              $targetBundleMapping[$targetBundle] = $targetBundleSingular;
            }

            if (!$referenceTargetBundles) {
              continue;
            }

            if (!array_intersect($referenceTargetBundles, array_keys($fieldTypes)) &&
                 !array_key_exists($targetType, $this->definitions) ||
                 !array_intersect($referenceTargetBundles, array_keys($this->definitions[$targetType]))
               ) {
              continue;
            }

            if (count($referenceTargetBundles) > 1) {
              $fieldInfo['isUnion'] = TRUE;
              $unionType = u($fieldName)->prepend($type)->title()->append('Union')->toString();
              $fieldInfo['type_sdl'] = $unionType;
              $unions[$unionType]['type'] = $targetType;
              $unions[$unionType]['type_sdl'] = $unionType;
              $unions[$unionType]['target_bundles'] = $targetBundles;
              $unions[$unionType]['target_bundles_sdl'] = $referenceTargetBundles;
              $unions[$unionType]['mapping'] = $targetBundleMapping;
              $fieldInfo['producers'][0]['id'] = 'entity_reference';
            }
            else {
              $referenceTargetBundle = $targetType . '_' . reset($referenceTargetBundles);
              if (array_key_exists($referenceTargetBundle, $fieldTypes)) {
                $fieldInfo = $fieldTypes[$referenceTargetBundle];
              }

              $referenceTargetBundle = reset($referenceTargetBundles);
              if ($this->definitions[$targetType][$referenceTargetBundle]) {
                $fieldInfo['type_sdl'] = $this->definitions[$targetType][$referenceTargetBundle]['type_sdl'];
              }
            }
          }

          $fieldInfo['type'] = $field->getType();
          $fieldInfo['name'] = $field->getName();
          $fieldInfo['label'] = $field->getLabel();
          $fieldInfo['description'] = $field->getDescription();
          $fieldInfo['name_sdl'] = u($field->getName())->trimPrefix('field_')->camel()->toString();
          $fieldInfo['isMultiple'] = $field->getFieldStorageDefinition()->isMultiple()
            || (array_key_exists('isUnion', $fieldInfo) && $fieldInfo['isUnion']);

          if (!$fieldInfo['producers']) {
            continue;
          }
        }

        if (!$fieldInfo) {
          continue;
        }

        $fields[$field->getName()] = $fieldInfo;
      }

      $singular = $this->inflector->singularize($type)[0];
      $plural = $this->inflector->pluralize($singular)[0];
      $typeSdl = u($singular)->camel()->title()->toString();

      $this->definitions[$entityType][$typeSdl] = [
        'id' => $entityId,
        'type' => $singular,
        'interface' => $this->settings[$entityType]['interface'] ?: [],
        'label' => $entityLabel,
        'description' => $entityDescription,
        'type_plural' => $plural,
        'type_sdl' => $typeSdl,
        'fields' => $fields,
        'unions' => $unions,
      ];
    }
  }

  /**
   *
   */
  protected function getEntities() {
    // Settings.
    $types = $this->getSettings('types');
    // $fields = $this->getSettings('fields');
    // Types.
    $users = $this->getDefinitions('user');
    $taxonomyTerms = $this->getDefinitions('taxonomy_term');
    if ($this->moduleHandler->moduleExists('media')) {
      $medias = $this->getDefinitions('media');
    }
    else {
      $medias = [];
    }
    if ($this->moduleHandler->moduleExists('paragraphs')) {
      $paragraphs = $this->getDefinitions('paragraph');
    }
    else {
      $paragraphs = [];
    }
    $nodes = $this->getDefinitions('node');

    return [
      'types' => $types,
      'user' => $users,
      'taxonomy_term' => $taxonomyTerms,
      'media' => $medias,
      'paragraph' => $paragraphs,
      'node' => $nodes,
    ];
  }

  /**
   *
   */
  protected function getMergedEntities() {
    $entities = [];
    foreach ($this->getEntities() as $entity) {
      $entities = array_merge($entities, $entity);
    }

    return $entities;
  }

  /**
   *
   */
  public function getEntityTypes() {
    $entities = [];
    foreach ($this->getEntities() as $entityTypeId => $entityTypes) {
      $types = [];
      foreach ($entityTypes as $entityType) {
        $types[] = [
          'id' => $entityType['id'],
          'type' => $entityType['type_sdl'],
          'typePlural' => u($entityType['type_plural'])->title()->toString(),
          'querySingular' => $entityType['type'],
          'queryPlural' => $entityType['type_plural'],
        ];
      }
      $entities[] = [
        'id' => $entityTypeId,
        'types' => $types,
      ];
    }

    return $entities;
  }

  /**
   *
   */
  public function getFragments() {
    $entities = $this->getMergedEntities();
    $types = $this->getSettings('types');
    foreach ($entities as $entityId => $entity) {
      foreach ($entity['fields'] as $fieldId => $field) {
        $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = FALSE;
        $entities[$entityId]['fields'][$fieldId]['fragment']['isMultiple'] = FALSE;
        if (in_array($field['type_sdl'], array_keys($types))) {
          $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = TRUE;
          $entities[$entityId]['fields'][$fieldId]['fragment']['type_sdl'] = $types[$field['type_sdl']]['type_sdl'];
          continue;
        }
        if (in_array($field['type_sdl'], array_keys($entities))) {
          $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = TRUE;
          $entities[$entityId]['fields'][$fieldId]['fragment']['type_sdl'] = $entities[$field['type_sdl']]['type_sdl'];
          continue;
        }
        if (array_key_exists('isUnion', $field) && $field['isUnion']) {
          $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = TRUE;
          $entities[$entityId]['fields'][$fieldId]['fragment']['isMultiple'] = TRUE;
          $entities[$entityId]['fields'][$fieldId]['fragment']['type_sdl'] = $entities[$entityId]['unions'][$field['type_sdl']]['target_bundles_sdl'];
        }
      }
    }

    return $entities;
  }

  /**
   *
   */
  public function getFragmentsAsSdl() {
    $renderArray = [
      '#theme' => 'entity_fragments',
      '#entities' => $this->getFragments(),
      '#showWrappers' => FALSE,
    ];

    return $this->renderGraphqls($renderArray);
  }

  /**
   * Render wrapper to fix twig debug artifacts.
   *
   * @param array $build
   *   Drupal render array.
   * @param null|RenderContext $context
   *   Render context to use.
   *
   * @return mixed
   *   Result of the render.
   */
  public function renderGraphqls(array $build, ?RenderContext $context = NULL) {
    if ($twig_debug = $this->twig->isDebug()) {
      $this->twig->disableDebug();
    }

    $context = $context ?: new RenderContext();
    $result = $this->renderer->executeInRenderContext($context, function () use ($build) {
      return $this->renderer->render($build);
    });

    if ($twig_debug) {
      $this->twig->enableDebug();
    }

    return $result;
  }

}
