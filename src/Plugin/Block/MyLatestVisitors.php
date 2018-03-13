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
 *   id = "my_latest_visitors",
 *   admin_label = @Translation("My Latest Visitors"),
 * )
 */
class MyLatestVisitors extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $user = \Drupal::currentUser();
    $visitors = array();
    $query = db_select('user_visits', 'uv')
        ->fields('uv', array( 'vuid', 'visit', 'referer'))
        ->condition('uv.uid', $user->id())
        ->orderBy('uv.visit', 'DESC')
        ->range(0, $config['my_latest']);
    $results = $query->execute();
    $total = db_select('user_visits', 'uv');
    $total
        ->condition('uv.uid', $user->id())
        ->addExpression('COUNT(visit)', 'count');
    $total = $total->execute()->fetchField();
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
    $visitors['total'] = $total;
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
    $form['my_latest'] = array(
      '#type' => 'select',
      '#title' => t('Number of items'),
      '#default_value' => $config['my_latest'],
      '#options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25)
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['my_latest'] = $form_state->getValue('my_latest');
  }
}