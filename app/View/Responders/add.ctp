
<style>
.ValidationErrors{
color:red;
}
</style>
<script>
jQuery(function(){
	jQuery("#name").validate({
		expression: "if (VAL) return true; else return false;",
		message: "Please enter name"
	});jQuery("#days").validate({
		expression: "if (VAL) return true; else return false;",
		message: "Please enter days"
	});jQuery("#ResponderGroupId").validate({
		expression: "if (VAL > 0) return true; else return false;",
		message: "Please select group"
	});
});
</script>
<script type="text/javascript">
$(document).ready(function (){
	$('textarea[maxlength]').live('keyup change', function() {
		var str = $(this).val()
		var mx = parseInt($(this).attr('maxlength'))
		if (str.length > mx) {
		 $(this).val(str.substr(0, mx))
		 return false;
		}
	})
	
});
</script>
<script>
var count = "148";
function update(){
	var tex = $("#message").val();
	var count1 = (148);
	tex = tex.replace('[','');
	tex = tex.replace(']','');
	tex = tex.replace('~','');
	tex = tex.replace(';','');
	tex = tex.replace('`','');tex = tex.replace('"','');
	var len = tex.length;
	$("#message").val(tex);
	if(len > count){
	tex = tex.substring(0,count1);
	return false;
	}
	$("#limit").val(count-len);
}
function update1(){
	var tex = $("#message1").val();
	var count1 = (148);
	tex = tex.replace('[','');
	tex = tex.replace(']','');
	tex = tex.replace('~','');
	tex = tex.replace(';','');
	tex = tex.replace('`','');tex = tex.replace('"','');
	var len = tex.length;
	$("#message1").val(tex);
	if(len > count){
	tex = tex.substring(0,count1);
	return false;
	}
	$("#limit1").val(count-len);
}
function confirmpagemessage(id){
	var messageview= $('#message').val();
	if(id>0){
		$.ajax({
			url: "<?php  echo SITE_URL ?>/messages/mobile_pages/"+id,
			type: "POST",
			dataType: "html",
			success: function(response) {
				if(messageview!=''){
					if($('#sms').prop('checked')==true){
					$('#message').val(response.substr(0,148));
					}else{
					$('#message').val(response.substr(0,148));
					}
					if($('#mms').prop('checked')==true){
					$('#message1').val(response.substr(0,148));
					}
					return;
			    }else{
					if($('#sms').prop('checked')==true){
					$('#message').html(response.substr(0,148));
					}else{
					$('#message').html(response.substr(0,148));
					}
					if($('#mms').prop('checked')==true){
					$('#message1').html(response.substr(0,148));
					}
			    }
			}
	 
	    });
    }
}
function confirmmessage(id){
	var message= $('#message').val();
	if(id>0){
		 $.ajax({
				url: "<?php  echo SITE_URL ?>/messages/checktemplatedata/"+id,
				type: "POST",
				dataType: "html",
				success: function(response) {
					if(message!=''){
					if($('#sms').prop('checked')==true){
					$('#message').val(response.substr(0,148));
					}else{
					$('#message').val(response.substr(0,148));
					}
					if($('#mms').prop('checked')==true){
					$('#message1').val(response.substr(0,148));
					}
					return;
				}else{
					if($('#sms').prop('checked')==true){
					$('#message').html(response.substr(0,148));
					}else{
					$('#message').html(response.substr(0,148));
					}
					if($('#mms').prop('checked')==true){
					$('#message1').html(response.substr(0,148));
					}
				}
			}
		 
	    });
    }
}
function popmessagepickwidgetnexmo(value){
	$('#message').text(value);
	$('#pick_button').val('');
	$('#check_img_validation').val(0);
	$('#message3').val(value);
}
/*window.onload = function(){
	if(window.File && window.FileList && window.FileReader){
		$('#files').live("change", function(event){
			var files = event.target.files; 
			var output = document.getElementById("result");
			var check=$('img').hasClass('thumbnail');
			if(check==true){
			$('.thumbnail').remove();
			}
			for(var i = 0; i< files.length; i++){
				var file = files[i];
				if(file.type.match('image.*')){
					var picReader = new FileReader();
					picReader.addEventListener("load",function(event){
						var picFile = event.target;
						var div = document.createElement("div");
                                                div.style.cssText = 'width:80px;float: left;margin-left:5px;';
                                                div.innerHTML = "<img style='height:70px!important;width: 70px!important;' class='thumbnail' src='" + picFile.result + "'" +
"title='preview image'/>";

						output.insertBefore(div,null);


					});
					$('#clear, #result').show();
					picReader.readAsDataURL(file);
				}else{
				alert("You can only upload image file.");
				$(this).val("");
				}
			}
		});
	}
}*/

