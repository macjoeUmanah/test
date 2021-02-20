<?php

class AppointmentsController extends AppController
{
    var $name = 'Appointments';
    var $components = array('Cookie', 'Twilio', 'Mms', 'Nexmomessage', 'Slooce', 'Plivo');
    public $uses = array('User', 'Contact', 'ContactGroup', 'Appointment', 'AppointmentSetting','ContactGroup','AppointmentCsv');
    public $useModel = false;

    function view()
    {
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        $this->Appointment->recursive = 1;
        $appointment = $this->Appointment->find('all', array('conditions' => array('Appointment.user_id' => $user_id)));
        $this->set('appointment', $appointment);
        $appointmentsetting = $this->AppointmentSetting->find('first', array('conditions' => array('AppointmentSetting.user_id' => $user_id)));
        $this->set('appointmentsetting', $appointmentsetting);

    }

    function event_add()
    {
        $this->layout = 'popup';
        $user_id = $this->Session->read('User.id');
        //$this->Contact->recursive = -1;
        //$contact = $this->Contact->find('all',array('conditions' => array('Contact.user_id'=>$user_id),'order' =>array('Contact.name' => 'asc'),'fields' => array('Contact.id','Contact.name','Contact.phone_number')));
        //app::import('Model', 'ContactGroup');
        //$this->Invoice = new ContactGroup();
        $contact = $this->ContactGroup->find('all', array('conditions' => array('ContactGroup.user_id' => $user_id), 'order' => array('Contact.name' => 'asc'), 'fields' => array('DISTINCT Contact.id', 'Contact.name', 'Contact.phone_number')));
        $this->set('contact', $contact);
        if (!empty($this->request->data)) {
            $this->request->data['Appointment']['user_id'] = $user_id;
            $this->request->data['Appointment']['app_date_time'] = date('Y-m-d H:i:s', strtotime($this->request->data['Appointment']['app_date_time']));
            $this->request->data['Appointment']['appointment_status'] = $this->request->data['Appointment']['appointment_status'];
            if ($this->Appointment->save($this->request->data)) {
                $this->Session->setFlash(__('Appointment has been created', true));
                //$this->redirect(array('controller' =>'appointments', 'action'=>'index'));
                $this->redirect(array('controller' => 'appointments', 'action' => 'event_add?status=success'));
            } else {
                $this->Session->setFlash(__('The appointments could not be saved. Please, try again.', true));
            }
        }

    }

    function event_edit($id = null)
    {
        $this->layout = 'popup';
        $user_id = $this->Session->read('User.id');
        $appointment = $this->Appointment->find('first', array('conditions' => array('Appointment.id' => $id, 'Appointment.user_id' => $user_id)));
        $this->set('appointment', $appointment);
        if (!empty($this->request->data)) {
            //echo"<pre>";print_r($this->request->data);die;
            $this->request->data['Appointment']['id'] = $id;
            $this->request->data['Appointment']['app_date_time'] = date('Y-m-d H:i:s', strtotime($this->request->data['Appointment']['app_date_time']));
            $this->request->data['Appointment']['appointment_status'] = $this->request->data['Appointment']['appointment_status'];
            if ($this->Appointment->save($this->request->data)) {
                $this->Session->setFlash(__('Appointment has been updated', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'event_edit?status=success'));
            } else {
                $this->Session->setFlash(__('The appointments could not be saved. Please, try again.', true));
            }
        }

    }

    function events_view($id = null)
    {
        $this->layout = 'popup';
        $user_id = $this->Session->read('User.id');
        $this->Appointment->recursive = 1;
        $appointment = $this->Appointment->find('first', array('conditions' => array('Appointment.id' => $id)));
        $this->set('appointment', $appointment);
        $appointmentsetting = $this->AppointmentSetting->find('first', array('conditions' => array('AppointmentSetting.user_id' => $user_id)));
        $this->set('appointmentsetting', $appointmentsetting);

    }

    function events_delete($id = null)
    {
        $this->autoRender = false;
        if ($this->Appointment->delete($id)) {
            $this->Session->setFlash(__('Appointment has been deleted', true));
            $this->redirect(array('controller' => 'appointments', 'action' => 'view'));
        }
    }

    function delete($id = null)
    {
        $this->layout = 'admin_new_layout';
        if ($this->Appointment->delete($id)) {
            $this->Session->setFlash(__('Appointment has been deleted', true));
            $this->redirect(array('controller' => 'appointments', 'action' => 'index'));
        }
    }

    function index()
    {
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        $this->Appointment->recursive = 1;
        $this->paginate = array('conditions' => array('Appointment.user_id' => $user_id), 'order' => array('Appointment.id' => 'desc'), 'limit' => 50);
        $appointment = $this->paginate('Appointment');
        $this->set('appointment', $appointment);
        $appointmentsetting = $this->AppointmentSetting->find('first', array('conditions' => array('AppointmentSetting.user_id' => $user_id)));
        $this->set('appointmentsetting', $appointmentsetting);

    }

