<script>
jQuery(function(){
   $('#api_type').change(function(){
   if($('#api_type').val() == 2){
	 $('#showslooce').show();
	 $("#shownumberlimit").hide();
   }else {
	 $('#showslooce').hide();
	 $("#shownumberlimit").show();
   }
  });
});	
    
</script>    
<ul  class="secondary_nav">
				<?php
				$navigation = array(
					  	'List Users' => '/admin/users/index',
					  	'Add User' => '/admin/users/add',
						
					   					   
				);				
				$matchingLinks = array();
				
				foreach ($navigation as $link) {
						if (preg_match('/^'.preg_quote($link, '/').'/', substr($this->here, strlen($this->base)))) {
								$matchingLinks[strlen($link)] = $link;
						}
				}
				
				krsort($matchingLinks);
				
				//$activeLink = ife(!empty($matchingLinks), array_shift($matchingLinks));
				$activeLink = !empty($matchingLinks)?array_shift($matchingLinks):'';
				$out = array();
				
				foreach ($navigation as $title => $link) {
						//$out[] = '<li>'.$html->link($title, $link, ife($link == $activeLink, array('class' => 'current'))).'</li>';
						$out[] = '<li>'.$this->Html->link($title, $link, $link == $activeLink ? array('class' => 'current'):'').'</li>';
				}
				
				echo join("\n", $out);
				?>			
</ul>
<br/><br/>
<link href="<?php echo SITE_URL; ?>/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<div style="display:table;width:100%"><div style="height:100%; width: 24%; margin-right: 10px; float:left; border: solid 1px; border-color: rgb(204, 204, 204);"> <h3 style="color: rgb(51, 51, 51); background-color: #fffdc9;margin-top:0px;margin-bottom:0px;padding:5px;text-align:center"><i class="fa fa-user" style="color:#26C281"></i>&nbsp;Client Stats :: Active Clients</h3><table style="width:100%;height:140px;padding:5px"><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Past 7 days</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('activeclientsweek')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Month</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('activeclientsmonth')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Year</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('activeclientsyear')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Overall</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('activeclientsall')?></td></tr></table> </div>

<div style="height:100%; width: 24%; margin-right: 10px; float:left; border: solid 1px; border-color: rgb(204, 204, 204);"> <h3 style="color: rgb(51, 51, 51); background-color: #fffdc9;margin-top:0px;margin-bottom:0px;padding:5px;text-align:center"><i class="fa fa-comments" aria-hidden="true" style="color:#26C281"></i> SMS Stats :: SMS Sent</h3><table style="width:100%;height:140px;padding:5px"><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Past 7 days</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smssentweek')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Month</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smssentmonth')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Year</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smssentyear')?></td></tr><!--<tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Overall</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smssentall')?></td></tr>--></table> </div>

<div style="height:100%; width: 24%; margin-right: 10px; float:left; border: solid 1px; border-color: rgb(204, 204, 204);"> <h3 style="color: rgb(51, 51, 51); background-color: #fffdc9;margin-top:0px;margin-bottom:0px;padding:5px;text-align:center"><i class="fa fa-comments-o" aria-hidden="true" style="color:#26C281"></i> SMS Stats :: SMS Received</h3><table style="width:100%;height:140px;padding:5px"><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Past 7 days</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smsreceivedweek')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Month</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smsreceivedmonth')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Year</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smsreceivedyear')?></td></tr><!--<tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Overall</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('smsreceivedall')?></td></tr>--></table> </div>

