<?php
	header("Content-type:text/html;charset=utf-8");
$to = "574078142@qq.com";
$subject = "HTML email";

$message = "
<html>
<head>
<title>HTML email</title>
</head>
<body>
<p>This email contains HTML Tags!</p>
<table>
<tr>
<th>Firstname</th>
<th>Lastname</th>
</tr>
<tr>
<td>John</td>
<td>Doe</td>
</tr>
</table>
</body>
</html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";

// 更多报头
$headers .= 'From: eazymov' . "\r\n";


 
var_dump(mail($to,$subject,$message,$headers));
?>