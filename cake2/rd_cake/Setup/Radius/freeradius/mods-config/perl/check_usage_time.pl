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

sub authorize {

    #We will reply, depending on the usage
    if(exists($RAD_CHECK{'Rd-Total-Time'}) && exists($RAD_CHECK{'Rd-Used-Time'})){
        $RAD_CHECK{'Rd-Avail-Time'} = $RAD_CHECK{'Rd-Total-Time'} - $RAD_CHECK{'Rd-Used-Time'};
    }else{
        return RLM_MODULE_NOOP;
    }
    if($RAD_CHECK{'Rd-Avail-Time'} <= 0){
        if($RAD_CHECK{'Rd-Reset-Type-Time'} ne 'never'){
            $RAD_REPLY{'Reply-Message'} = "Maximum $RAD_CHECK{'Rd-Reset-Type-Time'} usage exceeded";
        }else{
            $RAD_REPLY{'Reply-Message'} = "Maximum usage exceeded";
        }
        return RLM_MODULE_REJECT;
    }

    #Set the Rd-Tmp-Avail-Time if it is not already set
    #This will be used to indicate whether the user already went through or not 
    if(!exists($RAD_CHECK{'Rd-Tmp-Avail-Time'})){
        &radiusd::radlog(L_DBG, "Rd-Tmp-Avail-Time does not exist. Set it equal to ".$RAD_CHECK{'Rd-Avail-Time'});
        $RAD_CHECK{'Rd-Tmp-Avail-Time'}                = $RAD_CHECK{'Rd-Avail-Time'};
    }else{
        if($RAD_CHECK{'Rd-Tmp-Avail-Time'} < $RAD_CHECK{'Rd-Avail-Time'}){    #Make it the smallest
            &radiusd::radlog(L_DBG, "Using the smaller Available data of user ".$RAD_CHECK{'Rd-Tmp-Avail-Time'}." instead of ".$RAD_CHECK{'Rd-Avail-Time'});
            $RAD_CHECK{'Rd-Avail-Time'} = $RAD_CHECK{'Rd-Tmp-Avail-Time'};
        }
    }

    #We do not set any reply attributes here since it will later be compared against other s to determine the smallest
    #value of "Session-Timeout"

    return RLM_MODULE_UPDATED;
}
