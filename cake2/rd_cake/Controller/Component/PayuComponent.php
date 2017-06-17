<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: 
//---- Date: 06-05-2014
//------------------------------------------------------------

App::uses('Component', 'Controller');

class PayuComponent extends Component {


    public $settings;

    function setTransaction($data){

        //$data should contain: 'amountInCents'; 'description'; 'returnUrl', 'cancelUrl', 'merchantReference'
        //email; firstName; lastName; mobile

        //---Location of radclient----
        $this->settings = Configure::read('payu');

        // 1. Building the Soap array  of data to send    
        $setTransactionArray            = array();    
        $setTransactionArray['Api']     = $this->settings['apiVersion'];
        $setTransactionArray['Safekey'] = $this->settings['safeKey'];
        $setTransactionArray['TransactionType'] = 'PAYMENT';

        //---Additional information---
        $setTransactionArray['AdditionalInformation']['merchantReference']        = $data['merchantReference'];    
        $setTransactionArray['AdditionalInformation']['cancelUrl']                = $data['cancelUrl'];
        $setTransactionArray['AdditionalInformation']['returnUrl']                = $data['returnUrl'];
	    $setTransactionArray['AdditionalInformation']['supportedPaymentMethods']  = 'CREDITCARD';
        $setTransactionArray['AdditionalInformation']['notificationUrl']          = $this->settings['notificationUrl'];

        //---Basket---  
        $setTransactionArray['Basket']['description']       = $data['description'];
        $setTransactionArray['Basket']['amountInCents']     = $data['amountInCents'];
        $setTransactionArray['Basket']['currencyCode']      = 'ZAR';

        //----Customer---
        $setTransactionArray['Customer']['email']           = $data['email'];
        $setTransactionArray['Customer']['firstName']       = $data['firstName'];
        $setTransactionArray['Customer']['lastName']        = $data['lastName'];
        $setTransactionArray['Customer']['mobile']          = $data['mobile'];


         // 2. Creating a XML header 
        $headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
        $headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
        $headerXml .= '<wsse:Username>'.$this->settings['soapUsername'].'</wsse:Username>';
        $headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->settings['soapPassword'].'</wsse:Password>';
        $headerXml .= '</wsse:UsernameToken>';
        $headerXml .= '</wsse:Security>';
        $headerbody = new SoapVar($headerXml, XSD_ANYXML, null, null, null);

        // 3. Create Soap Header.        
        $ns         = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS. 
        $header     = new SOAPHeader($ns, 'Security', $headerbody, true);        

        // 4. Make new instance of the PHP Soap client
        $soap_client= new SoapClient($this->settings['soapWdslUrl'], array("trace" => 1, "exception" => 0)); 

        // 5. Set the Headers of soap client. 
        $soap_client->__setSoapHeaders($header); 

        // 6. Do the setTransaction soap call to PayU
        $soapCallResult = $soap_client->setTransaction($setTransactionArray); 

        // 7. Decode the Soap Call Result
        $returnData = json_decode(json_encode($soapCallResult),true);


        if( 
            isset($returnData['return']['successful'])&& 
            ($returnData['return']['successful'] === true)&& 
            isset($returnData['return']['payUReference'])
        ){

            return array('success' => true, 'payUReference' =>$returnData['return']['payUReference']);
               
        }else{
            //TODO Here we need to tell them what whent wrong!
            $this->log("PAYU: ".$returnData['return']['displayMessage']);
            $this->log("PAYU: ".$returnData['return']['resultMessage']);
            $this->log("PAYU: ".$returnData['return']['resultCode']);
            return array('success' => false, 'Error' =>$returnData['return']['displayMessage']);
        }
    }


    function getTransaction($payUReference){

        //---Location of radclient----
        $this->settings = Configure::read('payu');

        // 1. Building the Soap array  of data to send
        $soapDataArray              = array();
        $soapDataArray['Api']       = $this->settings['apiVersion'];
        $soapDataArray['Safekey']   = $this->settings['safeKey'];
        $soapDataArray['AdditionalInformation']['payUReference'] = $payUReference;

        // 2. Creating a XML header for sending in the soap heaeder (creating it raw a.k.a xml mode)
        $headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
        $headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
        $headerXml .= '<wsse:Username>'.$this->settings['soapUsername'].'</wsse:Username>';
        $headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->settings['soapPassword'].'</wsse:Password>';
        $headerXml .= '</wsse:UsernameToken>';
        $headerXml .= '</wsse:Security>';
        $headerbody = new SoapVar($headerXml, XSD_ANYXML, null, null, null);

        // 3. Create Soap Header.        
        $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS. 
        $header = new SOAPHeader($ns, 'Security', $headerbody, true);        

        // 4. Make new instance of the PHP Soap client
        $soap_client = new SoapClient($this->settings['soapWdslUrl'], array("trace" => 1, "exception" => 0)); 

        // 5. Set the Headers of soap client. 
        $soap_client->__setSoapHeaders($header); 

        // 6. Do the setTransaction soap call to PayU
        $soapCallResult = $soap_client->getTransaction($soapDataArray); 

        // 7. Decode the Soap Call Result
        $returnData = json_decode(json_encode($soapCallResult),true);

        return $returnData;         

    }

    function ipn_xml_to_data($xml_string =''){

     $dummy_input = "<PaymentNotification>".
    "<MerchantReference>MREF026</MerchantReference>".
    "<TransactionType>PAYMENT</TransactionType>".
    "<TransactionState>SUCCESSFUL</TransactionState>".
    "<ResultCode>00</ResultCode>".
    "<ResultMessage>Successful</ResultMessage>".
    "<PayUReference>80a0c8eb-fa63-40d3-94f0-8bdabc324932</PayUReference>".
    "<Basket>".
        "<Description>ADS026</Description>".
        "<AmountInCents>2100</AmountInCents>".
        "<CurrencyCode>ZAR</CurrencyCode>".
    "</Basket>".
    "<PaymentMethodsUsed>".
        '<CreditCard Information="Visa" NameOnCard="Mr Soap" CardNumber="522112xxxxxx1234" AmountInCents="10000" />'.
    "</PaymentMethodsUsed>".
    "<IpnExtraInfo>".
        "<ResponseHash>7a06fe382948e97ad9207b8528d8c1f6847ac10d6230118ff9b3fb90eeaa4743</ResponseHash>".
    "</IpnExtraInfo>".
"</PaymentNotification>";
        $xml        = simplexml_load_string($xml_string);
        $json       = json_encode($xml);
        $array      = json_decode($json,TRUE);
        $log_string = "PAYU: MerchantReference: ".$array['MerchantReference'].
            " TransactionType: ".$array['TransactionType'].
            " TransactionState: ".$array['TransactionState'].
            " ResultCode: ".$array['ResultCode'].
            " ResultMessage: ".$array['ResultMessage'].
            " PayUReference:  ".$array['PayUReference'];
        $this->log($log_string,'debug');

        $ret_array = array(
            'MerchantReference'     => $array['MerchantReference'],
            'TransactionType'       => $array['TransactionType'],
            'TransactionState'      => $array['TransactionState'],
            'ResultCode'            => $array['ResultCode'],
            'ResultMessage'         => $array['ResultMessage'],
            'PayUReference'         => $array['PayUReference'],
        );
        return $ret_array;
    }

}

?>
