<?php

class RespondersController extends AppController
{
    var $name = 'Responders';
    var $components = array('Twilio', 'Qr', 'Qrsms');

    //var $layout="default";
    function index()
    {
        $this->layout = 'admin_new_layout';
        $this->Responder->recursive = 0;
        $user_id = $this->Session->read('User.id');
        app::import('Model', 'User');
        $this->User = new User();
        $users = $this->User->find('first', array('conditions' => array('User.id' => $user_id, 'User.mms' => 1)));
        app::import('Model', 'UserNumber');
        $this->UserNumber = new UserNumber();
        $numbers_mms = $this->UserNumber->find('first', array('conditions' => array('UserNumber.user_id' => $user_id, 'UserNumber.mms' => 1)));
        $this->set('numbers_mms', $numbers_mms);
        $this->set('users', $users);
        app::import('Model', 'Group');
        $this->Group = new Group();
        $this->paginate = array('conditions' => array('Responder.user_id' => $user_id), 'order' => array('Group.group_name' => 'ASC', 'Responder.days' => 'ASC'), 'paramType' => 'querystring');
        $data = $this->paginate('Responder');
        $this->set('responders', $data);
    }

    function add()
    {
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        app::import('Model', 'Group');
        $this->Group = new Group();
        $Group = $this->Group->find('list', array('conditions' => array('Group.user_id' => $user_id), 'fields' => 'Group.group_name', 'order' => array('Group.group_name' => 'asc')));
        $this->set('Group', $Group);
        app::import('Model', 'Smstemplate');
        $this->Smstemplate = new Smstemplate();
        $Smstemplate = $this->Smstemplate->find('list', array('conditions' => array('Smstemplate.user_id' => $user_id), 'fields' => 'Smstemplate.messagename', 'order' => array('Smstemplate.messagename' => 'asc')));
        $this->set('Smstemplate', $Smstemplate);
        app::import('Model', 'MobilePage');
        $this->MobilePage = new MobilePage();
        $mobilespage = $this->MobilePage->find('list', array('conditions' => array('MobilePage.user_id' => $user_id), 'fields' => 'MobilePage.title', 'order' => array('MobilePage.title' => 'asc')));
        $this->set('mobilespages', $mobilespage);
        app::import('Model', 'UserNumber');
        $this->UserNumber = new UserNumber();
        $numbers_sms = $this->UserNumber->find('first', array('conditions' => array('UserNumber.user_id' => $user_id, 'UserNumber.sms' => 1)));
        $numbers_mms = $this->UserNumber->find('first', array('conditions' => array('UserNumber.user_id' => $user_id, 'UserNumber.mms' => 1)));
        $this->set('numbers_mms', $numbers_mms);
        $this->set('numbers_sms', $numbers_sms);
        $users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
        $this->set('users', $users);


        if (!empty($this->request->data)) {
            /* echo'<pre>';
            print_r($this->request->data);
            die(); */
            app::import('Model', 'Responder');
            $this->Responder = new Responder();

            $responder = $this->Responder->find('first', array('conditions' => array('Responder.user_id' => $user_id, 'Responder.group_id' => $this->request->data['Responder']['group_id'], 'Responder.days' => $this->request->data['Responder']['days'], 'Responder.ishour' => $this->request->data['Responder']['ishour'])));

            if (!empty($responder)) {
                $this->Session->setFlash(__('A responder has already been created for this group and # of days/hours', true));
                $this->redirect(array('action' => 'index'));
            }


            $this->request->data['Responder']['user_id'] = $user_id;
            $this->request->data['Responder']['created'] = date('Y-m-d h:i:s');
            $this->request->data['Responder']['group_id'] = $this->request->data['Responder']['group_id'];
            $this->request->data['Responder']['name'] = $this->request->data['Responder']['name'];
            if (!empty($this->request->data['Responder']['msg_type'])) {
                $this->request->data['Responder']['sms_type'] = $this->request->data['Responder']['msg_type'];
            } else {
                $this->request->data['Responder']['sms_type'] = 1;
            }
            if ($this->request->data['Responder']['message'] != '') {
                $this->request->data['Responder']['message'] = $this->request->data['Responder']['message'];
            }
            if ($this->request->data['Responder']['message1'] != '') {
                $this->request->data['Responder']['message'] = $this->request->data['Responder']['message1'];
            }
            $this->request->data['Responder']['systemmsg'] = $this->request->data['Responder']['systemmsg'];
            $this->request->data['Responder']['days'] = $this->request->data['Responder']['days'];
            if ($this->request->data['Message']['pick_file'] != '') {
                $this->request->data['Responder']['image_url'] = $this->request->data['Message']['pick_file'];
            } else if ($this->request->data['Message']['image'][0]['name'] != '') {
                $image_arr = '';
                foreach ($this->request->data['Message']['image'] as $value) {
                    $image = str_replace(' ', '_', mt_rand() . $value["name"]);
                    move_uploaded_file($value['tmp_name'], "mms/" . $image);
                    if ($image_arr != '') {
                        $image_arr = $image_arr . ',' . SITE_URL . '/mms/' . $image;
                    } else {
                        $image_arr = SITE_URL . '/mms/' . $image;
                    }
                }
                $this->request->data['Responder']['image_url'] = $image_arr;
            }

            if (!empty($this->request->data['Responder']['ishour'])) {
                $this->request->data['Responder']['ishour'] = $this->request->data['Responder']['ishour'];
            } else {
                $this->request->data['Responder']['ishour'] = 1;
            }

            $this->Responder->save($this->request->data);
            $this->Session->setFlash(__('The Responder has been saved', true));
            $this->redirect(array('action' => 'index'));
        }
    }


