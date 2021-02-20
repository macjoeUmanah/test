<?php

class PaypalsController extends AppController
{
    var $name = 'Paypals';
    var $uses = array('Stripe');

    function admin_index()
    {
        $this->Paypal->recursive = 0;
        $this->set('paypals', $this->paginate());
    }

    function admin_stripeindex()
    {
        $stripes = $this->Stripe->find('first', array('conditions' => array('Stripe.id' => 1)));
        $this->set('stripes', $stripes);
    }

    function admin_view($id = null)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid paypal', true));
            $this->redirect(array('action' => 'index'));
        }
        $this->set('paypal', $this->Paypal->read(null, $id));
    }

    function admin_add()
    {
        $paypals = $this->Paypal->find('first', array('conditions' => array('Paypal.id' => 1)));
        if (!empty($paypals)) {
            $this->Session->setFlash(__('The paypal already created', true));
            $this->redirect(array('action' => 'index'));
        } else {
            if (!empty($this->request->data)) {
                $this->Paypal->create();
                $this->request->data['Paypal']['created'] = date('Y-m-d h:i:s');
                $this->request->data['Paypal']['paypal_api_password'] = $this->request->data['Paypal']['password'];
                if ($this->Paypal->save($this->request->data)) {
                    $this->Session->setFlash(__('The paypal has been saved', true));
                    $this->redirect(array('action' => 'index'));
                } else {
                    $this->Session->setFlash(__('The paypal could not be saved. Please, try again.', true));
                }
            }
        }
    }

    function admin_edit($id = null)
    {
        if (!$id && empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid Paypal', true));
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->request->data)) {
            $this->request->data['Paypal']['created'] = date('Y-m-d h:i:s');
            if ($this->Paypal->save($this->request->data)) {
                $this->Session->setFlash(__('The paypal has been updated', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The paypal could not be updated. Please, try again.', true));
            }
        }
        if (empty($this->request->data)) {
            $this->request->data = $this->Paypal->read(null, $id);
        }
    }

    function admin_stripeedit($id = null)
    {
        if (!$id && empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid Stripe', true));
            $this->redirect(array('action' => 'stripeindex'));
        }
        if (!empty($this->request->data)) {
            $this->request->data['Stripe']['created'] = date('Y-m-d h:i:s');
            if ($this->Stripe->save($this->request->data)) {
                $this->Session->setFlash(__('The stripe has been updated', true));
                $this->redirect(array('action' => 'stripeindex'));
            } else {
                $this->Session->setFlash(__('The stripe could not be updated. Please, try again.', true));
            }
        }
        if (empty($this->request->data)) {
            $this->request->data = $this->Stripe->find('first', array('conditions' => array('Stripe.id' => $id)));
        }
    }

    function admin_delete($id = null)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Paypal', true));
            $this->redirect(array('action' => 'index'));
        }
        if ($this->Paypal->delete($id)) {
            $this->Session->setFlash(__('Paypal deleted', true));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Paypal was not deleted', true));
        $this->redirect(array('action' => 'index'));
    }

    /* db config code */
    function admin_dbconfig()
    {
        $this->Dbconfig->recursive = 0;
        $this->set('dbdata', $this->paginate('Dbconfig'));
    }

    function admin_dbconfigview($id = null)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid Dbconfig', true));
            $this->redirect(array('action' => 'dbconfig'));
        }
        $this->set('dbconfig', $this->Dbconfig->read(null, $id));
    }

    function admin_dbconfigadd()
    {
        if (!empty($this->request->data)) {
            $this->request->data['Dbconfig']['dbname'] = $this->request->data['Paypal']['dbname'];
            $this->request->data['Dbconfig']['dbusername'] = $this->request->data['Paypal']['dbusername'];
            $this->request->data['Dbconfig']['dbpassword'] = $this->request->data['Paypal']['password'];
            $this->request->data['Dbconfig']['created'] = date('Y-m-d');
            if ($this->Dbconfig->save($this->request->data)) {
                $this->Session->setFlash(__('The Dbconfig has been saved', true));
                $this->redirect(array('action' => 'dbconfig'));
            } else {
                $this->Session->setFlash(__('The Dbconfig could not be saved. Please, try again.', true));
            }
        }
    }

    function admin_dbconfigedit($id = null)
    {
        $this->set('id', $id);
        if (!$id && empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid Id for Dbconfig', true));
            $this->redirect(array('action' => 'dbconfig'));
        }
        if (!empty($this->request->data)) {
            if (!empty($this->request->data['Dbconfig']['id'])) {
                $this->request->data['Dbconfig']['id'] = $this->request->data['Dbconfig']['id'];
                $this->request->data['Dbconfig']['dbname'] = $this->request->data['Dbconfig']['dbname'];
                $this->request->data['Dbconfig']['dbusername'] = $this->request->data['Dbconfig']['dbusername'];
                $this->request->data['Dbconfig']['dbpassword'] = $this->request->data['Dbconfig']['dbpassword'];
                $this->request->data['Dbconfig']['created'] = date('Y-m-d');
                if ($this->Dbconfig->save($this->request->data)) {
                    $this->Session->setFlash(__('The Dbconfig has been updated', true));
                    $this->redirect(array('action' => 'dbconfig'));
                } else {
                    $this->Session->setFlash(__('The Dbconfig could not be updated. Please, try again.', true));
                }
            }
        }
        if (empty($this->request->data)) {
            $this->request->data = $this->Dbconfig->read(null, $id);
        }
    }

    function admin_dbconfigdelete($id = null)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Dbconfig', true));
            $this->redirect(array('action' => 'dbconfig'));
        }
        if ($this->Dbconfig->delete($id)) {
            $this->Session->setFlash(__('DbConfig deleted', true));
            $this->redirect(array('action' => 'dbconfig'));
        }
        $this->Session->setFlash(__('Dbconfig was not deleted', true));
        $this->redirect(array('action' => 'dbconfig'));
    }
} ?>