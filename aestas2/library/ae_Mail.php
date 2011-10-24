<?php


class ae_Mail {

	protected $to = array();
	protected $from = '';
	protected $subject = '';
	protected $message = '';
	protected $headers = array();
	protected $charset = 'utf-8';


	/**
	 * Tries to send the mail.
	 * Returns true if the mail has been accepted as valid, false otherwise.
	 */
	public function send() {
		$to = $this->generate_to();
		$headers = $this->generate_headers();

		if( empty( $to ) ) {
			throw new Exception( 'Email has no receivers.' );
		}

		return mail( $to, $this->subject, $this->message, $headers );
	}


	/**
	 * Adds a receiver mail.
	 */
	public function add_receiver( $receiver ) {
		if( !ae_Validate::isEmail( $receiver ) ) {
			throw new Exception( 'Not a valid email-address.' );
		}
		$this->to[] = $receiver;
	}


	/**
	 * Adds a header.
	 */
	public function add_header( $type, $content ) {
		if( strtolower( $type ) == 'mime-version' ) {
			throw new Exception( 'It is not possible to set a MIME-Version for the email.' );
		}
		$type = strtolower( $type );
		$this->headers[$type] = $content;
	}


	/**
	 * Sets the sender.
	 */
	public function setSender( $sender ) {
		$this->from = $sender;
	}


	/**
	 * Sets the subject.
	 */
	public function setSubject( $subject ) {
		$this->subject = $subject;
	}


	/**
	 * Sets the message.
	 */
	public function setMessage( $message ) {
		$this->message = $message;
	}


	/**
	 * Sets the charset. Default is "utf-8".
	 */
	public function setCharset( $charset ) {
		$this->charset = $charset;
	}



	//---------- protected functions


	/**
	 * Generates the "to" string
	 */
	protected function generate_to() {
		$to = '';
		foreach( $this->to as $receiver ) {
			$to .= $receiver . ', ';
		}
		return $to;
	}


	/**
	 * Generates the headers string.
	 */
	protected function generate_headers() {
		if( !isset( $this->headers['content-type'] ) ) {
			$this->headers['content-type'] = 'text/plain; charset=' . $this->charset;
		}

		$headers = 'MIME-Version: 1.0' . PHP_EOL;
		if( $this->from != '' ) {
			$headers .= 'From: ' . $this->from . PHP_EOL;
		}

		foreach( $this->headers as $type => $content ) {
			$headers .= ucfirst( $type ) . ': ' . $content . PHP_EOL;
		}
		return $headers;
	}


}
