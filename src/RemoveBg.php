<?php

namespace Mtownsend\RemoveBg;

use Exception;
use GuzzleHttp\Client as Guzzle;

/**
 * @author Mark Townsend
 *
 */
class RemoveBg
{
    /**
     * Your remove.bg api key
     *
     * @var string
     */
    public $apiKey;

    /**
     * The remove.bg url endpoint used for the request
     *
     * @var string
     */
    public $endpoint;

    /**
     * The name the image should be returned as
     *
     * @var string
     */
    public $fileName;

    /**
     * The request headers
     * @var array
     */
    public $headers;

    /**
     * The remove.bg image format type
     * @var string
     */
    public $imageFormat;

    /**
     * The url, base64 encoded, or raw image
     *
     * @var string
     */
    public $payload;

    /**
     * A string used internally to specify the format the image is returned as
     *
     * @var string
     */
    public $returnFormat;

    /**
     * The api response
     *
     * @var GuzzleHttp\Psr7\Response
     */
    public $response;

    /**
     * The Guzzle request options
     *
     * @var array
     */
    public $requestOptions;

    /**
     * Instantiate the RemoveBg class
     *
     * @param string $apiKey Your remove.bg api key
     * @param array  $headers Request http headers
     */
    public function __construct($apiKey = '', $headers = [])
    {
        $this->apiKey = $apiKey;
        $this->returnFormat = 'raw';
        $this->endpoint = 'https://api.remove.bg/v1.0/removebg';
        $this->headers = $headers;
        $this->requestOptions = [
            'http_errors' => false,
            'decode_content' => false
        ];
        $this->setApiKey();
    }

    /**
     * Accepts a base64 encoded image string
     * and utilizes remove.bg's image_file_b64 feature
     *
     * @param  string $base64 Base64 encoded image
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    public function base64(string $base64)
    {
        $this->imageFormat = 'image_file_b64';
        $this->payload = $base64;
        return $this;
    }

    /**
     * Return the fully qualified remove.bg url
     * to which an api request will be sent
     *
     * @return string Fully qualified remove.bg url
     */
    protected function endpoint()
    {
        switch ($this->returnFormat) {
            case 'raw':
                break;
            case 'base64':
                $this->header('Accept', 'application/json');
                break;
        }
        return $this->endpoint;
    }

    /**
     * Accepts a path to an image file
     * and utilizes remove.bg's image_file feature
     *
     * @param  string $file Path to image
     * @param  string $name The file name
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    public function file(string $file, $name = '')
    {
        $this->imageFormat = 'image_file';
        $this->payload = $file;
        $this->fileName = empty($name) ? basename($file) : $name;
        return $this;
    }

    /**
     * Format the file name to be .png file extension
     * because remove.bg only returns .png images
     *
     * @param  string $filename The file's name
     * @return string
     */
    protected function formatFileName($filename)
    {
        return substr($filename, 0, strpos($filename, '.')) . '.png';
    }

    /**
     * Formats the request into the acceptable
     * format for the remove.bg api
     *
     * @return array
     */
    protected function formatPayload()
    {
        switch ($this->imageFormat) {
            case 'image_url':
                return ['form_params' => [$this->imageFormat => $this->payload]];
                break;
            case 'image_file':
                return ['multipart' => [[
                    'name' => 'image_file',
                    'contents' => file_get_contents($this->payload),
                    'filename' => $this->formatFileName($this->fileName)
                ]]];
                break;
            case 'image_file_b64':
                return ['form_params' => [$this->imageFormat => $this->payload]];
                break;
        }
    }

    /**
     * Send the api request and get the result as a string
     *
     * @return string Base64 or raw image, depending on the return format
     */
    public function get()
    {
        $this->send();
        return $this->getImageContents();
    }

    /**
     * Get the image as base64
     *
     * @return string Base64 encoded image
     */
    public function getBase64()
    {
        $this->returnFormat = 'base64';
        return $this->get();
    }

    /**
     * Get the base64 encoded image from
     * remove.bg's api response
     *
     * @return string Base64 encoded image string
     */
    protected function getBase64Image()
    {
        $response = json_decode((string) $this->response->getBody(), true);
        return $response['data']['result_b64'];
    }

    /**
     * Get the image contents
     *
     * @return string Raw or base64 image contents
     */
    protected function getImageContents()
    {
        switch ($this->returnFormat) {
            case 'raw':
                return $this->getRawImage();
                break;
            case 'base64':
                return $this->getBase64Image();
                break;
        }
    }

    /**
     * Get the raw image string from
     * remove.bg's api response
     *
     * @return strong Raw image string
     */
    protected function getRawImage()
    {
        return (string) $this->response->getBody();
    }

    /**
     * Determine if the response contained errors
     *
     * @return bool true for error, false for successful
     */
    protected function hasErrors()
    {
        return substr($this->response->getStatusCode(), 0, 1) == 4 ? true : false;
    }

    /**
     * Set a single api request header
     *
     * @param  string $key The header key
     * @param  string $value The header value
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    public function header(string $key, string $value)
    {
        $this->headers([$key => $value]);
        return $this;
    }

    /**
     * Set the api request's headers
     *
     * @param  array  $headers An associative array of headers
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    public function headers(array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set request options for the api request
     *
     * @param  array  $options
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    public function requestOptions(array $options)
    {
        $this->requestOptions = array_merge($this->requestOptions, $options);
        return $this;
    }

    /**
     * Save the image to the file system
     *
     * @param  string  $path
     * @param  bool  $lock
     * @return int
     */
    public function save($path, $lock = false)
    {
        $this->send();
        return file_put_contents($path, $this->getImageContents(), $lock ? LOCK_EX : 0);
    }

    /**
     * Set the remove.bg api key as a header
     *
     * @return void
     */
    protected function setApiKey()
    {
        if (empty($this->apiKey)) {
            throw new Exception('You must supply a valid remove.bg API key');
        }
        $this->headers(['X-API-Key' => $this->apiKey]);
    }

    /**
     * Send the api request
     *
     * @return void
     */
    protected function send()
    {
        $request = new Guzzle();
        $this->response = $request->request('POST', $this->endpoint(), array_merge(
            $this->requestOptions,
            ['headers' => $this->headers],
            $this->formatPayload()
        ));

        if ($this->hasErrors()) {
            return $this->throwApiException();
        }
    }

    /**
     * Throw an Exception with the error text
     * provided by the remove.bg api
     *
     * @return Exception
     */
    protected function throwApiException()
    {
        if (is_object($this->response) && method_exists($this->response, 'getResponse')) {
            $response = json_decode((string) $this->response->getResponse()->getBody(), true);
        } elseif (is_object($this->response) && method_exists($this->response, 'getBody')) {
            $response = json_decode((string) $this->response->getBody(), true);
        } else {
            $response = json_decode($this->response, true);
        }
        if (!isset($response['errors'])) {
            throw new Exception('Unprocessable error');
        }
        throw new Exception(implode(' - ', reset($response['errors'])));
    }

    /**
     * Accepts a fully qualified image url
     * and utilizes remove.bg's image_url feature
     *
     * @param  string $url Fully qualfied image url
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    public function url(string $url)
    {
        $this->imageFormat = 'image_url';
        $this->payload = $url;
        return $this;
    }
}