    function delete($id = null, $type = null)
    {
        $this->autoRender = false;
        app::import('Model', 'Responder');
        $this->Responder = new Responder();
        if ($this->Responder->delete($id)) {
            if ($type == 1) {
                $this->redirect(array('action' => 'mms'));
                $this->Session->setFlash(__('Responder deleted', true));
            } else if ($type == 0) {
                $this->redirect(array('action' => 'index'));
                $this->Session->setFlash(__('Responder deleted', true));
            }
        } else {
            $this->Session->setFlash(__('Responder  not deleted', true));
        }
        $this->redirect(array('action' => 'index'));
    }

    function edit($id = null)
    {
        $this->layout = 'admin_new_layout';
        $this->set('id', $id);
        $user_id = $this->Session->read('User.id');
        app::import('Model', 'UserNumber');
        $this->UserNumber = new UserNumber();
        $numbers_sms = $this->UserNumber->find('first', array('conditions' => array('UserNumber.user_id' => $user_id, 'UserNumber.sms' => 1)));
        $numbers_mms = $this->UserNumber->find('first', array('conditions' => array('UserNumber.user_id' => $user_id, 'UserNumber.mms' => 1)));
        $this->set('numbers_mms', $numbers_mms);
        $this->set('numbers_sms', $numbers_sms);
        $users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
        $responder_arr = $this->Responder->find('first', array('conditions' => array('Responder.id' => $id)));
        $this->set('users', $users);
        $this->set('responder', $responder_arr);

        $responder = $this->Responder->find('first', array('conditions' => array('Responder.id !=' => $id, 'Responder.user_id' => $user_id, 'Responder.group_id' => $this->request->data['Responder']['group_id'], 'Responder.days' => $this->request->data['Responder']['days'], 'Responder.ishour' => $this->request->data['Responder']['ishour'])));

        if (!empty($responder)) {
            $this->Session->setFlash(__('A responder has already been created for this group and # of days/hours', true));
            $this->redirect(array('action' => 'index'));
        } else {
            if (!$id && empty($this->request->data)) {
                $this->Session->setFlash(__('Invalid Responder', true));
                $this->redirect(array('action' => 'index'));
            }
        }

        if (!empty($this->request->data)) {
            $this->request->data['Responder']['id'] = $this->request->data['Responder']['id'];
            $this->request->data['Responder']['group_id'] = $this->request->data['Responder']['group_id'];
            $this->request->data['Responder']['name'] = $this->request->data['Responder']['name'];
            if (!empty($this->request->data['Responder']['msg_type'])) {
                $this->request->data['Responder']['sms_type'] = $this->request->data['Responder']['msg_type'];
            } else {
                $this->request->data['Responder']['sms_type'] = 1;
            }
            if ($this->request->data['Responder']['msg_type'] == 1) {
                $this->request->data['Responder']['message'] = $this->request->data['Responder']['message'];
            }
            if ($this->request->data['Responder']['msg_type'] == 2) {
                $this->request->data['Responder']['message'] = $this->request->data['Responder']['message1'];
            }
            $this->request->data['Responder']['systemmsg'] = $this->request->data['Responder']['systemmsg'];
            $this->request->data['Responder']['days'] = $this->request->data['Responder']['days'];
            if ($this->request->data['Responder']['check_img_validation'] == 0) {
                if ($this->request->data['Message']['pick_file'] != '') {
                    $this->request->data['Responder']['image_url'] = $this->request->data['Message']['pick_file'];
                } else {
                    $this->request->data['Responder']['image_url'] = $this->request->data['Message']['pick_file_old'];
                }
            } else {
                if ($this->request->data['Message']['image'][0]['name'] != '') {
                    $image_arr = '';
                    foreach ($this->request->data['Message']['image'] as $value) {
                        $image = str_replace(' ', '_', mt_rand() . $value["name"]);
                        move_uploaded_file($value['tmp_name'], "mms/" . $image);
                        if ($image_arr != '') {
                            $image_arr = $image_arr . ',' . SITE_URL . '/mms/' . $image;
                        } else {
                            $image_arr = SITE_URL . '/mms/' . $image;
                        }
                    }
                    $this->request->data['Responder']['image_url'] = $image_arr;
                } else {
                    $this->request->data['Responder']['image_url'] = $this->request->data['Message']['mms_image'];
                }
            }
            if ($this->Responder->save($this->request->data)) {
                $this->Session->setFlash(__('The Responder has been edited', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The Responder could not be edited. Please, try again.', true));
            }
        }
        app::import('Model', 'Smstemplate');
        $this->Smstemplate = new Smstemplate();
        $Smstemplate = $this->Smstemplate->find('list', array('conditions' => array('Smstemplate.user_id' => $user_id), 'fields' => 'Smstemplate.messagename', 'order' => array('Smstemplate.messagename' => 'asc')));
        $this->set('Smstemplate', $Smstemplate);
        app::import('Model', 'MobilePage');
        $this->MobilePage = new MobilePage();
        $mobilespage = $this->MobilePage->find('list', array('conditions' => array('MobilePage.user_id' => $user_id), 'fields' => 'MobilePage.title', 'order' => array('MobilePage.title' => 'asc')));
        $this->set('mobilespages', $mobilespage);
        if (empty($this->request->data)) {
            $this->request->data = $this->Responder->read(null, $id);
        }
        app::import('Model', 'Group');
        $this->Group = new Group();
        $Group = $this->Group->find('list', array('conditions' => array('Group.user_id' => $user_id), 'fields' => 'Group.group_name', 'order' => array('Group.group_name' => 'asc')));
        $this->set('Group', $Group);
    }

    function edit_mms($id = null)
    {
        //$this->layout= 'admin_new_layout';
        $this->set('id', $id);
        $user_id = $this->Session->read('User.id');
        if (!$id && empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid Responder', true));
            $this->redirect(array('action' => 'index'));
        }
        app::import('Model', 'Responder');
        $this->Responder = new Responder();
        $responder_arr = $this->Responder->find('first', array('conditions' => array('Responder.id' => $id)));
        $this->set('responder_arr', $responder_arr);
        if (!empty($this->request->data)) {
            $responder_id = $this->request->data['Responder']['id'];
            $name = $this->request->data['message']['name'];
            $name = $this->request->data['message']['name'];
            $tmp_name = $this->request->data['message']['tmp_name'];
            $type = $this->request->data['message']['type'];
            $imageTypes = array("image/gif", "image/jpeg", "image/png");
            if (!empty($name)) {
                if ((in_array($type, $imageTypes)) || (empty($type))) {
                    $filename = time() . $name;
                    $full_image_path = SITE_URL . '/mms_image/' . $filename;
                    $this->request->data['Responder']['image_url'] = $full_image_path;
                    $this->request->data['Responder']['user_id'] = $user_id;
                    $this->Responder->save($this->request->data);
                    if (move_uploaded_file($tmp_name, 'mms_image/' . $filename)) {
                        $this->Session->setFlash(__('The Responder has been edited', true));
                        $this->redirect(array('action' => 'mms'));
                    } else {
                        $this->Session->setFlash(__('The Responder could not be edited. Please, try again.', true));
                    }
                } else {
                    $this->Session->setFlash('Please upload correct file .');
                    $this->redirect('edit_mms/' . $responder_id);
                }
            } else {
                $this->request->data['Responder']['user_id'] = $user_id;
                if ($this->Responder->save($this->request->data)) {
                    $this->Session->setFlash(__('The Responder has been edited', true));
                    $this->redirect(array('action' => 'mms'));
                } else {
                    $this->Session->setFlash(__('The Responder could not be edited. Please, try again.', true));
                }
            }
        }
        app::import('Model', 'Smstemplate');
        $this->Smstemplate = new Smstemplate();
        $Smstemplate = $this->Smstemplate->find('list', array('conditions' => array('Smstemplate.user_id' => $user_id), 'fields' => 'Smstemplate.messagename', 'order' => array('Smstemplate.messagename' => 'asc')));
        $this->set('Smstemplate', $Smstemplate);
        app::import('Model', 'MobilePage');
        $this->MobilePage = new MobilePage();
        $mobilespage = $this->MobilePage->find('list', array('conditions' => array('MobilePage.user_id' => $user_id), 'fields' => 'MobilePage.title', 'order' => array('MobilePage.title' => 'asc')));
        $this->set('mobilespages', $mobilespage);
        if (empty($this->request->data)) {
            $this->request->data = $this->Responder->read(null, $id);
        }
        app::import('Model', 'Group');
        $this->Group = new Group();
        $Group = $this->Group->find('list', array('conditions' => array('Group.user_id' => $user_id), 'fields' => 'Group.group_name', 'order' => array('Group.group_name' => 'asc')));
        $this->set('Group', $Group);
    }
} ?>