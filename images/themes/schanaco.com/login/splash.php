<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Schana Mail</title>
	<META NAME="robot" CONTENT="index,follow">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta http-equiv="imagetoolbar" content="no">
    <style type="text/css">
      <!--
      td,table
      {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #777777;
      }
      -->
    </style>
</head>
<body bgcolor="#FFFFFF" text="#000000" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="pageback">

<table width="100%" height="80%" border="0" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" valign="middle">

      <?=$iil_error?>

			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td>
						<img src="/images/themes/schanaco.com/colors/default/folder_logo.gif" width="150" height="130">
					</td>
					<td>
					
						<table summary="" width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td valign="top" align="center">
								&nbsp;<br>
								
									<form method="post" action="/index.php">
                    <input name="port" type="hidden" size="25" value="<?php echo $LOGIN_PORT; ?>">
                    <input name="host" type="hidden" size="25" value="<?php echo $LOGIN_HOST; ?>">
								
										<table summary="" border="0" cellspacing="0" cellpadding="0" class="tx">
											<tr> 
												<td colspan="2"><input name="user" type="text" size="25" placeholder="Email" value=""></td>
											</tr>
											<tr> 
												<td height="5" colspan="2"></td>
											</tr>
											<tr> 
												<td colspan="2"><input name="password" type="password" size="25" placeholder="Password" value=""></td>
											</tr>
											<tr> 
												<td height="5" colspan="2"></td>
											</tr>
											<tr> 
												<td align="left">&nbsp;</td>
												<td align="right"><input name="submit" type="submit" class="button" value="Log In"></td>
											</tr>
										</table>
						
									</form>
								
								</td>
							</tr>
						</table>		
					
					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>

</body>
</html>