<div style="height:100%; width: 24%; margin-right: 10px; float:left; border: solid 1px; border-color: rgb(204, 204, 204);"> <h3 style="color: rgb(51, 51, 51); background-color: #fffdc9;margin-top:0px;margin-bottom:0px;padding:5px;text-align:center"><i class="fa fa-usd" aria-hidden="true" style="color:#26C281"></i> Financial Stats :: Revenue</h3><table style="width:100%;height:140px;padding:5px"><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Past 7 days</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('revenueweek')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Month</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('revenuemonth')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Current Year</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('revenueyear')?></td></tr><tr><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);">Overall</td><td style="font-weight:bold;color: #3598dc;border: solid 1px; border-color: rgb(204, 204, 204);"><?php echo $this->Session->read('revenueall')?></td></tr></table> </div>
</div>
<div class="pages form">
<?php echo $this->Form->create('User',array('style'=>'width:98%'));?>
	<fieldset>
		<legend><?php echo('Add User'); ?></legend>
	<?php
	$Option3=array('Pacific/Midway'=>'(UTC-11:00) Midway Island',
    'Pacific/Samoa'=>'(UTC-11:00) Samoa',
    'Pacific/Honolulu'=>'(UTC-10:00) Hawaii',
    'US/Alaska'=>'(UTC-09:00) Alaska',
    'America/Los_Angeles'=>'(UTC-08:00) Pacific Time (US & Canada)',
    'America/Tijuana'=>'(UTC-08:00) Tijuana',
    'US/Arizona'=>'(UTC-07:00) Arizona',
    'America/Chihuahua'=>'(UTC-07:00) Chihuahua',
    'America/Chihuahua'=>'(UTC-07:00) La Paz',
    'America/Mazatlan'=>'(UTC-07:00) Mazatlan',
    'US/Mountain'=>'(UTC-07:00) Mountain Time (US & Canada)',
    'America/Managua'=>'(UTC-06:00) Central America',
    'US/Central'=>'(UTC-06:00) Central Time (US & Canada)',
    'America/Mexico_City'=>'(UTC-06:00) Guadalajara',
    'America/Mexico_City'=>'(UTC-06:00) Mexico City',
    'America/Monterrey'=>'(UTC-06:00) Monterrey',
    'Canada/Saskatchewan'=>'(UTC-06:00) Saskatchewan',
    'America/Bogota'=>'(UTC-05:00) Bogota',
    'US/Eastern'=>'(UTC-05:00) Eastern Time (US & Canada)',
    'US/East-Indiana'=>'(UTC-05:00) Indiana (East)',
    'America/Lima'=>'(UTC-05:00) Lima',
    'America/Bogota'=>'(UTC-05:00) Quito',
    'Canada/Atlantic'=>'(UTC-04:00) Atlantic Time (Canada)',
    'America/Caracas'=>'(UTC-04:30) Caracas',
    'America/La_Paz'=>'(UTC-04:00) La Paz',
    'America/Santiago'=>'(UTC-04:00) Santiago',
    'Canada/Newfoundland'=>'(UTC-03:30) Newfoundland',
    'America/Sao_Paulo'=>'(UTC-03:00) Brasilia',
    'America/Argentina/Buenos_Aires'=>'(UTC-03:00) Buenos Aires',
    'America/Argentina/Buenos_Aires'=>'(UTC-03:00) Georgetown',
    'America/Godthab'=>'(UTC-03:00) Greenland',
    'America/Noronha'=>'(UTC-02:00) Mid-Atlantic',
    'Atlantic/Azores'=>'(UTC-01:00) Azores',
    'Atlantic/Cape_Verde'=>'(UTC-01:00) Cape Verde Is.',
    'Africa/Casablanca'=>'(UTC+00:00) Casablanca',
    'Europe/London'=>'(UTC+00:00) Edinburgh',
    'Etc/Greenwich'=>'(UTC+00:00) Greenwich Mean Time : Dublin',
    'Europe/Lisbon'=>'(UTC+00:00) Lisbon',
    'Europe/London'=>'(UTC+00:00) London',
    'Africa/Monrovia'=>'(UTC+00:00) Monrovia',
    'UTC'=>'(UTC+00:00) UTC',
    'Europe/Amsterdam'=>'(UTC+01:00) Amsterdam',
    'Europe/Belgrade'=>'(UTC+01:00) Belgrade',
    'Europe/Berlin'=>'(UTC+01:00) Berlin',
    'Europe/Berlin'=>'(UTC+01:00) Bern',
    'Europe/Bratislava'=>'(UTC+01:00) Bratislava',
    'Europe/Brussels'=>'(UTC+01:00) Brussels',
    'Europe/Budapest'=>'(UTC+01:00) Budapest',
    'Europe/Copenhagen'=>'(UTC+01:00) Copenhagen',
    'Europe/Ljubljana'=>'(UTC+01:00) Ljubljana',
    'Europe/Madrid'=>'(UTC+01:00) Madrid',
    'Europe/Paris'=>'(UTC+01:00) Paris',
    'Europe/Prague'=>'(UTC+01:00) Prague',
    'Europe/Rome'=>'(UTC+01:00) Rome',
    'Europe/Sarajevo'=>'(UTC+01:00) Sarajevo',
    'Europe/Skopje'=>'(UTC+01:00) Skopje',
    'Europe/Stockholm'=>'(UTC+01:00) Stockholm',
    'Europe/Vienna'=>'(UTC+01:00) Vienna',
    'Europe/Warsaw'=>'(UTC+01:00) Warsaw',
    'Africa/Lagos'=>'(UTC+01:00) West Central Africa',
    'Europe/Zagreb'=>'(UTC+01:00) Zagreb',
    'Europe/Athens'=>'(UTC+02:00) Athens',
    'Europe/Bucharest'=>'(UTC+02:00) Bucharest',
    'Africa/Cairo'=>'(UTC+02:00) Cairo',
    'Africa/Harare'=>'(UTC+02:00) Harare',
    'Europe/Helsinki'=>'(UTC+02:00) Helsinki',
    'Europe/Istanbul'=>'(UTC+02:00) Istanbul',
    'Asia/Jerusalem'=>'(UTC+02:00) Jerusalem',
    'Europe/Helsinki'=>'(UTC+02:00) Kyiv',
    'Africa/Johannesburg'=>'(UTC+02:00) Pretoria',
    'Europe/Riga'=>'(UTC+02:00) Riga',
    'Europe/Sofia'=>'(UTC+02:00) Sofia',
    'Europe/Tallinn'=>'(UTC+02:00) Tallinn',
    'Europe/Vilnius'=>'(UTC+02:00) Vilnius',
    'Asia/Baghdad'=>'(UTC+03:00) Baghdad',
    'Asia/Kuwait'=>'(UTC+03:00) Kuwait',
    'Europe/Minsk'=>'(UTC+03:00) Minsk',
    'Africa/Nairobi'=>'(UTC+03:00) Nairobi',
    'Asia/Riyadh'=>'(UTC+03:00) Riyadh',
    'Europe/Volgograd'=>'(UTC+03:00) Volgograd',
    'Asia/Tehran'=>'(UTC+03:30) Tehran',
    'Asia/Muscat'=>'(UTC+04:00) Abu Dhabi',
    'Asia/Baku'=>'(UTC+04:00) Baku',
    'Europe/Moscow'=>'(UTC+04:00) Moscow',
    'Asia/Muscat'=>'(UTC+04:00) Muscat',
    'Europe/Moscow'=>'(UTC+04:00) St. Petersburg',
    'Asia/Tbilisi'=>'(UTC+04:00) Tbilisi',
    'Asia/Yerevan'=>'(UTC+04:00) Yerevan',
    'Asia/Kabul'=>'(UTC+04:30) Kabul',
    'Asia/Karachi'=>'(UTC+05:00) Islamabad',
    'Asia/Karachi'=>'(UTC+05:00) Karachi',
    'Asia/Tashkent'=>'(UTC+05:00) Tashkent',
    'Asia/Calcutta'=>'(UTC+05:30) Chennai',
    'Asia/Kolkata'=>'(UTC+05:30) Kolkata',
    'Asia/Calcutta'=>'(UTC+05:30) Mumbai',
    'Asia/Calcutta'=>'(UTC+05:30) New Delhi',
    'Asia/Calcutta'=>'(UTC+05:30) Sri Jayawardenepura',
    'Asia/Katmandu'=>'(UTC+05:45) Kathmandu',
    'Asia/Almaty'=>'(UTC+06:00) Almaty',
    'Asia/Dhaka'=>'(UTC+06:00) Astana',
    'Asia/Dhaka'=>'(UTC+06:00) Dhaka',
    'Asia/Yekaterinburg'=>'(UTC+06:00) Ekaterinburg',
    'Asia/Rangoon'=>'(UTC+06:30) Rangoon',
    'Asia/Bangkok'=>'(UTC+07:00) Bangkok',
    'Asia/Bangkok'=>'(UTC+07:00) Hanoi',
    'Asia/Jakarta'=>'(UTC+07:00) Jakarta',
    'Asia/Novosibirsk'=>'(UTC+07:00) Novosibirsk',
    'Asia/Hong_Kong'=>'(UTC+08:00) Beijing',
    'Asia/Chongqing'=>'(UTC+08:00) Chongqing',
    'Asia/Hong_Kong'=>'(UTC+08:00) Hong Kong',
    'Asia/Krasnoyarsk'=>'(UTC+08:00) Krasnoyarsk',
    'Asia/Kuala_Lumpur'=>'(UTC+08:00) Kuala Lumpur',
    'Australia/Perth'=>'(UTC+08:00) Perth',
    'Asia/Singapore'=>'(UTC+08:00) Singapore',
    'Asia/Taipei'=>'(UTC+08:00) Taipei',
    'Asia/Ulan_Bator'=>'(UTC+08:00) Ulaan Bataar',
    'Asia/Urumqi'=>'(UTC+08:00) Urumqi',
    'Asia/Irkutsk'=>'(UTC+09:00) Irkutsk',
    'Asia/Tokyo'=>'(UTC+09:00) Osaka',
    'Asia/Tokyo'=>'(UTC+09:00) Sapporo',
    'Asia/Seoul'=>'(UTC+09:00) Seoul',
    'Asia/Tokyo'=>'(UTC+09:00) Tokyo',
    'Australia/Adelaide'=>'(UTC+09:30) Adelaide',
    'Australia/Darwin'=>'(UTC+09:30) Darwin',
    'Australia/Brisbane'=>'(UTC+10:00) Brisbane',
    'Australia/Canberra'=>'(UTC+10:00) Canberra',
    'Pacific/Guam'=>'(UTC+10:00) Guam',
    'Australia/Hobart'=>'(UTC+10:00) Hobart',
    'Australia/Melbourne'=>'(UTC+10:00) Melbourne',
    'Pacific/Port_Moresby'=>'(UTC+10:00) Port Moresby',
    'Australia/Sydney'=>'(UTC+10:00) Sydney',
    'Asia/Yakutsk'=>'(UTC+10:00) Yakutsk',
    'Asia/Vladivostok'=>'(UTC+11:00) Vladivostok',
    'Pacific/Auckland'=>'(UTC+12:00) Auckland',
    'Pacific/Fiji'=>'(UTC+12:00) Fiji',
    'Pacific/Kwajalein'=>'(UTC+12:00) International Date Line West',
    'Asia/Kamchatka'=>'(UTC+12:00) Kamchatka',
    'Asia/Magadan'=>'(UTC+12:00) Magadan',
    'Pacific/Fiji'=>'(UTC+12:00) Marshall Is.',
    'Asia/Magadan'=>'(UTC+12:00) New Caledonia',
    'Asia/Magadan'=>'(UTC+12:00) Solomon Is.',
    'Pacific/Auckland'=>'(UTC+12:00) Wellington',
    'Pacific/Tongatapu'=>'(UTC+13:00) Nukualofa');

