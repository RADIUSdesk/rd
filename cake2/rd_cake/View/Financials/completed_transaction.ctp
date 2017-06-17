<?php

    if(isset($return_data['error'])){
        echo $this->Html->div('error', $return_data['error']);
    }else{
        echo "<h1>Transaction detail</h1>";
        echo "<table>";
        echo $this->Html->tableHeaders(array('Item', 'Value'));
        foreach(array_keys($return_data['record']['FinPayuTransaction']) as $key){
            $value = $return_data['record']['FinPayuTransaction'][$key];
            if($value != ''){
                echo $this->Html->tableCells(array(
                    array($key, $value),
                ));
            }
        }
        echo "</table>";
    }

?>
