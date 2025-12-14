<?php

declare(strict_types=1);

namespace BbApp\RestAPI\WordPressBase;

use BbApp\RestAPI\RESTAPI;
use WP_REST_Response;

/**
 * Base REST helpers shared across WordPress-backed content sources.
 */
abstract class WordPressBaseRESTAPI extends RESTAPI
{
	/**
	 * Allow GET in batch endpoints when bb-app context is detected.
	 */
	public function modify_batch_endpoint(array $endpoints): array
	{
		if (!empty($endpoints['/batch/v1'])) {
			$endpoints['/batch/v1']['args']['requests']['items']['properties']['method']['enum'][] = 'GET';
		}

		return $endpoints;
	}

	/**
	 * Expand envelope bodies with embedded data for batch GET requests.
	 */
	public function modify_envelope_response(
		array $envelope,
		WP_REST_Response $response
	): array {
		if (
			str_ends_with($_GET['rest_route'] ?? $_SERVER['REQUEST_URI'], '/batch/v1') &&
			isset($_GET['_embed'])
		) {
			$envelope['body'] = rest_get_server()->response_to_data($response, true);
		}

		return $envelope;
	}

	/**
	 * Register REST field hooks when bb-app context is present.
	 */
	public function register(): void
	{
		add_filter('rest_endpoints', [$this, 'modify_batch_endpoint'], 20);
		add_filter('rest_envelope_response', [$this, 'modify_envelope_response'], 20, 2);
	}

	/**
	 * Initialize global REST filters and optional Basic Auth handler.
	 */
	public function init(): void
	{
	}
}
