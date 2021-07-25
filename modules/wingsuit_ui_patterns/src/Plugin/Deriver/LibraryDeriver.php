<?php

namespace Drupal\wingsuit_ui_patterns\Plugin\Deriver;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\ui_patterns\Plugin\Deriver\AbstractYamlPatternsDeriver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Class LibraryDeriver.
 *
 * @package Drupal\ui_patterns_library\Deriver
 */
class LibraryDeriver extends AbstractYamlPatternsDeriver {

  /**
   * The base plugin ID.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The app root.
   *
   * @var string
   */
  protected $suffixes;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Extension discovery class.
   *
   * @var \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected $extensionDiscovery;

  /**
   * List of extension locations.
   *
   * @var array
   */
  protected $extensionLocations = [];

  /**
   * List of valid definition file extensions.
   *
   * @var array
   */
  protected $fileExtensions = [];

  /**
   * Constructor.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   Typed data manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * @param string $root
   *   Application root directory.
   * @param array $extensions
   *   File extensions.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   * @param ConfigFactory $config_factory
   *   Config factory service.
   */
  public function __construct($base_plugin_id, TypedDataManager $typed_data_manager, MessengerInterface $messenger, FileSystemInterface $file_system, $root, array $extensions, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, ConfigFactory $config_factory) {
    parent::__construct($base_plugin_id, $typed_data_manager, $messenger, $file_system);
    $this->root = $root;
    $this->fileExtensions = $extensions;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->extensionDiscovery = new ExtensionDiscovery($root);
    $this->config = $config_factory->getEditable('wingsuit_companion.config');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('typed_data_manager'),
      $container->get('messenger'),
      $container->get('file_system'),
      $container->get('app.root'),
      $container->getParameter('wingsuit_companion.file_extensions'),
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFileExtensions() {
    return $this->fileExtensions;
  }

  /**
   * Removes Wingsuit YAML extensions.
   */
  private function removeWingsuitExtensions(&$definition) {

    if (isset($definition['fields'])) {
      $fields =  & $definition['fields'];
      foreach ($fields as &$field) {
        if (isset($field['preview']['faker'])) {
          unset($field['preview']['faker']);
          $field['preview'] = 'Faked text';
          continue;
        }

        // Remove preview lists.
        if (isset($field['preview'][0]['id'])) {
          $field['preview'] = $field['preview'][0];
        }

        if (isset($field['preview']['id'])) {
          $field['preview']['theme'] = $field['preview']['id'];
        }

        foreach (['id', 'settings', 'fields', 'variant'] as $key) {
          if (isset($field['preview'][$key])) {
            unset($field['preview'][$key]);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = [];
    foreach ($this->getDirectories() as $provider => $directory) {
      try {
        foreach ($this->fileScanDirectory($directory) as $file_path => $file) {
          $host_extension = $this->getHostExtension($file_path);
          if ($host_extension == FALSE || $host_extension == $provider) {
            $content = file_get_contents($file_path);
            foreach (Yaml::decode($content) as $id => $definition) {
              $definition['id'] = $id;
              $definition['base path'] = dirname($file_path);
              $definition['file name'] = basename($file_path);
              $definition['provider'] = $provider;
              $this->removeWingsuitExtensions($definition);
              $patterns[] = $this->getPatternDefinition($definition);
            }
          }
        }
      } catch (\Throwable $ex) {
        \Drupal::messenger()->addError("Error while parsing scaning directory " . $directory . ' ' . $ex->getMessage());
      }
    }

    return $patterns;
  }

  /**
   * Create a list of all directories to scan.
   *
   * This includes all module directories and directories of the default theme
   * and all of its possible base themes.
   *
   * @return array
   *   An array containing directory paths keyed by their extension name.
   */
  protected function getDirectories() {
    $dist_path = $this->config->get('dist_path');
    $directories['wingsuit_companion'] = $dist_path;
    return $directories;
  }

  /**
   * Get extension name that hosts the given YAML definition file.
   *
   * @param string $pathname
   *   YAML definition file full path.
   *
   * @return bool|string
   *   Either extension machine name or FALSE if not found.
   */
  protected function getHostExtension($pathname) {
    $extensions = $this->getExtensionLocations();
    $parts = explode(DIRECTORY_SEPARATOR, $pathname);
    while (!empty($parts)) {
      $path = implode(DIRECTORY_SEPARATOR, $parts);
      if (isset($extensions[$path])) {
        return $extensions[$path];
      }
      array_pop($parts);
    }
    return FALSE;
  }

  /**
   * Get extension locations.
   *
   * @return array
   *   Array of extensions keyed by their path location.
   */
  protected function getExtensionLocations() {
    /** @var \Drupal\Core\Extension\Extension[] $extensions */
    if (empty($this->extensionLocations)) {
      $extensions = $this->extensionDiscovery->scan('theme') + $this->extensionDiscovery->scan('module');
      foreach ($extensions as $name => $extension) {
        $this->extensionLocations[$this->root . DIRECTORY_SEPARATOR . $extension->getPath()] = $name;
      }
    }
    return $this->extensionLocations;
  }

}
