<?php

/**
 * Exception for Diigo class
 */
class DiigoException extends Exception
{
    
}

/**
 * Basic PHP Wrapper for Diigo API.
 *
 * It is writter for a project with its own needs so not all methods or params from API implemented.
 * Can be used as follows:
 * <pre>
 * try {
 *      $diigo = new Diigo($myDiigoUsername, $myDiigoPassword, $myDiigoKey);
 *      $response = json_decode($diigo->getAllBookMarks(100, 'all'), true);
 * } catch (DiigoException $exc) {
 *      // your code here to handle exceptions from Diigo library
 * } catch (Exception $exc) {
 *      //your code here to handle "Diigo librabry not found" exception or any other general exception
 * }
 * </pre>
 * @link http://www.diigo.com/api_dev/docs
 * @author Sergio G  https://github.com/bearwebua/
 * @version 0.1
 */
class Diigo
{

    /**
     * @var string username to access API
     */
    protected $username;

    /**
     * @var string password to access API
     * @access protected
     */
    protected $password;

    /**
     * @var string api key to access API
     * @access protected
     */
    protected $apiKey;

    /**
     * @var string URL array for API
     * @access protected
     */
    protected $apiUrl = 'https://secure.diigo.com/api/v2/';

    /**
     * @var array options array
     * @access public
     */
    public $options = array(
        'useSSL' => true, // default TRUE
        'certificatePath' => null, // default NULL
        'timeOut' => 10,
        'userAgent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)',
    );

    /**
     * Getter for $apiUrl property
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Constructor.
     * Validates data and set properties to access API
     * @param string $username - Diigo username
     * @param string $password Diigo user password
     * @param string $apiKey - your application API key {@link http://www.diigo.com/api_dev/docs#section-key}
     * @throws DiigoException if CURL not installed or username/password/apiKey are empty
     */
    public function __construct($username, $password, $apiKey)
    {

        if (!function_exists("curl_init")){
            throw new DiigoException('Please install cUrl for PHP', 501);
        }
        if ((empty($username)) || (empty($password)) || (empty($apiKey))){
            throw new DiigoException('Please provide complete details for class initialization', 500);
        }
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;
    }

    /**
     * Get bookmarks for a user.
     * @link: http://www.diigo.com/api_dev/docs#section-methods
     * @param integer $limit - how many bookmarks to return [MAX]
     * @param string $filter - filter to use. Default to NULL which means that only public bookmarks will be returned.
     * List of values can be found in API ocumentation. For now there are values such: "all", "public"
     * @return string - json string with request results
     * @throws DiigoException - if there was an error with request
     */
    public function getAllBookMarks($limit = 10, $filter = null)
    {
        $suffix = '';
        if (!is_null($filter)){
            $suffix = '&filter=' . $filter;
        }
        //@todo: - implement additional params API accepts like: list , tags, sort, etc
        $url = $this->apiUrl . 'bookmarks' . '?key=' . $this->apiKey . '&user=' . $this->username . '&count=' . $limit . $suffix;
        return $this->httpRequest($url);
    }

