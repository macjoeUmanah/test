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
	});jQuery("#BirthdayGroupId").validate({
		expression: "if (VAL > 0) return true; else return false;",
		message: "Please select group"
	}); 
});
$(document).ready(function (){
	$('textarea[maxlength]').live('keyup change', function() {
		var str = $(this).val()
		var mx = parseInt($(this).attr('maxlength'))
		if (str.length > mx) {
			$(this).val(str.substr(0, mx))
			return false;
		}
	});
	
});
function popmessagepickwidgetnexmo(value){
	$('#message').text(value);
	$('#pick_button').val('');
	$('#check_img_validation').val(0);
	$('#message3').val(value);
}
function popmessagepickwidget(value){
	if($('#sms').prop('checked')==true){
		$('#message').text(value);
		$('#pick_button').val('');
		$('#check_img_validation').val(0);
		$('#message3').val(value);
	}else if($('#mms').prop('checked')==true){
			$('#pick_button').val('set');
			$('#check_img_validation').val(0);
			var check=$('img').hasClass('thumbnail');
		if(check==true){
			$('.thumbnail').remove();
		}
		$('#resultpick').append('<div style="width: 80px;"><img style="height:70px!important;width:70px!important;float: left;" class="thumbnail" title="preview" src='+value+'></div>');
		$('#message3').val(value);
	}
}
$(document).ready(function(){
	var type=$('#msg_type').val();
	if(type==1){
		$('#textmsg').show();
	}else if(type==2){
		$('#upload').show();
	}
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
		var check=$('img').hasClass('thumbnail');
		var mms_image=document.getElementById("check_img_validation").value;
		if(mms_image==0 && check==false){
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
var count = "148";
function update(){
	var tex = $("#message").val();
	var count1 = (148);
	tex = tex.replace('[','');
	tex = tex.replace(']','');
	tex = tex.replace('~','');
	tex = tex.replace(';','');
	tex = tex.replace('`','');
	tex = tex.replace('"','');
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
	tex = tex.replace('`','');
	tex = tex.replace('"','');
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
/*window.onload = function(){
	if(window.File && window.FileList && window.FileReader){
		$('#files').live("change", function(event) {
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
<div class="page-content-wrapper">
	<div class="page-content">              
		<h3 class="page-title"> Birthday SMS Wishes
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
					<a href="<?php echo SITE_URL;?>/birthday">Birthday SMS Wishes</a>
				</li>
			</ul>                
		</div>
		<div class="clearfix"></div>
		<div class="portlet mt-element-ribbon light portlet-fit ">
<div class="ribbon ribbon-right ribbon-clip ribbon-shadow ribbon-border-dash-hor ribbon-color-success uppercase">
<div class="ribbon-sub ribbon-clip ribbon-right"></div>
edit birthday sms wishes form
</div>
			<div class="portlet-title">
				<div class="caption font-red-sunglo">
					<i class="fa fa-birthday-cake font-red-sunglo"></i>
					<span class="caption-subject bold uppercase"> </span>
				</div>
			</div>
			<?php echo $this->Session->flash(); ?>
			<div class="portlet-body form">
				<form method="post" action="<?php echo SITE_URL?>/birthday/edit/<?php echo $id;?>" enctype="multipart/form-data" onsubmit="return ValidateForm(this)" >
							<input type="hidden" name="data[Birthday][id]" value="<?php echo $id;?>">
					<div class="form-body">
						<div class="form-group">
							<label>Group</label>
								<?php
								if(!empty($this->data['Birthday']['group_id'])){
									$disabled = 'true';
								}else{
									$disabled = 'false';
								}
								$Group[0]='Select Group';
								echo $this->Form->input('Birthday.group_id', array('class' => 'form-control', 'label'=>false, 'type'=>'select','default'=>0, 'options' => $Group,'readonly'=>$disabled));
								?>
						</div>
						<div class="form-group">
						   <label>Name<span class="required_star"></span></label>
								<?php echo $this->Form->input('Birthday.name',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'name'))?>
						</div>
						<div class="form-group">
							<label>Message template</label>
							<?php
								$Smstemplate[0]='Select Message Template';
								echo $this->Form->input('Smstemplate.id', array('label'=>false,'class' => 'form-control','default'=>0,'type'=>'select','onchange'=>'confirmmessage(this.value)','options' => $Smstemplate));	?>
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
									}
									?>
								<option "<?php echo  $selected; ?> " value="<?php echo  $row; ?>"><?php echo  $value; ?></option>
								<?php }	?>
							</select>
						</div>
						<input type="hidden" name="data[Message][pick_file]" id="message3" value=""/>	
						<?php if(API_TYPE==0){ ?>	
						<div class="form-group">
							<!--<label class="radio-inline">-->
							   <input type="hidden" name="data[Message][pick_file_old]" value="<?php echo $birthday['Birthday']['image_url'] ?>" id="pick_button" />
								<input type="hidden"  value="<?php echo $birthday['Birthday']['sms_type'] ?>" id="msg_type" />
								<?php
								if((!empty($numbers_sms))||($users['User']['sms']==1)){ ?>
									SMS<input type="radio" value="1" name="data[Birthday][msg_type]" id="sms" 
								<?php if($birthday['Birthday']['sms_type']==1){ ?> checked <?php } ?>/>
								<?php } ?>
								<?php if((!empty($numbers_mms))||($users['User']['mms']==1)){ ?>
								MMS<input type="radio" value="2" name="data[Birthday][msg_type]" id="mms" 
								<?php if($birthday['Birthday']['sms_type']==2){ ?> checked <?php } ?> />
								<?php if($birthday['Birthday']['sms_type']==2){ ?>
								<input type="hidden" name="data[Message][mms_image]" value="<?php echo $birthday['Birthday']['image_url'] ?>" id="mms_image" />
							<?php } } ?>
							<!--</label>-->
						</div>
						<?php if (FILEPICKERON == 'Yes'){?>
							<div id="pickfile" style="display:none;margin-bottom:10px">
								<input onchange="popmessagepickwidget(event.fpfile.url)" data-fp-container="modal" data-fp-mimetypes="*/*" data-fp-apikey=<?php echo FILEPICKERAPIKEY?> type="filepicker">
							</div>	
						<?php } ?>
						<?php } else{ ?>
						
						<?php if (FILEPICKERON == 'Yes'){ ?>
<div style="margin-bottom:10px">
							<input onchange="popmessagepickwidgetnexmo(event.fpfile.url)" data-fp-container="modal" data-fp-mimetypes="*/*" data-fp-apikey=<?php echo FILEPICKERAPIKEY?> type="filepicker">
</div>
						<?php } ?>
						<div class="form-group">
							<label>Birthday Message<span class="required_star"></label>
							<?php echo $this->Form->input('Birthday.message',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'message','maxlength'=>'148','rows'=>'6','cols'=>46))?>
							<!--<div id='counter' style="margin-top:10px">Remaining Characters&nbsp;<script type="text/javascript">
							document.write("<input type=text  class='form-control input-small'  name=limit id=limit size=4 readonly value="+count+">");
							</script></div>
							Special characters not allowed such as ~ [ ] ; "-->
							<span id="messageErr" class="ValidationErrors"></span>
						</div>
						<?php } ?>
						<?php if(API_TYPE==0){ ?>	
						<div id='textmsg' style="display:none;">
							<div class="form-group">
								<label>Birthday Message<span class="required_star"></label>
								<?php echo $this->Form->input('Birthday.message',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'message','maxlength'=>'148','rows'=>'6','cols'=>46,'value'=>$birthday['Birthday']['message']))?>
								<!--<div id='counter' style="margin-top:10px">Remaining Characters&nbsp;<script type="text/javascript">
								document.write("<input type=text  class='form-control input-small'  name=limit id=limit size=4 readonly value="+count+">");
								</script></div>
								Special characters not allowed such as ~ [ ] ; "-->
							</div>
						</div>
						<div id='upload' style="display:none;" >
							<div class="form-group" >
								<!--<label for="some21">Image<span class="required_star"></span>&nbsp;
<a href="javascript:;" data-container="body" data-trigger="hover" data-content="Please only upload a max of 10 images. This is the max number of images allowed by our SMS gateway" data-original-title="Images" class="popovers"> <i class="fa fa-question-circle" style="font-size:18px"></i> </a>
</label>  
								<input type="file" id="files" name="data[Message][image][]" multiple onclick="check_image()"/>
								<input type="hidden" name="data[Birthday][check_img_validation]"  id="check_img_validation" value="0" />
								<br>
								<div id="resultpick"></div>
								<div id="result" style="margin-top:10px"></div>-->
<!--*************************-->
<div data-provides="fileinput" class="fileinput fileinput-new">
<input type="hidden" name="data[Birthday][check_img_validation]"  id="check_img_validation" value="0" />

                                                        <div style="width: 200px; height: 150px;" class="fileinput-new thumbnail">
                                                            <img alt="" src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=new+image"> </div>
                                                        <div style="max-width: 200px; max-height: 150px; line-height: 10px;" class="fileinput-preview fileinput-exists thumbnail"></div>
                                                        <div>
                                                            <span class="btn default btn-file">
                                                                <span class="fileinput-new"> Select image </span>
                                                                <span class="fileinput-exists"> Change </span>
                                                                <input type="hidden" value="" name="..."><input type="file" id="files" name="data[Message][image][]" onclick="check_image()" > </span>
                                                            <a data-dismiss="fileinput" class="btn red fileinput-exists" href="javascript:;"> Remove </a>
                                                        </div>
                                                    </div>
<!--**************************-->
								<?php 
								if($birthday['Birthday']['sms_type']==2){
									$check=strpos($birthday['Birthday']['image_url'],":");
									if($check!=''){
										$comma=strpos($birthday['Birthday']['image_url'],",");
									if($comma!=''){
										$image_arr=explode(",",$birthday['Birthday']['image_url']);
										foreach($image_arr as $value){	
								?>
								<img  class="thumbnail" src="<?php echo $value; ?>" style="height:70px!important;width:70px!important;float: left;margin-left:5px;" />
								<?php }	}else{ ?>
									<!--<img class="thumbnail" src="<?php echo $birthday['Birthday']['image_url']; ?>" style="height:70px!important;width:70px!important;float: left;margin-left:5px;" />-->
<div style="max-width: 200px; max-height: 150px; line-height: 10px;" class="fileinput-exists thumbnail" ><img class="thumbnail" src="<?php echo $birthday['Birthday']['image_url']; ?>" style="max-width: 190px; max-height: 140px;margin-bottom:0;border:0;padding:0" /></div>
								<?php }	} } ?>
							</div>
							<!--<div style="margin-top:-10px;padding-bottom:10px;">(Upload multiple images to return image galleries. Perfect for real estate, automotive, and other image-driven niches)</div>-->
							<div class="form-group" style="margin-top:0px">
								<label>Birthday Message</label>
									<?php echo $this->Form->input('Birthday.message1',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'message1','maxlength'=>'148','rows'=>'6','cols'=>46,'value'=>$birthday['Birthday']['message']))?>
									<!--<div id='counter' style="margin-top:10px">Remaining Characters&nbsp;<script type="text/javascript">
									document.write("<input type=text  class='form-control input-small'  name=limit id=limit1 size=4 readonly value="+count+">");
									</script></div>
									Special characters not allowed such as ~ [ ] ; "-->
							</div>
						</div>
						<?php } ?>
						<div class="form-group">
							<label>Mandatory Appended Message<span class="required_star"></label>
								<?php echo $this->Form->input('Birthday.systemmsg',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'message','maxlength'=>'148','value'=>$birthday['Birthday']['systemmsg'],'readonly'=>'','style'=>'color:#808080'))?>
						</div>
						<div class="form-group">
							 <label>Send # of days <i>before</i> birthday - Enter 0 to send the day of: <span class="required_star"></span></label>
								<!--<?php echo $this->Form->input('Birthday.days',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'days'))?>-->
<?php echo $this->Form->text('Birthday.days',array('div'=>false,'label'=>false, 'class' => 'form-control','id'=>'days','type'=>'number','min'=>'0','max'=>'365'))?>
						</div>
					</div>
					<div class="form-actions">
						<?php echo $this->Form->submit('Update',array('div'=>false,'class'=>'btn blue'));?>
						<?php echo $this->Html->link(__('Cancel', true), array('controller' => 'birthday','action' => 'index'),array('class'=>'btn default','style'=>'margin:0px;')); ?>
					</div>
				</form>
			</div>
		</div>                     
	</div>
</div>         