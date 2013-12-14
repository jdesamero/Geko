<?php

class Geko_Table_Output_Html extends Geko_Table_Output_Default
{
	
	//
	public function echoTitle( $aParams ) {
		?>
		<div class="title <?php echo $aParams[ 'table_class' ]; ?>"><?php echo $aParams[ 'table_title' ]; ?></div>
		<?php
		return $this;
	}
	
	//
	public function echoBeginTable( $aParams ) {
		?>
		<table class="<?php echo $aParams[ 'table_class' ]; ?>">
		<?php
		return $this;	
	}
	
	//
	public function echoHeadings( $aMeta ) {
		?>
		<tr>
			<?php foreach ( $aMeta as $aCol ): ?>
				<th><?php echo $aCol[ 'title' ]; ?></th>
			<?php endforeach; ?>
		</tr>
		<?php
		return $this;
	}
	
	//
	public function echoBeginRow() {
		?>
		<tr>
		<?php
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow ) {
		?>
		<td><?php echo trim( $this->getFieldVal( $aCol, $mRow ) ); ?></td>
		<?php
		return $this;	
	}
	
	//
	public function echoEndRow() {
		?>
		</tr>
		<?php
		return $this;	
	}
	
	//
	public function echoEndTable() {
		?>
		</table>
		<?php
		return $this;	
	}
	
	
	
	//
	public function echoCss() {
		?>
		<style type="text/css">
			
			div.##table_class##.title {
				font-size: 18px
			}
			
			div.##table_class##.title,
			table.##table_class## th,
			table.##table_class## td {
				font-family: 'Helvetica', 'Arial', 'sans-serif';
			}
			
			table.##table_class## th,
			table.##table_class## td {
				font-size: 12px;
			}
			
			table.##table_class## {
				margin: 0;
				padding: 0;
				border: 0;
				border-collapse: collapse;
			}
			
			table.##table_class## tr th,
			table.##table_class## tr td {
				border-top: solid 1px #000;
				border-left: solid 1px #000;
				vertical-align: top;
				padding: 3px 6px;
			}
			
			table.##table_class## tr th:last-child,
			table.##table_class## tr td:last-child {
				border-right: solid 1px #000;
			}
			
			table.##table_class## tr:last-child td {
				border-bottom: solid 1px #000;
			}
			
		</style>
		<?php
		
		return $this;
	}
	
	//
	public function renderCss( $aParams ) {
		
		echo str_replace(
			array(
				'##table_class##'
			),
			array(
				$aParams[ 'table_class' ]
			),
			Geko_String::fromOb( array( $this, 'echoCss' ) )
		);
		
		return $this;
	}
	
	
}