    function add()
    {
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        //$this->Contact->recursive = -1;
        //$contact = $this->Contact->find('all',array('conditions' => array('Contact.user_id'=>$user_id),'order' =>array('Contact.name' => 'asc'),'fields' => array('Contact.id','Contact.name','Contact.phone_number')));
        $contact = $this->ContactGroup->find('all', array('conditions' => array('ContactGroup.user_id' => $user_id), 'order' => array('Contact.name' => 'asc'), 'fields' => array('DISTINCT Contact.id', 'Contact.name', 'Contact.phone_number')));
        $this->set('contact', $contact);
        if (!empty($this->request->data)) {
            $this->request->data['Appointment']['user_id'] = $user_id;
            $this->request->data['Appointment']['app_date_time'] = date('Y-m-d H:i:s', strtotime($this->request->data['Appointment']['app_date_time']));
            $this->request->data['Appointment']['appointment_status'] = $this->request->data['Appointment']['appointment_status'];
            if ($this->Appointment->save($this->request->data)) {
                $this->Session->setFlash(__('Appointment has been created', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'index'));
            } else {
                $this->Session->setFlash(__('The appointments could not be saved. Please, try again.', true));
            }
        }

    }

    function edit($id = null)
    {
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        $appointment = $this->Appointment->find('first', array('conditions' => array('Appointment.id' => $id, 'Appointment.user_id' => $user_id)));
        $this->set('appointment', $appointment);
        if (!empty($this->request->data)) {
            //echo"<pre>";print_r($this->request->data);die;
            $this->request->data['Appointment']['id'] = $id;
            $this->request->data['Appointment']['app_date_time'] = date('Y-m-d H:i:s', strtotime($this->request->data['Appointment']['app_date_time']));
            $this->request->data['Appointment']['appointment_status'] = $this->request->data['Appointment']['appointment_status'];
            if ($this->Appointment->save($this->request->data)) {
                $this->Session->setFlash(__('Appointment has been updated', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'index'));
            } else {
                $this->Session->setFlash(__('The appointment could not be updated. Please, try again.', true));
            }
        }

    }


