<?php
class Core_Request_Conversation {

	private $cookies = array();

    /**
     *
     * @param string $url
     * @return Core_Request_Post
     */
	public function newPost( $url ) {
		$post = new Core_Request_Post( $url, $this );
		$post->setCookies( $this->cookies );
		return $post;
	}

    /**
     *
     * @param string $url
     * @return Core_Request_Get
     */
	public function newGet( $url ) {
		$get = new Core_Request_Get( $url, $this );
		$get->setCookies( $this->cookies );
		return $get;
	}
	
	public function addCookies( $cookies ) {
		$this->cookies = array_merge( $cookies, $this->cookies );
	}
	
	public function getCookies() {
		return $this->cookies;
	}
	
	
}