</script>

<script type="text/javascript">

function popmessagepickwidget(value){
	if($('#sms').prop('checked')==true){
		$('#message').val(value);
		$('#pick_button').val('');
		$('#message3').val(value);
	}else if($('#mms').prop('checked')==true){
		$('#pick_button').val('set');
		$('#message3').val(value);
		$('#resultpick').append('<div style="width: 80px;"><img style="height:70px!important;width:70px!important;float: left;" class="thumbnail" title="preview" src='+value+'></div>');
	}
}
$(document).ready(function(){
	if($('#sms').prop('checked')==true){
		$('#textmsg').show();
		$('#pickfile').show();
	}
	$('#mms').click(function(){
		$('#textmsg').hide();
		$('#upload').show();
		$('#pickfile').hide();
	});
	$('#sms').click(function(){
		$('#upload').hide();
		$('#textmsg').show();
		$('#pickfile').show();
	});
});
function check_image(){
	if($('#mms').prop('checked')==true){
		$('#check_img_validation').val(2);
	}
}
function ValidateForm(form){
	if($('#mms').prop('checked')==true){
		var mms_image=document.getElementById("check_img_validation").value;
		if(mms_image==0){
			alert( "Please Upload a image" );
		return false;
		}
		var msg11=$('#message1').val();
	}
   if($('#sms').prop('checked')==true){
		var sms_msg=document.getElementById("message").value;
		if(sms_msg==''){
			alert( "Please enter a message" );
		return false;
		}
	}

   
}
</script>
<div class="page-content-wrapper">
	<div class="page-content">              
		<h3 class="page-title"> Auto Responders
			<small></small>
		</h3>
		<div class="page-bar">
			<ul class="page-breadcrumb">
				<li>
					<i class="icon-home"></i>
					<a href="<?php echo SITE_URL;?>/users/dashboard">Dashboard</a>
					<i class="fa fa-angle-right"></i>
				</li>
				<li>
					<a href="<?php echo SITE_URL;?>/responders/index">Auto Responders</a>
				</li>
			</ul>                
		</div>
		<div class="clearfix"></div>
		<div class="portlet mt-element-ribbon light portlet-fit ">
<div class="ribbon ribbon-right ribbon-clip ribbon-shadow ribbon-border-dash-hor ribbon-color-success uppercase">
<div class="ribbon-sub ribbon-clip ribbon-right"></div>
create autoresponder form
</div>
			<div class="portlet-title">
				<div class="caption font-red-sunglo">
					<i class="fa fa-repeat font-red-sunglo"></i>
					<span class="caption-subject bold uppercase"> </span>
				</div>
			</div>

