<?php
define('SOLRMEKA_PLUGIN_VERSION', get_plugin_ini('Solrmeka', 'version'));

add_plugin_hook('install', 'solrmeka_install');
add_plugin_hook('uninstall', 'solrmeka_uninstall');
//add_plugin_hook('define_routes', 'solrmeka_define_routes');

// Add filters.
//add_filter('admin_navigation_main', 'simple_pages_admin_navigation_main');
//add_filter('public_navigation_main', 'solrmeka_public_navigation_main');

function solrmeka_install()
{
	set_option('solrmeka_plugin_version', SOLRMEKA_PLUGIN_VERSION);
}

function solrmeka_uninstall()
{
	delete_option('solrmeka_plugin_version');
}

class Apache_Solr_Service
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision: 16 $';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id: Service.php 16 2009-08-04 18:23:50Z donovan.jimenez $';

	/**
	 * Response version we support
	 */
	const SOLR_VERSION = '1.2';

	/**
	 * Response writer we'll request - JSON. See http://code.google.com/p/solr-php-client/issues/detail?id=6#c1 for reasoning
	 */
	const SOLR_WRITER = 'json';

	/**
	 * NamedList Treatment constants
	 */
	const NAMED_LIST_FLAT = 'flat';
	const NAMED_LIST_MAP = 'map';

	/**
	 * Search HTTP Methods
	 */
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';

	/**
	 * Servlet mappings
	 */
	const PING_SERVLET = 'admin/ping';
	const UPDATE_SERVLET = 'update';
	const SEARCH_SERVLET = 'select';
	const THREADS_SERVLET = 'admin/threads';

	/**
	 * Server identification strings
	 *
	 * @var string
	 */
	protected $_host, $_port, $_path;

	/**
	 * Whether {@link Apache_Solr_Response} objects should create {@link Apache_Solr_Document}s in
	 * the returned parsed data
	 *
	 * @var boolean
	 */
	protected $_createDocuments = true;

	/**
	 * Whether {@link Apache_Solr_Response} objects should have multivalue fields with only a single value
	 * collapsed to appear as a single value would.
	 *
	 * @var boolean
	 */
	protected $_collapseSingleValueArrays = true;

	/**
	 * How NamedLists should be formatted in the output.  This specifically effects facet counts. Valid values
	 * are {@link Apache_Solr_Service::NAMED_LIST_MAP} (default) or {@link Apache_Solr_Service::NAMED_LIST_FLAT}.
	 *
	 * @var string
	 */
	protected $_namedListTreatment = self::NAMED_LIST_MAP;

	/**
	 * Query delimiters. Someone might want to be able to change
	 * these (to use &amp; instead of & for example), so I've provided them.
	 *
	 * @var string
	 */
	protected $_queryDelimiter = '?', $_queryStringDelimiter = '&';

	/**
	 * Constructed servlet full path URLs
	 *
	 * @var string
	 */
	protected $_pingUrl, $_updateUrl, $_searchUrl, $_threadsUrl;

	/**
	 * Keep track of whether our URLs have been constructed
	 *
	 * @var boolean
	 */
	protected $_urlsInited = false;

	/**
	 * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
	 *
	 * NOTE: inside a phrase fewer characters need escaped, use {@link Apache_Solr_Service::escapePhrase()} instead
	 *
	 * @param string $value
	 * @return string
	 */
	static public function escape($value)
	{
		//list taken from http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
		$pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
		$replace = '\\\$1';

		return preg_replace($pattern, $replace, $value);
	}

	/**
	 * Escape a value meant to be contained in a phrase for special query characters
	 *
	 * @param string $value
	 * @return string
	 */
	static public function escapePhrase($value)
	{
		$pattern = '/("|\\\)/';
		$replace = '\\\$1';

		return preg_replace($pattern, $replace, $value);
	}

	/**
	 * Convenience function for creating phrase syntax from a value
	 *
	 * @param string $value
	 * @return string
	 */
	static public function phrase($value)
	{
		return '"' . self::escapePhrase($value) . '"';
	}

	/**
	 * Constructor. All parameters are optional and will take on default values
	 * if not specified.
	 *
	 * @param string $host
	 * @param string $port
	 * @param string $path
	 */
	public function __construct($host = 'localhost', $port = 8180, $path = '/solr/')
	{
		$this->setHost($host);
		$this->setPort($port);
		$this->setPath($path);

		$this->_initUrls();
	}

	/**
	 * Return a valid http URL given this server's host, port and path and a provided servlet name
	 *
	 * @param string $servlet
	 * @return string
	 */
	protected function _constructUrl($servlet, $params = array())
	{
		if (count($params))
		{
			//escape all parameters appropriately for inclusion in the query string
			$escapedParams = array();

			foreach ($params as $key => $value)
			{
				$escapedParams[] = urlencode($key) . '=' . urlencode($value);
			}

			$queryString = $this->_queryDelimiter . implode($this->_queryStringDelimiter, $escapedParams);
		}
		else
		{
			$queryString = '';
		}

		return 'http://' . $this->_host . ':' . $this->_port . $this->_path . $servlet . $queryString;
	}

	/**
	 * Construct the Full URLs for the three servlets we reference
	 */
	protected function _initUrls()
	{
		//Initialize our full servlet URLs now that we have server information
		$this->_pingUrl = $this->_constructUrl(self::PING_SERVLET);
		$this->_updateUrl = $this->_constructUrl(self::UPDATE_SERVLET, array('wt' => self::SOLR_WRITER ));
		$this->_searchUrl = $this->_constructUrl(self::SEARCH_SERVLET);
		$this->_threadsUrl = $this->_constructUrl(self::THREADS_SERVLET, array('wt' => self::SOLR_WRITER ));

		$this->_urlsInited = true;
	}

	/**
	 * Central method for making a get operation against this Solr Server
	 *
	 * @param string $url
	 * @param float $timeout Read timeout in seconds
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If a non 200 response status is returned
	 */
	protected function _sendRawGet($url, $timeout = FALSE)
	{
		//set up the stream context so we can control
		// the timeout for file_get_contents
		$context = stream_context_create();

		// set the timeout if specified, without this I assume
		// that the default_socket_timeout ini setting is used
		if ($timeout !== FALSE && $timeout > 0.0)
		{
			// timeouts with file_get_contents seem to need
			// to be halved to work as expected
			$timeout = (float) $timeout / 2;

			stream_context_set_option($context, 'http', 'timeout', $timeout);
		}

		//$http_response_header is set by file_get_contents
		$response = new Apache_Solr_Response(@file_get_contents($url, false, $context), $http_response_header, $this->_createDocuments, $this->_collapseSingleValueArrays);

		if ($response->getHttpStatus() != 200)
		{
			throw new Exception('"' . $response->getHttpStatus() . '" Status: ' . $response->getHttpStatusMessage(), $response->getHttpStatus());
		}

		return $response;
	}

	/**
	 * Central method for making a post operation against this Solr Server
	 *
	 * @param string $url
	 * @param string $rawPost
	 * @param float $timeout Read timeout in seconds
	 * @param string $contentType
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If a non 200 response status is returned
	 */
	protected function _sendRawPost($url, $rawPost, $timeout = FALSE, $contentType = 'text/xml; charset=UTF-8')
	{
		//set up the stream context for posting with file_get_contents
		$context = stream_context_create(
			array(
				'http' => array(
					// set HTTP method
					'method' => 'POST',

					// Add our posted content type
					'header' => "Content-Type: $contentType",

					// the posted content
					'content' => $rawPost
				)
			)
		);

		// set the timeout if specified, without this I assume
		// that the default_socket_timeout ini setting is used
		if ($timeout !== FALSE && $timeout > 0.0)
		{
			// timeouts with file_get_contents seem to need
			// to be halved to work as expected
			$timeout = (float) $timeout / 2;

			stream_context_set_option($context, 'http', 'timeout', $timeout);
		}

		//$http_response_header is set by file_get_contents
		$response = new Apache_Solr_Response(@file_get_contents($url, false, $context), $http_response_header, $this->_createDocuments, $this->_collapseSingleValueArrays);

		if ($response->getHttpStatus() != 200)
		{
			throw new Exception('"' . $response->getHttpStatus() . '" Status: ' . $response->getHttpStatusMessage(), $response->getHttpStatus());
		}

		return $response;
	}

	/**
	 * Returns the set host
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->_host;
	}

	/**
	 * Set the host used. If empty will fallback to constants
	 *
	 * @param string $host
	 */
	public function setHost($host)
	{
		//Use the provided host or use the default
		if (empty($host))
		{
			throw new Exception('Host parameter is empty');
		}
		else
		{
			$this->_host = $host;
		}

		if ($this->_urlsInited)
		{
			$this->_initUrls();
		}
	}

	/**
	 * Get the set port
	 *
	 * @return integer
	 */
	public function getPort()
	{
		return $this->_port;
	}

	/**
	 * Set the port used. If empty will fallback to constants
	 *
	 * @param integer $port
	 */
	public function setPort($port)
	{
		//Use the provided port or use the default
		$port = (int) $port;

		if ($port <= 0)
		{
			throw new Exception('Port is not a valid port number');
		}
		else
		{
			$this->_port = $port;
		}

		if ($this->_urlsInited)
		{
			$this->_initUrls();
		}
	}

	/**
	 * Get the set path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Set the path used. If empty will fallback to constants
	 *
	 * @param string $path
	 */
	public function setPath($path)
	{
		$path = trim($path, '/');

		$this->_path = '/' . $path . '/';

		if ($this->_urlsInited)
		{
			$this->_initUrls();
		}
	}

	/**
	 * Set the create documents flag. This determines whether {@link Apache_Solr_Response} objects will
	 * parse the response and create {@link Apache_Solr_Document} instances in place.
	 *
	 * @param unknown_type $createDocuments
	 */
	public function setCreateDocuments($createDocuments)
	{
		$this->_createDocuments = (bool) $createDocuments;
	}

	/**
	 * Get the current state of teh create documents flag.
	 *
	 * @return boolean
	 */
	public function getCreateDocuments()
	{
		return $this->_createDocuments;
	}

	/**
	 * Set the collapse single value arrays flag.
	 *
	 * @param boolean $collapseSingleValueArrays
	 */
	public function setCollapseSingleValueArrays($collapseSingleValueArrays)
	{
		$this->_collapseSingleValueArrays = (bool) $collapseSingleValueArrays;
	}

	/**
	 * Get the current state of the collapse single value arrays flag.
	 *
	 * @return boolean
	 */
	public function getCollapseSingleValueArrays()
	{
		return $this->_collapseSingleValueArrays;
	}

	/**
	 * Set how NamedLists should be formatted in the response data. This mainly effects
	 * the facet counts format.
	 *
	 * @param string $namedListTreatment
	 * @throws Exception If invalid option is set
	 */
	public function setNamedListTreatmet($namedListTreatment)
	{
		switch ((string) $namedListTreatment)
		{
			case Apache_Solr_Service::NAMED_LIST_FLAT:
				$this->_namedListTreatment = Apache_Solr_Service::NAMED_LIST_FLAT;
				break;

			case Apache_Solr_Service::NAMED_LIST_MAP:
				$this->_namedListTreatment = Apache_Solr_Service::NAMED_LIST_MAP;
				break;

			default:
				throw new Exception('Not a valid named list treatement option');
		}
	}

	/**
	 * Get the current setting for named list treatment.
	 *
	 * @return string
	 */
	public function getNamedListTreatment()
	{
		return $this->_namedListTreatment;
	}


	/**
	 * Set the string used to separate the path form the query string.
	 * Defaulted to '?'
	 *
	 * @param string $queryDelimiter
	 */
	public function setQueryDelimiter($queryDelimiter)
	{
		$this->_queryDelimiter = $queryDelimiter;
	}

	/**
	 * Set the string used to separate the parameters in thequery string
	 * Defaulted to '&'
	 *
	 * @param string $queryStringDelimiter
	 */
	public function setQueryStringDelimiter($queryStringDelimiter)
	{
		$this->_queryStringDelimiter = $queryStringDelimiter;
	}

	/**
	 * Call the /admin/ping servlet, can be used to quickly tell if a connection to the
	 * server is able to be made.
	 *
	 * @param float $timeout maximum time to wait for ping in seconds, -1 for unlimited (default is 2)
	 * @return float Actual time taken to ping the server, FALSE if timeout or HTTP error status occurs
	 */
	public function ping($timeout = 2)
	{
		$start = microtime(true);

		// when using timeout in context and file_get_contents
		// it seems to take twice the timout value
		$timeout = (float) $timeout / 2;

		if ($timeout <= 0.0)
		{
			$timeout = -1;
		}

		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'HEAD',
					'timeout' => $timeout
				)
			)
		);

		// attempt a HEAD request to the solr ping page
		$ping = @file_get_contents($this->_pingUrl, false, $context);

		// result is false if there was a timeout
		// or if the HTTP status was not 200
		if ($ping !== false)
		{
			return microtime(true) - $start;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Call the /admin/threads servlet and retrieve information about all threads in the
	 * Solr servlet's thread group. Useful for diagnostics.
	 *
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function threads()
	{
		return $this->_sendRawGet($this->_threadsUrl);
	}

	/**
	 * Raw Add Method. Takes a raw post body and sends it to the update service.  Post body
	 * should be a complete and well formed "add" xml document.
	 *
	 * @param string $rawPost
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function add($rawPost)
	{
		return $this->_sendRawPost($this->_updateUrl, $rawPost);
	}

	/**
	 * Add a Solr Document to the index
	 *
	 * @param Apache_Solr_Document $document
	 * @param boolean $allowDups
	 * @param boolean $overwritePending
	 * @param boolean $overwriteCommitted
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function addDocument(Apache_Solr_Document $document, $allowDups = false, $overwritePending = true, $overwriteCommitted = true)
	{
		$dupValue = $allowDups ? 'true' : 'false';
		$pendingValue = $overwritePending ? 'true' : 'false';
		$committedValue = $overwriteCommitted ? 'true' : 'false';

		$rawPost = '<add allowDups="' . $dupValue . '" overwritePending="' . $pendingValue . '" overwriteCommitted="' . $committedValue . '">';
		$rawPost .= $this->_documentToXmlFragment($document);
		$rawPost .= '</add>';

		return $this->add($rawPost);
	}

	/**
	 * Add an array of Solr Documents to the index all at once
	 *
	 * @param array $documents Should be an array of Apache_Solr_Document instances
	 * @param boolean $allowDups
	 * @param boolean $overwritePending
	 * @param boolean $overwriteCommitted
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function addDocuments($documents, $allowDups = false, $overwritePending = true, $overwriteCommitted = true)
	{
		$dupValue = $allowDups ? 'true' : 'false';
		$pendingValue = $overwritePending ? 'true' : 'false';
		$committedValue = $overwriteCommitted ? 'true' : 'false';

		$rawPost = '<add allowDups="' . $dupValue . '" overwritePending="' . $pendingValue . '" overwriteCommitted="' . $committedValue . '">';

		foreach ($documents as $document)
		{
			if ($document instanceof Apache_Solr_Document)
			{
				$rawPost .= $this->_documentToXmlFragment($document);
			}
		}

		$rawPost .= '</add>';

		return $this->add($rawPost);
	}

	/**
	 * Create an XML fragment from a {@link Apache_Solr_Document} instance appropriate for use inside a Solr add call
	 *
	 * @return string
	 */
	protected function _documentToXmlFragment(Apache_Solr_Document $document)
	{
		$xml = '<doc';

		if ($document->getBoost() !== false)
		{
			$xml .= ' boost="' . $document->getBoost() . '"';
		}

		$xml .= '>';

		foreach ($document as $key => $value)
		{
			$key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
			$fieldBoost = $document->getFieldBoost($key);

			if (is_array($value))
			{
				foreach ($value as $multivalue)
				{
					$xml .= '<field name="' . $key . '"';

					if ($fieldBoost !== false)
					{
						$xml .= ' boost="' . $fieldBoost . '"';

						// only set the boost for the first field in the set
						$fieldBoost = false;
					}

					$multivalue = htmlspecialchars($multivalue, ENT_NOQUOTES, 'UTF-8');

					$xml .= '>' . $multivalue . '</field>';
				}
			}
			else
			{
				$xml .= '<field name="' . $key . '"';

				if ($fieldBoost !== false)
				{
					$xml .= ' boost="' . $fieldBoost . '"';
				}

				$value = htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');

				$xml .= '>' . $value . '</field>';
			}
		}

		$xml .= '</doc>';

		// replace any control characters to avoid Solr XML parser exception
		return $this->_stripCtrlChars($xml);
	}

	/**
	 * Replace control (non-printable) characters from string that are invalid to Solr's XML parser with a space.
	 *
	 * @param string $string
	 * @return string
	 */
	protected function _stripCtrlChars($string)
	{
		// See:  http://w3.org/International/questions/qa-forms-utf-8.html
		// Printable utf-8 does not include any of these chars below x7F
		return preg_replace('@[\x00-\x08\x0B\x0C\x0E-\x1F]@', ' ', $string);
	}

	/**
	 * Send a commit command.  Will be synchronous unless both wait parameters are set to false.
	 *
	 * @param boolean $optimize Defaults to true
	 * @param boolean $waitFlush Defaults to true
	 * @param boolean $waitSearcher Defaults to true
	 * @param float $timeout Maximum expected duration (in seconds) of the commit operation on the server (otherwise, will throw a communication exception). Defaults to 1 hour
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function commit($optimize = true, $waitFlush = true, $waitSearcher = true, $timeout = 3600)
	{
		$optimizeValue = $optimize ? 'true' : 'false';
		$flushValue = $waitFlush ? 'true' : 'false';
		$searcherValue = $waitSearcher ? 'true' : 'false';

		$rawPost = '<commit optimize="' . $optimizeValue . '" waitFlush="' . $flushValue . '" waitSearcher="' . $searcherValue . '" />';

		return $this->_sendRawPost($this->_updateUrl, $rawPost, $timeout);
	}

	/**
	 * Raw Delete Method. Takes a raw post body and sends it to the update service. Body should be
	 * a complete and well formed "delete" xml document
	 *
	 * @param string $rawPost Expected to be utf-8 encoded xml document
	 * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function delete($rawPost, $timeout = 3600)
	{
		return $this->_sendRawPost($this->_updateUrl, $rawPost, $timeout);
	}

	/**
	 * Create a delete document based on document ID
	 *
	 * @param string $id Expected to be utf-8 encoded
	 * @param boolean $fromPending
	 * @param boolean $fromCommitted
	 * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function deleteById($id, $fromPending = true, $fromCommitted = true, $timeout = 3600)
	{
		$pendingValue = $fromPending ? 'true' : 'false';
		$committedValue = $fromCommitted ? 'true' : 'false';

		//escape special xml characters
		$id = htmlspecialchars($id, ENT_NOQUOTES, 'UTF-8');

		$rawPost = '<delete fromPending="' . $pendingValue . '" fromCommitted="' . $committedValue . '"><id>' . $id . '</id></delete>';

		return $this->delete($rawPost, $timeout);
	}

	/**
	 * Create a delete document based on a query and submit it
	 *
	 * @param string $rawQuery Expected to be utf-8 encoded
	 * @param boolean $fromPending
	 * @param boolean $fromCommitted
	 * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function deleteByQuery($rawQuery, $fromPending = true, $fromCommitted = true, $timeout = 3600)
	{
		$pendingValue = $fromPending ? 'true' : 'false';
		$committedValue = $fromCommitted ? 'true' : 'false';

		// escape special xml characters
		$rawQuery = htmlspecialchars($rawQuery, ENT_NOQUOTES, 'UTF-8');

		$rawPost = '<delete fromPending="' . $pendingValue . '" fromCommitted="' . $committedValue . '"><query>' . $rawQuery . '</query></delete>';

		return $this->delete($rawPost, $timeout);
	}

	/**
	 * Send an optimize command.  Will be synchronous unless both wait parameters are set
	 * to false.
	 *
	 * @param boolean $waitFlush
	 * @param boolean $waitSearcher
	 * @param float $timeout Maximum expected duration of the commit operation on the server (otherwise, will throw a communication exception)
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function optimize($waitFlush = true, $waitSearcher = true, $timeout = 3600)
	{
		$flushValue = $waitFlush ? 'true' : 'false';
		$searcherValue = $waitSearcher ? 'true' : 'false';

		$rawPost = '<optimize waitFlush="' . $flushValue . '" waitSearcher="' . $searcherValue . '" />';

		return $this->_sendRawPost($this->_updateUrl, $rawPost, $timeout);
	}

	/**
	 * Simple Search interface
	 *
	 * @param string $query The raw query string
	 * @param int $offset The starting offset for result documents
	 * @param int $limit The maximum number of result documents to return
	 * @param array $params key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function search($query, $offset = 0, $limit = 10, $params = array(), $method = self::METHOD_GET)
	{
		if (!is_array($params))
		{
			$params = array();
		}

		// construct our full parameters
		// sending the version is important in case the format changes
		$params['version'] = self::SOLR_VERSION;

		// common parameters in this interface
		$params['wt'] = self::SOLR_WRITER;
		$params['json.nl'] = $this->_namedListTreatment;

		$params['q'] = $query;
		$params['start'] = $offset;
		$params['rows'] = $limit;

		// use http_build_query to encode our arguments because its faster
		// than urlencoding all the parts ourselves in a loop
		$queryString = http_build_query($params, null, $this->_queryStringDelimiter);

		// because http_build_query treats arrays differently than we want to, correct the query
		// string by changing foo[#]=bar (# being an actual number) parameter strings to just
		// multiple foo=bar strings. This regex should always work since '=' will be urlencoded
		// anywhere else the regex isn't expecting it
		$queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

		if ($method == self::METHOD_GET)
		{
			return $this->_sendRawGet($this->_searchUrl . $this->_queryDelimiter . $queryString);
		}
		else if ($method == self::METHOD_POST)
		{
			return $this->_sendRawPost($this->_searchUrl, $queryString, FALSE, 'application/x-www-form-urlencoded');
		}
		else
		{
			throw new Exception("Unsupported method '$method', please use the Apache_Solr_Service::METHOD_* constants");
		}
	}
}

class Apache_Solr_Document implements IteratorAggregate
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision: 15 $';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id: Document.php 15 2009-08-04 17:53:08Z donovan.jimenez $';

	/**
	 * Document boost value
	 *
	 * @var float
	 */
	protected $_documentBoost = false;

	/**
	 * Document field values, indexed by name
	 *
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * Document field boost values, indexed by name
	 *
	 * @var array array of floats
	 */
	protected $_fieldBoosts = array();

	/**
	 * Clear all boosts and fields from this document
	 */
	public function clear()
	{
		$this->_documentBoost = false;

		$this->_fields = array();
		$this->_fieldBoosts = array();
	}

	/**
	 * Get current document boost
	 *
	 * @return mixed will be false for default, or else a float
	 */
	public function getBoost()
	{
		return $this->_documentBoost;
	}

	/**
	 * Set document boost factor
	 *
	 * @param mixed $boost Use false for default boost, else cast to float that should be > 0 or will be treated as false
	 */
	public function setBoost($boost)
	{
		$boost = (float) $boost;

		if ($boost > 0.0)
		{
			$this->_documentBoost = $boost;
		}
		else
		{
			$this->_documentBoost = false;
		}
	}

	/**
	 * Add a value to a multi-valued field
	 *
	 * NOTE: the solr XML format allows you to specify boosts
	 * PER value even though the underlying Lucene implementation
	 * only allows a boost per field. To remedy this, the final
	 * field boost value will be the product of all specified boosts
	 * on field values - this is similar to SolrJ's functionality.
	 *
	 * <code>
	 * $doc = new Apache_Solr_Document();
	 *
	 * $doc->addField('foo', 'bar', 2.0);
	 * $doc->addField('foo', 'baz', 3.0);
	 *
	 * // resultant field boost will be 6!
	 * echo $doc->getFieldBoost('foo');
	 * </code>
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $boost Use false for default boost, else cast to float that should be > 0 or will be treated as false
	 */
	public function addField($key, $value, $boost = false)
	{
		if (!isset($this->_fields[$key]))
		{
			// create holding array if this is the first value
			$this->_fields[$key] = array();
		}
		else if (!is_array($this->_fields[$key]))
		{
			// move existing value into array if it is not already an array
			$this->_fields[$key] = array($this->_fields[$key]);
		}

		if ($this->getFieldBoost($key) === false)
		{
			// boost not already set, set it now
			$this->setFieldBoost($key, $boost);
		}
		else if ((float) $boost > 0.0)
		{
			// multiply passed boost with current field boost - similar to SolrJ implementation
			$this->_fieldBoosts[$key] *= (float) $boost;
		}

		// add value to array
		$this->_fields[$key][] = $value;
	}

	/**
	 * Handle the array manipulation for a multi-valued field
	 *
	 * @param string $key
	 * @param string $value
	 * @param mixed $boost Use false for default boost, else cast to float that should be > 0 or will be treated as false
	 *
	 * @deprecated Use addField(...) instead
	 */
	public function setMultiValue($key, $value, $boost = false)
	{
		$this->addField($key, $value, $boost);
	}

	/**
	 * Get field information
	 *
	 * @param string $key
	 * @return mixed associative array of info if field exists, false otherwise
	 */
	public function getField($key)
	{
		if (isset($this->_fields[$key]))
		{
			return array(
				'name' => $key,
				'value' => $this->_fields[$key],
				'boost' => $this->getFieldBoost($key)
			);
		}

		return false;
	}

	/**
	 * Set a field value. Multi-valued fields should be set as arrays
	 * or instead use the addField(...) function which will automatically
	 * make sure the field is an array.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $boost Use false for default boost, else cast to float that should be > 0 or will be treated as false
	 */
	public function setField($key, $value, $boost = false)
	{
		$this->_fields[$key] = $value;
		$this->setFieldBoost($key, $boost);
	}

	/**
	 * Get the currently set field boost for a document field
	 *
	 * @param string $key
	 * @return float currently set field boost, false if one is not set
	 */
	public function getFieldBoost($key)
	{
		return isset($this->_fieldBoosts[$key]) ? $this->_fieldBoosts[$key] : false;
	}

	/**
	 * Set the field boost for a document field
	 *
	 * @param string $key field name for the boost
	 * @param mixed $boost Use false for default boost, else cast to float that should be > 0 or will be treated as false
	 */
	public function setFieldBoost($key, $boost)
	{
		$boost = (float) $boost;

		if ($boost > 0.0)
		{
			$this->_fieldBoosts[$key] = $boost;
		}
		else
		{
			$this->_fieldBoosts[$key] = false;
		}
	}

	/**
	 * Return current field boosts, indexed by field name
	 *
	 * @return array
	 */
	public function getFieldBoosts()
	{
		return $this->_fieldBoosts;
	}

	/**
	 * Get the names of all fields in this document
	 *
	 * @return array
	 */
	public function getFieldNames()
	{
		return array_keys($this->_fields);
	}

	/**
	 * Get the values of all fields in this document
	 *
	 * @return array
	 */
	public function getFieldValues()
	{
		return array_values($this->_fields);
	}

	/**
	 * IteratorAggregate implementation function. Allows usage:
	 *
	 * <code>
	 * foreach ($document as $key => $value)
	 * {
	 * 	...
	 * }
	 * </code>
	 */
	public function getIterator()
	{
		$arrayObject = new ArrayObject($this->_fields);

		return $arrayObject->getIterator();
	}

	/**
	 * Magic get for field values
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->_fields[$key];
	}

	/**
	 * Magic set for field values. Multi-valued fields should be set as arrays
	 * or instead use the addField(...) function which will automatically
	 * make sure the field is an array.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		$this->setField($key, $value);
	}

	/**
	 * Magic isset for fields values.  Do not call directly. Allows usage:
	 *
	 * <code>
	 * isset($document->some_field);
	 * </code>
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->_fields[$key]);
	}

	/**
	 * Magic unset for field values. Do not call directly. Allows usage:
	 *
	 * <code>
	 * unset($document->some_field);
	 * </code>
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		unset($this->_fields[$key]);
		unset($this->_fieldBoosts[$key]);
	}
}

class Apache_Solr_Response
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision: 16 $';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id: Response.php 16 2009-08-04 18:23:50Z donovan.jimenez $';

	/**
	 * Holds the raw response used in construction
	 *
	 * @var string
	 */
	protected $_rawResponse;

	/**
	 * Parsed values from the passed in http headers
	 *
	 * @var string
	 */
	protected $_httpStatus, $_httpStatusMessage, $_type, $_encoding;

	/**
	 * Whether the raw response has been parsed
	 *
	 * @var boolean
	 */
	protected $_isParsed = false;

	/**
	 * Parsed representation of the data
	 *
	 * @var mixed
	 */
	protected $_parsedData;

	/**
	 * Data parsing flags.  Determines what extra processing should be done
	 * after the data is initially converted to a data structure.
	 *
	 * @var boolean
	 */
	protected $_createDocuments = true,
			$_collapseSingleValueArrays = true;

	/**
	 * Constructor. Takes the raw HTTP response body and the exploded HTTP headers
	 *
	 * @param string $rawResponse
	 * @param array $httpHeaders
	 * @param boolean $createDocuments Whether to convert the documents json_decoded as stdClass instances to Apache_Solr_Document instances
	 * @param boolean $collapseSingleValueArrays Whether to make multivalued fields appear as single values
	 */
	public function __construct($rawResponse, $httpHeaders = array(), $createDocuments = true, $collapseSingleValueArrays = true)
	{
		//Assume 0, 'Communication Error', utf-8, and  text/plain
		$status = 0;
		$statusMessage = 'Communication Error';
		$type = 'text/plain';
		$encoding = 'UTF-8';

		//iterate through headers for real status, type, and encoding
		if (is_array($httpHeaders) && count($httpHeaders) > 0)
		{
			//look at the first headers for the HTTP status code
			//and message (errors are usually returned this way)
			//
			//HTTP 100 Continue response can also be returned before
			//the REAL status header, so we need look until we find
			//the last header starting with HTTP
			//
			//the spec: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.1
			//
			//Thanks to Daniel Andersson for pointing out this oversight
			while (isset($httpHeaders[0]) && substr($httpHeaders[0], 0, 4) == 'HTTP')
			{
				$parts = split(' ', substr($httpHeaders[0], 9), 2);

				$status = $parts[0];
				$statusMessage = trim($parts[1]);

				array_shift($httpHeaders);
			}

			//Look for the Content-Type response header and determine type
			//and encoding from it (if possible - such as 'Content-Type: text/plain; charset=UTF-8')
			foreach ($httpHeaders as $header)
			{
				if (strncasecmp($header, 'Content-Type:', 13) == 0)
				{
					//split content type value into two parts if possible
					$parts = split(';', substr($header, 13), 2);

					$type = trim($parts[0]);

					if ($parts[1])
					{
						//split the encoding section again to get the value
						$parts = split('=', $parts[1], 2);

						if ($parts[1])
						{
							$encoding = trim($parts[1]);
						}
					}

					break;
				}
			}
		}

		$this->_rawResponse = $rawResponse;
		$this->_type = $type;
		$this->_encoding = $encoding;
		$this->_httpStatus = $status;
		$this->_httpStatusMessage = $statusMessage;
		$this->_createDocuments = (bool) $createDocuments;
		$this->_collapseSingleValueArrays = (bool) $collapseSingleValueArrays;
	}

	/**
	 * Get the HTTP status code
	 *
	 * @return integer
	 */
	public function getHttpStatus()
	{
		return $this->_httpStatus;
	}

	/**
	 * Get the HTTP status message of the response
	 *
	 * @return string
	 */
	public function getHttpStatusMessage()
	{
		return $this->_httpStatusMessage;
	}

	/**
	 * Get content type of this Solr response
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Get character encoding of this response. Should usually be utf-8, but just in case
	 *
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->_encoding;
	}

	/**
	 * Get the raw response as it was given to this object
	 *
	 * @return string
	 */
	public function getRawResponse()
	{
		return $this->_rawResponse;
	}

	/**
	 * Magic get to expose the parsed data and to lazily load it
	 *
	 * @param unknown_type $key
	 * @return unknown
	 */
	public function __get($key)
	{
		if (!$this->_isParsed)
		{
			$this->_parseData();
			$this->_isParsed = true;
		}

		if (isset($this->_parsedData->$key))
		{
			return $this->_parsedData->$key;
		}

		return null;
	}

	/**
	 * Parse the raw response into the parsed_data array for access
	 */
	protected function _parseData()
	{
		//An alternative would be to use Zend_Json::decode(...)
		$data = json_decode($this->_rawResponse);

		//if we're configured to collapse single valued arrays or to convert them to Apache_Solr_Document objects
		//and we have response documents, then try to collapse the values and / or convert them now
		if (($this->_createDocuments || $this->_collapseSingleValueArrays) && isset($data->response) && is_array($data->response->docs))
		{
			$documents = array();

			foreach ($data->response->docs as $originalDocument)
			{
				if ($this->_createDocuments)
				{
					$document = new Apache_Solr_Document();
				}
				else
				{
					$document = $originalDocument;
				}

				foreach ($originalDocument as $key => $value)
				{
					//If a result is an array with only a single
					//value then its nice to be able to access
					//it as if it were always a single value
					if ($this->_collapseSingleValueArrays && is_array($value) && count($value) <= 1)
					{
						$value = array_shift($value);
					}

					$document->$key = $value;
				}

				$documents[] = $document;
			}

			$data->response->docs = $documents;
		}

		$this->_parsedData = $data;
	}
}

