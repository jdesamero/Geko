<?php

//
class Gloc_Layout_Main extends Gloc_Layout
{
	
	protected $_mBodyClass = '##body_class##';
	protected $_mStyles = 'gloc';
	
	
	
	//
	public function echoMain() {
		
		$this->doEnqueue();
		$this->doGetHeader();
		
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes() ?>>
		
		<head profile="http://gmpg.org/xfn/11">
			
			<title><?php echo $this->applyTitle( '' ); ?></title>
			
			<?php $this->doMeta(); ?>
			
			<?php $this->doHeadEarly(); ?>
			<?php $this->doWpHead(); ?>
			<?php $this->doHeadLate(); ?>
			
		</head>
		
		<body class="<?php echo $this->applyBodyClass( '' ); ?>">
			
			<?php $this->doContent(); ?>
			
			<?php $this->doWpFooter(); ?>
			
		</body>
		
		</html><?php
		
		$this->doGetFooter();
		$this->doFooterLate();
		
	}
	
	
}