<?php $active=$this->Session->read('User.active');?>
				<?php if((empty($numbers_sms))&&($users['User']['sms']==0)){ ?>
			<div class="portlet-body form">				
				<h3 style="margin-top:5px">You need to get a SMS enabled online number to use this feature.</h3><br>
				<b>Purchase Number to use this feature by </b>
					<?php  
					if(API_TYPE==0){
						echo $this->Html->link('Get Number', array('controller' =>'twilios', 'action' =>'searchcountry'), array('class' => 'nyroModal' ,'style'=>'color:#ff0000;'));
					}else if(API_TYPE==1){
						echo $this->Html->link('Get Number', array('controller' =>'nexmos', 'action' =>'searchcountry'), array('class' => 'nyroModal' ,'style'=>'color:#ff0000;'));
					}else if(API_TYPE==3){
						echo $this->Html->link('Get Number', array('controller' =>'plivos', 'action' =>'searchcountry'), array('class' => 'nyroModal' ,'style'=>'color:#ff0000;'));
					}  
					?>
			</div>

<?php }elseif ($active==0){?>
					<h3>Oops! You need to activate your account to use this feature.</h3><br>
					<?php $payment=PAYMENT_GATEWAY;
					if($payment=='1' && PAY_ACTIVATION_FEES=='1'){?>
						Activate account with PayPal by <?php echo $this->Html->link('Clicking Here', array('controller' =>'users', 'action' =>'activation/'.$payment))?>.<br />
					<?php }else if($payment=='2' && PAY_ACTIVATION_FEES=='1'){?>
						Activate account with Credit Card by <?php echo $this->Html->link('Clicking Here', array('controller' =>	'users', 'action' =>'activation/'.$payment))?>.<br />
					<?php }else if($payment=='3' && PAY_ACTIVATION_FEES=='1'){ ?>
						Activate account with <b><?php echo $this->Html->link('PayPal', array('controller' =>'users', 'action' =>'activation/1'))?></b> or <b><?php echo $this->Html->link('Credit Card', array('controller' =>'users', 'action' =>'activation/2'))?></b><br />
					<?php } ?> 

		<?php }else{ ?>
		  <?php echo $this->Session->flash(); ?>	
			<div class="portlet-body form">
					<?php echo $this->Form->create('Responder',array('action'=> 'add','enctype'=>'multipart/form-data','onsubmit'=>' return ValidateForm(this)'));?>
				<div class="form-body">
					<div class="form-group">
						<label>Group<span class="required_star"></span></label>
						<?php
							$Group[0]='Select Group';
							echo $this->Form->input('Responder.group_id', array(
							'class'=>'form-control',
							'label'=>false,
							'type'=>'select',
							'default'=>0,
							'options' => $Group));
						?>
					</div>
					<div class="form-group">
						<label>Name<span class="required_star"></span></label>
						<div>
							<?php echo $this->Form->input('Responder.name',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'name'))?>
						</div>
					</div>
					<div class="form-group">
						<label>Message Template</label>
						<?php
							$Smstemplate[0]='Select Message Template';
							echo $this->Form->input('Smstemplate.id', array(
							'class'=>'form-control',
							'label'=>false,
							'default'=>0,
							'type'=>'select',
							'onchange'=>'confirmmessage(this.value)',
							'options' => $Smstemplate));									
						?>
					</div>
					<div class="form-group">
						<label>Mobile Splash Page<span class="required_star"></span></label>
						<select id="mobilespagesId" class="form-control"  name="data[mobilespages][id]" onchange="confirmpagemessage(this.value)">
							<?php
							$mobilespages[0]="Select Mobile Splash Page";	
							foreach($mobilespages as $row => $value){
								$selected = '';
									if($row == $mobilepageid){
									$selected = ' selected="selected"';
								}?>
							<option "<?php echo  $selected; ?> " value="<?php echo  $row; ?>"><?php echo  $value; ?></option>
					  <?php }?>
						</select>	
					</div>
							<?php if(API_TYPE==0){ ?>	
								<div class="form-group">
												<?php if((!empty($numbers_sms))||($users['User']['sms']==1)){ ?>
									SMS<input type="radio" value="1" name="data[Responder][msg_type]" id="sms" checked />
												<?php } ?>
												<?php if((!empty($numbers_mms))||($users['User']['mms']==1)){ ?>
									MMS<input type="radio" value="2" name="data[Responder][msg_type]" id="mms"/>	
												<?php } ?>
								</div>
								<?php if (FILEPICKERON == 'Yes'){ ?>
								<div id="pickfile" style="display:none;margin-bottom:10px">
									<input onchange="popmessagepickwidget(event.fpfile.url)" data-fp-container="modal" data-fp-mimetypes="*/*" data-fp-apikey=<?php echo FILEPICKERAPIKEY?> type="filepicker" id="pickbutton">	
									<input type="hidden" name="data[Message][pick_button]" value="" id="pick_button" />
								</div>
								<?php } ?>
							<?php  }else{ ?>
								<?php if (FILEPICKERON == 'Yes'){ ?>
<div style="margin-bottom:10px">
									<input  onchange="popmessagepickwidgetnexmo(event.fpfile.url)" data-fp-container="modal" data-fp-mimetypes="*/*" data-fp-apikey=<?php echo FILEPICKERAPIKEY?> type="filepicker" id="pickbutton">	
									<input type="hidden" name="data[Message][pick_button]" value="" id="pick_button" />
</div>
								<?php  }  ?>
								<div class="form-group">
									<label>Responder Message<span class="required_star"></label>&nbsp;<a href="javascript:;" data-container="body" data-trigger="hover" data-html="true" data-content="Use <b>%%CURRENTDATE%%</b> to output the current date in the responder message that gets sent to your subscribers" data-original-title="Merge Tags" class="popovers"><i class="fa fa-tags" style="font-size:18px"></i></a>
										<?php echo $this->Form->input('Responder.message',array('div'=>false,'label'=>false, 'class' => 'form-control ','id'=>'message','maxlength'=>'148','rows'=>'6','cols'=>46))?>
										<!--<div id='counter' style="margin-top:10px">Remaining Characters&nbsp;
											<input type="text" class="form-control input-small" name="limit" id="limit" size=4 readonly value="148">
										</div>
										Special characters not allowed such as ~ [ ] ; "-->
									<span id="messageErr" class="ValidationErrors"></span>
								</div>
						<?php } ?>
						<input type="hidden" name="data[Message][pick_file]" id="message3" value=""/>		
						<?php if(API_TYPE==0){ ?>
						<div id='textmsg' style="display:none;">
							<div class="form-group">
								<label>Responder Message<span class="required_star"></label>&nbsp;<a href="javascript:;" data-container="body" data-trigger="hover" data-html="true" data-content="Use <b>%%CURRENTDATE%%</b> to output the current date in the responder message that gets sent to your subscribers" data-original-title="Merge Tags" class="popovers"><i class="fa fa-tags" style="font-size:18px"></i></a>
								<?php echo $this->Form->input('Responder.message',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'message','maxlength'=>'148','rows'=>'6','cols'=>46))?>
									<!--<div id='counter' style="margin-top:10px">Remaining Characters&nbsp;
										<input type="text" class="form-control input-small" name="limit" id="limit" size=4 readonly value="148">
									</div>
								Special characters not allowed such as ~ [ ] ; "-->
							</div>
						</div>
						<div id='upload' style="display:none;">
							<div class="form-group" style="margin-bottom:0px">
								<!--<label for="some21">Image<span class="required_star"></span>&nbsp;
