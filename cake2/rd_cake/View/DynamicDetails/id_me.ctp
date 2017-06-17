<h3>The User Agent of Your Browser is:</h3>
<div style="padding:10px; background-color:yellow; border-style:solid; border-width:1px;">
<?php
print($u_a);
?>
</div>
<div>
<br>If you want to serve the <b>jQuery Mobile login pages</b> to devices with these browsers, you need to specify this value (or a certain part of it) inside:<br><br>
<b>Apache:</b> /var/www/cake2/rd_cake/Config/RadiusDesk.php<br>
<b>Nginx:</b> /usr/share/nginx/html/cake2/rd_cake/Config/RadiusDesk.php<br>
</div>
