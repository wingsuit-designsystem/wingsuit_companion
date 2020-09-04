<?php

namespace Drupal\wingsuit_companion\StreamWrapper;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StreamWrapper\LocalReadOnlyStream;
use Drupal\system_stream_wrapper\StreamWrapper\ExtensionStreamBase;

/**
 * Defines the read-only theme:// stream wrapper for theme files.
 */
class WingsuitStreamWrapper extends LocalReadOnlyStream {

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

  /**
   * Returns the directory path to Wingsuits dist directory set in settings.php.
   * Otherwise it will return the path to Wingsuits default dist location.
   *
   * @return string
   */
  public function getDirectoryPath() {
    $dist_path = $this->config->get('dist_path');
    return $this->getThemeHandler()->getTheme('wingsuit')->getPath() . $dist_path . '/assets';
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

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $dir = $this->getDirectoryPath();
    if (empty($dir)) {
      throw new \InvalidArgumentException("Extension directory for {$this->uri} does not exist.");
    }
    $path = rtrim(base_path() . $dir . '/' . $this->getTarget(), '/');
    return $this->getRequest()->getUriForPath($path);
  }

  /**
   * Returns the current request object.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The current request object.
   */
  protected function getRequest() {
    if (!isset($this->request)) {
      $this->request = \Drupal::service('request_stack')->getCurrentRequest();
    }
    return $this->request;
  }

}
