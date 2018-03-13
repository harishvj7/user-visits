<?php
namespace Drupal\user_visits\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserVisitsExit implements EventSubscriberInterface {

  public function user_visits_count() {
  $user = \Drupal::currentUser();
  if (!$user->id()) {
    return;
  }
  /**
 * Check if user should be counted.
 */
  $config = \Drupal::config('user_visits.settings');
  $roles = $config->get('user_visits_hidden_roles');
  foreach ($user->getRoles() as $record) {
    if (in_array($record, $roles)) {
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name == 'entity.user.canonical') {
      $current_path = \Drupal::service('path.current')->getPath();
      $path_args = explode('/', $current_path);
      if($path_args[2]!= $user->id()){
        $delete_query = db_delete('user_visits')
          ->condition('uid', $path_args[2])
          ->condition('vuid', $user->id())
          ->execute();
        $query = db_insert('user_visits')
          ->fields([
            'uid' => $path_args[2],
            'vuid' => $user->id(),
            'visit' => REQUEST_TIME,
            'referer' => '/user/'.$user->id(),
          ])
          ->execute();
      }
    }
        break;
    }
  }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
     return [KernelEvents::RESPONSE  => [['user_visits_count']]];
  }

}
