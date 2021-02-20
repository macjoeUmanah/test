	<div>
<div class="clearfix"></div>
<div class="portlet box blue-dark">
<div class="portlet-title">
	<div class="caption">
		Purchase Number Subscription
	</div>
</div>

<div class="portlet-body">

<!--<div class="purchase_credit">
	<h2>Purchase Subscription</h2>-->
	<div class="message">Please confirm the following before making your purchase</div>
	<p>Credits for: <b><?php echo $this->Session->read('User.username')?></b></p>
	<p>Item: <b><?php echo $monthlynumberpackage['MonthlyNumberPackage']['package_name']?></b></p>
	<p>Total Cost: <b>
	
	<?php 
	       $currencycode=PAYMENT_CURRENCY_CODE;
			
	       if($currencycode=='MXN' || $currencycode=='USD' || $currencycode=='AUD' || $currencycode=='CAD' || $currencycode=='HKD' || $currencycode=='NZD' || $currencycode=='SGD'){?>
              
              $<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='JPY'){ ?>
              ¥<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='EUR'){ ?>
              €<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='GBP'){ ?>
              £<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='DKK' || $currencycode=='NOK' || $currencycode=='SEK'){ ?>
              kr<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='CHF'){ ?>
              CHF<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='BRL'){ ?>
              R$<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='PHP'){ ?>
              ₱<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php } else if($currencycode=='ILS'){ ?>
              ₪<?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount']?>/month</b></p>
              <?php }?>
			  <?php if($users['User']['monthly_number_subscription_id']==''){ ?>
				<form action="<?php echo SITE_URL; ?>/users/purchase_subscription_stripe_numbers/<?php echo $monthlynumberpackage['MonthlyNumberPackage']['id']?>" method="post" id="signupForm" name="signupForm">
					<button class="btn blue" id="customButtonpay">Subscribe</button>
				</form>
			<?php }else{ ?>
				<!--<a href="<?php echo SITE_URL;?>/users/upgrade_monthly_numbers_subscription/<?php echo $monthlynumberpackage['MonthlyNumberPackage']['id']?>" class="btn blue">Subscribe</a>-->

<?php echo $this->Html->link(__('Subscribe', true), array('controller'=>'users','action' => 'upgrade_monthly_numbers_subscription/'.$monthlynumberpackage['MonthlyNumberPackage']['id']), array('class' => 'btn blue'),sprintf(__('Are you sure you want to change your numbers plan? This will place you on the new plan and automatically adjust your invoice for next month.',true))) ;?> 
<?php ?>
			<?php } ?>
	
</div>
</div>
</div>
<?php if($users['User']['monthly_number_subscription_id']==''){ ?>
<script>
		var handler = StripeCheckout.configure({
			key: '<?php echo PUBLISHABLE_KEY;?>',
			locale: 'auto',
			image: '<?php echo SITE_URL?>/app/webroot/img/security.png',
			token: function(token) {
				var form$ = $("#signupForm");
				var token = token.id;
				form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
				form$.get(0).submit();
				// You can access the token ID with `token.id`.
				// Get the token ID to your server-side code for use.
			}
		});
		document.getElementById('customButtonpay').addEventListener('click', function(e) {
			// Open Checkout with further options:
			handler.open({
				name: '<?php echo SITENAME;?>',
				description: '<?php echo $monthlynumberpackage['MonthlyNumberPackage']['package_name']; ?>',
				currency: '<?php echo PAYMENT_CURRENCY_CODE;?>',
				amount: <?php echo $monthlynumberpackage['MonthlyNumberPackage']['amount'] * 100;?>,
				zipCode: true,
				billingAddress: true
			});
			e.preventDefault();
		});
		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler.close();
		});
	</script>
<?php } ?>