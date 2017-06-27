#! /usr/bin/perl -w
use strict;
use POSIX;
# use ...
# This is very important !
use vars qw(%RAD_CHECK);
use constant RLM_MODULE_OK=> 2; # /* the module is OK,continue */
use constant RLM_MODULE_NOOP=> 7;
use constant RLM_MODULE_UPDATED=> 8; # /* OK (pairs modified) */

sub authorize {
    #Find out when the reset time should be 
    if($RAD_CHECK{'Rd-Reset-Type-Data'} =~ /never/i){ #Return immediately 
        return RLM_MODULE_NOOP;
    }

    if($RAD_CHECK{'Rd-Reset-Type-Data'} =~ /monthly/i){
        $RAD_CHECK{'Rd-Start-Time'} = start_of_month()
    }
    if($RAD_CHECK{'Rd-Reset-Type-Data'} =~ /weekly/i){
        $RAD_CHECK{'Rd-Start-Time'} = start_of_week()
    }
    if($RAD_CHECK{'Rd-Reset-Type-Data'} =~ /daily/i){
        $RAD_CHECK{'Rd-Start-Time'} = start_of_day()
    }

    #Add a dynamic reset interval where the start time will be now minus the interval
    if($RAD_CHECK{'Rd-Reset-Type-Data'} =~ /dynamic/i){
        my $dyn_start = dynamic_start();
        if($dyn_start != 0){
            $RAD_CHECK{'Rd-Start-Time'} = $dyn_start;
        }
    }

    if(exists($RAD_CHECK{'Rd-Start-Time'})){
        return RLM_MODULE_UPDATED;    
    }else{
        return RLM_MODULE_NOOP;
    }
}

sub start_of_month {

    my $reset_on = 1; #you decide when the monthly CAP will reset
    
    if(exists($RAD_CHECK{'Rd-Reset-Day'})){
        $reset_on = $RAD_CHECK{'Rd-Reset-Day'};
    }

    #Get the current timestamp; 
    my $unixtime;
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
    if($mday < $reset_on ){
        $unixtime = mktime (0, 0, 0, $reset_on, $mon-1, $year, 0, 0);
        #We use the previous month
    }else{
        $unixtime = mktime (0, 0, 0, $reset_on, $mon, $year, 0, 0);
        #We use this month
    }
    return $unixtime;
}

sub start_of_week {
    #Get the current timestamp;
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
    #create a new timestamp:
    my $unixtime = mktime (0, 0, 0, $mday-$wday, $mon, $year, 0, 0);
    return $unixtime;
}

sub start_of_day {
    #Get the current timestamp;
    my $unixtime;
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);

    if(exists($RAD_CHECK{'Rd-Reset-Hour-Data'})){
        my $reset_hour = $RAD_CHECK{'Rd-Reset-Hour-Data'};
        #If the hour now is now before the reset hour; use the previous day
        if($hour < $reset_hour){
           $mday = $mday-1;
        }
        $unixtime = mktime (0, 0, $reset_hour, $mday, $mon, $year, 0, 0);
    }else{
        #create a new timestamp:
        $unixtime = mktime (0, 0, 0, $mday, $mon, $year, 0, 0);
    }
    return $unixtime;
}

sub dynamic_start{
    my $dyn_start = 0;  #Default
    #With this we can specify that a user can only get so much data every 'Rd-Reset-Interval-Data' seconds
    if(exists($RAD_CHECK{'Rd-Reset-Interval-Data'})){
        $dyn_start = time-$RAD_CHECK{'Rd-Reset-Interval-Data'}; #start in the past
    }
    return $dyn_start;
}
