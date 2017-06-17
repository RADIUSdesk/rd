<?php
	$redirect_to = $url.$x_trans_id;
?>

<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="1;url=<?php echo($redirect_to); ?>">
        <script type="text/javascript">
            window.location.href = "<?php echo($redirect_to); ?>"
        </script>
        <title>Page Redirection</title>
    </head>

    <body>
        <!-- Note: don't tell people to `click` the link, just tell them that it is a link. -->
        If you are not redirected automatically, follow the <a href='<?php echo($redirect_to); ?>'><?php echo($redirect_to); ?></a>
    </body>
</html>

