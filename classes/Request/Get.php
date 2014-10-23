<?php

/**
* Pateysoft Creation
* All rights reserved
* 2007-2008
* @author Ludovic PATEY
* @version 1.0
*
* This is a class used to communicate with others servers trough the GetMethod.
*/

class Core_Request_Get extends Core_Request_Abstract {

	public function __construct( $url, Core_Request_Conversation $conversation ) {
		parent::__construct( $url, $conversation );
	}

	public function getContext() {

		$headers = '';
		foreach( $this->getHeaders() as $name => $value ) {
			$headers .= $name . ': ' . $value . "\r\n";
		}

        	$content = '';
		$context = stream_context_create( 
			array( 'http' => array( 'user_agent' => 'Pateysoft User Agent',
									'method' => 'GET',
									'content' => $content,
									'header' => $headers ) ) );
		return $context;
	}
	
	
}

?>
