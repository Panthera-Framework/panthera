<?php
namespace Panthera\Components\Controller;
use Panthera\Classes\BaseExceptions\ValidationException;
use Panthera\Components\Validator\Validator;

/**
 * Request handler
 *
 * @package Panthera\Components\Controller
 */
class Request
{
    /** @var array $params */
    protected $params = [];

    /** @var array $get */
    protected $get = [];

    /** @var array $post */
    protected $post = [];

    /**
     * @param array $params
     * @param array $get
     * @param array $post
     */
    public function __construct($params, $get, $post)
    {
        $this->params = (array)$params;
        $this->get    = (array)$get;
        $this->post   = (array)$post;
    }

    /**
     * Querystring parameters getter
     *
     * @param string|null $parameter
     * @param string|null $validator
     *
     * @return string|int|float|array
     */
    public function get($parameter = null, $validator = null)
    {
        return $this->getKey($parameter, 'get', $validator);
    }

    /**
     * Returns parameter from POST request
     *
     * @param string|null $parameter
     * @param string|null $validator
     *
     * @return string|int|float|array
     */
    public function post($parameter = null, $validator = null)
    {
        return $this->getKey($parameter, 'post', $validator);
    }

    /**
     * Route query string parameters getter
     *
     * @param string|null $parameter
     * @param string|null $validator
     *
     * @return string|int|float|array
     */
    public function params($parameter = null, $validator = null)
    {
        return $this->getKey($parameter, 'params', $validator);
    }

    /**
     * @param string $parameter
     * @param string $array Array name
     * @param string $validatorString
     *
     * @return string|int|float|array
     */
    protected function getKey($parameter, $array, $validatorString)
    {
        if ($parameter === null)
        {
            return $this->$array;
        }

        $arr = $this->$array;

        if (isset($arr[$parameter]))
        {
            if ($validatorString)
            {
                $validation = Validator::validate($arr[$parameter], $validatorString);

                if (is_string($validation))
                {
                    throw new ValidationException('Parameter "' . $parameter . '" validation failed, reason: ' . $validation, 'REQUEST_VARIABLE_VALIDATION_FAILED');
                }
            }

            return $arr[$parameter];
        }
    }
}