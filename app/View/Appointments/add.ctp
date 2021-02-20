<style>
.ValidationErrors{color:red;}
div#ui-datepicker-div {z-index:9000 !important}
</style>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="https://code.jquery.com/jquery-migrate-1.0.0.js"></script>
<?php
	echo $this->Html->script('jQvalidations/jquery.validation.functions');
	echo $this->Html->script('jQvalidations/jquery.validate');
	echo $this->Html->css('jquery-ui-1.8.16.custom');
	echo $this->Html->script('jquery-ui-timepicker-addon');
?>
   <!--	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>-->
	
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" />
	<script src="<?php echo SITE_URL; ?>/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
	
		<div class="page-content-wrapper">
			<div class="page-content">              
				<h3 class="page-title"> <?php echo('Appointments');?></h3>
				<div class="page-bar">
					<ul class="page-breadcrumb">
						<li>
							<i class="icon-home"></i>
							<a href="<?php echo SITE_URL;?>/users/dashboard">Dashboard</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<a href="<?php echo SITE_URL;?>/appointments">Appointments </a>
						</li>
					</ul>  
				</div>
				<div class="portlet mt-element-ribbon light portlet-fit  ">
					<div class="ribbon ribbon-right ribbon-clip ribbon-shadow ribbon-border-dash-hor ribbon-color-success uppercase">
						<div class="ribbon-sub ribbon-clip ribbon-right"></div>
						<?php echo('Create Appointment Form');?>
					</div>
					<div class="portlet-title">
						<div class="caption font-red-sunglo">
							<i class="fa fa-calendar font-red-sunglo"></i>
						</div>
					</div>
					<div class="portlet-body form">
						<form  method="post" >
							<div class="form-body">
								<div class="form-group">
									<label for="some21" style="display:block;">Contacts</label>	
									<select data-show-subtext="true" data-live-search="true" id="contacts" class="form-control selectpicker"  name="data[Appointment][contact_id]" >	
									
										<?php foreach($contact as $contact_arr){ ?>
										<!--if(trim($contact_arr['Contact']['name'])!=''){  ?>-->
					                       <option data-subtext="<?php echo $contact_arr['Contact']['phone_number']?>" value="<?php echo $contact_arr['Contact']['id']?>"><?php echo $contact_arr['Contact']['name']?> </option>
				                      	<?php } ?>									
									</select>
								</div>							
								<div class="form-group">
									<label>Appointment Date/Time</label>
									<input type="text"  name="data[Appointment][app_date_time]" id="sent_on" class="form-control" placeholder="Appointment date/time" >
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
			</div>
		</div>
		<script>
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