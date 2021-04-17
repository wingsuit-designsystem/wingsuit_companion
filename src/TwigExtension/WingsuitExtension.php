<?php

namespace Drupal\wingsuit_companion\TwigExtension;

use Drupal\Component\Utility\Html;
use Drupal\Core\Site\Settings;
use Drupal\Core\Template\TwigExtension;
use Twig\TwigFunction;

/**
 * Class WingsiutTheme.
 *
 * @package Drupal\keytec_theme\TwigExtension
 */
class WingsuitExtension extends TwigExtension {

  /**
   * @inheritdoc
   */
  public function getFunctions() {
    return [
      new TwigFunction('ws_itok', [$this, 'wsItok']),
      new TwigFunction('uuid', [$this, 'wsUuid']),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getName() {
    return 'wingsuit_companion';
  }

  /**
   * Uses deployment key as cache key for generated svgs.
   *
   * @return mixed|null
   */
  public static function wsItok() {
    return urlencode(Settings::get('deployment_identifier'));
  }

  /**
   * Returns a unique id.
   *
   * @return mixed|null
   */
  public static function wsUuid() {
    return Html::getId(\Drupal::service('uuid')->generate());
  }

}
