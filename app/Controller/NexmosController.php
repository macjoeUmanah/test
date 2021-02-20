<?php
App::import('Vendor', 'mailchimp', array('file' => 'mailchimp/MailChimp.php'));
App::import('Vendor', 'getresponse', array('file' => 'getresponse/GetResponse.php'));
App::import('Vendor', 'activecampaign', array('file' => 'activecampaign/ActiveCampaign.class.php'));
App::import('Vendor', 'aweber', array('file' => 'aweber/aweber_api.php'));
App::import('Vendor', 'mailin', array('file' => 'mailin/Mailin.php'));
App::uses('CakeEmail', 'Network/Email');

class NexmosController extends AppController
{
    var $uses = 'Users';
    var $components = array('Nexmo', 'Nexmomessage');
   
    function searchcountry()
    {
        //$this->layout= 'admin_new_layout';
        $this->layout = 'popup';
    }

    function pattrenbuynumber()
    {
        $this->layout = 'popup';
        $country = $_REQUEST['country'];
        $feature = $_REQUEST['feature'];
        $this->set('feature', $feature);
        $this->set('country', $country);
    }

    function searchnumber()
    {
        $this->layout = 'popup';
        $api_key = NEXMO_KEY;
        $api_secret = NEXMO_SECRET;
        //$country=$_REQUEST['country'];
        $country_code = $_REQUEST['country'];
        $feature = $_REQUEST['feature'];
        $pattern = $_REQUEST['pattren'];
        $response = $this->Nexmo->listNumbers($api_key, $api_secret, $country_code, $pattern, $feature);
        if ($response->count == 0) {
            $this->Session->setFlash(__('We did not find any phone numbers by that search', true));
        } else {
            $this->set('AvailablePhoneNumbers', $response);
            $this->set('country_code', $country_code);
        }
    }

    function assignthisnumber()
    {
        $this->autoRender = false;
        $api_key = NEXMO_KEY;
        $api_secret = NEXMO_SECRET;
        $country_code = $_REQUEST['country'];
        $PhoneNumber = $_REQUEST['number'];
        $siteurl = SITE_URL;
        $response = $this->Nexmo->assignthisnumber($country_code, $PhoneNumber, $api_key, $api_secret);
        $sucess_response = $response->{'error-code'};
        
        if ($sucess_response == 200) {
            $api_key = NEXMO_KEY;
            $api_secret = NEXMO_SECRET;
            $this->Nexmo->updatenumber($country_code, $PhoneNumber, $siteurl, $api_key, $api_secret);
            Controller::loadModel('User');
            $user_id = $this->Session->read('User.id');
            $someone = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
            if ($someone['User']['assigned_number'] == 0) {
                $this->User->id = $this->Session->read('User.id');
                $this->User->saveField('assigned_number', $_REQUEST['number']);
                $this->User->saveField('country_code', $_REQUEST['country']);
                if ($_REQUEST['voice'] == 'SMS') {
                    $this->User->saveField('sms', 1);
                }
                if ($_REQUEST['voice'] == 'VOICE') {
                    $this->User->saveField('voice', 1);
                }
                if ($_REQUEST['sms'] == 'SMS') {
                    $this->User->saveField('sms', 1);
                } else if ($_REQUEST['sms'] == 'VOICE') {
                    $this->User->saveField('voice', 1);
                }
                $this->User->saveField('number_limit_count', 1);
                echo 'sucess';
            } else {
                app::import('Model', 'UserNumber');
                $this->UserNumber = new UserNumber();
                $arr['UserNumber']['user_id'] = $user_id;
                $arr['UserNumber']['number'] = $_REQUEST['number'];
                $arr['UserNumber']['country_code'] = $_REQUEST['country'];
                if ($_REQUEST['voice'] == 'SMS') {
                    $arr['UserNumber']['sms'] = 1;
                } else if ($_REQUEST['voice'] == 'VOICE') {
                    $arr['UserNumber']['voice'] = 1;
                }
                if ($_REQUEST['sms'] == 'SMS') {
                    $arr['UserNumber']['sms'] = 1;
                } else if ($_REQUEST['sms'] == 'VOICE') {
                    $arr['UserNumber']['voice'] = 1;
                }
                $arr['UserNumber']['api_type'] = API_TYPE;
                $this->UserNumber->save($arr);
                if (!empty($someone)) {
                    $arr_number['User']['id'] = $user_id;
                    $arr_number['User']['number_limit_count'] = $someone['User']['number_limit_count'] + 1;
                    $this->User->save($arr_number);
                }
                echo 'sucess';
            }
        } else {
            echo 'error';
            $errorcode = $response->{'error-code-label'};
            $this->Session->setFlash(__($errorcode, true));
        }
        
        
    }

    function sendsms($id = null)
    {
        $this->autoRender = false;
        $userDetails = $this->getLoggedUserDetails();
        $this->Nexmomessage->Key = NEXMO_KEY;
        $this->Nexmomessage->Secret = NEXMO_SECRET;
        if ($userDetails['User']['sms_balance'] > 0) {
            $to = ($this->request->data['nexmos']['phone_number']) ? $this->request->data['nexmos']['phone_number'] : $this->request->data['nexmos']['phone'];
            if (!empty($userDetails)) {
                if ($userDetails['User']['sms'] == 1) {
                    $from = $userDetails['User']['assigned_number'];
                } else {
                    app::import('Model', 'UserNumber');
                    $this->UserNumber = new UserNumber();
                    $user_numbers = $this->UserNumber->find('first', array('conditions' => array('UserNumber.user_id' => $userDetails['User']['id'], 'UserNumber.sms' => 1)));
                    if (!empty($user_numbers)) {
                        $from = $user_numbers['UserNumber']['number'];
                    } else {
                        $from = $userDetails['User']['assigned_number'];
                    }
                }
            }

            app::import('Model', 'Contact');
            $this->Contact = new Contact();
            $contact = $this->Contact->find('first', array('conditions' => array('Contact.user_id' => $userDetails['User']['id'], 'Contact.phone_number' => $to)));

            $stickyfrom = $contact['Contact']['stickysender'];
            if ($stickyfrom != 0) {
                $from = $stickyfrom;
            }

            $body = $this->request->data['nexmos']['message'];
            $response = $this->Nexmomessage->sendsms($to, $from, $body);
            foreach ($response->messages as $doc) {
                $message_id = $doc->messageid;
                if ($message_id != '') {
                    $status = $doc->status;
                    $message_id = $doc->messageid;
                } else {
                    $status = $doc->status;
                    $errortext = $doc->errortext;
                }
            }
            //saving logs
            Controller::loadModel('Log');
            $this->Log->create();
            $this->request->data['Log']['sms_id'] = $message_id;
            $this->request->data['Log']['user_id'] = $this->Session->read('User.id');
            $this->request->data['Log']['phone_number'] = $to;
            $this->request->data['Log']['text_message'] = $body;
            $this->request->data['Log']['route'] = 'outbox';
            if ($status != 0) {
                $this->request->data['Log']['sms_status'] = 'failed';
                //$ErrorMessage = $response->ErrorMessage;
                $this->request->data['Log']['error_message'] = $errortext;
            } else {
                $this->request->data['Log']['sms_status'] = 'sent';
            }
            $this->Log->save($this->request->data);
            if ($status != 0) {
                $this->Session->setFlash(__($errortext, true));
                if (!empty($id)) {
                    $this->redirect(array('controller' => 'groups'));
                } else {
                    $this->redirect(array('controller' => 'contacts'));
                }
            } else if ($message_id != '') {
                Controller::loadModel('User');
                $this->User->id = $this->Session->read('User.id');
                if ($this->User->id != '') {

                    //$curcredits = $userDetails['User']['sms_balance'];
                    $length = strlen(utf8_decode(substr($body, 0, 160)));
                    if (strlen($body) != strlen(utf8_decode($body))) {
                        $credits = ceil($length / 70);
                    } else {
                        $credits = ceil($length / 160);
                    }

                    //$this->User->saveField('sms_balance', ($userDetails['User']['sms_balance']-1));
                    $this->User->saveField('sms_balance', ($userDetails['User']['sms_balance'] - $credits));
                    app::import('Model', 'User');
                    $this->User = new User();
                    $usersbalance = $this->User->find('first', array('conditions' => array('User.id' => $userDetails['User']['id'])));
                    if ($usersbalance['User']['email_alert_credit_options'] == 0) {
                        if ($usersbalance['User']['sms_balance'] <= $usersbalance['User']['low_sms_balances']) {
                            if ($usersbalance['User']['sms_credit_balance_email_alerts'] == 0) {
                                $username = $usersbalance['User']['username'];
                                $email = $usersbalance['User']['email'];
                                //echo $phone = $usersmail['User']['assigned_number'];
                                $subject = "Low SMS Credit Balance";
                                $sitename = str_replace(' ', '', SITENAME);
                                /*$this->Email->to = $email;
                                $this->Email->subject = $subject;
                                $this->Email->from = $sitename;
                                $this->Email->template = 'low_sms_credit_template';
                                $this->Email->sendAs = 'html';
                                $this->Email->Controller->set('username', $username);
                                $this->Email->Controller->set('low_sms_balances', $usersbalance['User']['low_sms_balances']);
                                $this->Email->send();*/
                                
                                $Email = new CakeEmail();
                                if(EMAILSMTP==1){
                                    $Email->config('smtp');
                                }
                                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                                $Email->to($email);
                                $Email->subject($subject);
                                $Email->template('low_sms_credit_template');
                                $Email->emailFormat('html');
                                $Email->viewVars(array('username' => $username));
                                $Email->viewVars(array('low_sms_balances' => $usersbalance['User']['low_sms_balances']));
                                $Email->send();

                                $this->User->id = $usersbalance['User']['id'];
                                $this->User->saveField('sms_credit_balance_email_alerts', 1);
                            }
                        }
                    }
                }
                $this->Session->setFlash(__('SMS message sent', true));
                if (!empty($id)) {
                    $this->redirect(array('controller' => 'groups'));
                } else {
                    $this->redirect(array('controller' => 'contacts'));
                }
            }
        } else {
            $this->Session->setFlash(__('SMS Balance too low.', true));
        }
    }

