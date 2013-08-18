<?php

//
class GekoX_Test_Simple extends Geko_Entity
{
	
	//
	public function retPermalink() {
		return 'http://' . $this->getUri();
	}
		
}

