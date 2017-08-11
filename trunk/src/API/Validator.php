<?php

/**
 * Created by PhpStorm.
 * User: websupporter
 * Date: 31.12.16
 * Time: 09:37
 */
class WCAV_Validator {


	/**
	 * The API.
	 *
	 * @var WCAV_API | NULL
	 */
	private $api = NULL;

	/**
	 * The response object.
	 *
	 * @var mixed
	 */
	private $response = NULL;

	/**
	 * The validation status.
	 *
	 * @var string
	 */
	private $status = '';

	/**
	 * The sanitized address.
	 *
	 * @var null|WCAV_Address
	 */
	private $sanitized = NULL;

	/**
	 * WCAV_API constructor.
	 *
	 * @param WCAV_API $api The API.
	 */
	public function __construct( WCAV_API $api ) {

		$this->api = $api;
	}

	/**
	 * Validate an Address.
	 *
	 * @param WCAV_Address $address The address.
	 *
	 * @return bool
	 */
	public function run( WCAV_Address $address ) {

		$this->response = $this->api->validate( $address );

		// When we can't reach the server, we validate for TRUE.
		if ( is_wp_error( $this->response ) || empty( $this->response['response']['code'] ) || 200 !== (int) $this->response['response']['code'] ) {

			/**
			 * Filters if an address is valid.
			 *
			 * @param array        array     The statuse.
			 * @param array        $response The response from the API.
			 * @param WCAV_Address $address  The address in question.
			 */
			return apply_filters(
				'wcav::is_valid',
				TRUE,
				$this->response,
				$address
			);
		}

		$json = json_decode( $this->response['body'] );

		$this->status = $json->status;

		$this->sanitized = new WCAV_Address(
			$json->street . ' ' . $json->streetnumber,
			$address->get_additional_address_info(),
			$json->postalcode,
			$json->city,
			( ! empty( $json->state ) ) ? $json->state : '',
			$json->country
		);

		// Set admin notice, if the key is invalid.
		if ( 'API_KEY_INVALID_OR_DEPLETED' === $json->status ) {
			update_option( 'wcav-invalid-key', 1 );
		}

		/**
		 * Filters the valide statuse.
		 *
		 * @param array        array     The statuse.
		 * @param array        $response The response from the API.
		 * @param WCAV_Address $address  The address in question.
		 */
		$valide_status = apply_filters(
			'wcav::valid_status',
			array(
				'API_KEY_INVALID_OR_DEPLETED',
				'VALID',
			),
			$this->response,
			$address
		);

		if ( in_array( strtoupper( $json->status ), $valide_status, TRUE ) ) {

			/**
			 * Filters if an address is valid.
			 *
			 * @param array        array     The statuse.
			 * @param array        $response The response from the API.
			 * @param WCAV_Address $address  The address in question.
			 */
			return apply_filters(
				'wcav::is_valid',
				TRUE,
				$this->response,
				$address
			);
		}

		/**
		 * Filters if an address is valid.
		 *
		 * @param array        array     The statuse.
		 * @param array        $response The response from the API.
		 * @param WCAV_Address $address  The address in question.
		 */
		return apply_filters(
			'wcav::is_valid',
			FALSE,
			$this->response,
			$address
		);
	}


	/**
	 * Get the status of the address.
	 *
	 * @return string
	 */
	public function get_status() {

		return $this->status;
	}

	/**
	 * Returns the sanitized address.
	 *
	 * @return null|WCAV_Address
	 */
	public function get_sanitized_address() {

		return $this->sanitized;
	}
}
