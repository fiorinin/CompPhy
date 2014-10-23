<?php

/**
* Pateysoft Creation
* All rights reserved
* 2007-2008
* @author Ludovic PATEY
* @version 2.0
* @website http://www.pateysoft.fr/Envoyer-une-requete-POST-par-PHP.html
* @license BSD
*
* This is a class used to communicate with others servers trough POST.
*/

class Core_Request_Post extends Core_Request_Abstract {

	private $data = array();
	private $files = array();
	private $boundary;

	public function __construct( $url, Core_Request_Conversation $conversation ) {
		parent::__construct( $url, $conversation );
		$this->boundary = md5( microtime() );
		$this->setHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	}
		
	public function setData( $name, $value ) {
		$this->data[ $name ] = $value;
	}
	
	public function setFile( $name, $path, $mime ) {
		$contentType = 'multipart/form-data, boundary=' . $this->boundary;
		$this->setHeader( 'Content-Type', $contentType );
		$this->files[ $name ] = array( 
			'path' => $path,
			'mime' => $mime );
	}
	
	public function getContext() {

		$headers = '';
		foreach( $this->getHeaders() as $name => $value ) {
			$headers .= $name . ': ' . $value . "\r\n";
		}
		
		if( $this->files ) {
	
			$content = $this->buildMultipartQuery();
		
		}
		else {
			$content = http_build_query( $this->data );
		}

		$headers.= 'Content-Length: ' . strlen( $content );
	
		$context = stream_context_create( 
			array( 'http' => array( 'user_agent' => 
			'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) Gecko/20061010 Firefox/2.0',
									'method' => 'POST',
									'content' => $content,
									'header' => $headers ) ) );
									
		return $context;
	}

	
	private function buildMultipartQuery() {
	
		$content = '--' . $this->boundary . "\n";
		
		foreach( $this->data as $key => $value ) {
			$content .= 'content-disposition: form-data; name="' 
				. $key . '"' . "\n\n" . $value . "\n" . '--' . $this->boundary . "\n";
		}
		
		foreach( $this->files as $key => $file ) {
		
			$content .= 'content-disposition: form-data; name="' 
				. $key . '"; filename=" ' . basename($file['path']) . '"' . "\n";
			$content .= 'Content-Type: ' . $file['mime'] . "\n";
			$content .= 'Content-Transfer-Encoding: binary' . "\n\n";
			$content .= file_get_contents( $file['path'] );
			$content .= "\n" . '--' . $this->boundary . "\n";
		}
		
		return $content;
	}

}

?>
