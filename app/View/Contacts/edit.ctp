<script>

function flashvalidation11(){

 var a = document.getElementById('ContactName').value;
 var b = document.getElementById('ContactPhoneNumber').value;
 var bday = document.getElementById('birthday').value;
 <?php if(API_TYPE==0) {?>
 var fax = document.getElementById('ContactFaxNumber').value;
 <?} ?>
 /*if(a==''){
 
 alert('Please enter a name');
 return false;
 
 }
*/ if(b==''){
 
 alert('Please enter a phone number');
 return false;
 
 }else{
    var phone =(/^[+0-9]+$/);

    if(!b.match(phone)){
      alert("Please enter correct phone number with NO spaces, dashes, or parentheses.");
      return false;  
    }
 }
 
 <?php if(API_TYPE==0) {?>
 if(fax!=''){

    var faxphone =(/^[+0-9]+$/);

    if(!fax.match(faxphone)){
      alert("Please enter correct fax number with NO spaces, dashes, or parentheses.");
      return false;  
    }
 }
 <?} ?>

if (bday !='' && bday !='0000-00-00'){
    if (!isValidDate(bday)) {
        alert('Please enter date in format: YYYY-MM-DD');
        return false;
    }
 }
 
}

function isValidDate(dateString) {

  var regEx = /^\d{4}-\d{2}-\d{2}$/;
  //alert (dateString.match(regEx));
  
  if(!dateString.match(regEx)){
    return false;  // Invalid format
  }else{

     var d;

     d = new Date(dateString);

     if(!((d = new Date(dateString))|0)){
        return false;
     }else{
        return true;
     }
     
  }

  
}
</script>

<div>
<div class="clearfix"></div>
<div class="portlet box blue-dark">
<div class="portlet-title">
	<div class="caption">
		Edit Contact
	</div>
</div>



<div class="portlet-body">

<!-- login box-->
<div class="loginbox">
	<div class="loginner">
		<div class="login-left">

<div class="contacts form">
<?php echo $this->Form->create('Contact', array('id' => 'editContactForm','onsubmit'=>'return flashvalidation11();'));?>
	<fieldset>
		<legend><?php //__('Edit Contact'); ?></legend>
		<!--Javascript validation goes Here---------->
		<div id="validationMessages" style="display:none"></div>
		<!--Javascript validation goes Here---------->
		
		
		<div class="feildbox form-group" >
			<label for="some21">Groups</label>	

		
		
			<select id="KeywordId" class="form-control txt" multiple="multiple" name="data[Group][id][]" >
			<?php
			
			
			foreach($groupsnames as $Groups){ 
		
			//$groupid=$messages['Group']['id'];
			if($Groups['Group']['keyword']!='?')
			{
			
		
			
			?>
    <option <?php if(isset($groupid[$Groups['Group']['id']])){ ?> selected <?php } ?>value="<?php echo $Groups['Group']['id']; ?>"><?php echo ucwords($Groups['Group']['group_name']).'('.$Groups['Group']['totalsubscriber'].')'; ?></option>
    <?php }} ?>
			
</select>
			
	
	</div>
	<?php echo $this->Form->input('id');	?>
	<div class="form-group" >	
			<label>Name</label>	
	<?php echo $this->Form->input('name',array('class' =>'form-control','div'=>false,'label'=>false));?>
	</div>
	<div class="form-group" >	
			<label>Phone Number</label>	
	<?php echo $this->Form->input('phone_number',array('class' =>'form-control','div'=>false,'label'=>false)); ?>
	</div>
	<?php if(API_TYPE==0) {?>
	<div class="form-group" >	
			<label>Fax Number</label>	
	<?php echo $this->Form->input('fax_number',array('class' =>'form-control','div'=>false,'label'=>false)); ?>
	</div>
	<?}?>
        <div class="form-group" >	
			<label>Email</label>	
        <input type="email" class="form-control" id="email" name="data[Contact][email]" value="<?php echo $email; ?>" />
        </div>
        <div class="form-group" >	
			<label>Birthday</label>	
			<?php

                        if ($birthday == '0000-00-00'){
                            $birthday = '';
                        }

                        ?>
        <input type="date" class="form-control" id="birthday" name="data[Contact][birthday]" placeholder="Format: YYYY-MM-DD" value="<?php echo $birthday; ?>"/>

        </div>
	</fieldset>
	<br/>
	<div class="note note-warning">NO spaces, dashes, or parentheses in numbers<br/>
        <font style="color:red;font-weight:bold">Include country code in the number</font>
        <br/>US Example: 12025248725
        <br/>UK Example: 447481340516</div>
<input type="Submit" value="Save" class="btn btn-primary">
<?php 
echo $this->Form->end();
echo $this->Validation->rules(array('Contact'),array('formId'=>'editContactForm'));
?>
</div>
		</div>
	</div>
</div>
</div>
</div>
</div>