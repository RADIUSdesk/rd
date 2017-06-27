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
    #If FRBG-Total-Bytes is larger than the 32-bit limit we have to set a Gigaword attribute


    if(exists($RAD_CHECK{'Rd-Total-Bytes'}) && exists($RAD_CHECK{'Rd-Used-Bytes'})){
        $RAD_CHECK{'Rd-Avail-Bytes'} = $RAD_CHECK{'Rd-Total-Bytes'} - $RAD_CHECK{'Rd-Used-Bytes'};
    }else{
        return RLM_MODULE_NOOP;
    }
    if($RAD_CHECK{'Rd-Avail-Bytes'} <= 0){
        if($RAD_CHECK{'Rd-Reset-Type'} ne 'never'){
            $RAD_REPLY{'Reply-Message'} = "Maximum $RAD_CHECK{'Rd-Reset-Type'} usage exceeded";
        }else{
            $RAD_REPLY{'Reply-Message'} = "Maximum usage exceeded";
        }
        return RLM_MODULE_REJECT;
    }

    #Set the Rd-Tmp-Avail-Bytes if it is not already set
    #This will be used to indicate whether the user already went through or not 
    if(!exists($RAD_CHECK{'Rd-Tmp-Avail-Bytes'})){
        &radiusd::radlog(L_DBG, "Rd-TmpAvail-Bytes does not exist. Set it equal to ".$RAD_CHECK{'Rd-Avail-Bytes'});
        $RAD_CHECK{'Rd-Tmp-Avail-Bytes'}                = $RAD_CHECK{'Rd-Avail-Bytes'};
    }else{
        if($RAD_CHECK{'Rd-Tmp-Avail-Bytes'} < $RAD_CHECK{'Rd-Avail-Bytes'}){    #Make it the smallest
            &radiusd::radlog(L_DBG, "Using the smaller Available data of user ".$RAD_CHECK{'Rd-Tmp-Avail-Bytes'}." instead of ".$RAD_CHECK{'Rd-Avail-Bytes'});
            $RAD_CHECK{'Rd-Avail-Bytes'} = $RAD_CHECK{'Rd-Tmp-Avail-Bytes'};
        }
    }

    #My test with the broadcom based OpenWRT/Linksys did not like these reply attributes 
    if(!exists($RAD_REQUEST{'EAP-Message'})){
        if($RAD_CHECK{'Rd-Avail-Bytes'} >= $int_max){
            #Mikrotik's reply attributes
            $RAD_REPLY{'Mikrotik-Total-Limit'}              = $RAD_CHECK{'Rd-Avail-Bytes'} % $int_max;
            $RAD_REPLY{'Mikrotik-Total-Limit-Gigawords'}    = int($RAD_CHECK{'Rd-Avail-Bytes'} / $int_max );

            #Coova Chilli's reply attributes
            $RAD_REPLY{'ChilliSpot-Max-Total-Octets'}       = $RAD_CHECK{'Rd-Avail-Bytes'} % $int_max;
            $RAD_REPLY{'ChilliSpot-Max-Total-Gigawords'}    = int($RAD_CHECK{'Rd-Avail-Bytes'} / $int_max );

        }else{
            $RAD_REPLY{'Mikrotik-Total-Limit'}              = $RAD_CHECK{'Rd-Avail-Bytes'};
            $RAD_REPLY{'ChilliSpot-Max-Total-Octets'}       = $RAD_CHECK{'Rd-Avail-Bytes'};
        }
    }

    return RLM_MODULE_UPDATED;
}
