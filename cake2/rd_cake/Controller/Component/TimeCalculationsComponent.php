<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: 
//---- Date: 02-08-15
//------------------------------------------------------------

App::uses('Component', 'Controller');

class TimeCalculationsComponent extends Component {

    public function time_if($timespan='hour'){
        $hour   = (60*60);
        $day    = $hour*24;
        $week   = $day*7;
        $month  = $week*30;
        
        if($timespan == 'hour'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$hour);
        }

        if($timespan == 'day'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$day);
        }

        if($timespan == 'week'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$week);
        }
        
        if($timespan == 'month'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$week);
        }
        
		return $modified;
    }
    
    
    function time_elapsed_string($datetime, $full = false,$no_suffix = false) {
        $now = new DateTime;
        $then = new DateTime( $datetime );
        $diff = (array) $now->diff( $then );

        $diff['w']  = floor( $diff['d'] / 7 );
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach( $string as $k => & $v )
        {
            if ( $diff[$k] )
            {
                $v = $diff[$k] . ' ' . $v .( $diff[$k] > 1 ? 's' : '' );
            }
            else
            {
                unset( $string[$k] );
            }
        }

        if ( ! $full ) $string = array_slice( $string, 0, 1 );
        if($no_suffix){
            return implode( ', ', $string );
        }else{
            return $string ? implode( ', ', $string ) . ' ago' : 'just now';
        }
    }
    
}

