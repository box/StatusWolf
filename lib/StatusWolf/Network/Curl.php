<?php
/**
 * Curl
 *
 * Simple Curl class to fetch data from remote URLs
 *
 * @package StatusWolf.Network
 */

class Curl
{
	/**
	 * URL to fetch data from
	 *
	 * @var string
	 */
	protected static $_url = null;

	/**
	 * Proxy server URL, if used
	 *
	 * @var string
	 */
	public static $_proxy = null;

	public function __construct($url)
	{
		self::$_url = $url;

		if (SWConfig::read_values('datasource.proxy') === "true")
		{
			if (! self::$_proxy = SWConfig::read_values('datasource.proxy_url'))
				throw new SWException ('Configuration is missing proxy server URL');
		}
	}

  /**
   * Curl::request()
   *
   * Perform the curl operation for the desired URL
   *
   * @return mixed
   * @throws SWException
   */
  public function request()
	{
		$curl_object = curl_init(self::$_url);
		curl_setopt($curl_object, CURLOPT_RETURNTRANSFER, 1);
		if (isset(self::$_proxy))
		{
			curl_setopt($curl_object, CURLOPT_PROXY, self::$_proxy);
		}

		$data = curl_exec($curl_object);
		$status = curl_getinfo($curl_object, CURLINFO_HTTP_CODE);
		curl_close($curl_object);

		if ($status !== 200 && $status !== 202)
		{
			throw new SWException("Failed to fetch data from: \nURL: " . self::$_url . "\nResponse Code: " . $status . "\nResponse Body: " . $data);
		}

		return $data;
	}
}
