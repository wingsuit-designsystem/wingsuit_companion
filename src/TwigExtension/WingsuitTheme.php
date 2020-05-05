<?php

namespace Drupal\wingsuit_companion\TwigExtension;

use Drupal\Core\Site\Settings;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\TwigExtension;

/**
 * Class WingsiutTheme.
 *
 * @package Drupal\keytec_theme\TwigExtension
 */
class WingsuitTheme extends TwigExtension {
  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters()
  {
    return [
        new \Twig_SimpleFilter('naked_field', [$this, 'renderNakedField']),
    ];
  }
  /**
   * @inheritdoc
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('modifier_array', array($this, 'modifierArray')),
      new \Twig_SimpleFunction('modifier_class', array($this, 'modifierClass')),
      new \Twig_SimpleFunction('svg_cache_key', array($this, 'svgCacheKey')),
    ];
  }

  /**
   * Make render of var, removes html comments from string, do strip_tags, remove new lines => naked string
   * Example: A string which has value  <!-- Start DEBUG --> ABCD <!-- End DEBUG -->
   * will be returned the output ABCD after using the the following function.
   * @param string $string A string, which have html comments.
   * @return string A string, which have no html comments.
   */
  public function renderNakedField($string)
  {
    $rendered = $this->renderer->render($string);
    $withoutComments = preg_replace('/<!--(.|\s)*?-->/', '', $rendered);
    $naked = strip_tags(str_replace(["\n", "\r"], '', $withoutComments));

    return trim($naked);
  }

  /**
   * @inheritdoc
   */
  public function getName() {
    return 'wingsuit_theme';
  }

  /**
   * Builds modifier class(es).
   *
   * @param string $base_class
   *   The base class to be "modified", e.g. `my-class`
   * @param string|array $modifier
   *   One or more modifier strings, either as array, space-separated string or
   *   array of space-separated strings
   *
   * @return string
   *   A space-separated string of modifier classes. If modifier is empty an
   *   empty string will be returned.
   */
  public static function modifierClass($base_class, $modifier) {
    // Modifier may be an array or a whitespace separated string, or both. So we
    // first glue everything together and split afterwards to get an array of
    // modifiers.
    if (is_array($modifier)) {
      $modifier = implode(' ', $modifier);
    }
    $modifiers = array_filter(array_unique(explode(' ', $modifier)));
    foreach ($modifiers as &$mod) {
      $mod = $base_class . '--' . $mod;
    }
    return implode(' ', $modifiers);
  }

  /**
   * Builds an array of modifier strings.
   *
   * @param string|array $modifier
   *   One or more modifier strings, either as array, space-separated string or
   *   array of space-separated strings
   *
   * @return string
   *   An array of single modifiers.
   */
   public static function modifierArray ($modifier) {
    // Modifier may be an array or a whitespace separated string, or both. So we
    // first glue everything together and split afterwards to get an array of
    // modifiers.
    if (is_array($modifier)) {
      $modifier = implode(' ', $modifier);
    }
    return array_filter(array_unique(explode(' ', $modifier)));
  }

  /**
   * Uses deployment key as cache key for generated svgs.
   *
   * @return mixed|null
   */
  public static function svgCacheKey () {
    return Settings::get('deployment_identifier');
  }
}
