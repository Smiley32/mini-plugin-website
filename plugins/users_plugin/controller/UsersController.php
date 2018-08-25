<?php

class UsersController extends Controller {
  function __construct($action, $subAction) {
    parent::__construct($action, $subAction);
  }

  protected function action_login() {
    $this->addScript('redirect.js');

    if(!isset($_GET['submit'])) {
      // Nothing was submited, just display the page
      return;
    }

    $pseudo = $this->checkGet('pseudo');
    $password = $this->checkGet('password');

    if($this->data['error']) {
      return;
    }

    $ret = Plugins::callFunction('users_plugin', 'connect', $pseudo, $password);
    if(true !== $ret) {
      switch($ret) {
        case 1:
          $this->addError('{>error_already_connected<}');
          break;
        case 2:
          $this->addError('{>error_wrong_password_pseudo<}');
          break;
        default:
          $this->addError('{>error_database<}');
          break;
      }
    } else {
      if(isset($_GET['redirect']) && '' != $_GET['redirect']) {
        // TODO: protect
        header('Location: ' . $_GET['redirect']);
      }
    }
  }

  protected function action_signup() {
    if(!isset($_GET['submit'])) {
      // Nothing was submited, just display the page
      return;
    }

    $pseudo = $this->checkGet('pseudo');
    $password = $this->checkGet('password');
    $passwordConfirmation = $this->checkGet('confirmation');

    if($this->data['error']) {
      return;
    }

    if($password != $passwordConfirmation) {
      $this->addError('The paswords are different');
      return;
    }

    // Add user
    $model = $this->getModel();
    $ret = $model->addUser($pseudo, $password);
    if(true !== $ret) {
      switch($ret) {
        case 1:
          $this->addError('{>error_existing_user<}');
          break;
        default:
          $this->addError('{>error_database<}');
          break;
      }
    }
  }

  protected function action_logout() {
    Plugins::callFunction('users_plugin', 'disconnect');
    Settings::redirect('posts', 'search');

    if(isset($_GET['redirect']) && '' != $_GET['redirect']) {
      // TODO: protect
      header('Location: ' . $_GET['redirect']);
    }
  }

  protected function action_api() {
    $this->setAjax(true);

    $this->_reserved['body'] = '{"error": -1}';
    if(isset($_GET['get'])) {
      if($_GET['get'] == 'favorites' && isset($_GET['user']) && '' != $_GET['user']) {
        $user = $_GET['user'];

        // Doesn't work anymore
        // $favorites = Plugins::callFunction('database_plugin', 'getFavorites', $user);
        exit();

        if(false === $favorites) {
          $this->_reserved['body'] = '{"error": 1}';
        } else {
          $this->_reserved['body'] = json_encode($favorites);
        }
      }
    }
  }
}

?>
