<?php

namespace Drupal\user_visits\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "latest_visitors",
 *   admin_label = @Translation("Latest Visitors"),
 * )
 */
class LatestVisitors extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
  $config = $this->getConfiguration();
  $route_name = \Drupal::routeMatch()->getRouteName();
	$current_path = \Drupal::service('path.current')->getPath();
	$path_args = explode('/', $current_path);
    $visitors = array();
    $query = db_select('user_visits', 'uv')
        ->fields('uv', [ 'vuid', 'visit', 'referer'])
        ->condition('uv.uid', $path_args[2])
        ->orderBy('uv.visit', 'DESC')
        ->range(0, 5);
    $results = $query->execute();
    $total = db_select('user_visits', 'uv');
    $total
        ->condition('uv.uid', $path_args[2])
        ->addExpression('COUNT(visit)', 'count');
    $totals = $total->execute()->fetchField();
    foreach ($results as $record) {
      $uname = user_load($record->vuid);
      $uname = $uname->getUsername();
      $time = date(DATE_RFC3339,$record->visit);
      $visitors[] = [
        'vuid' => $uname,
        'visit' => $time,
        'referer' => $record->referer
      ];
    }
    $visitors['total'] = $totals;
    $build = [];
    $build['ticker_block'] = [
    '#theme' => 'user_visits',
    '#list1' => $visitors,
  ];
  $build['#cache']['max-age'] = 0;
    return $build;
  }
  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'administer user_visits');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form = array();
    $form['latest'] = [
      '#type' => 'select',
      '#title' => t('Number of items'),
      '#default_value' => $config['latest'],
      '#options' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25]
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['latest'] = $form_state->getValue('latest');
  }
}
