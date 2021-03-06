<?php

namespace Drupal\wingsuit_companion\Plugin\UiPatterns\Pattern;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\ui_patterns\Definition\PatternDefinition;
use Drupal\ui_patterns\Plugin\PatternBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Detect Wingsuit (*.wingsuit.yml) Pattern definitions.
 *
 * @UiPattern(
 *   id = "yaml",
 *   label = @Translation("Wingsuit Library Pattern"),
 *   description = @Translation("Pattern defined using a *.wingsuit.yml file."),
 *   deriver = "\Drupal\wingsuit_companion\Plugin\Deriver\LibraryDeriver"
 * )
 */
class LibraryPattern extends PatternBase {

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * UiPatternsManager constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $root, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $root, $module_handler);
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('app.root'),
      $container->get('module_handler'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeImplementation() {
    $item = parent::getThemeImplementation();
    $definition = $this->getPluginDefinition();
    $item[$definition['theme hook']] += $this->processTemplateProperty($definition);
    $item[$definition['theme hook']] += $this->processCustomThemeHookProperty($definition);
    return $item;
  }

  /**
   * Process 'custom hook theme' definition property.
   *
   * @param \Drupal\ui_patterns\Definition\PatternDefinition $definition
   *   Pattern definition array.
   *
   * @return array
   *   Processed hook definition portion.
   */
  protected function processCustomThemeHookProperty(PatternDefinition $definition) {
    /** @var \Drupal\Core\Extension\Extension $module */
    $return = [];
    if (!$definition->hasCustomThemeHook() && $this->moduleHandler->moduleExists($definition->getProvider())) {
      $module = $this->moduleHandler->getModule($definition->getProvider());
      $return['path'] = $module->getPath() . '/templates';
      if ($this->templateExists($definition->getBasePath(), $definition->getTemplate())) {
        $return['path'] = str_replace($this->root, '', $definition->getBasePath());
      }
    }
    return $return;
  }

  /**
   * Weather template exists in given directory.
   *
   * @param string $directory
   *   Directory full path.
   * @param string $template
   *   Template name, without default Twig extension.
   *
   * @return bool
   *   Weather template exists in given directory.
   */
  protected function templateExists($directory, $template) {
    return file_exists($directory . DIRECTORY_SEPARATOR . $template . '.html.twig');
  }

  /**
   * Process 'template' definition property.
   *
   * @param \Drupal\ui_patterns\Definition\PatternDefinition $definition
   *   Pattern definition array.
   *
   * @return array
   *   Processed hook definition portion.
   */
  protected function processTemplateProperty(PatternDefinition $definition) {
    $return = [];

    if ($definition->hasTemplate()) {
      $return = ['template' => $definition->getTemplate()];
    }
    return $return;
  }

}
