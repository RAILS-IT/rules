<?php

namespace Drupal\rules\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Sets the current node as a context on node routes.
 *
 * Modules may add properties to this global context by implementing
 * hook_data_type_info_alter(&$data_types) to modify the $data_types['site']
 * element.
 *
 * @todo Need a way to alter the global context contents to set a value for
 * any added site properties.
 */
class SiteContext implements ContextProviderInterface {
  use StringTranslationTrait;

  /**
   * The system.site configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $systemSiteConfig;

  /**
   * Constructs a new SiteContext.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config.factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->systemSiteConfig = $config_factory->get('system.site');
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    return $this->getSiteContext(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    return $this->getSiteContext(FALSE);
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
  protected function getSiteContext($runtime) {
    // If FALSE, we likely don't need to populate $site fully, but we do it
    // anyway. We should not generate Url objects, though; that can cause
    // infinite recursion issues at configure time.
    $front_url = '<front>';
    $login_url = 'user.page';
    if ($runtime) {
      $front_url = Url::fromRoute($front_url, [], ['absolute' => TRUE])->toString();
      $login_url = Url::fromRoute($login_url, [], ['absolute' => TRUE])->toString();
    }
    $site = [
      'url' => $front_url,
      'login-url' => $login_url,
      'name' => $this->systemSiteConfig->get('name'),
      'slogan' => $this->systemSiteConfig->get('slogan'),
      'mail' => $this->systemSiteConfig->get('mail'),
    ];

    $context_definition = new ContextDefinition('site', $this->t('Site information'));
    $context = new Context($context_definition, $site);
    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['site']);
    $context->addCacheableDependency($cacheability);

    $result = [
      'site' => $context,
    ];

    return $result;
  }

}
