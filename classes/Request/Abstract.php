<?php

abstract class Core_Request_Abstract {

	private $conversation;
	private $cookies = array();
	private $url;
	private $headers = array();
	private $meta = array();
	
	public function __construct( $url, Core_Request_Conversation $conversation ) {
		$this->conversation = $conversation;
		$this->url = $url;
	}
	
	public function getConversation() {
		return $this->conversation;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function getCookies() {
		return $this->cookies;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	public function setCookies( $cookies ) {
		$this->setHeader( 'cookie', $this->buildCookies( $cookies ) );
		$this->cookies = $cookies;
	}
	
	public function setHeader( $name, $value ) {
		$this->headers[ $name ] = $value;
	}
    
   	 private function buildCookies( $cookies ) {

		$return = '';
		foreach( $cookies as $name => $value ) {
			$return .= ' ' . $name . '=' . $value . ';';
		}

		return trim($return);
	}
    
	public function send() {
								
		$fh = fopen( $this->getUrl(), 'r', false, $this->getContext() );
		
		if( !$fh ) {
			throw new Exception( 'Cannot open url ' . $this->getUrl() );
		}

		$return = '';
		
		while( !feof( $fh ) ) {
			$return  .= fread( $fh, 1024 );
		}
		
		$this->meta = stream_get_meta_data( $fh );

		$cookies = array();

		$headers = $this->meta['wrapper_data'];
		if( isset( $headers['headers'] ) ) {
			$headers = $headers['headers'];
		}
		
		foreach( $headers as $data ) {
			if( preg_match( '/Set-Cookie: ([^=]+)=([^;]+)/', $data, $cookie ) ) {
				if ( !isset( $cookies[ $cookie[1] ] ) ) {
                    $cookies[ $cookie[1] ] = $cookie[2];
                }
			}
		}
		
		$this->cookies = $cookies;
		$this->getConversation()->addCookies( $cookies );
		
		
		
		fclose( $fh );
		
		return $return;
	}

	public abstract function getContext();
}

?>
