<?php

return [
	/*
	 * Path of certificate stored in storage
	 */
	'cert_path' => storage_path('cert/tbc.pem'),

	/*
	 * Certificate password
	 */
	'cert_pass' => 'CertificatePassword',

    /*
     * Merchant Handler on Submit
     */
    'merchant_handler' => 'https://ecommerce.ufc.ge:18443/ecomm2/MerchantHandler',

    /*
     * Client Handler
     */
    'client_handler' => 'https://ecommerce.ufc.ge/ecomm2/ClientHandler'
];