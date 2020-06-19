<?php

namespace Drupal\wingsuit_companion\StreamWrapper;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\system_stream_wrapper\StreamWrapper\ExtensionStreamBase;

/**
 * Defines the read-only theme:// stream wrapper for theme files.
 */
class WingsuitStreamWrapper extends ExtensionStreamBase {

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * WingsuitStreamWrapper constructor.
   *
   * @param ConfigFactory $config_factory
   *   Config factory service.
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->config = $config_factory->getEditable('wingsuit_companion.config');
  }

  protected function getOwnerName() {
    $app_name = parent::getOwnerName();
    return $app_name;
  }

  /**
   * Returns the directory path to Wingsuits dist directory set in settings.php.
   * Otherwise it will return the path to Wingsuits default dist location.
   *
   * @return string
   */
  protected function getDirectoryPath() {
    $dist_path = $this->config->get('dist_path');
    return $this->getThemeHandler()->getTheme('wingsuit')->getPath() . $dist_path . '/assets/' . $this->getOwnerName();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->t('Wingsuit files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Local files stored under wingsuit dist directory.');
  }

  /**
   * Returns the theme handler service.
   *
   * @return \Drupal\Core\Extension\ThemeHandlerInterface
   *   The theme handler service.
   */
  protected function getThemeHandler() {
    if (!isset($this->themeHandler)) {
      $this->themeHandler = \Drupal::service('theme_handler');
    }
    return $this->themeHandler;
  }

}
