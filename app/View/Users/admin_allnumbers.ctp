	
	<table id="tableOne" cellspacing="0" cellpadding="0"style="width:100%;" >
		<thead>
		<tr>
		
			<th class="tc">Number</th>
			<th class="tc">Type</th>
			<th class="tc">Action</th>
		
		</tr>
				
	    </thead>
		 <tbody>		
		<?php 
		if(!empty($numbers)){
		$i = 0;
		foreach($numbers as $invoicedetil) { 
		$class = null;
		
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		
		?>
	
	<tr <?php echo $class;?>> 
			 
			   <td style="text-align:left;"><?php echo $invoicedetil['UserNumber']['number'] ?></td>
			   <td style="text-align:left;" >Secondary</td>
			   <td style="text-align:left;" class="actions"><?php echo $this->Html->link(__('Release Number', true), array('action' => 'number_release', $invoicedetil['UserNumber']['id']), array('class' => 'forgetpass'), sprintf(__('Are you sure you want to release this number for this user?', true)));  ?></td> </tr>
			
			  <?php }} ?>
			  
			  <?php 
			  
			  if($usernumbers['User']['assigned_number'] > 0){ ?>
			 </tr>  
			 <td style="text-align:left;"><?php echo $usernumbers['User']['assigned_number'] ?></td>
			   <td style="text-align:left;">Primary</td>
			    <?php if(API_TYPE!=2){ ?>
	         <td style="text-align:left;" class="actions"><?php echo $this->Html->link(__('Release Number', true), array('action' => 'number_release_user', $usernumbers['User']['id']), array('class' => 'forgetpass'), sprintf(__('Are you sure you want to release this number for this user?', true)));  ?></td> </tr>
			  <?php } ?>
			  <?php } ?>
			 
			  </tbody>
</table>

	
		