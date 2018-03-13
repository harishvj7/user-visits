<?php

/**
 * @file
 * User visits - admin pages
 */

/**
 * Settings page.
 */
namespace Drupal\user_visits\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Configure user visits settings for this site.
 */
class UserVisitsSettingsForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_visits_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'user_visits.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
  $config = $this->config('user_visits.settings');
  $form['user_activity'] = [
    '#description' => t("Choose if you want the visitors to be displayed on the user's profile page or not. Alternatively you may use the provided <a href='@blocks'>blocks</a> to display a user's visitors.",['@blocks' => '/poc/admin/structure/block']),
    '#type' => 'fieldset',
    '#title' => t('Display settings'),
  ];
  $form['user_activity']['user_visits_display'] = [
    '#type' => 'radios',
    '#options' => [t("Don't display."), t('Display on user profile page')],
    '#default_value' => $config->get('user_visits_display'),
  ];
  $roles = user_roles(TRUE);
  foreach ($roles as $key => $label) {
    $roles_list[$key] = $label->get('label');
  }
  $form['user_activity_role'] = [
    '#description' => t("Choose roles and visits of selected roles will be not shown in user visit block."),
    '#type' => 'fieldset',
    '#title' => t('Role visibility'),
  ];
  $form['user_activity_role']['user_visits_hidden_roles'] = [
    '#description' => t('visits of selected roles will be not shown in user visit block.'),
    '#type' => 'select',
    '#title' => t('Hidden Roles'),
    '#options' => $roles_list,
    '#multiple' => TRUE,
    '#default_value' => $config->get('user_visits_hidden_roles'),
  ];
    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

       $this->configFactory->getEditable('user_visits.settings')
      ->set('user_visits_display', $form_state->getValue('user_visits_display'))
      ->set('user_visits_hidden_roles', $form_state->getValue('user_visits_hidden_roles'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
