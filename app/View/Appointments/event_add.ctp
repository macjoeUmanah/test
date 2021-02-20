<link href="<?php echo SITE_URL; ?>/assets/global/css/components.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SITE_URL; ?>/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  
<script src="<?php echo SITE_URL; ?>/assets/global/scripts/app.min.js" type="text/javascript"></script>		
<script src="<?php echo SITE_URL; ?>/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="https://code.jquery.com/jquery-migrate-1.0.0.js"></script>

<?php
	echo $this->Html->css('nyroModal');
	echo $this->Html->script('jQvalidations/jquery.validation.functions');
	echo $this->Html->script('jQvalidations/jquery.validate');
	echo $this->Html->script('jquery.nyroModal.custom');
	echo $this->Html->css('jquery-ui-1.8.16.custom');
	echo $this->Html->script('jquery-ui-timepicker-addon');
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('a.nyroModal').nyroModal();

	});
</script>
<?php if($_GET['status']=="success"){ ?>
	<script>
		jQuery(document).ready(function($) {
		  setTimeout(function(){ 
		  window.parent.jQuery.fancybox.close();
		  window.parent.close(); 
		  }, 200);  
		 });
	</script>
<?php } ?>
<style>			
#flashMessage {
	font-size: 16px;
	font-weight: normal;
}       
.message {
	background: #f6fff5 url("<?php echo SITE_URL; ?>/app/webroot/img/flashimportant.png") no-repeat scroll 15px 12px / 24px 24px;
	border: 1px solid #97db90;
	border-radius: 5px;
	color: #000;
	font-size: 13px;
	margin-bottom: 10px;
	padding: 13px 13px 13px 48px;
	text-decoration: none;
	text-shadow: 1px 1px 0 #fff;
}
.nyroModalCont {
    top: 0 !important;
}
.nyroModalCloseButton{
	top: 5px !important;
}
</style>
			

    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" />
	<script src="<?php echo SITE_URL; ?>/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
	
	<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>-->
	
	  	<div class="clearfix"></div>
				<div class="portlet light">
					<div class="portlet-title">
						<div class="caption font-green-sharp"><i class="fa fa-calendar-plus-o font-green-sharp"></i>
							Create Appointment 
						</div>
					</div>
					<div class="portlet-body form">
					<?php echo $this->Session->flash(); ?>
						<form  method="post" >
							<div class="form-body">
								<div class="form-group">
									<label for="some21" style="display:block;">Contacts</label>	
									<select data-show-subtext="true" data-live-search="true" id="contacts" class="form-control selectpicker"  name="data[Appointment][contact_id]" >	
									
										<?php foreach($contact as $contact_arr){ ?>
										<!--if(trim($contact_arr['Contact']['name'])!=''){  ?>-->
					                       <option data-subtext="<?php echo $contact_arr['Contact']['phone_number']?>" value="<?php echo $contact_arr['Contact']['id']?>"><?php echo $contact_arr['Contact']['name']?></option>
				                      	<?php } ?>
				                      
									</select>
								</div>							
								<div class="form-group">
									<label>Appointment Date/Time</label>
									<input type="text"  name="data[Appointment][app_date_time]" id="sent_on" class="form-control" placeholder="Appointment date/time" value="<?php echo date('d-m-Y H:i', strtotime($_REQUEST['date']));?>">
								</div>
								<div class="form-group">
									<label for="some21">Appointment Status<span class="required_star"></span></label>	
									<select id="contacts" class="form-control"  name="data[Appointment][appointment_status]" >
										<option value="0" selected>Unconfirmed</option>
										<option value="1" >Confirmed</option>
										<option value="2">Cancelled</option>
										<option value="3">Reschedule</option>
									</select>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div style="margin-top:15px;">
											<button type="submit" name="submit" class="btn blue">Save</button>
											
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>

<script type="text/javascript">
$('#sent_on').datetimepicker({
     minDate: 0,
    showSecond: false,
    //showMinute: false,
	dateFormat: 'dd-mm-yy',
	//timeFormat: 'hh',
	timeFormat: 'hh:mm',
	stepHour: 1,
	stepMinute: 5,
	stepSecond: 10,
	
});

jQuery(function(){
	jQuery("#sent_on").validate({
		expression: "if (VAL) return true; else return false;",
		message: "Please select the date/time"        
	});	
				
	jQuery("#contacts").validate({
		expression: "if (VAL) return true; else return false;",
		message: "Please select a contact"        
	});	
});		

</script>
