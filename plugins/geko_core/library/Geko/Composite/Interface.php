<?php

// a composite tree structure utilized by IndentedList
interface Geko_Composite_Interface
{
	// main methods
	public function setParent($oParent);
	public function setChild($aChild);
	public function hasChildren();
	public function getChildren();
	public function getParent();
	
	// hook methods
	public function setParams($oParams);
	public function setUp();
	
}