<a href="javascript:;" data-container="body" data-trigger="hover" data-content="Please only upload a max of 10 images. This is the max number of images allowed by our SMS gateway."   data-original-title="Images" class="popovers"> <i class="fa fa-question-circle" style="font-size:18px"></i> </a>
								</label>  -->
								
                                                                <!--<input type="file" id="files" name="data[Message][image][]" multiple onclick="check_image()"/>
								<input type="hidden" id="check_img_validation" value="0" />
								<br>
								<div id="resultpick"></div>
								<div id="result" style="margin-top:10px"></div>-->

<!--********************-->
<div data-provides="fileinput" class="fileinput fileinput-new">
<input type="hidden" id="check_img_validation" value="0" />
                                                        <div style="width: 200px; height: 150px;" class="fileinput-new thumbnail">
                                                            <img alt="" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image"> </div>
                                                        <div style="max-width: 200px; max-height: 150px; line-height: 10px;" class="fileinput-preview fileinput-exists thumbnail"></div>
                                                        <div>
                                                            <span class="btn default btn-file">
                                                                <span class="fileinput-new"> Select image </span>
                                                                <span class="fileinput-exists"> Change </span>
                                                                <input type="hidden" value="" name="..."><input type="file" id="files" name="data[Message][image][]" onclick="check_image()" > </span>
                                                            <a data-dismiss="fileinput" class="btn red fileinput-exists" href="javascript:;"> Remove </a>
                                                        </div>
