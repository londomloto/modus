<?php
namespace App\Module\User;

use Sys\Library\Uploader;

class User extends \Sys\Core\Module {
    
    public function updateProfileAction() {
        $data = array(
            'success' => FALSE,
            'message' => ''
        );

        $file = $this->request->hasFiles();

        if ($file) {
            $post = $this->request->getPost();
        } else {
            $post = $this->request->getInput();
        }

        if (count($post) > 0) {
            
            if (isset($post['noavatar']) && $post['noavatar'] == '1' && ! empty($post['avatar'])) {
                @unlink(PUBPATH.'upload'.DS.'avatar'.DS.$post['avatar']);
                $post['avatar'] = '';
                $post['avatar_name'] = '';
            }

            if ($file) {

                $this->uploader->setup(array(
                    'path' => PUBPATH.'upload'.DS.'avatar'.DS
                ));

                if (($this->uploader->upload())) {
                    
                    $upload = $this->uploader->getResult();

                    $post['avatar'] = $upload['file_name'];
                    $post['avatar_name'] = $upload['orig_name'];
                }
            }

            $keys = array('email' => $post['email']);

            if ($this->db->update('user', $post, $keys)) {
                $user = $this->db->fetchOne("SELECT * FROM user WHERE email = ?", array($post['email']));

                // check session
                $curr = $this->auth->getUser();

                if ($curr->email == $user->email) {
                    $this->auth->save($user);    
                    $data['user'] = $this->auth->getUser();
                    $data['success'] = TRUE;
                } else {
                    $data['success'] = TRUE;
                }
            }
        } else {
            $data['message'] = 'Tidak ada data yang dikirim';
        }

        $this->response->responseJson();
        return $data;
    }

    public function updateAccountAction() {
        $data = array(
            'success' => FALSE,
            'message' => ''
        );

        $post = $this->request->getInput();

        if (isset($post['passwd'])) {
            $salt = $this->security->generateSalt();
            $hash = $this->security->generateHash($post['passwd'], $salt);

            $post['passwd'] = $hash;
            $post['passwd_salt'] = $salt;

            if ($this->db->update('user', $post, array('email' => $post['email']))) {
                $data['success'] = TRUE;
            } else {
                $data['message'] = $this->db->getError();
            }
        }

        $this->response->responseJson();
        return $data;
    }

    public function deleteAccountAction() {
        $data = array(
            'success' => FALSE,
            'message' => ''
        );

        $email = $this->request->getInput('email');

        if ($this->db->delete('user', array('email' => $email))) {
            $data['success'] = TRUE;
        } else {
            $data['message'] = $this->db->getError();
        }

        $this->response->responseJson();
        return $data;
    }
    
}