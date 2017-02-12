<?php
namespace App\Module\Accounts;

use App\Module\Users\Users;

class Followers extends \Sys\Core\Module {

    public function findAction() {
        $email = $this->dispatcher->getParam('email');
        $user = Users::findByEmail($email);

        $result = array(
            'success' => TRUE,
            'data' => array(),
            'total' => 0
        );

        if ($user) {
            $options = array(
                'user_id' => $user->id
            );

            $start = $this->request->getParam('start');
            $limit = $this->request->getParam('limit');

            $options['start'] = $start;
            $options['limit'] = $limit;

            $result = self::query($options);
        }

        $this->response->setJsonContent($result);
    }

    public function findByEmailAction($email) {

    }

    public function createAction() {
        $current = $this->auth->getCurrentUser();
        $email = $this->dispatcher->getParam('email');

        $result = array(
            'success' => FALSE
        );

        if ($current && $email && ($current->email != $email)) {
            $user = Users::findByEmail($email);
            if ($user) {
                $following = Users::followStatus($current, $user);
                if ($following == 'N') {
                    $success = $this->db->insert(
                        'follower',
                        array(
                            'user_id' => $user->id,
                            'follower_id' => $current->id
                        )
                    );

                    if ($success) {
                        $this->notification->notify('follow', $current, $user);
                    }

                    $result['success'] = $success;
                }
            }
        }

        $this->response->setJsonContent($result);
    }

    public function updateAction($id) {

    }

    public function deleteAction($id) {
        $current = $this->auth->getCurrentUser();
        $email = $this->dispatcher->getParam('email');

        $result = array(
            'success' => FALSE
        );

        if ($email && $current) {
            $user = Users::findByEmail($email);
            if ($user) {
                $success = $this->db->delete(
                    'follower', 
                    array(
                        'user_id' => $user->id,
                        'follower_id' => $current->id
                    )
                );

                $result['success'] = $success;    
            }
        }
        $this->response->setJsonContent($result);
    }

    public static function query($options = array()) {
        $self = self::getInstance();

        $sql = "SELECT 
                    SQL_CALC_FOUND_ROWS 
                    a.follower_id AS id,
                    c.fullname,
                    c.email,
                    c.avatar
                FROM follower a
                    JOIN user c ON (a.follower_id = c.id)
                WHERE 1 = 1
            ";

        $params = array();

        if (isset($options['user_id'])) {
            $sql .= " AND (a.user_id = ?)";
            $params[] = $options['user_id'];
        }

        if (isset($options['follower_id'])) {
            $sql .= " AND (a.follower_id = ?)";
            $params[] = $options['follower_id'];
        }

        if (isset($options['start'], $options['limit'])) {
            $sql .= " LIMIT " . $options['start'] . ", " . $options['limit'];
        }

        $followers = $self->db->fetchAll($sql, $params);
        $total = $self->db->foundRows();

        return array(
            'success' => TRUE,
            'data' => $followers,
            'total' => $total
        );
    }
}