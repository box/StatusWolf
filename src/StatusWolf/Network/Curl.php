<?php
/**
 * Curl
 *
 * Simple Curl class to fetch data from remote URLs
 *
 * Author Mark Troyer <disco@box.com>
 * Date Created: 5 March 2014
 *
 */

namespace StatusWolf\Network;

use Silex\Application;
use StatusWolf\Exception\ApiNetworkFetchException;
use StatusWolf\Exception\InvalidConfigurationException;

class Curl
{
	/**
	 * URL to fetch data from
	 *
	 * @var string
	 */
	private $_url = null;

	/**
	 * Proxy server URL, if used
	 *
	 * @var string
	 */
	private $_proxy = null;

	public function __construct(Application $sw, $url, $proxy = false, $proxy_url = null) {

        $this->_logger = $sw['logger'];
		if (!$this->_url = $url) {
            return null;
        }

		if ($proxy) {
			if (!isset($proxy_url)) {
                throw new InvalidConfigurationException('Configuration is missing proxy server URL');
            } else {
                $this->_proxy = $proxy_url;
            }
		}
	}

  /**
   * Curl::request()
   *
   * Perform the curl operation for the desired URL
   *
   * @return mixed
   * @throws ApiNetworkFetchException
   */
  public function request()
	{
        $this->_logger->addDebug('Fetching request for ' . $this->_url);
		$curl_object = curl_init($this->_url);
		curl_setopt($curl_object, CURLOPT_RETURNTRANSFER, 1);
		if (isset($this->_proxy))
		{
			curl_setopt($curl_object, CURLOPT_PROXY, $this->_proxy);
            $this->_logger->addDebug('Setting CURLOPT_PROXY to ' . $this->_proxy);
		}

		$data = curl_exec($curl_object);
		$status = curl_getinfo($curl_object, CURLINFO_HTTP_CODE);
		curl_close($curl_object);

		if ($status !== 200 && $status !== 202)
		{
			throw new ApiNetworkFetchException("Failed to fetch data from: \nURL: " . $this->_url . "\nResponse Code: " . $status . "\nResponse Body: " . $data);
		}

		return $data;
	}
}
