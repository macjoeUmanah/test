<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * This is email configuration file.
 *
 * Use it to configure SMTP email transports.
 *
*/
 

class EmailConfig {

    //>>>>> SENDGRID SMTP CONFIG SAMPLE <<<<<<
    //>>>>> https://app.sendgrid.com/guide/integrate/langs/smtp <<<<<<
    public $smtp = array(
	    'transport' => 'Smtp',
        'host' => 'smtp.sendgrid.net',
        'port' => 25, 
        'timeout' => 30,
        'username' => 'apikey',
        'password' => 'XXXXXXXXXXXXXXX',
        'tls' => false,
    );
}

