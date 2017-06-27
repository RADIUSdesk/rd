#! /usr/bin/perl -w
use strict;
use POSIX;
# use ...
# This is very important !
use vars qw(%RAD_REQUEST %RAD_REPLY %RAD_CHECK);
use constant RLM_MODULE_REJECT=>    0;#  /* immediately reject the request */
use constant RLM_MODULE_OK=> 2; # /* the module is OK,continue */
use constant RLM_MODULE_NOOP=> 7;
use constant RLM_MODULE_UPDATED=> 8; # /* OK (pairs modified) */

sub authorize {
    #Check if we are afrer the activation time
    #Activation time will be in the same format as Expiration e.g. 6 Mar 2013
    if(exists($RAD_CHECK{'Rd-Account-Activation-Time'})){

        my @abbr        = qw( Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec );
        my @values      = split(' ', $RAD_CHECK{'Rd-Account-Activation-Time'});

        my $d_string    = $values[0];           
        my $m_string    = $values[1];
        my $y_string    = $values[2];
        my $month       = undef;
        my $year        = $y_string - 1900;

        my $counter     = 0;
        foreach my $val(@abbr){
            if($m_string =~ /$val/i){
                $month = $counter;
                last;
            }
            $counter ++;
        }

        my $unixtime = mktime (0, 0, 0, $d_string, $month, $year, 0, 0);

        if($unixtime > time){
            $RAD_REPLY{'Reply-Message'} = "Account activate on ".$RAD_CHECK{'Rd-Account-Activation-Time'};
            return RLM_MODULE_REJECT;
        } 
    }
    return RLM_MODULE_NOOP;
}