function solr_search($buttonText = "Search", $formProperties=array('id'=>'simple-search'), $uri = 'results') 
{ 
    $formProperties['action'] = $uri;
    $formProperties['method'] = 'get';
    $html  = '<form ' . _tag_attributes($formProperties) . '>' . "\n";
    $html .= '<fieldset>' . "\n\n";
    $html .= __v()->formText('q', html_escape($_REQUEST['q']), array('name'=>'textinput','class'=>'textinput'));
    $html .= '</fieldset>' . "\n\n";
    $html .= '</form>';
    return $html;
}

function solr_paginate($results, $q, $start, $limit, $page)
{
	$total = (int) $results->response->numFound;
	$start_doc = $start + 1;
	$total_pages = ceil($total / 10);
	$end = $page * $limit;
	$next = $page + 1;
	if ($page > 1)
	{
		$previous = $page - 1;
	}
	else{
		$previous = 0;
	}
	$current = $start / $limit + 1;

	$html .= '<div class="pagination" style="display:table;width:100%;">' . "\n" . '<div style="float:left">Results ' . $start_doc . ' - ' . $end . ' of ' . $total . '</div>' . "\n" . 
	'<div style="float:right"><ul class="pagination_list">';

	//Display First/Previous links
	if ($page > 1)
		{ 
		$html .= '<li class="pagination_first"><a href="' . '?q=' . $q . '">First</a></li><li class="pagination_previous"><a href="' . '?q=' . $q . '&page=' . $previous . '">Previous</a></li>';
	}

	//Display previous two pages if they meet numeric requirements
	if ($page - 2 > 0){
		$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page - 2) . '">' . ($page - 2) . '</a></li>';
	}
	if ($page - 1 > 0){
		$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page - 1) . '">' . ($page - 1) . '</a></li>';
	}

	//Display current page number
	$html .= '<li class="pagination_current">' . $page . '</li>';

	//Display next two pages if they meet numeric requirements	
	if ($page + 1 <= $total_pages){
		$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page + 1) . '">' . ($page + 1) . '</a></li>';
	}
	if ($page + 2 <= $total_pages){
		$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page + 2) . '">' . ($page + 2) . '</a></li>';
	}

	//Display Next/Last links
	if ($page < $total_pages)
		{ 
		$html .= '<li class="pagination_next"><a href="' . '?q=' . $q . '&page=' . $next . '">Next</a></li><li class="pagination_last"><a href="' . '?q=' . $q . '&page=' . $total_pages . '">Last</a></li>';
	}	
	$html .= '</div>' . "\n" . '</div>' . "\n";
	
	return $html;
}
/**
 * Displays the CSS layout for the exhibit in the header
 * 
 **/
/*function solrsearch_css() {
	// Add the stylesheet for the layout
	echo '<link rel="stylesheet" media="screen" href="results/results.css"/> ';
}*/

/**
 * Add the page title to the public main navigation.
 * 
 * @param array Navigation array.
 * @return array Filtered navigation array.

function solrmeka_public_navigation_main($nav)
{
    $pages = get_db()->getTable('SimplePagesPage')->findAll();
    foreach ($pages as $page) {
        // Only add the link to the public navigation if the page is published.
        if ($page->is_published && $page->add_to_public_nav) {
            $nav[$page->title] = uri($page->slug);
        }
    }
    return $nav;
} */

/**
 * Define the routes.
 * 
 * @param Zend_Controller_Router_Rewrite
 */
 /*function solrmeka_define_routes($router)
{
  $pages = get_db()->getTable('SimplePagesPage')->findAll();
    foreach($pages as $page) {
        $router->addRoute(
            'simple_pages_show_page_' . $page->id, 
            new Zend_Controller_Router_Route(
                $page->slug, 
                array(
                    'module'       => 'solrsearch', 
                    'controller'   => 'results', 
                    'action'       => 'show', 
                   // 'id'           => $page->id
                )
            )
        );
    }
}*/
