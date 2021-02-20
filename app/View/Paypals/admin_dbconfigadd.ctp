<ul  class="secondary_nav">
				<?php
				$navigation = array(
					  	'List Dbconfig' => '/admin/paypals/dbconfig',
						'Edit Dbconfig' => '/admin/paypals/dbconfigedit'
					   					   
				);				
				$matchingLinks = array();
				
				foreach ($navigation as $link) {
						if (preg_match('/^'.preg_quote($link, '/').'/', substr($this->here, strlen($this->base)))) {
								$matchingLinks[strlen($link)] = $link;
						}
				}
				
				krsort($matchingLinks);
				
				$activeLink = ife(!empty($matchingLinks), array_shift($matchingLinks));
				$out = array();
				
				foreach ($navigation as $title => $link) {
						$out[] = '<li>'.$html->link($title, $link, ife($link == $activeLink, array('class' => 'current'))).'</li>';
				}
				
				echo join("\n", $out);
				?>			
</ul>
<div class="configs form">
<?php echo $this->Form->create('Paypal');?>
	<fieldset>
		<legend><?php echo('Add DbConfig'); ?></legend>
	<?php
		echo $this->Form->input('dbusername');
		echo $this->Form->input('dbname');
		echo $this->Form->input('password','dbpassword');	
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
