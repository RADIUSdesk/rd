#
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA
#
#  Copyright 2002  The FreeRADIUS server project
#  Copyright 2002  Boian Jordanov <bjordanov@orbitel.bg>
#

#
# Example code for use with rlm_perl
#
# You can use every module that comes with your perl distribution!
#
# If you are using DBI and do some queries to DB, please be sure to
# use the CLONE function to initialize the DBI connection to DB.
#

use strict;
# use ...
# This is very important ! Without this script will not get the filled hashesh from main.
use vars qw(%RAD_REQUEST %RAD_REPLY %RAD_CHECK);
use Data::Dumper;

# This is hash wich hold original request from radius
#my %RAD_REQUEST;
# In this hash you add values that will be returned to NAS.
#my %RAD_REPLY;
#This is for check items
#my %RAD_CHECK;


#
# This the remapping of return values
#
	use constant    RLM_MODULE_REJECT=>    0;#  /* immediately reject the request */
	use constant	RLM_MODULE_FAIL=>      1;#  /* module failed, don't reply */
	use constant	RLM_MODULE_OK=>        2;#  /* the module is OK, continue */
	use constant	RLM_MODULE_HANDLED=>   3;#  /* the module handled the request, so stop. */
	use constant	RLM_MODULE_INVALID=>   4;#  /* the module considers the request invalid. */
	use constant	RLM_MODULE_USERLOCK=>  5;#  /* reject the request (user is locked out) */
	use constant	RLM_MODULE_NOTFOUND=>  6;#  /* user not found */
	use constant	RLM_MODULE_NOOP=>      7;#  /* module succeeded without doing anything */
	use constant	RLM_MODULE_UPDATED=>   8;#  /* OK (pairs modified) */
	use constant	RLM_MODULE_NUMCODES=>  9;#  /* How many return codes there are */

# Same as src/include/radiusd.h
use constant	L_DBG=>   1;
use constant	L_AUTH=>  2;
use constant	L_INFO=>  3;
use constant	L_ERR=>   4;
use constant	L_PROXY=> 5;
use constant	L_ACCT=>  6;

#___ RADIUSdesk _______
use lib "/usr/local/etc/raddb/rlm_perl_modules";
use SQLConnector;
use RdMain;
our $sql_connector;

sub CLONE {
    &radiusd::radlog(L_AUTH,"Forking a SQL connection");
    $sql_connector  = SQLConnector->new();
    $sql_connector->prepare_statements(%RAD_REQUEST);
}
#___ End RADIUSdesk ___




#  Global variables can persist across different calls to the module.
#
#
#	{
#	 my %static_global_hash = ();
#
#		sub post_auth {
#		...
#		}
#		...
#	}


# Function to handle authorize
sub authorize {

    &log_request_attributes;

    #Temp hack
    if(exists($RAD_CHECK{'Rd-Mac-Tmp-Username'})){
        return RLM_MODULE_UPDATED;  
    }


    if (authorize_worker()){
        return RLM_MODULE_UPDATED;
    }else{
        return RLM_MODULE_REJECT;
    }

	# For debugging purposes only
#	&log_request_attributes;

	# Here's where your authorization code comes
	# You can call another function from here:
	&test_call;

	return RLM_MODULE_OK;
}

# Function to handle authenticate
sub authenticate {
	# For debugging purposes only
#	&log_request_attributes;

	if ($RAD_REQUEST{'User-Name'} =~ /^baduser/i) {
		# Reject user and tell him why
		$RAD_REPLY{'Reply-Message'} = "Denied access by rlm_perl function";
		return RLM_MODULE_REJECT;
	} else {
		# Accept user and set some attribute
		$RAD_REPLY{'h323-credit-amount'} = "100";
		return RLM_MODULE_OK;
	}
}

# Function to handle preacct
sub preacct {
	# For debugging purposes only
#	&log_request_attributes;

	return RLM_MODULE_OK;
}

# Function to handle accounting
sub accounting {
	# For debugging purposes only
#	&log_request_attributes;

	# You can call another subroutine from here
	&test_call;

	return RLM_MODULE_OK;
}

# Function to handle checksimul
sub checksimul {
	# For debugging purposes only
#	&log_request_attributes;

	return RLM_MODULE_OK;
}

# Function to handle pre_proxy
sub pre_proxy {
	# For debugging purposes only
#	&log_request_attributes;

	return RLM_MODULE_OK;
}

# Function to handle post_proxy
sub post_proxy {
	# For debugging purposes only
#	&log_request_attributes;

	return RLM_MODULE_OK;
}

# Function to handle post_auth
sub post_auth {
	# For debugging purposes only
	&log_request_attributes;

	return RLM_MODULE_OK;
}

# Function to handle xlat
sub xlat {
	# For debugging purposes only
#	&log_request_attributes;

	# Loads some external perl and evaluate it
	my ($filename,$a,$b,$c,$d) = @_;
	&radiusd::radlog(L_DBG, "From xlat $filename ");
	&radiusd::radlog(L_DBG,"From xlat $a $b $c $d ");
	local *FH;
	open FH, $filename or die "open '$filename' $!";
	local($/) = undef;
	my $sub = <FH>;
	close FH;
	my $eval = qq{ sub handler{ $sub;} };
	eval $eval;
	eval {main->handler;};
}

# Function to handle detach
sub detach {
	# For debugging purposes only
#	&log_request_attributes;

	# Do some logging.
	&radiusd::radlog(L_DBG,"rlm_perl::Detaching. Reloading. Done.");
}

