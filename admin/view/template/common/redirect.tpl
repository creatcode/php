<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script type="text/javascript">
       var url="<?php echo $url; ?>";
       var state={
           url:url
       };
       history.pushState(state,null,url);
        $.get(url, function(res){
            $('#content-wrapper').html(res);
       });
    </script>
</head>
<body>

</body>
</html>