<?php

namespace Drupal\wingsuit_companion\StreamWrapper;

use Drupal\Core\Site\Settings;
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
   * Returns the directory path to Wingsuits dist directory set in settings.php.
   * Otherwise it will return the path to Wingsuits default dist location.
   *
   * @return string
   */
  protected function getDirectoryPath() {
    if (!empty(Settings::get('wingsuit_dist'))) {
      return Settings::get('wingsuit_dist');
    }
    return $this->getThemeHandler()->getTheme('wingsuit')->getPath() . '/../../dist/app-drupal';
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