    function sms()
    {
        ob_start();
        /*print_r($_REQUEST);
		$out1 = ob_get_contents();
		ob_end_clean();
		$file = fopen("debug/nexmossms".time().".txt", "w");
		fwrite($file, $out1);
		fclose($file);*/
        $this->autoRender = false;
        $_REQUEST['To'] = str_replace('+', '', $_REQUEST['to']);
        $_REQUEST['From'] = str_replace('+', '', $_REQUEST['msisdn']);
        $fromnumber = str_replace('+', '', $_REQUEST['to']);
        $phone = $_REQUEST['From'];

        $_REQUEST['text'] = trim($_REQUEST['text']);

        app::import('Model', 'User');
        $this->User = new User();
        $someone = $this->User->find('first', array('conditions' => array('assigned_number' => '' . trim($_REQUEST['To']) . '')));
        if (empty($someone)) {
            app::import('Model', 'UserNumber');
            $this->UserNumber = new UserNumber();
            $someone = $this->UserNumber->find('first', array('conditions' => array('UserNumber.number' => '' . trim($_REQUEST['To']) . '')));
        }

        $sms_balance = $someone['User']['sms_balance'];
        $active = $someone['User']['active'];

        if (($active == 0 || $sms_balance < 1) && strtoupper(trim($_REQUEST['text'])) != 'STOP') {
            exit;
        }

        $user_id = $someone['User']['id'];
        app::import('Model', 'Group');
        $this->Group = new Group();
        $group = $this->Group->find('first', array('conditions' => array('Group.keyword' => $_REQUEST['text'], array('Group.user_id' => $someone['User']['id']))));

        app::import('Model', 'ContactGroup');
        $this->ContactGroup = new ContactGroup();
        $contact = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.question_id >' => 0, 'ContactGroup.user_id' => $user_id), 'order' => array('ContactGroup.id' => 'desc')));
        $question_id = $contact['ContactGroup']['question_id'];

        //app::import('Model','ContactGroup');
        //$this->ContactGroup = new ContactGroup();
        //$contestid=$this->ContactGroup->find('first',array('conditions'=>array('Contact.phone_number'=>$phone,'ContactGroup.contest_id >'=>0,'ContactGroup.user_id'=> $user_id),'order' =>array('ContactGroup.id' => 'desc')));
        //$contest_id=$contestid['ContactGroup']['contest_id'];

        $contactname_arr = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.user_id' => $user_id, 'ContactGroup.un_subscribers !=' => 2), 'order' => array('ContactGroup.id' => 'desc')));
        $contactname = '';
        $contact_id = 0;
        if (!empty($contactname_arr)) {
            $contactname = $contactname_arr['Contact']['name'];
            $contact_id = $contactname_arr['Contact']['id'];
        }
        
        //****DOUBLE OPT-IN CHANGES****// 
        $contact_doubleoptin = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.user_id' => $user_id, 'ContactGroup.un_subscribers' => 2), 'order' => array('ContactGroup.id' => 'desc')));
        
        app::import('Model', 'Contest');
        $this->Contest = new Contest();
        //$contestkeywords = $this->Contest->find('first',array('conditions'=>array('Contest.id '=>$contest_id,'Contest.keyword'=>$_REQUEST['text'])));
        $contestkeywords = $this->Contest->find('first', array('conditions' => array('Contest.user_id' => $user_id, 'Contest.keyword' => $_REQUEST['text'])));
        $contest_id = $contestkeywords['Contest']['id'];

        app::import('Model', 'Option');
        $this->Option = new Option();
        $answers123 = $this->Option->find('first', array('conditions' => array('Option.question_id' => $question_id, 'Option.optionb' => $_REQUEST['text'])));

        app::import('Model', 'Smsloyalty');
        $this->Smsloyalty = new Smsloyalty();
        $smsloyalty_arr = $this->Smsloyalty->find('first', array('conditions' => array('Smsloyalty.user_id' => $user_id, 'Smsloyalty.coupancode' => strtoupper($_REQUEST['text']))));

        $timezone = $someone['User']['timezone'];
        date_default_timezone_set($timezone);

        app::import('Model', 'ActivityTimeline');
        $this->ActivityTimeline = new ActivityTimeline();

        app::import('Model', 'AppointmentSetting');
        $this->AppointmentSetting = new AppointmentSetting();
        $appointmentsetting = $this->AppointmentSetting->find('first', array('conditions' => array('AppointmentSetting.user_id' => $user_id)));

        $cancelkeyword = $appointmentsetting['AppointmentSetting']['cancel_keyword'];
        $confirmkeyword = $appointmentsetting['AppointmentSetting']['confirm_keyword'];
        $reschedulekeyword = $appointmentsetting['AppointmentSetting']['reschedule_keyword'];

        if (strtoupper(trim($confirmkeyword)) == strtoupper($_REQUEST['text'])) {

            app::import('Model', 'Appointment');
            $this->Appointment = new Appointment();
            $appointment = $this->Appointment->find('first', array('conditions' => array('Appointment.contact_id' => $contact_id, 'Appointment.user_id' => $user_id), 'order' => array('Appointment.id' => 'desc')));

            if (!empty($appointment)) {
                $confirmmessage = $appointmentsetting['AppointmentSetting']['confirm_message'];
                $confirmemailbody = $appointmentsetting['AppointmentSetting']['confirm_email_body'];
                $confirmemailsubject = $appointmentsetting['AppointmentSetting']['confirm_email_subject'];
                $confirmemailfrom = $appointmentsetting['AppointmentSetting']['confirm_email_from'];
                $confirmemailto = $appointmentsetting['AppointmentSetting']['confirm_email_to'];

                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $response = $this->Nexmomessage->sendsms($_REQUEST['From'], $_REQUEST['To'], $confirmmessage);

                $this->User->id = $someone['User']['id'];
                if ($this->User->id != '') {
                    $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - 1));
                }
                $this->smsmail($someone['User']['id']);

                $message_replace1 = str_replace('%%Name%%', $appointment['Contact']['name'], $confirmemailbody);
                $message_replace2 = str_replace('%%Email%%', $appointment['Contact']['email'], $message_replace1);
                $message_replace3 = str_replace('%%Number%%', $appointment['Contact']['phone_number'], $message_replace2);
                $apptdatetime = date('F j, Y \a\t g:i a', strtotime($appointment['Appointment']['app_date_time']));
                $message_replace4 = str_replace('%%ApptDate%%', $apptdatetime, $message_replace3);

                $sitename = str_replace(' ', '', SITENAME);
                /*$this->Email->to = $confirmemailto;
                $this->Email->subject = $confirmemailsubject;
                $this->Email->from = $sitename;
                $this->Email->sendAs = 'html';
                $this->Email->send($message_replace4);*/
                
                $Email = new CakeEmail();
                if(EMAILSMTP==1){
                    $Email->config('smtp');
                }
                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                $Email->to($confirmemailto);
                $Email->subject($confirmemailsubject);
                $Email->emailFormat('html');
                $Email->send($message_replace4);

                $this->Appointment->id = $appointment['Appointment']['id'];
                if ($this->Appointment->id != '') {
                    $this->Appointment->saveField('appointment_status', 1);
                }

                //*********** Save to activity timeline
                $timeline['ActivityTimeline']['user_id'] = $user_id;
                $timeline['ActivityTimeline']['contact_id'] = $appointment['Contact']['id'];
                $timeline['ActivityTimeline']['activity'] = 17;
                $timeline['ActivityTimeline']['title'] = 'Confirm Appointment';
                $timeline['ActivityTimeline']['description'] = $message_replace4;
                $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                $this->ActivityTimeline->save($timeline);
                //*************

                exit;
            }

        } elseif (strtoupper(trim($cancelkeyword)) == strtoupper($_REQUEST['text'])) {

            app::import('Model', 'Appointment');
            $this->Appointment = new Appointment();
            $appointment = $this->Appointment->find('first', array('conditions' => array('Appointment.contact_id' => $contact_id, 'Appointment.user_id' => $user_id), 'order' => array('Appointment.id' => 'desc')));

            if (!empty($appointment)) {
                $cancelmessage = $appointmentsetting['AppointmentSetting']['cancel_message'];
                $cancelemailbody = $appointmentsetting['AppointmentSetting']['cancel_email_body'];
                $cancelemailsubject = $appointmentsetting['AppointmentSetting']['cancel_email_subject'];
                $cancelemailfrom = $appointmentsetting['AppointmentSetting']['cancel_email_from'];
                $cancelemailto = $appointmentsetting['AppointmentSetting']['cancel_email_to'];

                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $response = $this->Nexmomessage->sendsms($_REQUEST['From'], $_REQUEST['To'], $cancelmessage);
                $this->User->id = $someone['User']['id'];
                if ($this->User->id != '') {
                    $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - 1));
                }
                $this->smsmail($someone['User']['id']);

                $message_replace1 = str_replace('%%Name%%', $appointment['Contact']['name'], $cancelemailbody);
                $message_replace2 = str_replace('%%Email%%', $appointment['Contact']['email'], $message_replace1);
                $message_replace3 = str_replace('%%Number%%', $appointment['Contact']['phone_number'], $message_replace2);
                $apptdatetime = date('F j, Y \a\t g:i a', strtotime($appointment['Appointment']['app_date_time']));
                $message_replace4 = str_replace('%%ApptDate%%', $apptdatetime, $message_replace3);

                $sitename = str_replace(' ', '', SITENAME);
                /*$this->Email->to = $cancelemailto;
                $this->Email->subject = $cancelemailsubject;
                $this->Email->from = $sitename;
                $this->Email->sendAs = 'html';
                $this->Email->send($message_replace4);*/
                
                $Email = new CakeEmail();
                if(EMAILSMTP==1){
                    $Email->config('smtp');
                }
                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                $Email->to($cancelemailto);
                $Email->subject($cancelemailsubject);
                $Email->emailFormat('html');
                $Email->send($message_replace4);

                $this->Appointment->id = $appointment['Appointment']['id'];
                if ($this->Appointment->id != '') {
                    $this->Appointment->saveField('appointment_status', 2);
                }

                //*********** Save to activity timeline
                $timeline['ActivityTimeline']['user_id'] = $user_id;
                $timeline['ActivityTimeline']['contact_id'] = $appointment['Contact']['id'];
                $timeline['ActivityTimeline']['activity'] = 18;
                $timeline['ActivityTimeline']['title'] = 'Cancel Appointment';
                $timeline['ActivityTimeline']['description'] = $message_replace4;
                $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                $this->ActivityTimeline->save($timeline);
                //*************

                exit;
            }

        } elseif (strtoupper(trim($reschedulekeyword)) == strtoupper($_REQUEST['text'])) {

            app::import('Model', 'Appointment');
            $this->Appointment = new Appointment();
            $appointment = $this->Appointment->find('first', array('conditions' => array('Appointment.contact_id' => $contact_id, 'Appointment.user_id' => $user_id), 'order' => array('Appointment.id' => 'desc')));

            if (!empty($appointment)) {
                $reschedulemessage = $appointmentsetting['AppointmentSetting']['reschedule_message'];
                $rescheduleemailbody = $appointmentsetting['AppointmentSetting']['reschedule_email_body'];
                $rescheduleemailsubject = $appointmentsetting['AppointmentSetting']['reschedule_email_subject'];
                $rescheduleemailfrom = $appointmentsetting['AppointmentSetting']['reschedule_email_from'];
                $rescheduleemailto = $appointmentsetting['AppointmentSetting']['reschedule_email_to'];

                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $response = $this->Nexmomessage->sendsms($_REQUEST['From'], $_REQUEST['To'], $reschedulemessage);
                $this->User->id = $someone['User']['id'];
                if ($this->User->id != '') {
                    $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - 1));
                }
                $this->smsmail($someone['User']['id']);

                $message_replace1 = str_replace('%%Name%%', $appointment['Contact']['name'], $rescheduleemailbody);
                $message_replace2 = str_replace('%%Email%%', $appointment['Contact']['email'], $message_replace1);
                $message_replace3 = str_replace('%%Number%%', $appointment['Contact']['phone_number'], $message_replace2);
                $apptdatetime = date('F j, Y \a\t g:i a', strtotime($appointment['Appointment']['app_date_time']));
                $message_replace4 = str_replace('%%ApptDate%%', $apptdatetime, $message_replace3);

                $sitename = str_replace(' ', '', SITENAME);
                /*$this->Email->to = $rescheduleemailto;
                $this->Email->subject = $rescheduleemailsubject;
                $this->Email->from = $sitename;
                $this->Email->sendAs = 'html';
                $this->Email->send($message_replace4);*/
                
                $Email = new CakeEmail();
                if(EMAILSMTP==1){
                    $Email->config('smtp');
                }
                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                $Email->to($rescheduleemailto);
                $Email->subject($rescheduleemailsubject);
                $Email->emailFormat('html');
                $Email->send($message_replace4);

                $this->Appointment->id = $appointment['Appointment']['id'];
                if ($this->Appointment->id != '') {
                    $this->Appointment->saveField('appointment_status', 3);
                }

                //*********** Save to activity timeline
                $timeline['ActivityTimeline']['user_id'] = $user_id;
                $timeline['ActivityTimeline']['contact_id'] = $appointment['Contact']['id'];
                $timeline['ActivityTimeline']['activity'] = 19;
                $timeline['ActivityTimeline']['title'] = 'Reschedule Appointment';
                $timeline['ActivityTimeline']['description'] = $message_replace4;
                $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                $this->ActivityTimeline->save($timeline);
                //*************

                exit;
            }

        }

        $checkmsgpart = explode(':', $_REQUEST['text']);
        $checkgroup = explode(' ', $checkmsgpart[0]);
        if (strtoupper($checkgroup[0]) == 'SEND') {
            $checkbroadcast = $this->User->find('first', array('conditions' => array('User.id' => $someone['User']['id'], 'User.broadcast' => '' . trim($_REQUEST['From']) . '')));
            if (!empty($checkbroadcast)) {
                app::import('Model', 'Group');
                $this->Group = new Group();
                $groupbroadcast = $this->Group->find('first', array('conditions' => array('Group.keyword' => $checkgroup[1], array('Group.user_id' => $someone['User']['id']))));
                $group_sms_id = 0;
                if (!empty($groupbroadcast)) {

                    $contactlist = $this->ContactGroup->find('all', array('conditions' => array('ContactGroup.group_id' => $groupbroadcast['Group']['id'], 'ContactGroup.un_subscribers' => 0, 'ContactGroup.user_id' => $user_id), 'fields' => array('Contact.phone_number')));
                    if (!empty($contactlist)) {
                        $credits = 0;
                        $faildsms = 0;
                        $successsms = 0;
                        $totalSubscriber = count($contactlist);
                        $sms_balance = $checkbroadcast['User']['sms_balance'];

                        $bodymsg = $checkmsgpart[1];
                        $length = strlen(utf8_decode(substr($bodymsg, 0, 1600)));
                        if (strlen($bodymsg) != strlen(utf8_decode($bodymsg))) {
                            $contactcredits = ceil($length / 70);
                        } else {
                            $contactcredits = ceil($length / 160);
                        }

                        if ($sms_balance < ($totalSubscriber * $contactcredits)) {
                            $message = "You do not have enough credits to broadcast this message to " . $groupbroadcast['Group']['group_name'];
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $response = $this->Nexmomessage->sendsms($_REQUEST['From'], $_REQUEST['To'], $message);


                            $this->User->id = $someone['User']['id'];
                            if ($this->User->id != '') {
                                $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - 1));
                            }
                            $this->smsmail($someone['User']['id']);
                            exit;
                        }
                        app::import('Model', 'GroupSmsBlast');
                        $this->GroupSmsBlast = new GroupSmsBlast();
                        $group_blast['GroupSmsBlast']['user_id'] = $someone['User']['id'];
                        $group_blast['GroupSmsBlast']['group_id'] = $groupbroadcast['Group']['id'];
                        $group_blast['GroupSmsBlast']['responder'] = 1;
                        $group_blast['GroupSmsBlast']['totals'] = $totalSubscriber;
                        $this->GroupSmsBlast->save($group_blast);
                        $group_sms_id = $this->GroupSmsBlast->id;
                        //app::import('Model', 'GroupSmsBlast');
                        //$this->GroupSmsBlast = new GroupSmsBlast();
                        $groupContacts = $this->GroupSmsBlast->find('first', array('conditions' => array('GroupSmsBlast.id' => $group_sms_id)));
                        foreach ($contactlist as $contactlists) {
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $tonumber = $contactlists['Contact']['phone_number'];
                            $fromnumber = $_REQUEST['To'];
                            $bodymsg = $checkmsgpart[1];
                            sleep(1);
                            $response = $this->Nexmomessage->sendsms($tonumber, $fromnumber, $bodymsg);
                            $message_id = '';
                            foreach ($response->messages as $doc) {
                                $message_id = $doc->messageid;
                                if ($message_id != '') {
                                    $status = $doc->status;
                                    $message_id = $doc->messageid;
                                } else {
                                    $status = $doc->status;
                                    $errortext = $doc->errortext;
                                }
                            }
                            
                            if ($message_id != '') {
                                //$credits = $credits + 1;
                                $credits = $credits + $contactcredits;
                                if (!empty($groupContacts)) {
                                    $successsms = $successsms + 1;
                                    app::import('Model', 'GroupSmsBlast');
                                    $this->GroupSmsBlast = new GroupSmsBlast();
                                    $GroupSmsBlast_arr['GroupSmsBlast']['id'] = $group_sms_id;
                                    //$GroupSmsBlast_arr['GroupSmsBlast']['total_successful_messages'] = $groupContacts['GroupSmsBlast']['total_successful_messages'] + 1;
                                    $GroupSmsBlast_arr['GroupSmsBlast']['total_successful_messages'] = $successsms;
                                    $this->GroupSmsBlast->save($GroupSmsBlast_arr);
                                }
                            } else if ($status != 0) {
                                if (!empty($groupContacts)) {
                                    $faildsms = $faildsms + 1;
                                    app::import('Model', 'GroupSmsBlast');
                                    $this->GroupSmsBlast = new GroupSmsBlast();
                                    $GroupSmsBlast_arr['GroupSmsBlast']['id'] = $group_sms_id;
                                    //$GroupSmsBlast_arr['GroupSmsBlast']['total_failed_messages'] = $groupContacts['GroupSmsBlast']['total_failed_messages'] + 1;
                                    $GroupSmsBlast_arr['GroupSmsBlast']['total_failed_messages'] = $faildsms;
                                    $this->GroupSmsBlast->save($GroupSmsBlast_arr);
                                }
                            }

                            $sms_id = '';
                            if ($message_id != '') {
                                $sms_id = $message_id;
                                $sms_status = 'sent';
                            } else {
                                $sms_status = 'failed';
                            }
                            Controller::loadModel('Log');
                            $this->Log->create();
                            $log_ar['Log']['sms_id'] = $sms_id;
                            $log_ar['Log']['user_id'] = $someone['User']['id'];
                            $log_ar['Log']['group_id'] = $groupbroadcast['Group']['id'];
                            $log_ar['Log']['group_sms_id'] = $group_sms_id;
                            $log_ar['Log']['phone_number'] = $tonumber;
                            $log_ar['Log']['text_message'] = $bodymsg;
                            $log_ar['Log']['sms_status'] = $sms_status;
                            $log_ar['Log']['error_message'] = $errortext;
                            $log_ar['Log']['sendfrom'] = $fromnumber;
                            $log_ar['Log']['route'] = 'outbox';
                            $this->Log->save($log_ar);
                        }


                        $message = "Your SMS broadcast has been sent to " . $groupbroadcast['Group']['group_name'];
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        sleep(1);
                        $response = $this->Nexmomessage->sendsms($_REQUEST['From'], $_REQUEST['To'], $message);
                        $credits = $credits + 1;
                        $this->User->id = $someone['User']['id'];
                        if ($this->User->id != '') {
                            $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - $credits));
                        }
                        $this->smsmail($someone['User']['id']);
                    }
                    exit;
                }

            }
        }

        if ($someone['User']['birthday_wishes'] == 0) {
            //$birthday_wishes = explode(':',$_REQUEST['text']);
            $birthday_wishes = $_REQUEST['text'];

            $tempDate = explode('-', $birthday_wishes);
            if(is_numeric($tempDate[0]) && is_numeric($tempDate[1]) && is_numeric($tempDate[2])){
                if (checkdate($tempDate[1], $tempDate[2], $tempDate[0])) {//checkdate(month, day, year)
                    $bday = 1;
                } else {
                    $bday = 0;
                }
            }

            //if((strtoupper($birthday_wishes[0])=='BIRTHDAY') || (strtoupper($birthday_wishes[0])=='BIRTH')){
            if ($bday == 1) {
                app::import('Model', 'ContactGroup');
                $this->ContactGroup = new ContactGroup();
                $contact = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.user_id' => $user_id), 'order' => array('ContactGroup.id' => 'desc')));
                if (!empty($contact)) {
                    app::import('Model', 'Contact');
                    $this->Contact = new Contact();
                    $cont['Contact']['id'] = $contact['Contact']['id'];
                    //$cont['Contact']['birthday'] = trim($birthday_wishes[1]);
                    $cont['Contact']['birthday'] = trim($birthday_wishes);
                    $this->Contact->save($cont);

                    //*********** Save to activity timeline
                    $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                    $timeline['ActivityTimeline']['contact_id'] = $contact['Contact']['id'];
                    $timeline['ActivityTimeline']['activity'] = 13;
                    $timeline['ActivityTimeline']['title'] = 'Capture Birthday';
                    $timeline['ActivityTimeline']['description'] = 'Contact texted in their birthday: ' . $birthday_wishes . ' so they can receive birthday SMS wishes.';
                    $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                    $this->ActivityTimeline->save($timeline);
                    //*************
                    $someoneuser = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                    if ($someoneuser['User']['sms_balance'] > 0) {
                        app::import('Model', 'User');
                        $this->User = new User();
                        $this->User->id = $someone['User']['id'];
                        if ($this->User->id != '') {
                            $this->User->saveField('sms_balance', ($someoneuser['User']['sms_balance'] - 1));
                        }

                        /*Controller::loadModel('Log');
						$this->Log->create();
						$this->request->data['Log']['user_id'] = $someone['User']['id'];
						$this->request->data['Log']['phone_number'] = $_REQUEST['From'];
						$this->request->data['Log']['text_message'] = $_REQUEST['text'];
                                                $this->request->data['Log']['sms_status'] = 'received';
						$this->request->data['Log']['route'] = 'inbox';
						$this->Log->save($this->request->data);*/
                        $message = 'Thanks for your response.';
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        $this->smsmail($someone['User']['id']);
                        exit;
                    }
                }
            }
        }

        if ($someone['User']['capture_email_name'] == 0) {
            $capture_email_name = explode(':', $_REQUEST['text']);
            if ((strtoupper($capture_email_name[0]) == 'EMAIL')) {
                app::import('Model', 'ContactGroup');
                $this->ContactGroup = new ContactGroup();
                $contact = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.user_id' => $user_id), 'order' => array('ContactGroup.id' => 'desc')));
                if (!empty($contact)) {
                    app::import('Model', 'Contact');
                    $this->Contact = new Contact();
                    $cont['Contact']['id'] = $contact['Contact']['id'];
                    $cont['Contact']['email'] = trim($capture_email_name[1]);
                    $this->Contact->save($cont);

//*********** Save to activity timeline
                    $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                    $timeline['ActivityTimeline']['contact_id'] = $contact['Contact']['id'];
                    $timeline['ActivityTimeline']['activity'] = 12;
                    $timeline['ActivityTimeline']['title'] = 'Capture Email';
                    $timeline['ActivityTimeline']['description'] = 'Contact texted in their email address: ' . $capture_email_name[1] . ' so you can also contact them via email if you choose.';
                    $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                    $this->ActivityTimeline->save($timeline);
                    //*************
                    $someoneuser = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                    if ($someoneuser['User']['email_apikey'] != '' && $someoneuser['User']['email_listid'] != '') {
                        if ($someoneuser['User']['email_service'] == 1) { //Mailchimp

                            $list_id = $someoneuser['User']['email_listid'];
                            $MailChimp = new MailChimp($someoneuser['User']['email_apikey']);

                            if ($contact['Contact']['name'] != '') {
                                $fullname = explode(' ', $contact['Contact']['name']);
                                $firstname = $fullname[0];

                                $result = $MailChimp->post("lists/$list_id/members", array(
                                    'email_address' => $capture_email_name[1],
                                    'status' => 'subscribed',
                                    'merge_fields' => array('FNAME' => $firstname),
                                ));

                            } else {
                                $result = $MailChimp->post("lists/$list_id/members", array(
                                    'email_address' => $capture_email_name[1],
                                    'status' => 'subscribed',
                                ));
                            }
                        } else if ($someoneuser['User']['email_service'] == 2) { //Getresponse

                            $list_id = $someoneuser['User']['email_listid'];
                            $GetResponse = new GetResponse($someoneuser['User']['email_apikey']);

                            if ($contact['Contact']['name'] != '') {

                                $result = $GetResponse->addContact(array(
                                    'email' => $capture_email_name[1],
                                    'name' => $contact['Contact']['name'],
                                    'campaign' => array('campaignId' => $list_id)));
                            } else {

                                $result = $GetResponse->addContact(array(
                                    'email' => $capture_email_name[1],
                                    'campaign' => array('campaignId' => $list_id)));

                            }

                        } else if ($someoneuser['User']['email_service'] == 3) { //Active Campaign

                            $ac = new ActiveCampaign($someoneuser['User']['email_apiurl'], $someoneuser['User']['email_apikey']);
                            $list_id = (int)$someoneuser['User']['email_listid'];
                            $fullname = explode(' ', $contact['Contact']['name']);
                            $firstname = $fullname[0];

                            $newcontact = array(
                                'email' => $capture_email_name[1],
                                'first_name' => $firstname,
                                'phone' => $contact['Contact']['phone_number'],
                                'p[{$list_id}]' => $list_id,
                                'status[{$list_id}]' => 1, // "Active" status
                            );

                            $contact_sync = $ac->api("contact/sync", $newcontact);


                        } else if ($someoneuser['User']['email_service'] == 4) { //AWeber

                            $aweber = new AWeberAPI($someoneuser['User']['consumerkey'], $someoneuser['User']['consumersecret']);
                            $account = $aweber->getAccount($someoneuser['User']['accesskey'], $someoneuser['User']['accesssecret']);

                            $account_id = $account->id;
                            $list_id = $someoneuser['User']['email_listid'];
                            $fullname = explode(' ', $contact['Contact']['name']);
                            $firstname = $fullname[0];

                            $listURL = "/accounts/{$account_id}/lists/{$list_id}";
                            $list = $account->loadFromUrl($listURL);

                            $params = array(
                                'email' => $capture_email_name[1],
                                'name' => $firstname,
                            );

                            $subscribers = $list->subscribers;
                            $new_subscriber = $subscribers->create($params);

                        } else if ($someoneuser['User']['email_service'] == 5) { //Sendinblue

                            $mailin = new Mailin(SENDINBLUE_APIURL, $someoneuser['User']['email_apikey']);

                            $list_id = (int)$someoneuser['User']['email_listid'];
                            $fullname = explode(' ', $contact['Contact']['name']);
                            $firstname = $fullname[0];

                            $data = array("email" => $capture_email_name[1],
                                "attributes" => array("FIRSTNAME" => $firstname),
                                "listid" => array($list_id)
                            );

                            $result = $mailin->create_update_user($data);

                        }


                    }

                    if ($someoneuser['User']['sms_balance'] > 0) {
                        app::import('Model', 'User');
                        $this->User = new User();
                        $this->User->id = $someone['User']['id'];
                        if ($this->User->id != '') {
                            $this->User->saveField('sms_balance', ($someoneuser['User']['sms_balance'] - 1));
                        }

                        /*Controller::loadModel('Log');
						$this->Log->create();
						$this->request->data['Log']['user_id'] = $someone['User']['id'];
						$this->request->data['Log']['phone_number'] = $_REQUEST['From'];
						$this->request->data['Log']['text_message'] = $_REQUEST['text'];
                                                $this->request->data['Log']['sms_status'] = 'received';
						$this->request->data['Log']['route'] = 'inbox';
						$this->Log->save($this->request->data);*/

                        $message = 'Thanks for your response.';
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        $this->smsmail($someone['User']['id']);

                        exit;
                    }
                }

            } else if ((strtoupper($capture_email_name[0]) == 'NAME')) {
                app::import('Model', 'ContactGroup');
                $this->ContactGroup = new ContactGroup();
                $contact = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.user_id' => $user_id), 'order' => array('ContactGroup.id' => 'desc')));
                if (!empty($contact)) {
                    app::import('Model', 'Contact');
                    $this->Contact = new Contact();
                    $cont['Contact']['id'] = $contact['Contact']['id'];
                    $cont['Contact']['name'] = trim($capture_email_name[1]);
                    $this->Contact->save($cont);

//*********** Save to activity timeline
                    $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                    $timeline['ActivityTimeline']['contact_id'] = $contact['Contact']['id'];
                    $timeline['ActivityTimeline']['activity'] = 11;
                    $timeline['ActivityTimeline']['title'] = 'Capture Name';
                    $timeline['ActivityTimeline']['description'] = 'Contact texted in their name: ' . $capture_email_name[1] . ' so you can personalize your bulk outbound messages by using the %%Name%% token inside the message body.';
                    $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                    $this->ActivityTimeline->save($timeline);
                    //*************
                    $someoneuser = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                    if ($someoneuser['User']['sms_balance'] > 0) {
                        app::import('Model', 'User');
                        $this->User = new User();
                        $this->User->id = $someone['User']['id'];

                        $message = EMAIL_CAPTURE_MSG;

                        $length = strlen(utf8_decode(substr($message, 0, 1600)));
                        if (strlen($message) != strlen(utf8_decode($message))) {
                            $credits = ceil($length / 70);
                        } else {
                            $credits = ceil($length / 160);
                        }

                        if ($this->User->id != '') {
                            //$this->User->saveField('sms_balance', ($someoneuser['User']['sms_balance']-1));
                            $this->User->saveField('sms_balance', ($someoneuser['User']['sms_balance'] - $credits));
                        }

                        /*Controller::loadModel('Log');
						$this->Log->create();
						$this->request->data['Log']['user_id'] = $someone['User']['id'];
						$this->request->data['Log']['phone_number'] = $_REQUEST['From'];
						$this->request->data['Log']['text_message'] = $_REQUEST['text'];
                                                $this->request->data['Log']['sms_status'] = 'received';
						$this->request->data['Log']['route'] = 'inbox';
						$this->Log->save($this->request->data);*/


                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        $this->smsmail($someone['User']['id']);
                        exit;
                    }
                }
            }
        }
        $smsloyalty_status = $this->Smsloyalty->find('first', array('conditions' => array('Smsloyalty.user_id' => $user_id, 'Smsloyalty.codestatus' => strtoupper($_REQUEST['text']))));

        if (!empty($smsloyalty_status)) {
            if ($someone['User']['sms_balance'] > 0) {
                $credits = 0;
                app::import('Model', 'ContactGroup');
                $this->ContactGroup = new ContactGroup();
                $contactgroupid = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.group_id' => $smsloyalty_status['Smsloyalty']['group_id'], 'ContactGroup.user_id' => $user_id)));
                if (!empty($contactgroupid)) {
                    app::import('Model', 'SmsloyaltyUser');
                    $this->SmsloyaltyUser = new SmsloyaltyUser();
                    $loyaltyuser = $this->SmsloyaltyUser->find('first', array('conditions' => array('SmsloyaltyUser.contact_id' => $contactgroupid['ContactGroup']['contact_id'], 'SmsloyaltyUser.sms_loyalty_id' => $smsloyalty_status['Smsloyalty']['id']), 'order' => array('SmsloyaltyUser.created' => 'desc')));
                    if (!empty($loyaltyuser)) {
                        $credits = 1;
                        //$message=str_replace('%%Name%%',$contactgroupid['Contact']['name'],$smsloyalty_status['Smsloyalty']['checkstatus']);
                        //$msg=str_replace('%%STATUS%%',$loyaltyuser['SmsloyaltyUser']['count_trial'],$message);
                        $message = str_replace('%%Name%%', $contactgroupid['Contact']['name'], $smsloyalty_status['Smsloyalty']['checkstatus']);
                        $msg = str_replace('%%STATUS%%', $loyaltyuser['SmsloyaltyUser']['count_trial'], $message);
                        $statusmsg = str_replace('%%GOAL%%', $smsloyalty_status['Smsloyalty']['reachgoal'], $msg);
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $statusmsg);
                        $this->smsmail($someone['User']['id']);
//*********** Save to activity timeline
                        $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                        $timeline['ActivityTimeline']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                        $timeline['ActivityTimeline']['activity'] = 6;
                        $timeline['ActivityTimeline']['title'] = 'Check Loyalty Status';
                        $timeline['ActivityTimeline']['description'] = $statusmsg;
                        $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                        $this->ActivityTimeline->save($timeline);
                        //*************
                    }
                } else {
                    $credits = 1;
                    $message = "You are not eligible to participate since you are not subscribed to our opt-in list. Please text in " . $smsloyalty_status['Group']['keyword'] . " to be added to our opt-in list.";
                    $this->Nexmomessage->Key = NEXMO_KEY;
                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                    $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                    $this->smsmail($someone['User']['id']);
                }
                if ($credits > 0) {
                    $update_user['User']['id'] = $someone['User']['id'];
                    $update_user['User']['sms_balance'] = $someone['User']['sms_balance'] - $credits;
                    $this->User->save($update_user);
                }

            }
            exit;
        } else if (!empty($smsloyalty_arr)) {
            if ($someone['User']['sms_balance'] > 0) {
                $credits = 0;
                app::import('Model', 'ContactGroup');
                $this->ContactGroup = new ContactGroup();
                $contactgroupid = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.group_id' => $smsloyalty_arr['Smsloyalty']['group_id'], 'ContactGroup.user_id' => $user_id)));
                if (!empty($contactgroupid)) {
                    $current_date = date('Y-m-d');
                    if ($smsloyalty_arr['Smsloyalty']['startdate'] > $current_date) {
                        $credits = 1;
                        $message = "Loyalty program " . $smsloyalty_arr['Smsloyalty']['program_name'] . " hasn't started yet. It begins on " . date('m/d/Y', strtotime($smsloyalty_arr['Smsloyalty']['startdate'])) . "";
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        $this->smsmail($someone['User']['id']);
                    } else if ($smsloyalty_arr['Smsloyalty']['enddate'] < $current_date) {
                        $credits = 1;
                        $message = "Loyalty program " . $smsloyalty_arr['Smsloyalty']['program_name'] . " ended on " . date('m/d/Y', strtotime($smsloyalty_arr['Smsloyalty']['enddate'])) . "";
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        $this->smsmail($someone['User']['id']);

                    } else {
                        $currentdate = date('Y-m-d');
                        app::import('Model', 'SmsloyaltyUser');
                        $this->SmsloyaltyUser = new SmsloyaltyUser();
                        $loyaltyuser = $this->SmsloyaltyUser->find('first', array('conditions' => array('SmsloyaltyUser.contact_id' => $contactgroupid['ContactGroup']['contact_id'], 'SmsloyaltyUser.sms_loyalty_id' => $smsloyalty_arr['Smsloyalty']['id'], 'SmsloyaltyUser.redemptions' => 0), 'order' => array('SmsloyaltyUser.msg_date' => 'desc')));
                        if (empty($loyaltyuser)) {

                            $loyaltyuserredeem = $this->SmsloyaltyUser->find('first', array('conditions' => array('SmsloyaltyUser.contact_id' => $contactgroupid['ContactGroup']['contact_id'], 'SmsloyaltyUser.sms_loyalty_id' => $smsloyalty_arr['Smsloyalty']['id'], 'SmsloyaltyUser.redemptions' => 1, 'SmsloyaltyUser.msg_date' => $currentdate), 'order' => array('SmsloyaltyUser.msg_date' => 'desc')));

                            if (empty($loyaltyuserredeem)) {
                                $loyalty_user['SmsloyaltyUser']['id'] = '';
                                $loyalty_user['SmsloyaltyUser']['unique_key'] = $this->random_generator(10);
                                $loyalty_user['SmsloyaltyUser']['user_id'] = $smsloyalty_arr['Smsloyalty']['user_id'];
                                $loyalty_user['SmsloyaltyUser']['sms_loyalty_id'] = $smsloyalty_arr['Smsloyalty']['id'];
                                $loyalty_user['SmsloyaltyUser']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                                $loyalty_user['SmsloyaltyUser']['keyword'] = $_REQUEST['text'];
                                $loyalty_user['SmsloyaltyUser']['count_trial'] = 1;
                                $loyalty_user['SmsloyaltyUser']['msg_date'] = $currentdate;
                                $loyalty_user['SmsloyaltyUser']['created'] = date('Y-m-d H:i:s');
                                if ($smsloyalty_arr['Smsloyalty']['reachgoal'] == 1) {
                                    $loyalty_user['SmsloyaltyUser']['is_winner'] = 1;
                                    if ($this->SmsloyaltyUser->save($loyalty_user)) {
                                        if ($smsloyalty_arr['Smsloyalty']['type'] == 1) {
                                            $credits = 1;
                                            $message = str_replace('%%Name%%', $contactgroupid['Contact']['name'], $smsloyalty_arr['Smsloyalty']['reachedatgoal']);
                                            $msg = str_replace('%%STATUS%%', $count_trial, $message);
                                            $redeem = "Click link to redeem " . SITE_URL . "/users/redeem/" . $loyalty_user['SmsloyaltyUser']['unique_key'] . "";
                                            $sms = $msg . ' ' . $redeem;
                                            $this->Nexmomessage->Key = NEXMO_KEY;
                                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                                            $this->Nexmomessage->sendsms($phone, $fromnumber, $sms);
                                            $this->smsmail($someone['User']['id']);

//*********** Save to activity timeline
                                            $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                                            $timeline['ActivityTimeline']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                                            $timeline['ActivityTimeline']['activity'] = 7;
                                            $timeline['ActivityTimeline']['title'] = 'Loyalty Program Winner';
                                            $timeline['ActivityTimeline']['description'] = $sms;
                                            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                                            $this->ActivityTimeline->save($timeline);
                                            //*************
                                        }

                                    }
                                } else {
                                    $this->SmsloyaltyUser->save($loyalty_user);
                                    $credits = 1;
                                    $message = str_replace('%%Name%%', $contactgroupid['Contact']['name'], $smsloyalty_arr['Smsloyalty']['addpoints']);
                                    $msg = str_replace('%%STATUS%%', 1, $message);
                                    $this->Nexmomessage->Key = NEXMO_KEY;
                                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                                    $this->Nexmomessage->sendsms($phone, $fromnumber, $msg);
                                    $this->smsmail($someone['User']['id']);

//*********** Save to activity timeline
                                    $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                                    $timeline['ActivityTimeline']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                                    $timeline['ActivityTimeline']['activity'] = 5;
                                    $timeline['ActivityTimeline']['title'] = 'Loyalty Program Punch';
                                    $timeline['ActivityTimeline']['description'] = $msg;
                                    $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                                    $this->ActivityTimeline->save($timeline);
                                    //*************
                                }
                            } else {
                                $credits = 1;
                                $message = "You have already redeemed your reward today.";
                                $this->Nexmomessage->Key = NEXMO_KEY;
                                $this->Nexmomessage->Secret = NEXMO_SECRET;
                                $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                                $this->smsmail($someone['User']['id']);
                            }
                        } else if ($loyaltyuser['SmsloyaltyUser']['msg_date'] < $currentdate) {
                            $count_trial = $loyaltyuser['SmsloyaltyUser']['count_trial'] + 1;
                            
                            /***08/11/2018*****/
                            if ($count_trial > $smsloyalty_arr['Smsloyalty']['reachgoal'] && $loyaltyuser['SmsloyaltyUser']['is_winner'] == 1){
                                $credits = 1;
                                $message = "You have already reached the goal of " . $smsloyalty_arr['Smsloyalty']['reachgoal'] . " points.";
                                $redeem = "Click link to redeem " . SITE_URL . "/users/redeem/" . $loyaltyuser['SmsloyaltyUser']['unique_key'] . "";
                                $sms = $message . ' ' . $redeem;
                                $this->Nexmomessage->Key = NEXMO_KEY;
                                $this->Nexmomessage->Secret = NEXMO_SECRET;
                                $this->Nexmomessage->sendsms($phone, $fromnumber, $sms);
                                $update_user['User']['id'] = $someone['User']['id'];
                                $update_user['User']['sms_balance'] = $someone['User']['sms_balance'] - $credits;
                                $this->User->save($update_user);
                                $this->smsmail($someone['User']['id']);
                                exit;
                            }
                            /******************/
                            
                            $loyalty_user['SmsloyaltyUser']['id'] = $loyaltyuser['SmsloyaltyUser']['id'];
                            $loyalty_user['SmsloyaltyUser']['user_id'] = $smsloyalty_arr['Smsloyalty']['user_id'];
                            $loyalty_user['SmsloyaltyUser']['sms_loyalty_id'] = $smsloyalty_arr['Smsloyalty']['id'];
                            $loyalty_user['SmsloyaltyUser']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                            $loyalty_user['SmsloyaltyUser']['keyword'] = $_REQUEST['text'];
                            $loyalty_user['SmsloyaltyUser']['count_trial'] = $count_trial;
                            $loyalty_user['SmsloyaltyUser']['msg_date'] = $currentdate;
                            $loyalty_user['SmsloyaltyUser']['created'] = date('Y-m-d H:i:s');
                            if ($this->SmsloyaltyUser->save($loyalty_user)) {
                                $loyaltyuser_list = $this->SmsloyaltyUser->find('first', array('conditions' => array('SmsloyaltyUser.contact_id' => $contactgroupid['ContactGroup']['contact_id'], 'SmsloyaltyUser.sms_loyalty_id' => $smsloyalty_arr['Smsloyalty']['id'], 'SmsloyaltyUser.redemptions' => 0), 'order' => array('SmsloyaltyUser.msg_date' => 'desc')));

                                if ($loyaltyuser_list['SmsloyaltyUser']['count_trial'] == $smsloyalty_arr['Smsloyalty']['reachgoal']) {
                                    $loyalty_user_arr['SmsloyaltyUser']['id'] = $loyaltyuser['SmsloyaltyUser']['id'];
                                    $loyalty_user_arr['SmsloyaltyUser']['is_winner'] = 1;
                                    if ($this->SmsloyaltyUser->save($loyalty_user_arr)) {
                                        if ($smsloyalty_arr['Smsloyalty']['type'] == 1) {
                                            $credits = 1;
                                            $message = str_replace('%%Name%%', $contactgroupid['Contact']['name'], $smsloyalty_arr['Smsloyalty']['reachedatgoal']);
                                            $msg = str_replace('%%STATUS%%', $count_trial, $message);
                                            $redeem = "Click link to redeem " . SITE_URL . "/users/redeem/" . $loyaltyuser_list['SmsloyaltyUser']['unique_key'] . "";
                                            $sms = $msg . ' ' . $redeem;
                                            $this->Nexmomessage->Key = NEXMO_KEY;
                                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                                            $this->Nexmomessage->sendsms($phone, $fromnumber, $sms);
                                            $this->smsmail($someone['User']['id']);

//*********** Save to activity timeline
                                            $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                                            $timeline['ActivityTimeline']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                                            $timeline['ActivityTimeline']['activity'] = 7;
                                            $timeline['ActivityTimeline']['title'] = 'Loyalty Program Winner';
                                            $timeline['ActivityTimeline']['description'] = $sms;
                                            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                                            $this->ActivityTimeline->save($timeline);
                                            //*************
                                        }
                                    }
                                } else {
                                    $credits = 1;
                                    $message = str_replace('%%Name%%', $contactgroupid['Contact']['name'], $smsloyalty_arr['Smsloyalty']['addpoints']);
                                    $msg = str_replace('%%STATUS%%', $count_trial, $message);
                                    //$redeem = "Click link to redeem ".SITE_URL."/users/redeem/".$loyaltyuser_list['SmsloyaltyUser']['unique_key']."";
                                    //$sms = $msg.' '.$redeem;
                                    $this->Nexmomessage->Key = NEXMO_KEY;
                                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                                    $this->Nexmomessage->sendsms($phone, $fromnumber, $msg);
                                    $this->smsmail($someone['User']['id']);

//*********** Save to activity timeline
                                    $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
                                    $timeline['ActivityTimeline']['contact_id'] = $contactgroupid['ContactGroup']['contact_id'];
                                    $timeline['ActivityTimeline']['activity'] = 5;
                                    $timeline['ActivityTimeline']['title'] = 'Loyalty Program Punch';
                                    $timeline['ActivityTimeline']['description'] = $msg;
                                    $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                                    $this->ActivityTimeline->save($timeline);
                                    //*************
                                }
                            }
                        } else if ($loyaltyuser['SmsloyaltyUser']['is_winner'] == 1) {
                            $credits = 1;
                            $message = "You have already reached the goal of " . $smsloyalty_arr['Smsloyalty']['reachgoal'] . " points.";
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                            $this->smsmail($someone['User']['id']);

                        } else {
                            $credits = 1;
                            $message = "You already punched your card today. Stop in tomorrow for the new punch code.";
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                            $this->smsmail($someone['User']['id']);
                        }
                    }

                } else {
                    $credits = 1;
                    $message = "You are not eligible to participate since you are not subscribed to our opt-in list. Please text in " . $smsloyalty_arr['Group']['keyword'] . " to be added to our opt-in list.";
                    $this->Nexmomessage->Key = NEXMO_KEY;
                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                    $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                    $this->smsmail($someone['User']['id']);

                }
                if ($credits > 0) {
                    $update_user['User']['id'] = $someone['User']['id'];
                    $update_user['User']['sms_balance'] = $someone['User']['sms_balance'] - $credits;
                    $this->User->save($update_user);
                }
            }
            exit;
        } else if (strtoupper(trim($_REQUEST['text'])) == 'HELP') {
            app::import('Model', 'User');
            $this->User = new User();
            $user_id = $someone['User']['id'];
            $sms_balance = $someone['User']['sms_balance'];
            $this->request->data['User']['id'] = $user_id;
            $this->request->data['User']['sms_balance'] = $sms_balance - 1;
            $this->User->save($this->request->data);
            $companyname = $someone['User']['company_name'];
            if (!empty($companyname)) {
                $message = "You have signed up to receive promotional messages from " . $companyname . ". Text STOP to cancel. Msg&Data Rates May Apply.";
            } else {
                $message = "Text STOP to cancel. Msg&Data Rates May Apply.";
            }
            //$message='Text STOP to unsubscribe. Msg&Data Rates May Apply.';
            $this->Nexmomessage->Key = NEXMO_KEY;
            $this->Nexmomessage->Secret = NEXMO_SECRET;
            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
            $this->smsmail($someone['User']['id']);

            //*********** Save to activity timeline
            $timeline['ActivityTimeline']['user_id'] = $user_id;
            $timeline['ActivityTimeline']['contact_id'] = $contact_id;
            $timeline['ActivityTimeline']['activity'] = 16;
            $timeline['ActivityTimeline']['title'] = 'Help';
            $timeline['ActivityTimeline']['description'] = $message;
            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
            $this->ActivityTimeline->save($timeline);
            //*************
            exit;
        }
        if (strtoupper(trim($_REQUEST['text'])) == 'START') {
            app::import('Model', 'User');
            $this->User = new User();
            //$someone1 = $this->User->find('first', array('conditions' => array('assigned_number' =>''.trim($_REQUEST['To']).'')));
            $user_id = $someone['User']['id'];
            $sms_balance = $someone['User']['sms_balance'];
            app::import('Model', 'ContactGroup');
            $this->ContactGroup = new ContactGroup();
            $contactsstart = $this->ContactGroup->find('all', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.un_subscribers' => 1, 'ContactGroup.user_id' => $user_id)));
            if (!empty($contactsstart)) {
                foreach ($contactsstart as $contact) {
                    app::import('Model', 'Contact');
                    $this->Contact = new Contact();
                    $contact_id = $contact['Contact']['id'];
                    $id = $contact['ContactGroup']['id'];
                    $this->request->data['ContactGroup']['id'] = $id;
                    $this->request->data['ContactGroup']['contact_id'] = $contact_id;
                    $this->request->data['ContactGroup']['un_subscribers'] = 0;
                    $this->request->data['ContactGroup']['created'] = date('Y-m-d H:i:s', time());
                    if ($this->ContactGroup->save($this->request->data)) {
                        $contact_arr['Contact']['id'] = $contact_id;
                        $contact_arr['Contact']['un_subscribers'] = 0;
                        if ($this->Contact->save($contact_arr)) {
                            app::import('Model', 'Group');
                            $this->Group = new Group();
                            $this->request->data['Group']['id'] = $contact['Group']['id'];
                            $this->request->data['Group']['totalsubscriber'] = $contact['Group']['totalsubscriber'] + 1;
                            $this->Group->save($this->request->data);
                        }
                    }
                }
                app::import('Model', 'User');
                $this->User = new User();
                $this->request->data['User']['id'] = $user_id;
                $this->request->data['User']['sms_balance'] = $sms_balance - 1;
                $this->User->save($this->request->data);
                $message = 'You have successfully been re-subscribed. Text STOP to cancel. Msg&Data Rates May Apply.';
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                $this->smsmail($someone['User']['id']);
//*********** Save to activity timeline
                $timeline['ActivityTimeline']['user_id'] = $user_id;
                $timeline['ActivityTimeline']['contact_id'] = $contact_id;
                $timeline['ActivityTimeline']['activity'] = 15;
                $timeline['ActivityTimeline']['title'] = 'Contact Resubscribed';
                $timeline['ActivityTimeline']['description'] = 'Contact has resubscribed and can now continue to receive messages.';
                $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                $this->ActivityTimeline->save($timeline);
                //*************
                exit;
            }
        } else if (strtoupper(trim($_REQUEST['text'])) == 'STOP') {
            app::import('Model', 'User');
            $this->User = new User();
            //$someone = $this->User->find('first', array('conditions' => array('assigned_number' =>''.trim($_REQUEST['To']).'')));
            $user_id = $someone['User']['id'];
            $sms_balance = $someone['User']['sms_balance'];
            app::import('Model', 'ContactGroup');
            $this->ContactGroup = new ContactGroup();
            $contacts = $this->ContactGroup->find('all', array('conditions' => array('Contact.phone_number' => $phone, 'ContactGroup.un_subscribers' => array(0,2), 'ContactGroup.user_id' => $user_id)));
            if (!empty($contacts)) {
                foreach ($contacts as $contact) {
                    //if($someone['User']['active']==1 || $someone['User']['sms_balance'] > 0){
                    app::import('Model', 'Contact');
                    $this->Contact = new Contact();
                    $contact_id = $contact['Contact']['id'];
                    $id = $contact['ContactGroup']['id'];
                    $this->request->data['ContactGroup']['id'] = $id;
                    $this->request->data['ContactGroup']['contact_id'] = $contact_id;
                    $this->request->data['ContactGroup']['un_subscribers'] = 1;
                    $this->request->data['ContactGroup']['created'] = date('Y-m-d H:i:s', time());
                    if ($this->ContactGroup->save($this->request->data)) {
                        $contact_arr['Contact']['id'] = $contact_id;
                        $contact_arr['Contact']['un_subscribers'] = 1;
                        //if ($this->Contact->save($contact_arr)) {
                        if ($this->Contact->save($contact_arr) && $contact['ContactGroup']['un_subscribers']==0) {
                            app::import('Model', 'Group');
                            $this->Group = new Group();
                            $this->request->data['Group']['id'] = $contact['Group']['id'];
                            $this->request->data['Group']['totalsubscriber'] = $contact['Group']['totalsubscriber'] - 1;
                            $this->Group->save($this->request->data);
                        }
                    }
                    //}
                }
                app::import('Model', 'User');
                $this->User = new User();
                $this->request->data['User']['id'] = $user_id;
                $this->request->data['User']['sms_balance'] = $sms_balance - 1;
                $this->User->save($this->request->data);
                $message = 'You have successfully been unsubscribed. Reply START to get added back to our list. Msg&Data Rates May Apply.';
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                $this->smsmail($someone['User']['id']);
//*********** Save to activity timeline
                $timeline['ActivityTimeline']['user_id'] = $user_id;
                $timeline['ActivityTimeline']['contact_id'] = $contact_id;
                $timeline['ActivityTimeline']['activity'] = 14;
                $timeline['ActivityTimeline']['title'] = 'Contact Unsubscribed';
                $timeline['ActivityTimeline']['description'] = 'Contact has unsubscribed from your list and can no longer receive messages.';
                $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                $this->ActivityTimeline->save($timeline);
                //*************
                app::import('Model', 'Contact');
                $this->Contact = new Contact();
                $number = $this->Contact->find('first', array('conditions' => array('Contact.id' => $contact_id), 'fields' => array('Contact.phone_number')));
                $beforecontactnumber = $number['Contact']['phone_number'];
                $aftercontactnumber = substr_replace($beforecontactnumber, '****', -4);
                app::import('Model', 'Log');
                $this->Log = new Log();
                $this->Log->updateAll(array('Log.phone_number' => "'$aftercontactnumber'"), array('Log.user_id'=>$user_id, 'Log.phone_number' => $beforecontactnumber));
                exit;
            }
        //****DOUBLE OPT-IN CHANGES****//    
        }  else if (strtoupper(trim($_REQUEST['text'])) == 'Y' && $contact_doubleoptin['ContactGroup']['un_subscribers']==2) {
            $this->Nexmomessage->Key = NEXMO_KEY;
            $this->Nexmomessage->Secret = NEXMO_SECRET;
            app::import('Model', 'Group');
            $this->Group = new Group();
            $group = $this->Group->find('first', array('conditions' => array('Group.keyword' => $contact_doubleoptin['ContactGroup']['group_subscribers'], array('Group.user_id' => $someone['User']['id']))));
            $sender_number = $_REQUEST['To'];
            $group_id = $group['Group']['id'];
            $group_name = $group['Group']['group_name'];
            $totalsubscriber = $group['Group']['totalsubscriber'];
            $sms_type = $group['Group']['sms_type'];
            $system_message = $group['Group']['system_message'];
            $auto_message = $group['Group']['auto_message'];
            $image_url = $group['Group']['image_url'];
            $group_type = $group['Group']['group_type'];
            $user_id = $someone['User']['id'];

            $this->request->data['ContactGroup']['id'] = $contact_doubleoptin['ContactGroup']['id'];
            $this->request->data['ContactGroup']['un_subscribers'] = 0;
            $this->request->data['ContactGroup']['created'] = date('Y-m-d H:i:s', time());
            $this->ContactGroup->save($this->request->data);

            app::import('Model', 'Contact');
            $this->Contact = new Contact();
            $contact_arr['Contact']['id'] = $contact_doubleoptin['ContactGroup']['contact_id'];
            $contact_arr['Contact']['un_subscribers'] = 0;
            $this->Contact->save($contact_arr);
            
            if ($someone['User']['email_alert_options'] == 0) {
                if ($someone['User']['email_alerts'] == 1) {
                    $username = $someone['User']['username'];
                    $email = $someone['User']['email'];
                    $date = date('Y-m-d H:i:s', time());
                    $subject = "New Subscriber to " . $group_name;
                    $sitename = str_replace(' ', '', SITENAME);
        
                    $Email = new CakeEmail();
                    if(EMAILSMTP==1){
                        $Email->config('smtp');
                    }
                    $Email->from(array(SUPPORT_EMAIL => SITENAME));
                    $Email->to($email);
                    $Email->subject($subject);
                    $Email->template('new_subscriber_template');
                    $Email->emailFormat('html');
                    $Email->viewVars(array('username' => $username));
                    $Email->viewVars(array('phoneno' => $phone));
                    $Email->viewVars(array('groupname' => $group_name));
                    $Email->viewVars(array('keyword' => $keyword));
                    $Email->viewVars(array('datetime' => $date));
                    $Email->send();
                }
            }
  
            $this->request->data['Group']['id'] = $group_id;
            $this->request->data['Group']['totalsubscriber'] = $totalsubscriber + 1;
            $this->Group->save($this->request->data);

            if ($group_type == 2) {
                $address = $group['Group']['property_address'];
                $price = $group['Group']['property_price'];
                $bed = $group['Group']['property_bed'];
                $bath = $group['Group']['property_bath'];
                $description = $group['Group']['property_description'];
                $url = $group['Group']['property_url'];

                $message = $address . "\n" . $price . "\nBed: " . $bed . "\nBath: " . $bath . "\n" . $description . "\n" . $url . "\n";
                $message = $message . $system_message . ' ' . $auto_message;
            } elseif ($group_type == 3) {
                $year = $group['Group']['vehicle_year'];
                $make = $group['Group']['vehicle_make'];
                $model = $group['Group']['vehicle_model'];
                $mileage = $group['Group']['vehicle_mileage'];
                $price = $group['Group']['vehicle_price'];
                $description = $group['Group']['vehicle_description'];
                $url = $group['Group']['vehicle_url'];

                $message = $year . ' ' . $make . ' ' . $model . "\n" . $mileage . "\n" . $price . "\n" . $description . "\n" . $url . "\n";
                $message = $message . $system_message . ' ' . $auto_message;

            } else {
                $message = $system_message . ' ' . $auto_message;
            }
            $current_datetime = date("n/d/Y");
            $message = str_replace('%%CURRENTDATE%%', $current_datetime, $message);

            //*********** Save to activity timeline
            $timeline['ActivityTimeline']['user_id'] = $user_id;
            $timeline['ActivityTimeline']['contact_id'] = $contact_doubleoptin['ContactGroup']['contact_id'];
            $timeline['ActivityTimeline']['activity'] = 1;
            if($contact_doubleoptin['ContactGroup']['subscribed_by_sms']==1){
                $timeline['ActivityTimeline']['title'] = 'Contact Subscribed via SMS';
            }elseif($contact_doubleoptin['ContactGroup']['subscribed_by_sms']==2){
                $timeline['ActivityTimeline']['title'] = 'Contact Subscribed via Web Widget';
            }
            $timeline['ActivityTimeline']['description'] = $message;
            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
            $this->ActivityTimeline->save($timeline);
            //*************
            
            $this->Nexmomessage->sendsms($phone, $sender_number, $message);
            sleep(2);
            $this->Immediatelyresponder($someone['User']['id'], $group_id, $phone, $sender_number);

            $curcredits = $someone['User']['sms_balance'];
            $length = strlen(utf8_decode(substr($message, 0, 1600)));

            //if ($sms_type == 1) {
                if (strlen($message) != strlen(utf8_decode($message))) {
                    $credits = ceil($length / 70);
                } else {
                    $credits = ceil($length / 160);
                }
            //} else {
            //    $credits = 3;
            //}
            $this->request->data['User']['sms_balance'] = $curcredits - $credits;
            $this->request->data['User']['id'] = $user_id;
            $this->User->save($this->request->data);
            $this->smsmail($someone['User']['id']);

            if ($contact_doubleoptin['ContactGroup']['subscribed_by_sms']==1){
                if ($someone['User']['capture_email_name'] == 0) {
                    $capture_email_name = NAME_CAPTURE_MSG;
                    $this->Nexmomessage->sendsms($phone, $sender_number, $capture_email_name);
                    sleep(2);
                    $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
    
                    $length = strlen(utf8_decode(substr($capture_email_name, 0, 1600)));
                    if (strlen($capture_email_name) != strlen(utf8_decode($capture_email_name))) {
                        $credits = ceil($length / 70);
                    } else {
                        $credits = ceil($length / 160);
                    }
                    if (!empty($someone_users)) {
                        $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                        $user_credit['User']['id'] = $user_id;
                        $this->User->save($user_credit);
                    }
                }
                if ($group['Group']['bithday_enable'] == 1) {
                    if ($someone['User']['birthday_wishes'] == 0) {
                        $birthday_wishes = BIRTHDAY_MSG;
                        $this->Nexmomessage->sendsms($phone, $sender_number, $birthday_wishes);
                        sleep(2);
    
                        $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
    
                        $length = strlen(utf8_decode(substr($birthday_wishes, 0, 1600)));
                        if (strlen($birthday_wishes) != strlen(utf8_decode($birthday_wishes))) {
                            $credits = ceil($length / 70);
                        } else {
                            $credits = ceil($length / 160);
                        }
                        if (!empty($someone_users)) {
                            $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                            $user_credit['User']['id'] = $user_id;
                            $this->User->save($user_credit);
                        }
    
                    }
                }
            }

            if ($group['Group']['notify_signup'] == 1) {
                $mobile = $group['Group']['mobile_number_input'];
                $groupname = $group['Group']['group_name'];
                $message = "New Subscriber Alert: " . $phone . " has joined group " . $groupname;
                $this->Nexmomessage->sendsms($mobile, $sender_number, $message);

                $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                if (!empty($someone_users)) {
                    $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - 1;
                    $user_credit['User']['id'] = $user_id;
                    $this->User->save($user_credit);
                }
            }            
        } else if (!empty($group)) {
            $keyword = $_REQUEST['text'];
            //$to=$_REQUEST['To'];
            $group_id = $group['Group']['id'];
            $group_name = $group['Group']['group_name'];
            $totalsubscriber = $group['Group']['totalsubscriber'];
            $sms_type = $group['Group']['sms_type'];
            $system_message = $group['Group']['system_message'];
            $auto_message = $group['Group']['auto_message'];
            $image_url = $group['Group']['image_url'];
            $group_type = $group['Group']['group_type'];
            //****DOUBLE OPT-IN CHANGES****//
            $double_optin = $group['Group']['double_optin'];
            
            app::import('Model', 'Contact');
            $this->Contact = new Contact();
            $contact = $this->Contact->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'Contact.user_id' => $user_id)));
            if ($someone['User']['sms_balance'] > 0) {
                if (empty($contact)) {
                    if (NUMVERIFY != '') {
                        $numbervalidation = $this->validateNumber($phone);
                        $errorcode = $numbervalidation['error']['code'];

                        if ($errorcode == '') {
                            $this->request->data['Contact']['carrier'] = $numbervalidation['carrier'];
                            $this->request->data['Contact']['location'] = $numbervalidation['location'];
                            $this->request->data['Contact']['phone_country'] = $numbervalidation['country_name'];
                            $this->request->data['Contact']['line_type'] = $numbervalidation['line_type'];
                        } else {
                            ob_start();
                            print_r($numbervalidation['error']['info']);
                            $out1 = ob_get_contents();
                            ob_end_clean();
                            $file = fopen("debug/NumberVerifyAPI" . time() . ".txt", "w");
                            fwrite($file, $out1);
                            fclose($file);

                        }
                    }
                    //****DOUBLE OPT-IN CHANGES****//
                    if($double_optin==1){
                       $this->request->data['Contact']['un_subscribers'] = 2; 
                    }
                    
                    $this->request->data['Contact']['phone_number'] = $phone;
                    $this->request->data['Contact']['user_id'] = $user_id;
                    $this->request->data['Contact']['created'] = date('Y-m-d H:i:s', time());
                    $this->request->data['Contact']['color'] = $this->choosecolor();
                    $this->Contact->save($this->request->data);
                    $contact_id = $this->Contact->id;
                } else {
                    $contact_id = $contact['Contact']['id'];
                }
                app::import('Model', 'ContactGroup');
                $this->ContactGroup = new ContactGroup();
                $contactgroupid = $this->ContactGroup->find('first', array('conditions' => array('ContactGroup.contact_id' => $contact_id, 'ContactGroup.group_id' => $group_id, 'ContactGroup.user_id' => $user_id)));
                if (!empty($contactgroupid)) {
                    if ($contactgroupid['ContactGroup']['un_subscribers'] == 1) {
                        exit;
                        
                        //****DOUBLE OPT-IN CHANGES****//
                        /*$this->request->data['ContactGroup']['id'] = $contactgroupid['ContactGroup']['id'];
                        $this->request->data['ContactGroup']['un_subscribers'] = 0;
                        $this->request->data['ContactGroup']['subscribed_by_sms'] = 1;
                        $this->request->data['ContactGroup']['created'] = date('Y-m-d H:i:s', time());
                        $this->ContactGroup->save($this->request->data);

                        app::import('Model', 'Contact');
                        $this->Contact = new Contact();

                        $contact_arr['Contact']['id'] = $contact_id;
                        $contact_arr['Contact']['un_subscribers'] = 0;
                        $this->Contact->save($contact_arr);

                        if ($someone['User']['email_alert_options'] == 0) {
                            if ($someone['User']['email_alerts'] == 1) {
                                $username = $someone['User']['username'];
                                $email = $someone['User']['email'];
                                $date = date('Y-m-d H:i:s', time());
                                $subject = "New Subscriber to " . $group_name;
                                $sitename = str_replace(' ', '', SITENAME);
                                
                                $Email = new CakeEmail();
                                if(EMAILSMTP==1){
                                    $Email->config('smtp');
                                }
                                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                                $Email->to($email);
                                $Email->subject($subject);
                                $Email->template('new_subscriber_template');
                                $Email->emailFormat('html');
                                $Email->viewVars(array('username' => $username));
                                $Email->viewVars(array('phoneno' => $phone));
                                $Email->viewVars(array('groupname' => $group_name));
                                $Email->viewVars(array('keyword' => $keyword));
                                $Email->viewVars(array('datetime' => $date));
                                $Email->send();
                            }
                        }
                        app::import('Model', 'Group');
                        $this->Group = new Group();
                        $this->request->data['Group']['id'] = $contactgroupid['Group']['id'];
                        $this->request->data['Group']['totalsubscriber'] = $contactgroupid['Group']['totalsubscriber'] + 1;
                        $this->Group->save($this->request->data);
                        if ($group_type == 2) {
                            $address = $group['Group']['property_address'];
                            $price = $group['Group']['property_price'];
                            $bed = $group['Group']['property_bed'];
                            $bath = $group['Group']['property_bath'];
                            $description = $group['Group']['property_description'];
                            $url = $group['Group']['property_url'];

                            $message = $address . "\n" . $price . "\nBed: " . $bed . "\nBath: " . $bath . "\n" . $description . "\n" . $url . "\n";
                            $message = $message . $system_message . ' ' . $auto_message;
                        } elseif ($group_type == 3) {
                            $year = $group['Group']['vehicle_year'];
                            $make = $group['Group']['vehicle_make'];
                            $model = $group['Group']['vehicle_model'];
                            $mileage = $group['Group']['vehicle_mileage'];
                            $price = $group['Group']['vehicle_price'];
                            $description = $group['Group']['vehicle_description'];
                            $url = $group['Group']['vehicle_url'];

                            $message = $year . ' ' . $make . ' ' . $model . "\n" . $mileage . "\n" . $price . "\n" . $description . "\n" . $url . "\n";
                            $message = $message . $system_message . ' ' . $auto_message;

                        } else {
                            $message = $system_message . ' ' . $auto_message;
                        }
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $current_datetime = date("n/d/Y");
                        $message = str_replace('%%CURRENTDATE%%', $current_datetime, $message);
                        //*********** Save to activity timeline
                        $timeline['ActivityTimeline']['user_id'] = $user_id;
                        $timeline['ActivityTimeline']['contact_id'] = $contact_id;
                        $timeline['ActivityTimeline']['activity'] = 1;
                        $timeline['ActivityTimeline']['title'] = 'Contact Subscribed via SMS';
                        $timeline['ActivityTimeline']['description'] = $message;
                        $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                        $this->ActivityTimeline->save($timeline);
                        //*************
                        //$message= $system_message.' '.$auto_message;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        sleep(2);
                        $this->Immediatelyresponder($user_id, $group_id, $phone, $fromnumber);
                        if (!empty($someone['User']['id'])) {
                            $users_sms_balance = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                            if (!empty($users_sms_balance)) {
                                //$credits = $users_sms_balance['User']['sms_balance'];
                                //$user_balance['User']['sms_balance']=$credits-1;
                                $curcredits = $users_sms_balance['User']['sms_balance'];
                                $length = strlen(utf8_decode(substr($message, 0, 1600)));
                                if (strlen($message) != strlen(utf8_decode($message))) {
                                    $credits = ceil($length / 70);
                                } else {
                                    $credits = ceil($length / 160);
                                }
                                $user_balance['User']['sms_balance'] = $curcredits - $credits;
                                $user_balance['User']['id'] = $someone['User']['id'];
                                $this->User->save($user_balance);
                            }
                        }
                        $this->smsmail($someone['User']['id']);


                        if ($someone['User']['capture_email_name'] == 0) {
                            $capture_email_name = NAME_CAPTURE_MSG;
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $capture_email_name);
                            $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                            $length = strlen(utf8_decode(substr($capture_email_name, 0, 1600)));
                            if (strlen($capture_email_name) != strlen(utf8_decode($capture_email_name))) {
                                $credits = ceil($length / 70);
                            } else {
                                $credits = ceil($length / 160);
                            }
                            if (!empty($someone_users)) {
                                //$user_credit['User']['sms_balance']=$someone_users['User']['sms_balance']-1;
                                $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                                $user_credit['User']['id'] = $user_id;
                                $this->User->save($user_credit);
                            }
                        }
                        if ($group['Group']['bithday_enable'] == 1) {
                            if ($someone['User']['birthday_wishes'] == 0) {
                                $birthday_wishes = BIRTHDAY_MSG;
                                $this->Nexmomessage->Key = NEXMO_KEY;
                                $this->Nexmomessage->Secret = NEXMO_SECRET;
                                $this->Nexmomessage->sendsms($phone, $fromnumber, $birthday_wishes);
                                $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                                $length = strlen(utf8_decode(substr($birthday_wishes, 0, 1600)));
                                if (strlen($birthday_wishes) != strlen(utf8_decode($birthday_wishes))) {
                                    $credits = ceil($length / 70);
                                } else {
                                    $credits = ceil($length / 160);
                                }
                                if (!empty($someone_users)) {
                                    //$user_credit['User']['sms_balance']=$someone_users['User']['sms_balance']-1;
                                    $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                                    $user_credit['User']['id'] = $user_id;
                                    $this->User->save($user_credit);
                                }
                            }
                        }
                        if ($group['Group']['notify_signup'] == 1) {
                            $mobile = $group['Group']['mobile_number_input'];
                            $groupname = $group['Group']['group_name'];
                            $message = "New Subscriber Alert: " . $phone . " has joined group " . $groupname;

                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($mobile, $fromnumber, $message);

                            $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                            if (!empty($someone_users)) {
                                $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - 1;
                                $user_credit['User']['id'] = $user_id;
                                $this->User->save($user_credit);
                            }
                        }*/
                    //****DOUBLE OPT-IN CHANGES****//    
                    } elseif ($contactgroupid['ContactGroup']['un_subscribers'] == 0) {
                        if (!empty($someone['User']['id'])) {
                            $users_sms_balance = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                        }
                        $this->smsmail($someone['User']['id']);
                        if ($group_type == 0) {
                            $message = $system_message . ' ' . $auto_message;
                            $current_datetime = date("n/d/Y");
                            $message = str_replace('%%CURRENTDATE%%', $current_datetime, $message);

                            //*********** Save to activity timeline
                            $timeline['ActivityTimeline']['user_id'] = $user_id;
                            $timeline['ActivityTimeline']['contact_id'] = $contact_id;
                            $timeline['ActivityTimeline']['activity'] = 9;
                            $timeline['ActivityTimeline']['title'] = 'Coupon Code Group';
                            $timeline['ActivityTimeline']['description'] = $message;
                            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                            $this->ActivityTimeline->save($timeline);
                            //*************
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                            sleep(2);

                            $curcredits = $users_sms_balance['User']['sms_balance'];
                            $length = strlen(utf8_decode(substr($message, 0, 1600)));
                            if (strlen($message) != strlen(utf8_decode($message))) {
                                $credits = ceil($length / 70);
                            } else {
                                $credits = ceil($length / 160);
                            }
                            $user_balance['User']['sms_balance'] = $curcredits - $credits;
                            $user_balance['User']['id'] = $someone['User']['id'];
                            $this->User->save($user_balance);
                            $this->Immediatelyresponder($user_id, $group_id, $phone, $fromnumber);
                            $this->smsmail($someone['User']['id']);

                            $name = $contactgroupid['Contact']['name'];
                            $email = $contactgroupid['Contact']['email'];
                            $bday = $contactgroupid['Contact']['birthday'];


                            if ($someone['User']['capture_email_name'] == 0 && $name == '') {
                                $capture_email_name = NAME_CAPTURE_MSG;
                                $this->Nexmomessage->Key = NEXMO_KEY;
                                $this->Nexmomessage->Secret = NEXMO_SECRET;
                                sleep(1);
                                $this->Nexmomessage->sendsms($phone, $fromnumber, $capture_email_name);

                                $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                                $length = strlen(utf8_decode(substr($capture_email_name, 0, 1600)));
                                if (strlen($capture_email_name) != strlen(utf8_decode($capture_email_name))) {
                                    $credits = ceil($length / 70);
                                } else {
                                    $credits = ceil($length / 160);
                                }
                                if (!empty($someone_users)) {
                                    //$user_credit['User']['sms_balance']=$someone_users['User']['sms_balance']-1;
                                    $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                                    $user_credit['User']['id'] = $user_id;
                                    $this->User->save($user_credit);
                                }
                            }
                            if ($group['Group']['bithday_enable'] == 1 && $bday == '0000-00-00') {
                                if ($someone['User']['birthday_wishes'] == 0) {
                                    $birthday_wishes = BIRTHDAY_MSG;
                                    $this->Nexmomessage->Key = NEXMO_KEY;
                                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                                    sleep(1);
                                    $this->Nexmomessage->sendsms($phone, $fromnumber, $birthday_wishes);
                                    $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                                    $length = strlen(utf8_decode(substr($birthday_wishes, 0, 1600)));
                                    if (strlen($birthday_wishes) != strlen(utf8_decode($birthday_wishes))) {
                                        $credits = ceil($length / 70);
                                    } else {
                                        $credits = ceil($length / 160);
                                    }
                                    if (!empty($someone_users)) {
                                        //$user_credit['User']['sms_balance']=$someone_users['User']['sms_balance']-1;
                                        $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                                        $user_credit['User']['id'] = $user_id;
                                        $this->User->save($user_credit);
                                    }

                                }
                            }
                        } else {
                            /*if($group_type==2){
                                                       $message='We have already sent you information on this property.';
                                                   }elseif ($group_type==3){
                                                       $message='We have already sent you information on this vehicle.';
                                                   }else{
					               $message='You are already subscribed to this list.';
                                                   }*/

                            $message = $group['Group']['ifmember_message'];
                            $length = strlen(utf8_decode(substr($message, 0, 1600)));
                            if (strlen($message) != strlen(utf8_decode($message))) {
                                $credits = ceil($length / 70);
                            } else {
                                $credits = ceil($length / 160);
                            }
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);

                            //$user_credit['User']['sms_balance']=$someone['User']['sms_balance']-1;
                            $user_credit['User']['sms_balance'] = $someone['User']['sms_balance'] - $credits;
                            $user_credit['User']['id'] = $user_id;
                            $this->User->save($user_credit);
                            $this->smsmail($someone['User']['id']);
                        }
                    //****DOUBLE OPT-IN CHANGES****//     
                    } elseif ($contactgroupid['ContactGroup']['un_subscribers'] == 2) {
                            $message = "Reply Y to confirm that you want to receive SMS messages from ". $someone['User']['company_name'];
                            $length = strlen(utf8_decode(substr($message, 0, 1600)));
                            if (strlen($message) != strlen(utf8_decode($message))) {
                                $credits = ceil($length / 70);
                            } else {
                                $credits = ceil($length / 160);
                            }
        
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
        
                            $user_credit['User']['sms_balance'] = $someone['User']['sms_balance'] - $credits;
                            $user_credit['User']['id'] = $user_id;
                            $this->User->save($user_credit);
                            $this->smsmail($user_id);
                            exit;
                        }
                } else {
                    
                    if (!empty($contact) && NUMVERIFY != '') {
                        $numbervalidation = $this->validateNumber($phone);
                        $errorcode = $numbervalidation['error']['code'];

                        if ($errorcode == '') {
                            $contactarr['carrier'] = $numbervalidation['carrier'];
                            $contactarr['location'] = $numbervalidation['location'];
                            $contactarr['phone_country'] = $numbervalidation['country_name'];
                            $contactarr['line_type'] = $numbervalidation['line_type'];
                        } else {
                            ob_start();
                            print_r($numbervalidation['error']['info']);
                            $out1 = ob_get_contents();
                            ob_end_clean();
                            $file = fopen("debug/NumberVerifyAPI" . time() . ".txt", "w");
                            fwrite($file, $out1);
                            fclose($file);

                        }
                        $contactarr['id'] = $contact_id;
                        $this->Contact->save($contactarr);
                    }
                    
                    app::import('Model', 'ContactGroup');
                    $this->ContactGroup = new ContactGroup();
                    $this->request->data['ContactGroup']['user_id'] = $user_id;;
                    $this->request->data['ContactGroup']['contact_id'] = $contact_id;
                    $this->request->data['ContactGroup']['group_id'] = $group_id;
                    $this->request->data['ContactGroup']['group_subscribers'] = $keyword;
                    $this->request->data['ContactGroup']['subscribed_by_sms'] = 1;
                    $this->request->data['ContactGroup']['created'] = date('Y-m-d H:i:s', time());
                    //****DOUBLE OPT-IN CHANGES****//
                    if($double_optin==1){
                       $this->request->data['ContactGroup']['un_subscribers'] = 2; 
                    }
                    
                    $this->ContactGroup->save($this->request->data);
                    
                    //****DOUBLE OPT-IN CHANGES****//
                    if($double_optin==0){
                        if ($someone['User']['email_alert_options'] == 0) {
                            if ($someone['User']['email_alerts'] == 1) {
                                $username = $someone['User']['username'];
                                $email = $someone['User']['email'];
                                $date = date('Y-m-d H:i:s', time());
                                $subject = "New Subscriber to " . $group_name;
                                $sitename = str_replace(' ', '', SITENAME);
                                /*$this->Email->to = $email;
                                $this->Email->subject = $subject;
                                $this->Email->from = $sitename;
                                $this->Email->template = 'new_subscriber_template';
                                $this->Email->sendAs = 'html';
                                $this->Email->Controller->set('username', $username);
                                $this->Email->Controller->set('phoneno', $phone);
                                $this->Email->Controller->set('groupname', $group_name);
                                $this->Email->Controller->set('keyword', $keyword);
                                $this->Email->Controller->set('datetime', $date);
                                $this->Email->send();*/
                                
                                $Email = new CakeEmail();
                                if(EMAILSMTP==1){
                                    $Email->config('smtp');
                                }
                                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                                $Email->to($email);
                                $Email->subject($subject);
                                $Email->template('new_subscriber_template');
                                $Email->emailFormat('html');
                                $Email->viewVars(array('username' => $username));
                                $Email->viewVars(array('phoneno' => $phone));
                                $Email->viewVars(array('groupname' => $group_name));
                                $Email->viewVars(array('keyword' => $keyword));
                                $Email->viewVars(array('datetime' => $date));
                                $Email->send();
                            }
                        }
                        app::import('Model', 'Group');
                        $this->Group = new Group();
                        $this->request->data['Group']['id'] = $group_id;
                        $this->request->data['Group']['totalsubscriber'] = $totalsubscriber + 1;
                        $this->Group->save($this->request->data);
                        if ($group_type == 2) {
                            $address = $group['Group']['property_address'];
                            $price = $group['Group']['property_price'];
                            $bed = $group['Group']['property_bed'];
                            $bath = $group['Group']['property_bath'];
                            $description = $group['Group']['property_description'];
                            $url = $group['Group']['property_url'];
    
                            $message = $address . "\n" . $price . "\nBed: " . $bed . "\nBath: " . $bath . "\n" . $description . "\n" . $url . "\n";
                            $message = $message . $system_message . ' ' . $auto_message;
                        } elseif ($group_type == 3) {
                            $year = $group['Group']['vehicle_year'];
                            $make = $group['Group']['vehicle_make'];
                            $model = $group['Group']['vehicle_model'];
                            $mileage = $group['Group']['vehicle_mileage'];
                            $price = $group['Group']['vehicle_price'];
                            $description = $group['Group']['vehicle_description'];
                            $url = $group['Group']['vehicle_url'];
    
                            $message = $year . ' ' . $make . ' ' . $model . "\n" . $mileage . "\n" . $price . "\n" . $description . "\n" . $url . "\n";
                            $message = $message . $system_message . ' ' . $auto_message;
    
                        } else {
                            $message = $system_message . ' ' . $auto_message;
                        }
                        if ($sms_type == 1) {
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            //$message= $system_message.' '.$auto_message;
                            $current_datetime = date("n/d/Y");
                            $message = str_replace('%%CURRENTDATE%%', $current_datetime, $message);
    
    //*********** Save to activity timeline
                            $timeline['ActivityTimeline']['user_id'] = $user_id;
                            $timeline['ActivityTimeline']['contact_id'] = $contact_id;
                            $timeline['ActivityTimeline']['activity'] = 1;
                            $timeline['ActivityTimeline']['title'] = 'Contact Subscribed via SMS';
                            $timeline['ActivityTimeline']['description'] = $message;
                            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                            $this->ActivityTimeline->save($timeline);
                            //*************
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                            sleep(2);
                            $this->Immediatelyresponder($user_id, $group_id, $phone, $fromnumber);
                        }
                        if (!empty($someone['User']['id'])) {
                            $users_sms_balance = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                            //$credits = $users_sms_balance['User']['sms_balance'];
                            $curcredits = $users_sms_balance['User']['sms_balance'];
                            $length = strlen(utf8_decode(substr($message, 0, 1600)));
    
                            if (!empty($users_sms_balance)) {
                                //$this->request->data['User']['sms_balance']=$credits-1;
                                if (strlen($message) != strlen(utf8_decode($message))) {
                                    $credits = ceil($length / 70);
                                } else {
                                    $credits = ceil($length / 160);
                                }
                                $this->request->data['User']['sms_balance'] = $curcredits - $credits;
                                $this->request->data['User']['id'] = $someone['User']['id'];
                                $this->User->save($this->request->data);
                            }
                        }
                        if ($someone['User']['capture_email_name'] == 0) {
                            $capture_email_name = NAME_CAPTURE_MSG;
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            sleep(1);
                            $this->Nexmomessage->sendsms($phone, $fromnumber, $capture_email_name);
    
                            $users_sms_balance = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                            $curcredits = $users_sms_balance['User']['sms_balance'];
    
                            $length = strlen(utf8_decode(substr($capture_email_name, 0, 1600)));
                            if (strlen($capture_email_name) != strlen(utf8_decode($capture_email_name))) {
                                $credits = ceil($length / 70);
                            } else {
                                $credits = ceil($length / 160);
                            }
                            if (!empty($users_sms_balance)) {
                                $this->request->data['User']['sms_balance'] = $curcredits - $credits;
                                $this->request->data['User']['id'] = $someone['User']['id'];
                                $this->User->save($this->request->data);
                            }
                        }
                        if ($group['Group']['bithday_enable'] == 1) {
                            if ($someone['User']['birthday_wishes'] == 0) {
                                $birthday_wishes = BIRTHDAY_MSG;
                                $this->Nexmomessage->Key = NEXMO_KEY;
                                $this->Nexmomessage->Secret = NEXMO_SECRET;
                                sleep(1);
                                $this->Nexmomessage->sendsms($phone, $fromnumber, $birthday_wishes);
    
                                $users_sms_balance = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                                $curcredits = $users_sms_balance['User']['sms_balance'];
    
                                $length = strlen(utf8_decode(substr($birthday_wishes, 0, 1600)));
                                if (strlen($birthday_wishes) != strlen(utf8_decode($birthday_wishes))) {
                                    $credits = ceil($length / 70);
                                } else {
                                    $credits = ceil($length / 160);
                                }
                                if (!empty($users_sms_balance)) {
                                    $this->request->data['User']['sms_balance'] = $curcredits - $credits;
                                    $this->request->data['User']['id'] = $someone['User']['id'];
                                    $this->User->save($this->request->data);
                                }
    
                            }
                        }
    
                        if ($group['Group']['notify_signup'] == 1) {
                            $mobile = $group['Group']['mobile_number_input'];
                            $groupname = $group['Group']['group_name'];
                            $message = "New Subscriber Alert: " . $phone . " has joined group " . $groupname;
    
                            $this->Nexmomessage->Key = NEXMO_KEY;
                            $this->Nexmomessage->Secret = NEXMO_SECRET;
                            $this->Nexmomessage->sendsms($mobile, $fromnumber, $message);
    
                            $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
    
                            if (!empty($someone_users)) {
                                $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - 1;
                                $user_credit['User']['id'] = $user_id;
                                $this->User->save($user_credit);
                            }
                        }
                        $this->smsmail($someone['User']['id']);
                }else{
                    //****DOUBLE OPT-IN CHANGES****//
                    $double_optin_msg = "Reply Y to confirm that you want to receive SMS messages from ". $someone['User']['company_name'];
                    $this->Nexmomessage->Key = NEXMO_KEY;
                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                    $this->Nexmomessage->sendsms($phone, $fromnumber, $double_optin_msg);
                    //sleep(2);
                    $someone_users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));

                    $length = strlen(utf8_decode(substr($double_optin_msg, 0, 1600)));
                    if (strlen($double_optin_msg) != strlen(utf8_decode($double_optin_msg))) {
                        $credits = ceil($length / 70);
                    } else {
                        $credits = ceil($length / 160);
                    }
                    if (!empty($someone_users)) {
                        $user_credit['User']['sms_balance'] = $someone_users['User']['sms_balance'] - $credits;
                        $user_credit['User']['id'] = $user_id;
                        $this->User->save($user_credit);
                    }
                }
            }
        }
    } elseif (!empty($answers123)) {
            $answers_id = $answers123['Option']['id'];
            $autorsponder_message = $answers123['Option']['autorsponder_message'];
            if (!empty($contact)) {
                $contact_id = $contact['Contact']['id'];
                app::import('Model', 'AnswerSubscriber');
                $this->AnswerSubscriber = new AnswerSubscriber();
                $ansersubs = $this->AnswerSubscriber->find('first', array('conditions' => array('AnswerSubscriber.contact_id' => $contact_id, 'AnswerSubscriber.question_id' => $question_id)));
                $usersbalance = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                if ($usersbalance['User']['sms_balance'] > 0) {
                    if (empty($ansersubs)) {
                        if ($answers_id != '') {
                            $this->Option->id = $answers_id;
                            $this->request->data['Option']['count'] = $answers123['Option']['count'] + 1;
                            $this->Option->save($this->request->data);
                        }
                        $this->request->data['AnswerSubscriber']['answer_id'] = $answers_id;
                        $this->request->data['AnswerSubscriber']['question_id'] = $question_id;
                        $this->request->data['AnswerSubscriber']['contact_id'] = $contact_id;
                        $this->request->data['AnswerSubscriber']['created'] = date('Y-m-d H:i:s', time());
                        $this->AnswerSubscriber->save($this->request->data);
                        app::import('Model', 'User');
                        $this->User = new User();
                        $users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                        $credits = $users['User']['sms_balance'];
                        $this->request->data['User']['sms_balance'] = $credits - 1;
                        $this->request->data['User']['id'] = $user_id;
                        $this->User->save($this->request->data);
                        //$this->smsmail($users['User']['id']);
                        if ($autorsponder_message != '') {
                            $message = $autorsponder_message;
                        } else {
                            $message = $answers123['Question']['autoreply_message'];
                        }
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);

//*********** Save to activity timeline
                        $timeline['ActivityTimeline']['user_id'] = $user_id;
                        $timeline['ActivityTimeline']['contact_id'] = $contact_id;
                        $timeline['ActivityTimeline']['activity'] = 2;
                        $timeline['ActivityTimeline']['title'] = 'Voted in Poll';
                        $timeline['ActivityTimeline']['description'] = 'Contact voted in poll: ' . $answers123['Question']['question'];
                        $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
                        $this->ActivityTimeline->save($timeline);
                        //*************
                    } else {
                        app::import('Model', 'User');
                        $this->User = new User();
                        $users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                        $credits = $users['User']['sms_balance'];
                        $this->request->data['User']['sms_balance'] = $credits - 1;
                        $this->request->data['User']['id'] = $user_id;
                        $this->User->save($this->request->data);
                        //$this->smsmail($users['User']['id']);
                        $message = "You have already voted in this poll";
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                        $this->smsmail($users['User']['id']);
                    }
                }
            }
        } else if (!empty($contestkeywords)) {
            app::import('Model', 'ContestSubscriber');
            $this->ContestSubscriber = new ContestSubscriber();
            $contestsubcount = $this->ContestSubscriber->find('count', array('conditions' => array('ContestSubscriber.phone_number' => $phone, 'ContestSubscriber.contest_id' => $contest_id)));
            $credits = $someone['User']['sms_balance'];
            
            if ($contestkeywords['Contest']['winning_phone_number'] !=''){
                $this->request->data['User']['sms_balance'] = $credits - 1;
                $this->request->data['User']['id'] = $contestkeywords['Contest']['user_id'];
                $this->User->save($this->request->data);
                $message1 = "A winner was already selected. Stay tuned for other contests we run!";
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $this->Nexmomessage->sendsms($phone, $fromnumber, $message1);
                $this->smsmail($user_id);
                exit;
            }

            if ($contestsubcount == $contestkeywords['Contest']['maxentries']) {
                $this->request->data['User']['sms_balance'] = $credits - 1;
                $this->request->data['User']['id'] = $contestkeywords['Contest']['user_id'];
                $this->User->save($this->request->data);
                $message1 = "You have already entered this contest " . $contestsubcount . " time(s) which is the maximum number allowed.";
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $this->Nexmomessage->sendsms($phone, $fromnumber, $message1);
                $this->smsmail($user_id);
                exit;
            }

            $current_date = date('Y-m-d');
            if ($contestkeywords['Contest']['startdate'] > $current_date) {
                $this->request->data['User']['sms_balance'] = $credits - 1;
                $this->request->data['User']['id'] = $contestkeywords['Contest']['user_id'];
                $this->User->save($this->request->data);
                $message = "Contest " . $contestkeywords['Contest']['group_name'] . " hasn't started yet. It begins on " . date('m/d/Y', strtotime($contestkeywords['Contest']['startdate'])) . "";
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                $this->smsmail($contestkeywords['Contest']['user_id']);
                exit;
            } else if ($contestkeywords['Contest']['enddate'] < $current_date) {
                $this->request->data['User']['sms_balance'] = $credits - 1;
                $this->request->data['User']['id'] = $contestkeywords['Contest']['user_id'];
                $this->User->save($this->request->data);
                $message = "Contest " . $contestkeywords['Contest']['group_name'] . " ended on " . date('m/d/Y', strtotime($contestkeywords['Contest']['enddate'])) . "";
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $this->Nexmomessage->sendsms($phone, $fromnumber, $message);
                $this->smsmail($contestkeywords['Contest']['user_id']);
                exit;
            }

            $contact = $this->ContactGroup->find('first', array('conditions' => array('Contact.phone_number' => $phone, 'Contact.user_id' => $user_id, 'ContactGroup.group_id' => $contestkeywords['Contest']['group_id'])));

            if (empty($contact)) {
                if (NUMVERIFY != '') {
                    $numbervalidation = $this->validateNumber($phone);
                    $errorcode = $numbervalidation['error']['code'];

                    if ($errorcode == '') {
                        $this->request->data['Contact']['carrier'] = $numbervalidation['carrier'];
                        $this->request->data['Contact']['location'] = $numbervalidation['location'];
                        $this->request->data['Contact']['phone_country'] = $numbervalidation['country_name'];
                        $this->request->data['Contact']['line_type'] = $numbervalidation['line_type'];
                    } else {
                        ob_start();
                        print_r($numbervalidation['error']['info']);
                        $out1 = ob_get_contents();
                        ob_end_clean();
                        $file = fopen("debug/NumberVerifyAPI" . time() . ".txt", "w");
                        fwrite($file, $out1);
                        fclose($file);
                    }
                }
                app::import('Model', 'Contact');
                $this->Contact = new Contact();
                $this->request->data['Contact']['phone_number'] = $phone;
                $this->request->data['Contact']['user_id'] = $user_id;
                $this->request->data['Contact']['created'] = date('Y-m-d H:i:s', time());
                $this->request->data['Contact']['color'] = $this->choosecolor();
                $this->Contact->save($this->request->data);
                $contact_id = $this->Contact->id;

                $this->request->data['ContactGroup']['user_id'] = $user_id;
                $this->request->data['ContactGroup']['contact_id'] = $contact_id;
                $this->request->data['ContactGroup']['group_id'] = $contestkeywords['Contest']['group_id'];
                $this->request->data['ContactGroup']['group_subscribers'] = $contestkeywords['Group']['keyword'];
                $this->request->data['ContactGroup']['subscribed_by_sms'] = 1;
                $this->request->data['ContactGroup']['created'] = date('Y-m-d H:i:s', time());
                $this->ContactGroup->save($this->request->data);
                if ($someone['User']['email_alert_options'] == 0) {
                    if ($someone['User']['email_alerts'] == 1) {
                        $username = $someone['User']['username'];
                        $email = $someone['User']['email'];
                        $date = date('Y-m-d H:i:s', time());
                        $subject = "New Subscriber to " . $contestkeywords['Group']['group_name'];
                        $sitename = str_replace(' ', '', SITENAME);
                        /*$this->Email->to = $email;
                        $this->Email->subject = $subject;
                        $this->Email->from = $sitename;
                        $this->Email->template = 'new_subscriber_template';
                        $this->Email->sendAs = 'html';
                        $this->Email->Controller->set('username', $username);
                        $this->Email->Controller->set('phoneno', $phone);
                        $this->Email->Controller->set('groupname', $contestkeywords['Group']['group_name']);
                        $this->Email->Controller->set('keyword', $contestkeywords['Group']['keyword']);
                        $this->Email->Controller->set('datetime', $date);
                        $this->Email->send();*/
                        
                        $Email = new CakeEmail();
                        if(EMAILSMTP==1){
                            $Email->config('smtp');
                        }
                        $Email->from(array(SUPPORT_EMAIL => SITENAME));
                        $Email->to($email);
                        $Email->subject($subject);
                        $Email->template('new_subscriber_template');
                        $Email->emailFormat('html');
                        $Email->viewVars(array('username' => $username));
                        $Email->viewVars(array('phoneno' => $phone));
                        $Email->viewVars(array('groupname' => $contestkeywords['Group']['group_name']));
                        $Email->viewVars(array('keyword' => $contestkeywords['Group']['keyword']));
                        $Email->viewVars(array('datetime' => $date));
                        $Email->send();
                    }
                }
                app::import('Model', 'Group');
                $this->Group = new Group();
                $this->request->data['Group']['id'] = $contestkeywords['Contest']['group_id'];
                $this->request->data['Group']['totalsubscriber'] = $contestkeywords['Group']['totalsubscriber'] + 1;
                $this->Group->save($this->request->data);
            }

            $this->request->data['ContestSubscriber']['user_id'] = $contestkeywords['Contest']['user_id'];
            $this->request->data['ContestSubscriber']['contest_id'] = $contestkeywords['Contest']['id'];
            $this->request->data['ContestSubscriber']['phone_number'] = $phone;
            $this->ContestSubscriber->save($this->request->data);
            app::import('Model', 'Contest');
            $this->Contest = new Contest();
            $totalsubscriberdata = $this->Contest->find('first', array('conditions' => array('Contest.id' => $contest_id)));
            $Contestdata['id'] = $contest_id;
            $Contestdata['totalsubscriber'] = $totalsubscriberdata['Contest']['totalsubscriber'] + 1;
            $this->Contest->save($Contestdata);
            $this->request->data['User']['sms_balance'] = $credits - 1;
            $this->request->data['User']['id'] = $user_id;
            $this->User->save($this->request->data);
            $this->Nexmomessage->Key = NEXMO_KEY;
            $this->Nexmomessage->Secret = NEXMO_SECRET;
            $this->Nexmomessage->sendsms($phone, $fromnumber, $contestkeywords['Contest']['system_message']);
            $this->smsmail($user_id);
            //*********** Save to activity timeline
            $timeline['ActivityTimeline']['user_id'] = $contestkeywords['Contest']['user_id'];
            $timeline['ActivityTimeline']['contact_id'] = $contact_id;
            $timeline['ActivityTimeline']['activity'] = 3;
            $timeline['ActivityTimeline']['title'] = 'Entered Contest';
            $timeline['ActivityTimeline']['description'] = 'Contact entered contest: ' . $contestkeywords['Contest']['group_name'];
            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
            $this->ActivityTimeline->save($timeline);
            //*************
        } else {
            //saving logs
//*********** Save to activity timeline
            $timeline['ActivityTimeline']['user_id'] = $someone['User']['id'];
            $timeline['ActivityTimeline']['contact_id'] = $contact_id;
            $timeline['ActivityTimeline']['activity'] = 10;
            $timeline['ActivityTimeline']['title'] = 'Incoming SMS Message';
            $timeline['ActivityTimeline']['description'] = $_REQUEST['text'];
            $timeline['ActivityTimeline']['created'] = date('Y-m-d H:i:s');
            $this->ActivityTimeline->save($timeline);
            //*************
            Controller::loadModel('Log');
            $this->Log->create();

            $this->request->data['Log']['user_id'] = $someone['User']['id'];
            $this->request->data['Log']['phone_number'] = $_REQUEST['From'];
            $this->request->data['Log']['name'] = $contactname;
            $this->request->data['Log']['contact_id'] = $contact_id;
            $this->request->data['Log']['inbox_type'] = 1;
            $this->request->data['Log']['email_to_sms_number'] = $_REQUEST['To'];
            $this->request->data['Log']['text_message'] = $_REQUEST['text'];
            $this->request->data['Log']['created'] = date('Y-m-d H:i:s', time());
            $this->request->data['Log']['sms_status'] = 'received';
            $this->request->data['Log']['route'] = 'inbox';
            pr($this->request->data['Log']);
            if ($someone['User']['email_to_sms'] == 0) {
                $random_generator = $this->random_generator(15);
                
                $subject = 'Incoming SMS to Email Notice-' . $random_generator;
                /*$this->Email->to = $someone['User']['email'];
                $this->Email->subject = $subject;
                $this->Email->from = SUPPORT_EMAIL;
                $this->Email->template = 'sendemail';
                $this->Email->sendAs = 'html';
                $this->set('phone', $_REQUEST['From']);
                $this->set('name', $contactname);
                $this->set('message', $_REQUEST['text']);
                $this->Email->send();*/
                
                $Email = new CakeEmail();
                if(EMAILSMTP==1){
                    $Email->config('smtp');
                }
                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                $Email->to($someone['User']['email']);
                $Email->subject($subject);
                $Email->template('sendemail');
                $Email->emailFormat('html');
                $Email->viewVars(array('phone' => $_REQUEST['From']));
                $Email->viewVars(array('name' => $contactname));
                $Email->viewVars(array('message' => $_REQUEST['text']));
                $Email->send();
                    
                $this->request->data['Log']['ticket'] = $random_generator;
            }
            $this->Log->save($this->request->data);
            if ($contact_id > 0) {
                app::import('Model', 'Contact');
                $this->Contact = new Contact();
                $contact_arra_save['Contact']['id'] = $contact_id;
                $contact_arra_save['Contact']['lastmsg'] = date('Y-m-d H:i:s');
                $this->Contact->save($contact_arra_save);
            } else {
                //********************
                if ($someone['User']['sms_balance'] > 0 && $someone['User']['incoming_nonkeyword'] == 1) {

                    $this->User->id = $someone['User']['id'];
                    if ($this->User->id != '') {
                        $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - 1));
                    }
                    $this->Log = new Log();
                    $message = $someone['User']['nonkeyword_autoresponse'];
                    $to = $_REQUEST['From'];
                    $from = $_REQUEST['To'];
                    $this->Nexmomessage->Key = NEXMO_KEY;
                    $this->Nexmomessage->Secret = NEXMO_SECRET;
                    $response = $this->Nexmomessage->sendsms($to, $from, $message);
                    foreach ($response->messages as $doc) {
                        $message_id = $doc->messageid;
                        if ($message_id != '') {
                            $status = $doc->status;
                            $message_id = $doc->messageid;
                        } else {
                            $status = $doc->status;
                            $errortext = $doc->errortext;
                        }
                    }
                    $this->request->data['Log']['contact_id'] = 0;
                    $this->request->data['Log']['group_sms_id'] = 0;
                    $this->request->data['Log']['sms_id'] = $message_id;
                    $this->request->data['Log']['user_id'] = $user_id;
                    $this->request->data['Log']['group_id'] = 0;
                    $this->request->data['Log']['phone_number'] = $to;
                    $this->request->data['Log']['text_message'] = $message;
                    $this->request->data['Log']['route'] = 'outbox';
                    $this->request->data['Log']['sms_status'] = '';
                    $this->request->data['Log']['error_message'] = '';
                    if ($Status != 0) {
                        $this->request->data['Log']['sms_status'] = 'failed';
                        $ErrorMessage = $errortext;
                        $this->request->data['Log']['error_message'] = $ErrorMessage;
                    }
                    if ($message_id != '') {
                        $this->request->data['Log']['sms_status'] = 'sent';
                    }
                    $this->Log->save($this->request->data);
                }
            }

            if ($someone['User']['sms_balance'] > 0) {

                if ($someone['User']['incomingsms_alerts'] == 0) {
                    if ($someone['User']['incomingsms_emailalerts'] == 1) {
                        $username = $someone['User']['username'];
                        $email = $someone['User']['email'];
                        $from = $_REQUEST['From'];
                        $date = date('Y-m-d H:i:s', time());
                        $sitename = str_replace(' ', '', SITENAME);
                        $subject = "New Incoming SMS To Your Account At " . SITENAME;
                        /*$this->Email->to = $email;
                        $this->Email->subject = $subject;
                        $this->Email->from = $sitename;
                        $this->Email->template = 'incoming_sms_email_alert';
                        $this->Email->sendAs = 'html';
                        $this->Email->Controller->set('username', $username);
                        $this->Email->Controller->set('from', $from);
                        $this->Email->Controller->set('name', $contactname);
                        $this->Email->Controller->set('body', $_REQUEST['text']);
                        $this->Email->send();*/
                        
                        $Email = new CakeEmail();
                        if(EMAILSMTP==1){
                            $Email->config('smtp');
                        }
                        $Email->from(array(SUPPORT_EMAIL => SITENAME));
                        $Email->to($email);
                        $Email->subject($subject);
                        $Email->template('incoming_sms_email_alert');
                        $Email->emailFormat('html');
                        $Email->viewVars(array('username' => $username));
                        $Email->viewVars(array('from' => $from));
                        $Email->viewVars(array('name' => $contactname));
                        $Email->viewVars(array('body' => $_REQUEST['text']));
                        $Email->send();
                
                    } elseif ($someone['User']['incomingsms_emailalerts'] == 2) {
                        $this->User->id = $someone['User']['id'];
                        if ($this->User->id != '') {
                            $this->User->saveField('sms_balance', ($someone['User']['sms_balance'] - 1));
                        }
                        $this->Log = new Log();
                        //$message = SITENAME." Incoming SMS Alert: " .$_REQUEST['text'];
                        $message = "Incoming SMS Alert From: " . $_REQUEST['From'] . " - " . $_REQUEST['text'];
                        $to = $username = $someone['User']['smsalerts_number'];
                        $from = $_REQUEST['To'];
                        $this->Nexmomessage->Key = NEXMO_KEY;
                        $this->Nexmomessage->Secret = NEXMO_SECRET;
                        $response = $this->Nexmomessage->sendsms($to, $from, $message);
                        foreach ($response->messages as $doc) {
                            $message_id = $doc->messageid;
                            if ($message_id != '') {
                                $status = $doc->status;
                                $message_id = $doc->messageid;
                            } else {
                                $status = $doc->status;
                                $errortext = $doc->errortext;
                            }
                        }
                        $this->request->data['Log']['contact_id'] = 0;
                        $this->request->data['Log']['group_sms_id'] = 0;
                        $this->request->data['Log']['sms_id'] = $message_id;
                        $this->request->data['Log']['user_id'] = $user_id;
                        $this->request->data['Log']['group_id'] = 0;
                        $this->request->data['Log']['phone_number'] = $to;
                        $this->request->data['Log']['text_message'] = $message;
                        $this->request->data['Log']['route'] = 'outbox';
                        $this->request->data['Log']['sms_status'] = '';
                        $this->request->data['Log']['error_message'] = '';
                        if ($Status != 0) {
                            $this->request->data['Log']['sms_status'] = 'failed';
                            $ErrorMessage = $errortext;
                            $this->request->data['Log']['error_message'] = $ErrorMessage;
                        }
                        if ($message_id != '') {
                            $this->request->data['Log']['sms_status'] = 'sent';
                        }
                        $this->Log->save($this->request->data);
                    }
                }
            }
        }
    }

    function voiceCallbackValue()
    {
        $this->autoRender = false;
        $_REQUEST['To'] = str_replace('+', '', $_REQUEST['voicenumber']);
        $_REQUEST['From'] = str_replace('+1', '', $_REQUEST['nexmo_caller_id']);
        Controller::loadModel('User');
        $someone = $this->User->find('first', array('conditions' => array('assigned_number' => '' . trim($_REQUEST['To']) . '')));
        // pr($someone);
        if (empty($someone)) {
            app::import('Model', 'UserNumber');
            $this->UserNumber = new UserNumber();
            $someone = $this->UserNumber->find('first', array('conditions' => array('UserNumber.number' => '' . trim($_REQUEST['To']) . '')));
        }
        date_default_timezone_set($someone['User']['timezone']);
        $active = $someone['User']['active'];
        if ($someone['User']['voice_balance'] > 0 && $active == 1) {
            if ($someone['User']['incomingcall_forward'] == 0 && $someone['User']['assign_callforward'] == trim($_REQUEST['To'])) {
                Controller::loadModel('Log');
                $this->Log->create();
                $this->request->data['Log']['user_id'] = $someone['User']['id'];
                $this->request->data['Log']['phone_number'] = $_REQUEST['From'];
                $this->request->data['Log']['voice_url'] = $_REQUEST['nexmo_call_id'];
                $this->request->data['Log']['route'] = 'inbox';
                $this->request->data['Log']['msg_type'] = 'callforward';
                ob_start();
                echo "<pre>";
                print_r($_REQUEST);
                echo "</pre>";
                $out1 = ob_get_contents();
                ob_end_clean();
                $file = fopen("debug/nexmocallforward" . time() . ".txt", "w");
                fwrite($file, $out1);
                fclose($file);
                $this->Log->save($this->request->data);
                header("content-type: text/xml");
                echo '<?xml version="1.0" encoding="UTF-8"?>
						<vxml version = "2.1" >
							<form>
								<transfer name="result" dest="tel:+' . $someone['User']['callforward_number'] . '" bridge="true" connecttimeout="20s">
                                                                </transfer>
                                                                
							</form>
						</vxml>';
                exit;

            } else {
                //saving logs
                Controller::loadModel('User');
                $usersvoicebalance = $this->User->find('first', array('conditions' => array('User.id' => $someone['User']['id'])));
                if ($usersvoicebalance['User']['email_alert_credit_options'] == 0) {
                    if ($usersvoicebalance['User']['voice_balance'] <= $usersvoicebalance['User']['low_voice_balances']) {
                        if ($usersvoicebalance['User']['VM_credit_balance_email_alerts'] == 0) {
                            $username = $usersvoicebalance['User']['username'];
                            $email = $usersvoicebalance['User']['email'];
                            $sitename = str_replace(' ', '', SITENAME);
                            $subject = "Low Voice Credit Balance";
                            /*$this->Email->to = $email;
                            $this->Email->subject = $subject;
                            $this->Email->from = $sitename;
                            $this->Email->template = 'low_voice_credit_template';
                            $this->Email->sendAs = 'html';
                            $this->Email->Controller->set('username', $username);
                            $this->Email->Controller->set('low_voice_balances', $usersvoicebalance['User']['low_voice_balances']);
                            $this->Email->send();*/
                            
                            $Email = new CakeEmail();
                            if(EMAILSMTP==1){
                                $Email->config('smtp');
                            }
                            $Email->from(array(SUPPORT_EMAIL => SITENAME));
                            $Email->to($email);
                            $Email->subject($subject);
                            $Email->template('low_voice_credit_template');
                            $Email->emailFormat('html');
                            $Email->viewVars(array('username' => $username));
                            $Email->viewVars(array('low_voice_balances' => $usersvoicebalance['User']['low_voice_balances']));
                            $Email->send();
                    
                            $this->User->id = $usersvoicebalance['User']['id'];
                            $this->User->saveField('VM_credit_balance_email_alerts', 1);
                        }
                    }
                }
                Controller::loadModel('Log');
                $this->Log->create();
                $this->request->data['Log']['user_id'] = $someone['User']['id'];
                $this->request->data['Log']['phone_number'] = $_REQUEST['From'];
                $this->request->data['Log']['voice_url'] = $_REQUEST['call-id'];
                $this->request->data['Log']['route'] = 'inbox';
                $this->request->data['Log']['msg_type'] = 'voice';
                $this->Log->save($this->request->data);
                $log_id = $this->Log->id;
                ob_start();
                echo "<pre>";
                print_r($_REQUEST);
                echo "</pre>";
                $out1 = ob_get_contents();
                ob_end_clean();
                $file = fopen("debug/nexmocall" . time() . ".txt", "w");
                fwrite($file, $out1);
                fclose($file);
                if ($someone['User']['welcome_msg_type'] == 1) {
                    $msg = '<audio src="' . SITE_URL . '/mp3/' . $someone['User']['mp3'] . '"/>';
                } else {
                    $msg = '<prompt>' . $someone['User']['defaultgreeting'] . '</prompt>';
                }
                header("content-type: text/xml");
                echo '<?xml version="1.0" encoding="UTF-8"?>
						<vxml version = "2.1" >
							<form>
								<record name="recording" beep="true" dtmfterm="true" maxtime="60s">';
                echo $msg;
                echo '<prompt xml:lang="en-us-male">
											After you are finished, press the star key to indicate that you are done recording.
										</prompt>
									<filled>
										<submit next="' . SITE_URL . "/nexmos/voicerecording/" . $log_id . '" method="post"  enctype="multipart/form-data"/>
									</filled>
								</record>
							</form>
						</vxml>';
                exit;

            }
        } else {
            header("content-type: text/xml");
            echo '<?xml version="1.0" encoding="UTF-8"?>
					<vxml version = "2.1" >
						<form>
							<block>
								<prompt xml:lang="en-us-male">
								</prompt>
							</block>
						</form>
					</vxml>';
            exit;
        }
    }

    function voicerecording($log_id = null)
    {
        $this->autoRender = false;
        ob_start();
        echo "<pre>";
        print_r($_REQUEST);
        print_r($_FILES);
        echo "</pre>";
        $out1 = ob_get_contents();
        ob_end_clean();
        $file = fopen("debug/voicerecording" . time() . ".txt", "w");
        fwrite($file, $out1);
        fclose($file);
        if (!empty($_FILES['recording']['name'])) {
            $temp_name = $_FILES['recording']['tmp_name'];
            $name = $_FILES['recording']['name'];
            move_uploaded_file($temp_name, "voicemail/" . $name);
            $recordingurl = SITE_URL . '/voicemail/' . $name;
            $this->Log->recursive = -1;
            $someone = $this->Log->find('first', array('conditions' => array('Log.id' => $log_id)));
            if (!empty($someone)) {
                $this->request->data['Log']['id'] = $log_id;
                $this->request->data['Log']['voice_url'] = $recordingurl;
                $this->Log->save($this->request->data);

                Controller::loadModel('User');
                $user = $this->User->find('first', array('conditions' => array('User.id' => $someone['Log']['user_id'])));

                if (!empty($user) && trim($someone['User']['voicemailnotifymail']!='')) {
                    #pr($someone);die();
                    $this->User->id = $user['User']['id'];
                    $email = $user['User']['voicemailnotifymail'];
                    #$email = 'simersd@gmail.com';
                    $id = $user['User']['id'];
                    $first_name = $user['User']['first_name'];
                    $sitename = str_replace(' ', '', SITENAME);
                    $subject = "New Voicemail: " . SITENAME . "";
                    $comment = SITE_URL;
                    #$this->Email->delivery = 'debug';
                    /*$this->Email->to = $email;
                    $this->Email->subject = $subject;
                    $this->Email->from = $sitename;
                    $this->Email->template = 'newvoicemail';
                    $this->Email->sendAs = 'html';
                    $this->Email->Controller->set('first_name', $first_name);
                    $this->Email->Controller->set('comment', $comment);
                    $this->Email->Controller->set('email', $email);
                    $this->Email->send();*/
                    
                    $Email = new CakeEmail();
                    if(EMAILSMTP==1){
                        $Email->config('smtp');
                    }
                    $Email->from(array(SUPPORT_EMAIL => SITENAME));
                    $Email->to($email);
                    $Email->subject($subject);
                    $Email->template('newvoicemail');
                    $Email->emailFormat('html');
                    $Email->viewVars(array('first_name' => $first_name));
                    $Email->viewVars(array('comment' => $comment));
                    $Email->viewVars(array('email' => $email));
                    $Email->send();
                }
            }
            header("content-type: text/xml");
            echo '<?xml version="1.0" encoding="UTF-8"?>
					<vxml version = "2.1" >
						<form>
							<block>
								<prompt xml:lang="en-us-male">Your message has been received.
								</prompt>
							</block>
						</form>
					</vxml>';
            exit;
        } else {
            header("content-type: text/xml");
            echo '<?xml version="1.0" encoding="UTF-8"?>
					<vxml version = "2.1" >
						<form>
							<block>
								<prompt xml:lang="en-us-male">Thanks For your response.
								</prompt>
							</block>
						</form>
					</vxml>';
            exit;
        }
    }

    function smsmail($user_id = null)
    {
        $this->autoRender = false;
        app::import('Model', 'User');
        $this->User = new User();
        $usersmail = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
        if ($usersmail['User']['email_alert_credit_options'] == 0) {
            if ($usersmail['User']['sms_balance'] <= $usersmail['User']['low_sms_balances']) {
                if ($usersmail['User']['sms_credit_balance_email_alerts'] == 0) {
                    $sitename = str_replace(' ', '', SITENAME);
                    $username = $usersmail['User']['username'];
                    $email = $usersmail['User']['email'];
                    //echo $phone = $usersmail['User']['assigned_number'];
                    $subject = "Low SMS Credit Balance";
                    /*$this->Email->to = $email;
                    $this->Email->subject = $subject;
                    $this->Email->from = $sitename;
                    $this->Email->template = 'low_sms_credit_template';
                    $this->Email->sendAs = 'html';
                    $this->Email->Controller->set('username', $username);
                    $this->Email->Controller->set('low_sms_balances', $usersmail['User']['low_sms_balances']);
                    $this->Email->send();*/
                    
                    $Email = new CakeEmail();
                    if(EMAILSMTP==1){
                        $Email->config('smtp');
                    }
                    $Email->from(array(SUPPORT_EMAIL => SITENAME));
                    $Email->to($email);
                    $Email->subject($subject);
                    $Email->template('low_sms_credit_template');
                    $Email->emailFormat('html');
                    $Email->viewVars(array('username' => $username));
                    $Email->viewVars(array('low_sms_balances' => $usersmail['User']['low_sms_balances']));
                    $Email->send();
                    
                    $this->User->id = $usersmail['User']['id'];
                    $this->User->saveField('sms_credit_balance_email_alerts', 1);
                }
            }
        }
    }

    function peoplecallrecordscript($group_id = null, $repeat = null, $language = null, $pause = null)
    {
        $this->autoRender = false;
        ob_start();
        echo "<pre>";
        print_r($_REQUEST);
        print_r($_FILES);
        echo "</pre>";
        $out1 = ob_get_contents();
        ob_end_clean();
        $file = fopen("debug/peoplecallrecordscript" . time() . ".txt", "w");
        fwrite($file, $out1);
        fclose($file);
        //$group_id = $_REQUEST['group_id'];
        //$repeat = $_REQUEST['repeat'];
        //$language = $_REQUEST['language'];
        //$pause = $_REQUEST['pause'];
        if ($language == 'pt-pt') {
            $voice = "male";
        } else {
            $voice = "female";
        }
        app::import('Model', 'VoiceMessage');
        $this->VoiceMessage = new VoiceMessage();
        $VoiceMessage = $this->VoiceMessage->find('first', array('conditions' => array('VoiceMessage.group_id' => $group_id)));
        $message_type = $VoiceMessage['VoiceMessage']['message_type'];
        if ($message_type == 1) {  // audio url
            $audio = SITE_URL . '/voice/' . $VoiceMessage['VoiceMessage']['audio'];
            echo '<?xml version="1.0" encoding="UTF-8"?>
				<vxml version = "2.1" >
					<form>
						<block><break time="' . $pause . 's"/>';
            for ($i = 1; $i <= $repeat; $i++) {
                echo '<audio src="' . $audio . '"/><break time="1s"/>';
            }
            echo '</block>
					</form>
				</vxml>';
        } else {
            $msg = $VoiceMessage['VoiceMessage']['text_message'];
            echo '<?xml version="1.0" encoding="UTF-8"?>
			<vxml version = "2.1" >
				<form>
					<block><break time="' . $pause . 's"/>';
            for ($i = 1; $i <= $repeat; $i++) {
                echo '<prompt xml:lang="' . $language . '-' . $voice . '">"' . $msg . '"</prompt><break time="1s"/>';
            }
            echo '</block>
				</form>
			</vxml>';
        }
    }

    function callStatus()
    {
        ob_start();
        echo "<pre>";
        print_r($_REQUEST);
        echo "</pre>";
        $out1 = ob_get_contents();
        ob_end_clean();
        $file = fopen("debug/callStatus" . time() . ".txt", "w");
        fwrite($file, $out1);
        fclose($file);
        $this->autoRender = false;
        if ($_REQUEST['log_id'] > 0) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->recursive = -1;
            $someone = $this->Log->find('first', array('conditions' => array('Log.id' => $_REQUEST['log_id'])));
            if (!empty($someone)) {
                $this->request->data['Log']['id'] = $_REQUEST['log_id'];
                $this->request->data['Log']['sms_status'] = $_REQUEST['status'];
                $this->Log->save($this->request->data);
            }
        }
    }

    function error_url()
    {
        ob_start();
        echo "<pre>";
        print_r($_REQUEST);
        echo "</pre>";
        $out1 = ob_get_contents();
        ob_end_clean();
        $file = fopen("debug/callStatus" . time() . ".txt", "w");
        fwrite($file, $out1);
        fclose($file);
        $this->autoRender = false;
        if ($_REQUEST['log_id'] > 0) {
            app::import('Model', 'Log');
            $this->Log = new Log();
            $this->Log->recursive = -1;
            $someone = $this->Log->find('first', array('conditions' => array('Log.id' => $_REQUEST['log_id'])));
            if (!empty($someone)) {
                $this->request->data['Log']['id'] = $_REQUEST['log_id'];
                $this->request->data['Log']['sms_status'] = $_REQUEST['status'];
                $this->Log->save($this->request->data);
                app::import('Model', 'User');
                $this->User = new User();
                $userdetails = $this->User->find('first', array('conditions' => array('User.id' => $someone['Log']['user_id'])));
                if (!empty($userdetails)) {
                    $user_arr['User']['id'] = $someone['Log']['user_id'];
                    $user_arr['User']['voice_balance'] = $someone['User']['voice_balance'] + 1;
                    $this->User->save($user_arr);
                }
            }
        }
    }

    function voiceStatusCallback()
    {
        ob_start();
        $this->autoRender = false;
        echo "<pre>";
        print_r($_REQUEST);
        echo "</pre>";
        $_REQUEST['To'] = str_replace('+', '', $_REQUEST['to']);
        $_REQUEST['From'] = str_replace('+1', '', $_REQUEST['from']);
        Controller::loadModel('User');
        $someone = $this->User->find('first', array('conditions' => array('assigned_number' => '' . trim($_REQUEST['To']) . '')));
        if (empty($someone)) {
            app::import('Model', 'UserNumber');
            $this->UserNumber = new UserNumber();
            $someone = $this->UserNumber->find('first', array('conditions' => array('UserNumber.number' => '' . trim($_REQUEST['To']) . '')));
        }
        date_default_timezone_set($someone['User']['timezone']);
        if ($someone['User']['voice_balance'] > 0) {
            $this->Log->recursive = -1;
            $calllog = $this->Log->find('first', array('conditions' => array('Log.voice_url' => '' . trim($_REQUEST['call-id']) . '')));
            if (!empty($calllog)) {
                if (isset($_REQUEST['call-duration'])) {
                    $duration = $_REQUEST['call-duration'];
                    /*$minute = gmdate("H:i:s", $_REQUEST['call-duration']);
					$minute_arr = explode(':',$minute);
					$hour = 0;
					$mint = 0;
					$secnd = 0;
					if($minute_arr[0] !='00'){
						$hour = $minute_arr[0] * 60;
					}
					if($minute_arr[1]  > 0){
						$mint = $minute_arr[1];
					}
					if($minute_arr[2]  > 0){
						$secnd = 1;
					}
					$minutes = $hour+$mint+$secnd;*/
                    $minutes = ceil($duration / 60);
                    $minutes = $minutes * 2;
                    if ($minutes > 0) {

                        Controller::loadModel('Log');
                        $this->request->data['Log']['user_id'] = $someone['User']['id'];
                        $this->request->data['Log']['id'] = $calllog['Log']['id'];
                        $this->request->data['Log']['call_duration'] = gmdate("H:i:s", $_REQUEST['call-duration']);
                        $this->Log->save($this->request->data);

                        $this->User->id = $someone['User']['id'];
                        $this->User->saveField('voice_balance', ($someone['User']['voice_balance'] - $minutes));

                        Controller::loadModel('User');
                        $usersvoicebalance = $this->User->find('first', array('conditions' => array('User.id' => $someone['User']['id'])));
                        if ($usersvoicebalance['User']['email_alert_credit_options'] == 0) {
                            if ($usersvoicebalance['User']['voice_balance'] <= $usersvoicebalance['User']['low_voice_balances']) {
                                if ($usersvoicebalance['User']['VM_credit_balance_email_alerts'] == 0) {
                                    $username = $usersvoicebalance['User']['username'];
                                    $email = $usersvoicebalance['User']['email'];
                                    $sitename = str_replace(' ', '', SITENAME);
                                    $subject = "Low Voice Credit Balance";
                                    /*$this->Email->to = $email;
                                    $this->Email->subject = $subject;
                                    $this->Email->from = $sitename;
                                    $this->Email->template = 'low_voice_credit_template';
                                    $this->Email->sendAs = 'html';
                                    $this->Email->Controller->set('username', $username);
                                    $this->Email->Controller->set('low_voice_balances', $usersvoicebalance['User']['low_voice_balances']);
                                    $this->Email->send();*/
                                    
                                    $Email = new CakeEmail();
                                    if(EMAILSMTP==1){
                                        $Email->config('smtp');
                                    }
                                    $Email->from(array(SUPPORT_EMAIL => SITENAME));
                                    $Email->to($email);
                                    $Email->subject($subject);
                                    $Email->template('low_voice_credit_template');
                                    $Email->emailFormat('html');
                                    $Email->viewVars(array('username' => $username));
                                    $Email->viewVars(array('low_voice_balances' => $usersvoicebalance['User']['low_voice_balances']));
                                    $Email->send();
                    
                                    $this->User->id = $usersvoicebalance['User']['id'];
                                    $this->User->saveField('VM_credit_balance_email_alerts', 1);
                                }
                            }
                        }
                    }
                }
            } elseif (isset($_REQUEST['call-duration'])) {
                $this->User->id = $someone['User']['id'];
                //$this->User->saveField('voice_balance', ($someone['User']['voice_balance']-1));
                $duration = $_REQUEST['call-duration'];
                $minutes = ceil($duration / 60);
                $this->User->saveField('voice_balance', ($someone['User']['voice_balance'] - $minutes));
                if ($minutes > 0) {
                    Controller::loadModel('User');
                    $usersvoicebalance = $this->User->find('first', array('conditions' => array('User.id' => $someone['User']['id'])));
                    if ($usersvoicebalance['User']['email_alert_credit_options'] == 0) {
                        if ($usersvoicebalance['User']['voice_balance'] <= $usersvoicebalance['User']['low_voice_balances']) {
                            if ($usersvoicebalance['User']['VM_credit_balance_email_alerts'] == 0) {
                                $username = $usersvoicebalance['User']['username'];
                                $email = $usersvoicebalance['User']['email'];
                                $sitename = str_replace(' ', '', SITENAME);
                                $subject = "Low Voice Credit Balance";
                                /*$this->Email->to = $email;
                                $this->Email->subject = $subject;
                                $this->Email->from = $sitename;
                                $this->Email->template = 'low_voice_credit_template';
                                $this->Email->sendAs = 'html';
                                $this->Email->Controller->set('username', $username);
                                $this->Email->Controller->set('low_voice_balances', $usersvoicebalance['User']['low_voice_balances']);
                                $this->Email->send();*/
                                
                                $Email = new CakeEmail();
                                if(EMAILSMTP==1){
                                    $Email->config('smtp');
                                }
                                $Email->from(array(SUPPORT_EMAIL => SITENAME));
                                $Email->to($email);
                                $Email->subject($subject);
                                $Email->template('low_voice_credit_template');
                                $Email->emailFormat('html');
                                $Email->viewVars(array('username' => $username));
                                $Email->viewVars(array('low_voice_balances' => $usersvoicebalance['User']['low_voice_balances']));
                                $Email->send();
                                    
                                $this->User->id = $usersvoicebalance['User']['id'];
                                $this->User->saveField('VM_credit_balance_email_alerts', 1);
                            }
                        }
                    }
                }
            }

            $out1 = ob_get_contents();
            ob_end_clean();
            $file = fopen("debug/voiceStatusCallback" . time() . ".txt", "w");
            fwrite($file, $out1);
            fclose($file);
        }
    }

    function Immediatelyresponder($user_id = null, $group_id = null, $to = null, $from = null)
    {
        $this->autoRender = false;
        app::import('Model', 'Responder');
        $this->Responder = new Responder();
        app::import('Model', 'User');
        $this->User = new User();
        $response = $this->Responder->find('first', array('conditions' => array('Responder.user_id' => $user_id, 'Responder.group_id' => $group_id, 'Responder.days' => 0)));
        $users = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
        if (!empty($response)) {
            $current_datetime = date("n/d/Y");
            if ($users['User']['sms_balance'] > 0) {
                $Responderid = $response['Responder']['id'];
                $group_id = $response['Responder']['group_id'];
                $sms_type = $response['Responder']['sms_type'];
                $image_url = $response['Responder']['image_url'];
                $message = str_replace('%%CURRENTDATE%%', $current_datetime, $response['Responder']['message']);
                $systemmsg = $response['Responder']['systemmsg'];
                $user_id = $response['Responder']['user_id'];
                $body = $message . " " . $systemmsg;
                $this->Nexmomessage->Key = NEXMO_KEY;
                $this->Nexmomessage->Secret = NEXMO_SECRET;
                $response = $this->Nexmomessage->sendsms($to, $from, $body);
                foreach ($response->messages as $doc) {
                    $message_id = $doc->messageid;
                    if ($message_id != '') {
                        $status = $doc->status;
                        $message_id = $doc->messageid;
                    } else {
                        $status = $doc->status;
                        $errortext = $doc->errortext;
                    }
                }
                if ($message_id != '') {
                    app::import('Model', 'User');
                    $this->User = new User();
                    $users_sms = $this->User->find('first', array('conditions' => array('User.id' => $user_id)));
                    $usersave['User']['id'] = $user_id;
                    $usersave['User']['sms_balance'] = $users_sms['User']['sms_balance'] - 1;
                    $this->User->save($usersave);
                }
            }
        }
    }

    function random_generator($digits)
    {
        srand((double)microtime() * 10000000);
        $input = array("A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
            "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $random_generator = "";// Initialize the string to store random numbers
        for ($i = 1; $i < $digits + 1; $i++) {
            // Loop the number of times of required digits
            if (rand(1, 2) == 1) {// to decide the digit should be numeric or alphabet
                $rand_index = array_rand($input);
                $random_generator .= $input[$rand_index]; // One char is added
            } else {
                $random_generator .= rand(1, 7); // one number is added

            }
        } // end of for loop
        return $random_generator;
    } // end of function
} 