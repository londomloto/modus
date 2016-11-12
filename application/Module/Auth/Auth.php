<?php
namespace App\Module\Auth;

class Auth extends \Sys\Core\Module {
    
    public function loginAction() {
        $post = $this->request->getInput();
        $data = array();

        if (isset($post['email'], $post['passwd'])) {
            if ($this->auth->login($post['email'], $post['passwd'])) {
                $data['success'] = TRUE;
                $data['user'] = $this->auth->getUser();
            } else {
                $data['success'] = FALSE;
                $data['message'] = $this->auth->getError();
            }
        } else {
            $data['success'] = FALSE;
            $data['message'] = 'Inputan email dan sandi tidak boleh kosong';
        }

        $this->response->responseJson();
        return $data;
    }

    public function logoutAction() {
        $this->auth->logout();
        $this->response->responseJson();

        return array(
            'success' => TRUE
        );
    }

    public function registerAction() {
        $post = $this->request->getInput();
        $data = array();

        if (isset($post['email'], $post['passwd'], $post['fullname'])) {
            $user = $this->auth->register($post);
            if ($user) {
                $this->auth->login($user['email'], $post['passwd']);
                $data['success'] = TRUE;
                $data['user'] = $this->auth->getUser();
            } else {
                $data['success'] = FALSE;
                $data['message'] = $this->auth->getError();
            }
        } else {
            $data['success'] = FALSE;
            $data['message'] = 'Inputan data tidak valid';
        }
        
        $this->response->responseJson();
        return $data;
    }

}