#
# Some functions that can be called from other functions
#

sub test_call {
	# Some code goes here
}

sub log_request_attributes {
	# This shouldn't be done in production environments!
	# This is only meant for debugging!
	for (keys %RAD_REQUEST) {
		&radiusd::radlog(L_DBG, "RAD_REQUEST: $_ = $RAD_REQUEST{$_}");
	}

    for (keys %RAD_CHECK) {
		&radiusd::radlog(L_DBG, "RAD_CHECK: $_ = $RAD_CHECK{$_}");
	}

     for (keys %RAD_REPLY) {
		&radiusd::radlog(L_DBG, "RAD_REPLY: $_ = $RAD_REPLY{$_}");
	}

}

#___ RADIUSdesk specific authorize implementation ____

sub authorize_worker {

    #TODO => Ignore if User-Name and User-Password not present.....
    #!! We can do a prelimnanary sql xlat on the radcheck table to do this step; if it passes; we can do this part

    my $user            = $RAD_REQUEST{'User-Name'};
    my $pw              = $RAD_REQUEST{'User-Password'};

    my $rd_main         = RdMain->new($sql_connector);
    #print(Dumper($rd_main->test_for_active_and_known($user)));

    my $pass_flag       = 0; #Fail by default

    #____ Test for voucher ____________


    #____ Test for User-Name that is a MAC _____


    #___ Test for permanent user ____
    my $ret_val         = $rd_main->test_for_known_user($user);
    if($ret_val == undef){
        $RAD_REPLY{'Reply-Message'} = "Unknown user"; #Do not even waste any more time....
        return $pass_flag;
    }else{
        if($ret_val->{'active'} == 1){
            $pass_flag = 1;
            $RAD_CHECK{'Realm'} = $ret_val->{'realm_name'}; #Set the realm's value
            #The account is active so now we can do some extra checks

            #___ NAS Specifics _________
            # Check if the realm that the user belongs to is allowed on the NAS
            $pass_flag = &check_realms();
            # Check if we need to set some tags based on existing tags on the NAS
            if($pass_flag != 0){
                &check_tags();
            }else{
                $RAD_REPLY{'Reply-Message'} = "Users from realm ".$RAD_CHECK{'Realm'}." not allowed to connect to ".$RAD_REQUEST{'NAS-IP-Address'};
                $pass_flag = 0; #Fail it
                return $pass_flag;
            }
            #___ END NAS Specifics ____

        }else{
            $RAD_REPLY{'Reply-Message'} = "Account disabled"; #Do not even waste any more time....
            return $pass_flag;    
        }
    }
    return $pass_flag;
}

#___ END RADIUSdesk specific authorize implementation ____

sub check_realms {
    &radiusd::radlog(L_DBG, "Test to see if user can connect from NAS");

    my $nasname = $RAD_REQUEST{'NAS-IP-Address'};
    my $realms  = $sql_connector->one_statement_value_return_all('realms_for_nas',$nasname);

    #If there was no realms assigned to this NAS; we allow anyone to connect
    my $count = scalar(@{$realms});
    if($count == 0){
        &radiusd::radlog(L_DBG, "No realms assigned to NAS; allow connection");
        return 1 #No realms assigned, allow connection for anyone
    }

    #Loop through the assigned list of realms; looking fo a match
    foreach my $item (@{$realms}){
        if($RAD_CHECK{'Realm'} eq @{$item}[0]){
            &radiusd::radlog(L_DBG, $RAD_CHECK{'Realm'}.' is one of the realms assigned to '.$nasname);
            return 1 #Found a match; go back
        }
    }
    &radiusd::radlog(L_DBG, 'Realm '.$RAD_CHECK{'Realm'}.' not in list of allowed realms');
    return 0;
}

sub check_tags {
    &radiusd::radlog(L_DBG, "See if we need to set up some tags");

    #Start by determining if the NAS devicehas some tags asigned to it
    my $nasname = $RAD_REQUEST{'NAS-IP-Address'};
    my $tags    = $sql_connector->one_statement_value_return_all('tags_for_nas',$nasname);

    my $count = scalar(@{$tags});
    if($count == 0){
        &radiusd::radlog(L_DBG, "No tags assigned to $nasname; return");
        return 1 #No tags assigned
    }


    print(Dumper($tags));

    #Get a list of Rd-Tag-* Check attributes assigned to user; starting with groups and moving over to personal.
    my $user_tags  = $sql_connector->one_statement_value_value_return_all('rd_tags_for_user',$RAD_REQUEST{'User-Name'},$RAD_REQUEST{'User-Name'});
    my $user_tags_hash  ={};

    foreach my $item(@{$user_tags}){
        print(Dumper($item));
        if(@{$item}[0] eq 'User-Profile'){ #Group Check attribute
            my $value = @{$item}[3];
            $user_tags_hash->{"$value"} = @{$item}[2];
        }else{  #Assume this is a Private check attribute
            my $value = @{$item}[1];
            $user_tags_hash->{"$value"} = @{$item}[0];
        }
    }
    print(Dumper($user_tags_hash));

    #Loop through the list of tags assigned to the NAS and see if one is equal to one of the $user_tags_hash keys
    foreach my $tag(@{$tags}){
        my $name = @{$tag}[0];
        if(exists($user_tags_hash->{"$name"})){ #Update the $RAD_REQUEST
            my $avp = $user_tags_hash->{"$name"};
            $RAD_REQUEST{$avp} = $name;
        }
    }
}

