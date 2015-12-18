<?php
namespace Panthera\Components\Versioning;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\InvalidArgumentException;
use Panthera\Components\Kernel\Framework;
use Symfony\Component\Yaml\Yaml;

class Version
{
    /** @var bool $framework */
    protected $framework = false;

    /**
     * @var string $path Path to configuration file (version.yml)
     */
    protected $path;

    /** @var array $data */
    protected $data = [];

    /**
     * Constructor
     *
     * @param bool $framework
     * @throws FileNotFoundException
     */
    public function __construct($framework = false)
    {
        $this->framework = $framework;
        $this->path = $framework ? PANTHERA_FRAMEWORK_PATH . '/version.yml' : Framework::getInstance()->appPath . '/.content/version.yml';

        if (!is_file($this->path))
        {
            throw new FileNotFoundException('Cannot find version.yml file in /lib/version.yml and in /.content/version.yml', 'NO_VERSION_FILE');
        }

        $this->parse();
    }

    /**
     * Parse configuration file
     */
    protected function parse()
    {
        $contents = file_get_contents($this->path);
        $this->data = Yaml::parse($contents);
    }

    /**
     * @param string $variable
     * @return string|null
     */
    protected function get($variable)
    {
        return isset($this->data[$variable]) ? $this->data[$variable] : null;
    }

    /**
     * @param string $variable
     * @param string|int|float $value
     * @return $this
     */
    protected function set($variable, $value)
    {
        $this->data[$variable] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->path;
    }

    /**
     * Get applications version
     *
     * @param bool $withRelease
     * @return string
     */
    public function getVersion($withRelease = true)
    {
        $version = $this->get('version') ? $this->get('version') : '0.1';

        if (!$withRelease)
        {
            $releasePos = strpos($version, '-');

            if ($releasePos)
            {
                return substr($version, 0, $releasePos);
            }
        }

        return $version;
    }

    /**
     * @return string
     */
    public function getMaturity()
    {
        return (string)$this->get('maturity');
    }

    /**
     * @param string $maturity
     * @throws InvalidArgumentException
     */
    public function setMaturity($maturity)
    {
        if (!in_array($maturity, ['stable', 'dev', 'testing', 'rc', '']))
        {
            throw new InvalidArgumentException('Invalid value for $maturity argument', 'INVALID_ARGUMENT_MATURITY');
        }

        $this->set('maturity', (string)$maturity);
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->set('version', $version);
        return $this;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setVersionTemplate($template)
    {
        $this->set('template', $template);
        return $this;
    }

    /**
     * @return string
     */
    public function getVersionTemplate()
    {
        return (string)$this->get('template');
    }

    /**
     * Release number/string
     *
     * @return string
     */
    public function getRelease()
    {
        $version = $this->getVersion();

        $releasePos = strpos($version, '-');

        if ($releasePos)
        {
            return substr($version, $releasePos + 1);
        }

        return '';
    }

    /**
     * Get data as string
     *
     * @return string
     */
    public function dump()
    {
        return Yaml::dump($this->data);
    }

    /**
     * Save data to version.yml file
     */
    public function save()
    {
        $data = Yaml::dump($this->data);

        $fp = fopen($this->path, 'w');
        fwrite($fp, $data);
        fclose($fp);

        return md5($data) === md5(file_get_contents($this->path));
    }
}