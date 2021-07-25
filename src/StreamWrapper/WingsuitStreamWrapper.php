<?php

namespace Drupal\wingsuit_companion\StreamWrapper;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StreamWrapper\LocalReadOnlyStream;
use Drupal\system_stream_wrapper\StreamWrapper\ExtensionStreamBase;

/**
 * Defines the read-only ws-assets:// stream wrapper for theme files.
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
  public function __construct(ConfigFactory $config_factory = NULL) {
    if ($config_factory === NULL) {
      $config_factory = \Drupal::configFactory();
    }
    $this->config = $config_factory->get('wingsuit_companion.config');
  }

  /**
   * Returns the directory path to Wingsuit dist directory set in settings.php.
   * Otherwise it will return the path to Wingsuit default dist location.
   *
   * @return string
   */
  public function getDirectoryPath() {
    $dist_path = $this->config->get('dist_path');
    return $dist_path;
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
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $dir = $this->getDirectoryPath();
    if (empty($dir)) {
      throw new \InvalidArgumentException(
        "Extension directory for {$this->uri} does not exist."
      );
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
