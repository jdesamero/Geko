<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	
	<title><?php echo bloginfo( 'name' ); ?></title>
	
	<style type="text/css">
		
		body {
			font-family: Helvetica, Arial, sans-serif;
		}
		
		#login th {
			text-align: right;
		}
		
		#login td.last {
			text-align: center;
		}
		
		.error {
			color: red;
			font-weight: bold;
		}
		
		.blurb {
			padding: 12px 0 24px 0;
		}
		
	</style>
	
</head>

<body>

<h1><?php echo bloginfo( 'name' ); ?></h1>

<hr />

<?php if ( $sBlurb = self::getOption( 'blurb' ) ): ?>
	<div class="blurb"><?php echo nl2br( $sBlurb ); ?></div>
<?php endif; ?>

<form id="login" method="post">
	
	<?php if ( $bLoginFailed ): ?>
		<p class="error">Incorrect login!</p>
	<?php endif; ?>
	
	<table>
		<tr>
			<th>Login</th>
			<td><input type="text" id="user" name="user" /></td>
		</tr>
		<tr>
			<th>Password</th>
			<td><input type="password" id="pass" name="pass" /></td>
		</tr>
		<tr>
			<td colspan="2" class="last"><input type="submit" value="Login" /></td>
		</tr>
	</table>
	
</form>

</body>

</html>