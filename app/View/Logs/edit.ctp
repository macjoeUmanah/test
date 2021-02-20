<div class="logs form">
<?php echo $this->Form->create('Log');?>
	<fieldset>
		<legend><?php echo('Edit Log'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('user_id');
		echo $this->Form->input('phone_number');
		echo $this->Form->input('text_message');
		echo $this->Form->input('voice_url');
		echo $this->Form->input('route');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php echo('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('Log.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Log.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Logs', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Users', true), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User', true), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>