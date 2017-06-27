#! usr/bin/perl -w
use strict;

# use ...
# This is very important!
use vars qw(%RAD_REQUEST %RAD_CHECK %RAD_REPLY);
use constant RLM_MODULE_OK=> 2;# /* the module is OK,continue */
use constant RLM_MODULE_UPDATED=> 8;# /* OK (pairs modified) */
use constant RLM_MODULE_REJECT=> 0;# /* immediately reject therequest */
use constant RLM_MODULE_NOOP=> 7;

# Same as src/include/radiusd.h
use constant	L_DBG=>   1;
use constant	L_AUTH=>  2;
use constant	L_INFO=>  3;
use constant	L_ERR=>   4;
use constant	L_PROXY=> 5;
use constant	L_ACCT=>  6;

my $int_max = 4294967296;

sub authorize {

    #We will reply, depending on the usage
    #If FRBG-Total-Data is larger than the 32-bit limit we have to set a Gigaword attribute


    if(exists($RAD_CHECK{'Rd-Total-Data'}) && exists($RAD_CHECK{'Rd-Used-Data'})){
        $RAD_CHECK{'Rd-Avail-Data'} = $RAD_CHECK{'Rd-Total-Data'} - $RAD_CHECK{'Rd-Used-Data'};
    }else{
        return RLM_MODULE_NOOP;
    }
    if($RAD_CHECK{'Rd-Avail-Data'} <= 0){
        if($RAD_CHECK{'Rd-Reset-Type-Data'} ne 'never'){
            $RAD_REPLY{'Reply-Message'} = "Maximum $RAD_CHECK{'Rd-Reset-Type-Data'} usage exceeded";
        }else{
            $RAD_REPLY{'Reply-Message'} = "Maximum usage exceeded";
        }
        return RLM_MODULE_REJECT;
    }

    #Set the Rd-Tmp-Avail-Data if it is not already set
    #This will be used to indicate whether the user already went through or not 
    if(!exists($RAD_CHECK{'Rd-Tmp-Avail-Data'})){
        &radiusd::radlog(L_DBG, "Rd-Tmp-Avail-Data does not exist. Set it equal to ".$RAD_CHECK{'Rd-Avail-Data'});
        $RAD_CHECK{'Rd-Tmp-Avail-Data'}                = $RAD_CHECK{'Rd-Avail-Data'};
    }else{
        if($RAD_CHECK{'Rd-Tmp-Avail-Data'} < $RAD_CHECK{'Rd-Avail-Data'}){    #Make it the smallest
            &radiusd::radlog(L_DBG, "Using the smaller Available data of user ".$RAD_CHECK{'Rd-Tmp-Avail-Data'}." instead of ".$RAD_CHECK{'Rd-Avail-Data'});
            $RAD_CHECK{'Rd-Avail-Data'} = $RAD_CHECK{'Rd-Tmp-Avail-Data'};
        }
    }

    #My test with the broadcom based OpenWRT/Linksys did not like these reply attributes 
    if(!exists($RAD_REQUEST{'EAP-Message'})){
        if($RAD_CHECK{'Rd-Avail-Data'} >= $int_max){
            #Mikrotik's reply attributes
            $RAD_REPLY{'Mikrotik-Total-Limit'}              = $RAD_CHECK{'Rd-Avail-Data'} % $int_max;
            $RAD_REPLY{'Mikrotik-Total-Limit-Gigawords'}    = int($RAD_CHECK{'Rd-Avail-Data'} / $int_max );

            #Coova Chilli's reply attributes
            $RAD_REPLY{'ChilliSpot-Max-Total-Octets'}       = $RAD_CHECK{'Rd-Avail-Data'} % $int_max;
            $RAD_REPLY{'ChilliSpot-Max-Total-Gigawords'}    = int($RAD_CHECK{'Rd-Avail-Data'} / $int_max );

        }else{
            $RAD_REPLY{'Mikrotik-Total-Limit'}              = $RAD_CHECK{'Rd-Avail-Data'};
            $RAD_REPLY{'ChilliSpot-Max-Total-Octets'}       = $RAD_CHECK{'Rd-Avail-Data'};
        }
    }

    return RLM_MODULE_UPDATED;
}
