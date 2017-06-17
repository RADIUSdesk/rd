<?php
/*

This file is used to match the authorize.net Simple Payment button id's with a product.
Here we will use it to pick which voucher it will generate
The silent POST function of A.net will contain this:

x_catalog_link_id=1abacb70-d45d-4d12-ba51-98f01523720d

We will then use this in the controller to determine what the voucher should contain by means of this lookup

Since we have no means to know what the description of the catalog is, we have to add our own which will be inserted 
when a transaction is recorded.

*/

$config['authorize_dot_net']['receipt_url'] = 'http://127.0.0.1/simple_login/intro.html?uamip=10.1.0.1&uamport=3990&x_trans_id=';

$config['authorize_dot_net']['1abacb70-d45d-4d12-ba51-98f01523720d'] = array(
									'description'		=> 'Basic Internet - 1Day',
                                    'precede'           => '',
                                    'profile_id'        => 9,
									'profile'			=> 'Data-Standard-1G',
                                    'realm_id'          => 34,
									'realm'				=> 'Residence Inn',
                                    'sel_language'      => '4_4',
                                    'user_id'           => '44'
                                );

$config['authorize_dot_net']['d93db923-9904-4e04-bb8b-f5cb5d88f4a2'] = array(
									'description'		=> 'Basic Internet - 1Week',
                                    'precede'           => '',
                                    'profile_id'        => 9,
									'profile'			=> 'Data-Standard-1G',
                                    'realm_id'          => 34,
									'realm'				=> 'Residence Inn',
                                    'sel_language'      => '4_4',
                                    'user_id'           => '44'
                                );

?>