</div>

<!--***********************-->



							</div>
							<!--<div style="margin-top:-20px;padding-bottom:10px;">(Upload multiple images to return image galleries. Perfect for real estate, automotive, and other image-driven niches)
							</div>-->


							<div class="form-group" style="margin-top:0px">
								<label>Responder Message</label>&nbsp;<a href="javascript:;" data-container="body" data-trigger="hover" data-html="true" data-content="Use <b>%%CURRENTDATE%%</b> to output the current date in the responder message that gets sent to your subscribers" data-original-title="Merge Tags" class="popovers"><i class="fa fa-tags" style="font-size:18px"></i></a>
								<?php echo $this->Form->input('Responder.message1',array('div'=>false,'label'=>false, 'class' => 'form-control' ,'id'=>'message1','maxlength'=>'148','rows'=>'6','cols'=>46))?>
								<!--<div id='counter' style="margin-top:10px">Remaining Characters&nbsp;
									<script type="text/javascript">
										document.write("<input type=text class='form-control input-small' name=limit1 id=limit1 size=4 readonly value="+count+">");
									</script>
								</div>
								Special characters not allowed such as ~ [ ] ;-->
							</div>
						</div>
					<?php  } ?>	
					<div class="form-group">
						<label>Mandatory Appended Message</label>
						<!--<?php echo $this->Form->input('Responder.systemmsg',array('div'=>false,'label'=>false, 'class' => 'form-control input-small','id'=>'message','maxlength'=>'148','rows'=>'2','cols'=>46,'value'=>'STOP to end','readonly'=>'readonly','style'=>'color:#808080'))?>-->
						<?php echo $this->Form->input('Responder.systemmsg',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'message','maxlength'=>'148','value'=>'STOP to end','readonly'=>'','style'=>'color:#808080'))?>
					</div>
					<div class="form-group">
                                                  Days<input type="radio" value="1" name="data[Responder][ishour]" id="senddays" checked />
						  Hours<input type="radio" value="2" name="data[Responder][ishour]" id="sendhours"/>&nbsp;<a href="javascript:;" data-container="body" data-trigger="hover" data-html="true" data-content="Send the responder either # of days or hours after subscriber joins your list. Enter 0 to send immediately.<br/><br/><b>NOTE: </b>Hours is a range in that hour of time. So if you selected 3 hours, it will send the responder to anyone that subscribed greater than 2 hours up to 3 hours ago." data-original-title="Send Responder" class="popovers"><i class="fa fa-question-circle" style="font-size:18px"></i></a>	<br/>
												
							<label style="margin-top:5px">  Send After Signup:</label>
							<!--<?php echo $this->Form->input('Responder.days',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'days'))?>-->
<?php echo $this->Form->text('Responder.days',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'days','type'=>'number','min'=>'0','max'=>'365'))?>

					</div>
				</div>
					<div class="form-actions">   
						<?php echo $this->Form->submit('Save',array('div'=>false,'class'=>'btn blue'));?>
						<?php echo $this->Html->link(__('Cancel', true), array('controller' => 'responders','action' => 'index'),array('class'=>'btn default','style'=>'margin:0px;')); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
  <?php } ?>	 
		</div> 	
	</div>
</div>   
    
	