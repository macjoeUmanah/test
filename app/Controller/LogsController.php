<?php

class LogsController extends AppController
{
    var $name = 'Logs';
    var $components = array('Cookie', 'Twilio');


    function index($type = 'smsinbox')
    {
        $this->layout = 'admin_new_layout';
        $this->Log->recursive = -1;
        $user_id = $this->getLoggedInUserId();

        $this->paginate = array('limit' => 50);

        if ($type == 'smsinbox') {
            
            $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'text'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);

            $data = $this->paginate('Log');
            $this->set('logs', $data);
            //$smsinbox = $this->Log->find('list',array('conditions' => array('Log.user_id' =>$user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'text'),'order' =>array('Log.id' => 'desc')));

            $this->Session->write('logs', $smsinbox);
        } else if ($type == 'smsoutbox' || $type == 'singlesmsoutbox' || $type == 'groupsmsoutbox') {
            if ($type == 'groupsmsoutbox') {
                app::import('Model', 'GroupSmsBlast');
                $this->GroupSmsBlast = new GroupSmsBlast();
                $this->paginate = array('conditions' => array('GroupSmsBlast.user_id' => $user_id), 'order' => array('GroupSmsBlast.id' => 'desc'), 'limit' => 50);
                $data = $this->paginate('GroupSmsBlast');
                $this->set('logs', $data);
            } else {
                $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.group_id' => 0, 'Log.route' => 'outbox', 'Log.text_message !=' => ''), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
                $data = $this->paginate('Log');
                $this->set('logs', $data);
                //$smsoutbox = $this->Log->find('all',array('conditions' => array('Log.user_id' =>$user_id,'Log.group_id' =>0,'Log.route' => 'outbox', 'Log.text_message !=' => ''),'order' =>array('Log.id' => 'desc')));
                $this->Session->write('logs', $smsoutbox);
            }
        } else if ($type == 'voice') {
            $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'voice'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
            $data = $this->paginate('Log');
            $this->set('logs', $data);
            //$voice = $this->Log->find('all',array('conditions' => array('Log.user_id' =>$user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'voice'),'order' =>array('Log.id' => 'desc')));
            $this->Session->write('logs', $voice);
        } else if ($type == 'broadcast') {
            $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'broadcast'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
            $data = $this->paginate('Log');
            $this->set('logs', $data);
            //$voice = $this->Log->find('all',array('conditions' => array('Log.user_id' =>$user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'broadcast'),'order' =>array('Log.id' => 'desc')));
            //$this->Session->write('logs', $voice);
            //$this->set('logs', $voice);
        } else if ($type == 'callforward') {
            $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'callforward'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
            $data = $this->paginate('Log');
            $this->set('logs', $data);
        } else if ($type == 'faxinbox') {
            $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'fax'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
            $data = $this->paginate('Log');
            $this->set('logs', $data);
        } else if ($type == 'faxoutbox') {
            $this->paginate = array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'fax'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
            $data = $this->paginate('Log');
            $this->set('logs', $data);
        }
        $this->set('type', $type);
    }

    function view($id = null)
    {
        $this->layout = 'popup';
        if (!$id) {
            $this->Session->setFlash(__('Invalid log', true));
            $this->redirect(array('action' => 'index'));
        }
        $this->set('log', $this->Log->read(null, $id));
        $this->Log->saveField('read', 1);
    }

    function errormessage($id = null)
    {
        $this->layout = 'popup';
        if (!$id) {
            $this->Session->setFlash(__('Invalid log', true));
            $this->redirect(array('action' => 'index'));
        }
        $this->set('log', $this->Log->read(null, $id));
        $this->Log->saveField('read', 1);
    }

    function add()
    {
        $this->layout = 'admin_new_layout';
        if (!empty($this->request->data)) {
            $this->Log->create();
            if ($this->Log->save($this->request->data)) {
                $this->Session->setFlash(__('The log has been saved', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The log could not be saved. Please, try again.', true));
            }
        }
        $users = $this->Log->User->find('list');
        $this->set(compact('users'));
    }

    function edit($id = null)
    {
        if (!$id && empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid log', true));
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->request->data)) {
            if ($this->Log->save($this->request->data)) {
                $this->Session->setFlash(__('The log has been saved', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The log could not be saved. Please, try again.', true));
            }
        }
        if (empty($this->request->data)) {
            $this->request->data = $this->Log->read(null, $id);
        }
        $users = $this->Log->User->find('list');
        $this->set(compact('users'));
    }

    function delete($id, $section)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for log', true));
            $this->redirect(array('action' => 'index'));
        }
        
        if(trim($section)=='faxinbox' || trim($section)=='faxoutbox'){
            $imageurl = $this->Log->read(null, $id);
        }
        
        if ($this->Log->delete($id)) {
            if(trim($section)=='faxinbox' || trim($section)=='faxoutbox'){
                $filename = substr($imageurl['Log']['voice_url'],strpos($imageurl['Log']['voice_url'], '/fax/')+5);
                $path = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['SCRIPT_NAME']).'/fax/'.$filename;
                if (!is_writable($path)){
                    chmod($path, 0755);
                }
                unlink($path);
            }
            $this->Session->setFlash(__('Log deleted', true));
            $this->redirect(array('action' => 'index', $section));
        }
        $this->Session->setFlash(__('Log was not deleted', true));
        $this->redirect(array('action' => 'index'));
    }

    function grouplogdelete($id)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for log', true));
            $this->redirect(array('action' => 'index'));
        }
        app::import('Model', 'GroupSmsBlast');
        $this->GroupSmsBlast = new GroupSmsBlast();
        if ($this->GroupSmsBlast->delete($id)) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.group_sms_id' => $id));
            $this->Session->setFlash(__('Group SMS deleted', true));
            $this->redirect(array('action' => 'index/groupsmsoutbox'));
        }
    }

    function sentstatistics($id = null)
    {
        $this->Log->recursive = -1;
        $this->layout = 'admin_new_layout';
        app::import('Model', 'GroupSmsBlast');
        $this->GroupSmsBlast = new GroupSmsBlast();
        $groupContact = $this->GroupSmsBlast->find('first', array('conditions' => array('GroupSmsBlast.id' => $id)));
        $this->set('groupContacts', $groupContact);
        //$this->paginate = array('conditions' => array('Log.group_sms_id' => $id, 'Log.route' => 'outbox', 'Log.text_message !=' => ''), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
        $this->paginate = array('conditions' => array('Log.group_sms_id' => $id, 'Log.route' => 'outbox'), 'order' => array('Log.id' => 'desc'), 'limit' => 50);
        $data = $this->paginate('Log');
        $this->set('logs', $data);
        //$smsoutbox = $this->Log->find('all',array('conditions' => array('Log.group_sms_id' =>$id,'Log.route' => 'outbox', 'Log.text_message !=' => ''),'order' =>array('Log.id' => 'desc')));
        $this->Session->write('logs', $smsoutbox);
    }

    function unsubscribe($id = null)
    {
        $this->autoRender = false;
        app::import('Model', 'Log');
        $this->Log = new Log();
        $this->Log->recursive = -1;
        $deletefaileds = $this->Log->find('all', array('conditions' => array('Log.group_sms_id' => $id, 'Log.sms_status =' => 'failed')));
        foreach ($deletefaileds as $deletefailed) {
            app::import('Model', 'Contact');
            $this->Contact = new Contact();
            $Contacts = $this->Contact->find('all', array('conditions' => array('Contact.phone_number' => $deletefailed['Log']['phone_number'])));
            foreach ($Contacts as $Contact) {
                $contact_id = $Contact['Contact']['id'];
                if ($this->Contact->delete($contact_id)) {
                    app::import('Model', 'ContactGroup');
                    $this->ContactGroup = new ContactGroup();
                    $Contactgroups = $this->ContactGroup->find('first', array('conditions' => array('ContactGroup.contact_id' => $contact_id)));
                    app::import('Model', 'ContactGroup');
                    $this->ContactGroup = new ContactGroup();
                    $group_id = $Contactgroups['ContactGroup']['group_id'];
                    app::import('Model', 'Group');
                    $this->Group = new Group();
                    $Group = $this->Group->find('first', array('conditions' => array('Group.id' => $group_id)));
                    $this->request->data['Group']['id'] = $group_id;
                    $this->request->data['Group']['totalsubscriber'] = $Group['Group']['totalsubscriber'] - 1;
                    $this->Group->save($this->request->data);
                    $this->ContactGroup->deleteAll(array('ContactGroup.contact_id' => $contact_id));
                }
            }
        }
        app::import('Model', 'GroupSmsBlast');
        $this->GroupSmsBlast = new GroupSmsBlast();
        $GroupSmsBlastid = $this->GroupSmsBlast->find('first', array('conditions' => array('GroupSmsBlast.id' => $id)));
        $GroupSmsBlastid['GroupSmsBlast']['id'];
        $this->request->data['GroupSmsBlast']['isdeleted'] = 1;
        $this->request->data['GroupSmsBlast']['id'] = $GroupSmsBlastid['GroupSmsBlast']['id'];
        $this->GroupSmsBlast->save($this->request->data);
        $this->Session->setFlash(__('Contacts deleted', true));
        $this->redirect(array('action' => 'sentstatistics/' . $id));
    }

    function deleteall($id = null, $type = null)
    {
        $this->autoRender = false;
        $user_id = $this->Session->read('User.id');
        $year = date('Y');
        if ($id == 1) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'text', 'Log.group_id' => 0, 'YEAR(Log.created) !=' => $year));
            $this->Session->setFlash(__('Inbox logs deleted', true));
            $this->redirect(array('action' => 'index/smsinbox'));
        } else if ($id == 2) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'text', 'Log.group_id' => 0, 'YEAR(Log.created) !=' => $year));
            $this->Session->setFlash(__('Outbox logs deleted', true));
            $this->redirect(array('action' => 'index/smsoutbox'));
        } else if ($id == 3) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'text', 'Log.group_id' => 0, 'YEAR(Log.created) !=' => $year));
            $this->Session->setFlash(__('Outbox logs deleted', true));
            $this->redirect(array('action' => 'index/singlesmsoutbox'));
        } else if ($id == 4) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'voice', 'Log.group_id' => 0));
            $this->Session->setFlash(__('Voice logs deleted', true));
            $this->redirect(array('action' => 'index/voice'));
        } else if ($id == 5) {
            app::import('Model', 'GroupSmsBlast');
            $this->GroupSmsBlast = new GroupSmsBlast();
            $this->GroupSmsBlast->deleteAll(array('GroupSmsBlast.user_id' => $user_id));
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'text', 'Log.group_id !=' => 0, 'YEAR(Log.created) !=' => $year));
            $this->Session->setFlash(__('Group sms logs deleted', true));
            $this->redirect(array('action' => 'index/groupsmsoutbox'));
        } else if ($id == 6) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'broadcast'));
            $this->Session->setFlash(__('Voice Broadcast logs deleted', true));
            $this->redirect(array('action' => 'index/broadcast'));
        } else if ($id == 7) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'callforward', 'Log.group_id' => 0));
            $this->Session->setFlash(__('Voice call forward logs deleted', true));
            $this->redirect(array('action' => 'index/callforward'));
        } else if ($id == 8) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'fax' ));
            $this->Session->setFlash(__('Fax inbox logs deleted', true));
            $this->redirect(array('action' => 'index/faxinbox'));
        } else if ($id == 9) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->deleteAll(array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'fax'));
            $this->Session->setFlash(__('Fax outbox logs deleted', true));
            $this->redirect(array('action' => 'index/faxoutbox'));
        }
    }

    function export($type = 'smsinbox', $groupsmsid = null)
    {
        $this->autoRender = false;
        $user_id = $this->Session->read('User.id');
        $this->Log->recursive = -1;
        ini_set('max_execution_time', 600); //increase max_execution_time to 10 min if data set is very large
        if ($type == 'smsinbox') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'text'), 'fields' => array('Log.created', 'Log.phone_number', 'Log.email_to_sms_number', 'Log.text_message', 'Log.voice_url', 'Log.call_duration', 'Log.route', 'Log.msg_type', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'smsoutbox' || $type == 'singlesmsoutbox') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.text_message !=' => '', 'Log.group_id' => 0), 'fields' => array('Log.created', 'Log.phone_number', 'Log.text_message', 'Log.voice_url', 'Log.call_duration', 'Log.route', 'Log.msg_type', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'groupsmsoutbox') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'text', 'Log.group_sms_id' => $groupsmsid), 'fields' => array('Log.created', 'Log.phone_number', 'Log.text_message', 'Log.sendfrom', 'Log.error_message', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'voice') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'voice'), 'fields' => array('Log.created', 'Log.phone_number', 'Log.text_message', 'Log.voice_url', 'Log.call_duration', 'Log.route', 'Log.msg_type', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'broadcast') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'broadcast'), 'fields' => array('Log.created', 'Log.phone_number', 'Log.text_message', 'Log.voice_url', 'Log.call_duration', 'Log.route', 'Log.msg_type', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'callforward') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'callforward'), 'fields' => array('Log.created', 'Log.phone_number', 'Log.text_message', 'Log.voice_url', 'Log.call_duration', 'Log.route', 'Log.msg_type', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'faxinbox') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'inbox', 'Log.msg_type' => 'fax'), 'fields' => array('Log.created', 'Log.phone_number', 'Log.inbox_type', 'Log.call_duration', 'Log.voice_url'), 'order' => array('Log.id' => 'desc')));
        } else if ($type == 'faxoutbox') {
            $logs = $this->Log->find('all', array('conditions' => array('Log.user_id' => $user_id, 'Log.route' => 'outbox', 'Log.msg_type' => 'fax'), 'fields' => array('Log.created', 'Log.phone_number', 'Log.inbox_type', 'Log.call_duration', 'Log.voice_url', 'Log.sms_status'), 'order' => array('Log.id' => 'desc')));
        }
        $filename = "logs" . date("Y.m.d") . ".csv";
        $csv_file = fopen('php://output', 'w');
        header('Content-type: application/csv');
        //header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        if ($type == 'smsinbox') {
            $header_row = array("Received On", "From Number", "To Number", "Message");
            fputcsv($csv_file, $header_row, ',', '"');
            foreach ($logs as $result) {
                // Array indexes correspond to the field names in your db table(s)
                $row = array($result['Log']['created'], $result['Log']['phone_number'], $result['Log']['email_to_sms_number'], $result['Log']['text_message']);
                fputcsv($csv_file, $row, ',', '"');
            }
        }elseif ($type == 'faxinbox') {
            $header_row = array("Fax Received On", "From Number", "Pages", "Fax Duration", "Fax URL");
            fputcsv($csv_file, $header_row, ',', '"');
            foreach ($logs as $result) {
                // Array indexes correspond to the field names in your db table(s)
                $row = array($result['Log']['created'], $result['Log']['phone_number'], $result['Log']['inbox_type'], $result['Log']['call_duration'], $result['Log']['voice_url']);
                fputcsv($csv_file, $row, ',', '"');
            }
        }elseif ($type == 'faxoutbox') {
            $header_row = array("Fax Sent On", "To Number", "Pages", "Fax Duration", "Fax URL", "Status");
            fputcsv($csv_file, $header_row, ',', '"');
            foreach ($logs as $result) {
                // Array indexes correspond to the field names in your db table(s)
                $row = array($result['Log']['created'], $result['Log']['phone_number'], $result['Log']['inbox_type'], $result['Log']['call_duration'], $result['Log']['voice_url'], $result['Log']['sms_status']);
                fputcsv($csv_file, $row, ',', '"');
            }
        }elseif ($type != 'groupsmsoutbox') {
            $header_row = array("Phone Number", "Message", "Voice Url", "Call Duration", "Route", "Msg Type", "Status", "Created");
            fputcsv($csv_file, $header_row, ',', '"');
            foreach ($logs as $result) {
                // Array indexes correspond to the field names in your db table(s)
                $row = array($result['Log']['phone_number'], $result['Log']['text_message'], $result['Log']['voice_url'], $result['Log']['call_duration'], $result['Log']['route'], $result['Log']['msg_type'], $result['Log']['sms_status'], $result['Log']['created']);
                fputcsv($csv_file, $row, ',', '"');
            }
        } else {
            $header_row = array("Sent On", "Sent From", "Sent To", "Message", "Status", "Error Message");
            fputcsv($csv_file, $header_row, ',', '"');
            foreach ($logs as $result) {
                // Array indexes correspond to the field names in your db table(s)
                $row = array($result['Log']['created'], $result['Log']['sendfrom'], $result['Log']['phone_number'], $result['Log']['text_message'], $result['Log']['sms_status'], $result['Log']['error_message']);
                fputcsv($csv_file, $row, ',', '"');
            }
        }
        fclose($csv_file);
    }

    function refreshstatistics($id = null)
    {
        /* Sometimes Twilio doesn't send the updated status back, so there are some records that are stuck at 'sent' when in fact, they have been
        'delivered' or 'undelivered'. This function calls Twilio for the updated statuses so our logs match that of Twilio's logs. */

        app::import('Model', 'Log');
        $this->Log = new Log();
        $this->Log->recursive = -1;
        $smsoutbox = $this->Log->find('all', array('conditions' => array('Log.group_sms_id' => $id, 'Log.route' => 'outbox'), 'fields' => array('Log.sms_id', 'Log.sms_status', 'Log.id'), 'order' => array('Log.id' => 'desc')));
        app::import('Model', 'GroupSmsBlast');
        $this->GroupSmsBlast = new GroupSmsBlast();
        $GroupSmsBlast = $this->GroupSmsBlast->find('first', array('conditions' => array('GroupSmsBlast.id' => $id)));

        $successcredits = 0;
        $failedcredits = 0;
        if (!empty($smsoutbox)) {
            $this->Twilio->AccountSid = TWILIO_ACCOUNTSID;
            $this->Twilio->AuthToken = TWILIO_AUTH_TOKEN;
            foreach ($smsoutbox as $logrecord) {
                $smsid = $logrecord['Log']['sms_id'];
                $logstatus = $logrecord['Log']['sms_status'];
                if (!empty($smsid)) {

                    $response = $this->Twilio->getstatus($smsid);

                    $status = $response->ResponseXml->Message->Status;
                    $this->request->data['Log']['sms_status'] = $status;
                    $this->request->data['Log']['error_message'] = $response->ResponseXml->Message->ErrorMessage;
                    $this->request->data['Log']['id'] = $logrecord['Log']['id'];
                    $this->request->data['Log']['group_sms_id'] = $id;
                    $this->request->data['Log']['sms_id'] = $logrecord['Log']['sms_id'];

                    $this->Log->save($this->request->data);

                    /*if (trim($status) == 'undelivered' && trim($logstatus) == 'sent'){
                          app::import('Model','User');
              $this->User = new User();
                          $user_id=$this->Session->read('User.id');
              $usersms = $this->User->find('first',array('conditions' =>array('User.id'=>$user_id)));

                          $body = $response->ResponseXml->Message->Body;
                          $length = strlen(utf8_decode(substr($body,0,1600)));

                                $msgtype = substr($smsid,0,2);

                                if ($msgtype == "SM"){
                                   if (strlen($body) != strlen(utf8_decode($body))){
                               $credits = ceil($length/70);
                                   }else{
                                       $credits = ceil($length/160);
                                   }
                                }else{
                                       $credits = 2;
                                }
              $this->request->data['User']['sms_balance']=$usersms['User']['sms_balance']+$credits;
              $this->request->data['User']['id']=$usersms['User']['id'];
              $this->User->save($this->request->data);
                       }*/

                    if (trim($status) == 'sent' || trim($status) == 'delivered') {
                        $successcredits = $successcredits + 1;
                    } else {
                        $failedcredits = $failedcredits + 1;
                    }


                } else if (trim($logstatus) == 'failed') {
                    $failedcredits = $failedcredits + 1;
                }

            }
            $this->request->data['GroupSmsBlast']['total_successful_messages'] = $successcredits;
            $this->request->data['GroupSmsBlast']['total_failed_messages'] = $failedcredits;
            $this->request->data['GroupSmsBlast']['id'] = $GroupSmsBlast['GroupSmsBlast']['id'];
            $this->GroupSmsBlast->save($this->request->data);

            $this->Session->setFlash(__('Statistics have been refreshed for this group', true));
        } else {
            $this->Session->setFlash(__('There are no logs to refresh for this group', true));
        }

        //$this->redirect(array('action'=>'index/groupsmsoutbox'));
        $this->redirect(array('action' => 'sentstatistics/' . $id));
//		$this->Session->write('logs', $smsoutbox);
    }
    
    function viewfax($id=null,$box=null)
    {
        $this->layout = 'admin_new_layout';
        app::import('Model', 'Log');
        $this->Log = new Log();
        $this->Log->recursive = -1;
        $log = $this->Log->find('first', array('conditions' => array('Log.id' => $id), 'fields' => array('Log.voice_url')));
        $this->set('faxurl', $log['Log']['voice_url']);
        $this->set('fax', $box);
    }
}