    /**
     * Update/create bookmark.
     * @param string $url - url of new/current bookmark [required, length: 1-250]
     * @param string $title - the title of the bookmark [required, length: 1-250]
     * @param array $optionalData - assoc. array with params to API request [optional].
     * See list of all possible values in API documentation here: @link http://www.diigo.com/api_dev/docs -> "Save bookmark" section.
     * 
     * Can be as follows:
     * <pre>
     * $optionalData = array('shared'=>'yes', 'tags'=>'links, data, badaboom', 'desc'=>'dummy description', 'readLater'=>'no');
     * </pre>
     * @return string - json string with request results
     * @throws DiigoException - if there was an error with request or validation
     */
    public function updateBookMark($url, $title, array $optionalData)
    {

        $this->paramsValidate($url, $title, true);
        $requestArray = array('url' => $url, 'title' => $title,);

        $bookmarkSaveParameters = array('shared', 'tags', 'desc', 'readLater');

        foreach ($optionalData as $key => $option) {
            //we go accross all elements in $optionalData and check if optional data is present. If yes -> we validate it
            if (in_array($key, $bookmarkSaveParameters)) {

                if (($key === 'shared') || ($key === 'readLater')) {
                    if (in_array($option, array('yes', 'no'))) {
                        $requestArray[$key] = $option; // validate it on your own if you need in your client code
                    } else {
                        $requestArray[$key] = 'no';
                    }
                } elseif ($key === 'tags') {
                    $temp = implode(',', explode(',', $option));
                    if (!empty($temp)) {
                        $requestArray[$key] = $option; // validate it on your own if you need in your client code
                    }
                } else {
                    $requestArray[$key] = $option; // validate it on your own if you need in your client code
                }
                $requestArray[$key] = substr($requestArray[$key], 0, 250); //final validation of all fields
            }
        }
        return $this->httpRequest($this->apiUrl . 'bookmarks' . '?key=' . $this->apiKey, $requestArray, 'PUT');
    }

    /**
     * Delete bookmark
     * @param string $url - url of current bookmark [required, length: 1-250]
     * @param string $title - the title of the bookmark [required, length: 1-250]
     * @return string - json string with request results
     * @throws DiigoException - if there was an error with request or validation
     */
    public function deleteBookMark($url, $title)
    {
        $this->paramsValidate($url, $title, true);
        $requestArray = array('url' => $url, 'title' => $title);
        return $this->httpRequest($this->apiUrl . 'bookmarks' . '?key=' . $this->apiKey, $requestArray, 'DELETE');
    }

    /**
     * Validates $url and $title params to be used in POST request to Diigo API
     * @param string $url
     * @param string $title
     * @param boolean $exception
     * @return boolean - if params invlalid and option to return boolean response is set will return FALSE
     * @throws DiigoException - if params invalid and option to throw exception is set
     */
    protected function paramsValidate($url, $title, $exception = true)
    {
        if ((strlen($url) < 2) || (strlen($title) < 2)) {
            if ($exception) {
                throw new DiigoException('Title or URL is too short', 500);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Makes HTTP(S) request to selected URL
     * @param string $url - URL to call
     * @param array $params - associative array with key => value pairs of additional request params.
     * @param string $request - method to execute. Default is "GET".
     * @param integer $rPause - forces each requests to sleep for $rPause seconds before being executed. By default not used.
     * @return string - json string on success request (@link: http://www.diigo.com/api_dev/docs )
     * @throws DiigoException - on error request
     */
    protected function httpRequest($url, $params = null, $request = 'GET', $rPause = 0)
    {

        if (($rPause !== 0) && (is_numeric($rPause))) {
            sleep($rPause); // to avoid being banned (just in case of many requests)
        }
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->options['timeOut']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->options['timeOut']);

        if ($this->options['useSSL'] === true) {
            if ((!is_null($this->options['certificatePath'])) && (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->options['certificatePath']))) {
                curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->options['certificatePath']);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

        if ((!is_null($params)) && (is_array($params))) {
            //curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->preparePostFields($params));
        }

        curl_setopt($ch, CURLOPT_USERAGENT, $this->options['userAgent']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);

        $data = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpStatusCode !== 200) {
            if ((empty($data) || (!is_string($data)))) {
                $data = 'Unknown Diigo network API error';
            }
            throw new DiigoException($data, $httpStatusCode);
        }

        return $data;
    }

    /**
     * Makes a string to be used as URL params from an array
     * @param array $array - associative array with key => value pairs
     * @return string - urlencoded string
     */
    protected function preparePostFields($array)
    {
        $params = array();
        foreach ($array as $key => $value) {
            $ret = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value));
            $params[] = urlencode($key) . '=' . urlencode($ret);
        }
        return implode('&', $params);
    }

}
