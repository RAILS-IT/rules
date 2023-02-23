<?php

namespace Drupal\rules\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Makes the current path available as a context variable.
 */
class CurrentPathContext implements ContextProviderInterface {
  use StringTranslationTrait;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * Constructs a new CurrentPathContext.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path_stack
   *   The current path stack service.
   */
  public function __construct(CurrentPathStack $current_path_stack) {
    $this->currentPathStack = $current_path_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    return $this->getCurrentPathContext(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    return $this->getCurrentPathContext(FALSE);
  }

  /**
   * Constructs the context values.
   *
   * @param bool $runtime
   *   (optional) If FALSE, the exact values inside the contexts don't matter.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   All available contexts keyed by the unqualified context ID.
   */
  protected function getCurrentPathContext($runtime) {
    // If FALSE, we likely don't need to populate $values fully, but we do it
    // anyway. We should not generate Url objects, though; that can cause
    // infinite recursion issues at configure time.
    $url = '<current>';
    if ($runtime) {
      $url = Url::fromRoute($url, [], ['absolute' => TRUE])->toString();
    }
    $values = [
      'path' => $this->currentPathStack->getPath(),
      'url' => $url,
    ];

    $context_definition = new ContextDefinition('current_path', $this->t('Current path'));
    $context = new Context($context_definition, $values);
    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['url.path']);
    $context->addCacheableDependency($cacheability);

    $result = [
      'current_path' => $context,
    ];

    return $result;
  }

}