    function settings()
    {
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        if (!empty($this->request->data)) {

            if (!empty($this->request->data['AppointmentSetting']['id'])) {
                $this->request->data['AppointmentSetting']['id'] = $this->request->data['AppointmentSetting']['id'];
            }

            $this->request->data['AppointmentSetting']['user_id'] = $user_id;

            app::import('Model', 'Group');
            $this->Group = new Group();

            app::import('Model', 'Contest');
            $this->Contest = new Contest();

            app::import('Model', 'Smsloyalty');
            $this->Smsloyalty = new Smsloyalty();

            $cancelkeyword = trim($this->request->data['AppointmentSetting']['cancel_keyword']);
            $confirmkeyword = trim($this->request->data['AppointmentSetting']['confirm_keyword']);
            $reschedulekeyword = trim($this->request->data['AppointmentSetting']['reschedule_keyword']);

            $groupkeyword = $this->Group->find('first', array('conditions' => array('Group.keyword ' => array($cancelkeyword, $confirmkeyword, $reschedulekeyword), 'Group.user_id ' => $user_id)));
            $contestkeyword = $this->Contest->find('first', array('conditions' => array('Contest.keyword ' => array($cancelkeyword, $confirmkeyword, $reschedulekeyword), 'Contest.user_id' => $user_id)));
            $loyaltykeyword = $this->Smsloyalty->find('first', array('conditions' => array('Smsloyalty.codestatus ' => array($cancelkeyword, $confirmkeyword, $reschedulekeyword), 'Smsloyalty.user_id' => $user_id)));

            $appointmentsetting = $this->AppointmentSetting->find('first', array('conditions' => array('AppointmentSetting.user_id' => $user_id)));
            $savedcancelkeyword = trim($appointmentsetting['AppointmentSetting']['cancel_keyword']);
            $savedconfirmkeyword = trim($appointmentsetting['AppointmentSetting']['confirm_keyword']);
            $savedreschedulekeyword = trim($appointmentsetting['AppointmentSetting']['reschedule_keyword']);

            if (!empty($cancelkeyword)) {
                if ((strtoupper($cancelkeyword) == strtoupper($savedconfirmkeyword)) || (strtoupper($cancelkeyword) == strtoupper($savedreschedulekeyword))) {
                    $this->Session->setFlash(__('Cancel keyword can not equal confirm or reschedule keywords. Keywords must be unique.', true));
                    $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
                }
            }

            if (!empty($confirmkeyword)) {
                if ((strtoupper($confirmkeyword) == strtoupper($savedcancelkeyword)) || (strtoupper($confirmkeyword) == strtoupper($savedreschedulekeyword))) {
                    $this->Session->setFlash(__('Confirm keyword can not equal cancel or reschedule keywords. Keywords must be unique.', true));
                    $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
                }
            }

            if (!empty($reschedulekeyword)) {
                if ((strtoupper($reschedulekeyword) == strtoupper($savedconfirmkeyword)) || (strtoupper($reschedulekeyword) == strtoupper($savedcancelkeyword))) {
                    $this->Session->setFlash(__('Reschedule keyword can not equal confirm or cancel keywords. Keywords must be unique.', true));
                    $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
                }
            }

            if (!empty($contestkeyword)) {
                $this->Session->setFlash(__('Keyword is already registered for a contest. Please choose another keyword.', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
            }

            if (!empty($loyaltykeyword)) {
                $this->Session->setFlash(__('Keyword is already registered for a loyalty program. Please choose another keyword.', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
            }

            if (!empty($groupkeyword)) {
                $this->Session->setFlash(__('Keyword is already registered for a group. Please choose another keyword.', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
            }

            if ($this->AppointmentSetting->save($this->request->data)) {
                $this->Session->setFlash(__('The Appointment Settings has been saved', true));
                $this->redirect(array('controller' => 'appointments', 'action' => 'settings'));
            } else {
                $this->Session->setFlash(__('The appointment settings could not be saved. Please, try again.', true));
            }
        } else {
            $AppointmentSetting = $this->AppointmentSetting->find('first', array('conditions' => array('AppointmentSetting.user_id' => $user_id)));

            if (empty($AppointmentSetting['AppointmentSetting']['cancel_keyword'])) {
                $AppointmentSetting['AppointmentSetting']['cancel_keyword'] = 'CL';
                $AppointmentSetting['AppointmentSetting']['cancel_message'] = 'Your appointment has been cancelled. Please contact us to setup a new appointment. Thanks!';
                $AppointmentSetting['AppointmentSetting']['cancel_color_picker'] = '#e7505a';
                $AppointmentSetting['AppointmentSetting']['cancel_email_subject'] = 'Appointment Cancellation Alert';
                $AppointmentSetting['AppointmentSetting']['cancel_email_body'] = '<p>Hello,</p><p>This is a notification that %%Name%% has <strong>cancelled</strong> their appointment on %%ApptDate%%.</p><p>Please contact them to reschedule at %%Number%% or %%Email%%.</p><p>Thank you.<br />&nbsp;</p>';
            }
            if (empty($AppointmentSetting['AppointmentSetting']['confirm_keyword'])) {
                $AppointmentSetting['AppointmentSetting']['confirm_keyword'] = 'CM';
                $AppointmentSetting['AppointmentSetting']['confirm_message'] = 'Thank you for confirming your appointment. We will see you then. Thanks!';
                $AppointmentSetting['AppointmentSetting']['confirm_color_picker'] = '#26C281';
                $AppointmentSetting['AppointmentSetting']['confirm_email_subject'] = 'Appointment Confirmation Alert';
                $AppointmentSetting['AppointmentSetting']['confirm_email_body'] = '<p>Hello,</p><p>This is a notification that %%Name%% has <strong>confirmed</strong> their appointment on %%ApptDate%%.</p><p>Thank you.<br />&nbsp;</p>';
            }
            if (empty($AppointmentSetting['AppointmentSetting']['reschedule_keyword'])) {
                $AppointmentSetting['AppointmentSetting']['reschedule_keyword'] = 'RS';
                $AppointmentSetting['AppointmentSetting']['reschedule_message'] = 'Thanks for letting us know you want to reschedule your appointment. We will contact you to reschedule.';
                $AppointmentSetting['AppointmentSetting']['reschedule_color_picker'] = '#F4D03F';
                $AppointmentSetting['AppointmentSetting']['reschedule_email_subject'] = 'Appointment Reschedule Alert';
                $AppointmentSetting['AppointmentSetting']['reschedule_email_body'] = '<p>Hello,</p><p>This is a notification that %%Name%% wants to <strong>reschedule</strong> their appointment on %%ApptDate%%.</p><p>Please contact them to reschedule at %%Number%% or %%Email%%.</p><p>Thank you.<br />&nbsp;</p>';
            }
            $this->set('AppointmentSetting', $AppointmentSetting);

        }
		
    }
	function upload(){
		$this->layout= 'admin_new_layout';
	}
	function show_next(){
		if(isset($this->request->data)){
			$user_id=$this->Session->read('User.id');
			$filename=$this->request->data['appointment_csv']['name'];
			$type=$this->request->data['appointment_csv']['type'];
			$tmp_name=$this->request->data['appointment_csv']['tmp_name'];
			$name=$this->request->data['appointment_csv']['tmp_name'];
			$handle = fopen($name, 'r');
			$header = fgetcsv($handle);		
			$ext = substr(strrchr($this->request->data['appointment_csv']['name'],'.'),1);
			if (strtoupper(trim($ext)) != 'CSV') {
				$this->Session->setFlash(__('Please only use CSV file type', true));
				$this->redirect(array('controller' => 'appointments','action' => 'upload'));
			}
			//move_uploaded_file($tmp_name,"app_csv/".time().$filename);
			//$path =  'app_csv/'.time().$filename;
			move_uploaded_file($tmp_name,"csvfile/".time().$filename);
			$path =  'csvfile/'.time().$filename;
			$handle = fopen($path, "r");
			fgetcsv($handle);
			$bSuccessRecords = 0;
			$bFailRecords = 0;
			while (($row = fgetcsv($handle)) !== FALSE) {
			    $phone = trim($row[0]);
				$contact_arr = $this->ContactGroup->find('first', array('conditions' => array('ContactGroup.user_id' => $user_id, 'Contact.phone_number' => $phone)));
				if(!empty($contact_arr)){
					$newapptdate = DateTime::createFromFormat('Y-m-d h:i A',$row[1]);
                    $appt_date = $newapptdate->format('Y-m-d H:i:s');
                    $import_csv['Appointment']['app_date_time'] = $appt_date;
					if(strtoupper(trim($row[2]))=='UNCONFIRMED' || strtoupper(trim($row[2]))=='UNCONFIRM'){
						$import_csv['Appointment']['appointment_status'] = 0;	
					}else if(strtoupper(trim($row[2]))=='CONFIRMED' || strtoupper(trim($row[2]))=='CONFIRM'){
						$import_csv['Appointment']['appointment_status'] = 1;	
					}else if(strtoupper(trim($row[2]))=='CANCELLED' || strtoupper(trim($row[2]))=='CANCELED' || strtoupper(trim($row[2]))=='CANCEL'){
						$import_csv['Appointment']['appointment_status'] = 2;	
					}else if(strtoupper(trim($row[2]))=='RESCHEDULED' || strtoupper(trim($row[2]))=='RESCHEDULE'){
						$import_csv['Appointment']['appointment_status'] = 3;	
					}else{
						$import_csv['Appointment']['appointment_status'] = 0;	
					}
					$import_csv['Appointment']['contact_id'] = $contact_arr['Contact']['id'];
					$import_csv['Appointment']['user_id'] = $user_id;
					$import_csv['Appointment']['created'] = date('Y-m-d h:i:s');	
					$import_csv['Appointment']['id'] = '';
					$this->Appointment->save($import_csv);
					$bSuccessRecords = 1;
				}else{
				    if ($bFailRecords == 0){
				        $this->AppointmentCsv->deleteAll(array('AppointmentCsv.user_id' => $user_id));
				    }
				    $appointment_arr['AppointmentCsv']['id'] = '';
					$appointment_arr['AppointmentCsv']['user_id'] = $user_id;
					$appointment_arr['AppointmentCsv']['phone_number'] = $phone;
					$newapptdate = DateTime::createFromFormat('Y-m-d h:i A',$row[1]);
                    $appt_date = $newapptdate->format('Y-m-d H:i:s');
					$appointment_arr['AppointmentCsv']['app_date_time'] = $appt_date;	
					$appointment_arr['AppointmentCsv']['appointment_status'] = $row[2];	
					$appointment_arr['AppointmentCsv']['created'] = date('Y-m-d h:i:s');
					$this->AppointmentCsv->save($appointment_arr);
					$bFailRecords = 1;
				}
			}
			fclose($handle);
			
			if($bFailRecords == 1){
			    $this->Session->setFlash(__('The contact numbers below do not exist in your contact list. Numbers must exist in your contact list before importing appointments.', true));
			    $this->redirect(array('controller' => 'appointments','action' => 'csv_list'));
			}else{
			    $this->Session->setFlash(__('Appointments have been imported successfully.', true));
			    $this->redirect(array('controller' => 'appointments','action' => 'index')); 
			}
		}
	}
	function csv_list(){
        $this->layout = 'admin_new_layout';
        $user_id = $this->Session->read('User.id');
        $this->AppointmentCsv->recursive = 1;
        $this->paginate = array('conditions' => array('AppointmentCsv.user_id' => $user_id), 'order' => array('AppointmentCsv.id' => 'desc'), 'limit' => 50);
        $appointment_arr = $this->paginate('AppointmentCsv');
		$this->set('appointment_arr', $appointment_arr);
	}
}	