$Option4=array('Afghanistan'=>'Afghanistan',
'Albania'=>'Albania',
'Algeria'=>'Algeria',
'American Samoa'=>'American Samoa',
'Andorra'=>'Andorra',
'Angola'=>'Angola',
'Anguilla'=>'Anguilla',
'Antarctica'=>'Antarctica',
'Antigua and Barbuda'=>'Antigua and Barbuda',
'Argentina'=>'Argentina',
'Armenia'=>'Armenia',
'Aruba'=>'Aruba',
'Australia'=>'Australia',
'Austria'=>'Austria',
'Azerbaijan'=>'Azerbaijan',
'Bahamas'=>'Bahamas',
'Bahrain'=>'Bahrain',
'Bangladesh'=>'Bangladesh',
'Barbados'=>'Barbados',
'Belarus'=>'Belarus',
'Belgium'=>'Belgium',
'Belize'=>'Belize',
'Benin'=>'Benin',
'Bermuda'=>'Bermuda',
'Bhutan'=>'Bhutan',
'Bolivia'=>'Bolivia',
'Bosnia and Herzegovina'=>'Bosnia and Herzegovina',
'Botswana'=>'Botswana',
'Bouvet Island'=>'Bouvet Island',
'Brazil'=>'Brazil',
'British Indian Ocean Territory'=>'British Indian Ocean Territory',
'Brunei Darussalam'=>'Brunei Darussalam',
'Bulgaria'=>'Bulgaria',
'Burkina Faso'=>'Burkina Faso',
'Burundi'=>'Burundi',
'Cambodia'=>'Cambodia',
'Cameroon'=>'Cameroon',
'Canada'=>'Canada',
'Cape Verde'=>'Cape Verde',
'Cayman Islands'=>'Cayman Islands',
'Central African Republic'=>'Central African Republic',
'Chad'=>'Chad',
'Chile'=>'Chile',
'China'=>'China',
'Christmas Island'=>'Christmas Island',
'Cocos Keeling Islands'=>'Cocos Keeling Islands',
'Colombia'=>'Colombia',
'Comoros'=>'Comoros',
'Congo'=>'Congo',
'Congo The Democratic Republic of The'=>'Congo The Democratic Republic of The',
'Cook Islands'=>'Cook Islands',
'Costa Rica'=>'Costa Rica',
'Croatia'=>'Croatia',
'Cuba'=>'Cuba',
'Cyprus'=>'Cyprus',
'Czech Republic'=>'Czech Republic',
'Denmark'=>'Denmark',
'Djibouti'=>'Djibouti',
'Dominica'=>'Dominica',
'Dominican Republic'=>'Dominican Republic',
'Ecuador'=>'Ecuador',
'Egypt'=>'Egypt',
'El Salvador'=>'El Salvador',
'Equatorial Guinea'=>'Equatorial Guinea',
'Eritrea'=>'Eritrea',
'Estonia'=>'Estonia',
'Ethiopia'=>'Ethiopia',
'Falkland Islands Malvinas'=>'Falkland Islands Malvinas',
'Faroe Islands'=>'Faroe Islands',
'Fiji'=>'Fiji',
'Finland'=>'Finland',
'France'=>'France',
'French Guiana'=>'French Guiana',
'French Polynesia'=>'French Polynesia',
'French Southern Territories'=>'French Southern Territories',
'Gabon'=>'Gabon',
'Gambia'=>'Gambia',
'Georgia'=>'Georgia',
'Germany'=>'Germany',
'Ghana'=>'Ghana',
'Gibraltar'=>'Gibraltar',
'Greece'=>'Greece',
'Greenland'=>'Greenland',
'Grenada'=>'Grenada',
'Guadeloupe'=>'Guadeloupe',
'Guam'=>'Guam',
'Guatemala'=>'Guatemala',
'Guernsey'=>'Guernsey',
'Guinea'=>'Guinea',
'Guinea-bissau'=>'Guinea-bissau',
'Guyana'=>'Guyana',
'Haiti'=>'Haiti',
'Heard Island and Mcdonald Islands'=>'Heard Island and Mcdonald Islands',
'Honduras'=>'Honduras',
'Hong Kong'=>'Hong Kong',
'Hungary'=>'Hungary',
'Iceland'=>'Iceland',
'India'=>'India',
'Indonesia'=>'Indonesia',
'Iran Islamic Republic of'=>'Iran Islamic Republic of',
'Iraq'=>'Iraq',
'Ireland'=>'Ireland',
'Isle of Man'=>'Isle of Man',
'Israel'=>'Israel',
'Italy'=>'Italy',
'Jamaica'=>'Jamaica',
'Japan'=>'Japan',
'Jersey'=>'Jersey',
'Jordan'=>'Jordan',
'Kazakhstan'=>'Kazakhstan',
'Kenya'=>'Kenya',
'Kiribati'=>'Kiribati',
'Korea Republic of'=>'Korea Republic of',
'Kuwait'=>'Kuwait',
'Kyrgyzstan'=>'Kyrgyzstan',
'Latvia'=>'Latvia',
'Lebanon'=>'Lebanon',
'Lesotho'=>'Lesotho',
'Liberia'=>'Liberia',
'Libyan Arab Jamahiriya'=>'Libyan Arab Jamahiriya',
'Liechtenstein'=>'Liechtenstein',
'Lithuania'=>'Lithuania',
'Luxembourg'=>'Luxembourg',
'Macao'=>'Macao',
'Macedonia The Former Yugoslav Republic of'=>'Macedonia The Former Yugoslav Republic of',
'Madagascar'=>'Madagascar',
'Malawi'=>'Malawi',
'Malaysia'=>'Malaysia',
'Maldives'=>'Maldives',
'Mali'=>'Mali',
'Malta'=>'Malta',
'Marshall Islands'=>'Marshall Islands',
'Martinique'=>'Martinique',
'Mauritania'=>'Mauritania',
'Mauritius'=>'Mauritius',
'Mayotte'=>'Mayotte',
'Mexico'=>'Mexico',
'Micronesi Federated States of'=>'Micronesia Federated States of',
'Moldova Republic of'=>'Moldova Republic of',
'Monaco'=>'Monaco',
'Mongolia'=>'Mongolia',
'Montenegro'=>'Montenegro',
'Montserrat'=>'Montserrat',
'Morocco'=>'Morocco',
'Mozambique'=>'Mozambique',
'Myanmar'=>'Myanmar',
'Namibia'=>'Namibia',
'Nauru'=>'Nauru',
'Nepal'=>'Nepal',
'Netherlands'=>'Netherlands',
'Netherlands Antilles'=>'Netherlands Antilles',
'New Caledonia'=>'New Caledonia',
'New Zealand'=>'New Zealand',
'Nicaragua'=>'Nicaragua',
'Niger'=>'Niger',
'Nigeria'=>'Nigeria',
'Niue'=>'Niue',
'Norfolk Island'=>'Norfolk Island',
'Northern Mariana Islands'=>'Northern Mariana Islands',
'Norway'=>'Norway',
'Oman'=>'Oman',
'Pakistan'=>'Pakistan',
'Palau'=>'Palau',
'Palestinian Territory Occupied'=>'Palestinian Territory Occupied',
'Panama'=>'Panama',
'Papua New Guinea'=>'Papua New Guinea',
'Paraguay'=>'Paraguay',
'Peru'=>'Peru',
'Philippines'=>'Philippines',
'Pitcairn'=>'Pitcairn',
'Poland'=>'Poland',
'Portugal'=>'Portugal',
'Puerto Rico'=>'Puerto Rico',
'Qatar'=>'Qatar',
'Reunion'=>'Reunion',
'Romania'=>'Romania',
'Russian Federation'=>'Russian Federation',
'Rwanda'=>'Rwanda',
'Saint Helena'=>'Saint Helena',
'Saint Kitts and Nevis'=>'Saint Kitts and Nevis',
'Saint Lucia'=>'Saint Lucia',
'Saint Pierre and Miquelon'=>'Saint Pierre and Miquelon',
'Saint Vincent and The Grenadines'=>'Saint Vincent and The Grenadines',
'Samoa'=>'Samoa',
'San Marino'=>'San Marino',
'Sao Tome and Principe'=>'Sao Tome and Principe',
'Saudi Arabia'=>'Saudi Arabia',
'Senegal'=>'Senegal',
'Serbia'=>'Serbia',
'Seychelles'=>'Seychelles',
'Sierra Leone'=>'Sierra Leone',
'Singapore'=>'Singapore',
'Slovakia'=>'Slovakia',
'Slovenia'=>'Slovenia',
'Solomon Islands'=>'Solomon Islands',
'Somalia'=>'Somalia',
'South Africa'=>'South Africa',
'South Georgia and The South Sandwich Islands'=>'South Georgia and The South Sandwich Islands',
'Spain'=>'Spain',
'Sri Lanka'=>'Sri Lanka',
'Sudan'=>'Sudan',
'Suriname'=>'Suriname',
'Svalbard and Jan Mayen'=>'Svalbard and Jan Mayen',
'Swaziland'=>'Swaziland',
'Sweden'=>'Sweden',
'Switzerland'=>'Switzerland',
'Syrian Arab Republic'=>'Syrian Arab Republic',
'Taiwan, Province of China'=>'Taiwan, Province of China',
'Tajikistan'=>'Tajikistan',
'Tanzania, United Republic of'=>'Tanzania, United Republic of',
'Thailand'=>'Thailand',
'Timor-leste'=>'Timor-leste',
'Togo'=>'Togo',
'Tokelau'=>'Tokelau',
'Tonga'=>'Tonga',
'Trinidad and Tobago'=>'Trinidad and Tobago',
'Tunisia'=>'Tunisia',
'Turkey'=>'Turkey',
'Turkmenistan'=>'Turkmenistan',
'Turks and Caicos Islands'=>'Turks and Caicos Islands',
'Tuvalu'=>'Tuvalu',
'Uganda'=>'Uganda',
'Ukraine'=>'Ukraine',
'United Arab Emirates'=>'United Arab Emirates',
'United Kingdom'=>'United Kingdom',
'United States'=>'United States',
'United States Minor Outlying Islands'=>'United States Minor Outlying Islands',
'Uruguay'=>'Uruguay',
'Uzbekistan'=>'Uzbekistan',
'Vanuatu'=>'Vanuatu',
'Venezuela'=>'Venezuela',
'Viet Nam'=>'Viet Nam',
'Virgin Islands - British'=>'Virgin Islands - British',
'Virgin Islands - U.S.'=>'Virgin Islands - U.S.',
'Wallis and Futuna'=>'Wallis and Futuna',
'Western Sahara'=>'Western Sahara',
'Yemen'=>'Yemen',
'Zambia'=>'Zambia',
'Zimbabwe'=>'Zimbabwe');
	
		for($i=1;$i<=50;$i++){
			$number_limit[$i] = $i;
		}
		echo $this->Form->input('id');
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('username');
		echo $this->Form->input('email');
		echo $this->Form->input('phone',array('label' => 'Personal Phone Number'));
		echo $this->Form->input('company_name');
		echo $this->Form->input('passwrd',array('div'=>false,'label'=>'<font style="color: #555;"><b>Password</b></font>', 'class' => 'inputtext', 'type' => 'password'));
		echo $this->Form->input('confirm_password',array('type'=>'password'));
		echo $this->Form->input('timezone',array('type' =>'select', 'div'=>false, 'options'=>$Option3, 'style'=>'margin-bottom:20px;width:350px;'));
        echo $this->Form->input('user_country',array('label'=>'Country','type' =>'select', 'div'=>false, 'options'=>$Option4, 'style'=>'margin-bottom:20px;width:350px;'));
		echo $this->Form->input('active', array('label'=>'Status', 'type' =>'select' ,'options' => array('0' => 'Inactive', '1' => 'Active'),'style'=>'width:350px;'));
		
	    $Option5=array(''=>'Select SMS Gateway','0'=>'Twilio','3'=>'Plivo','1'=>'Nexmo','2'=>'Slooce');
        echo $this->Form->input('api_type', array(
        'id'=>'api_type',
        'style'=>'width:350px;',
        'label'=>'SMS Gateway',
        'type'=>'select',
        'options' => $Option5
	    ));
	    echo '<div id=shownumberlimit>';
	    echo $this->Form->input('number_limit', array( 'id'=>'numberlimit','type' =>'select' ,'options' => $number_limit));
	    echo '</div>';
	    
	    echo '<div id=showslooce style=display:none>';
	    echo $this->Form->input('sms',array('type'=>'hidden','value'=>1));
		echo $this->Form->input('api_url', array('label' => 'Slooce API URL'));
		echo $this->Form->input('keyword', array('label' => 'Slooce Keyword'));
		echo $this->Form->input('partnerid', array('label' => 'Slooce Partner ID'));
		echo $this->Form->input('partnerpassword', array('label' => 'Slooce Partner Password'));
		echo $this->Form->input('assigned_number', array('label' => 'Short Code'));
		echo '</div>';

        /*
		if(API_TYPE==2){
			echo $this->Form->input('number_limit', array( 'type' =>'select' ,'options' => $number_limit,'disabled'=>true));
		}else{
			echo $this->Form->input('number_limit', array( 'type' =>'select' ,'options' => $number_limit));
		}
		if(API_TYPE==2){
			echo $this->Form->input('sms',array('type'=>'hidden','value'=>1));
			echo $this->Form->input('api_url', array('label' => 'Slooce API URL'));
			echo $this->Form->input('keyword', array('label' => 'Slooce Keyword'));
			echo $this->Form->input('partnerid', array('label' => 'Slooce Partner ID'));
			echo $this->Form->input('partnerpassword', array('label' => 'Slooce Partner Password'));
			echo $this->Form->input('assigned_number', array('label' => 'Short Code'));
		}*/
		
		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>