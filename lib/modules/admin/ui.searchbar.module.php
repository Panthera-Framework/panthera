<?php
/**
  * Admin UI: Search bar
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Admin UI: Search bar
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */

class uiSearchbar
{
    protected static $searchBars;
    protected $barName = "";

    /**
     * Constructor
     *
     * @param string $barName Search bar name that will be used to identify this searchbar in template
     * @return null :D
     */

    public function __construct ($barName)
    {
        global $panthera;
        $this -> panthera = $panthera;
        $this -> barName = $barName;
        self::$searchBars[$this->barName] = array(
            'icons' => array(),
            'settings' => array(),
            'formAction' => '?' .getQueryString('GET', '', '_,query'),
            'formMethod' => 'GET',
            'navigate' => False,
            'query' => @$_GET['query']
        );

        $panthera -> add_option('template.display', array($this, 'applyToTemplate'));
    }

    /**
     * List all filter values (user input)
     *
     * @return array Array with list of entered values
     */

    public function getFilters()
    {
        $sBar = self::$searchBars[$this->barName];
        $array = $_GET;
        $output = array();

        if ($sBar['formMethod'] == 'POST')
        {
            $array = $_POST;
        }

        foreach (self::$searchBars[$this->barName]['settings'] as $id => $values)
        {
            if (isset($array[$id]))
            {
                $output[$id] = $array[$id];
            }
        }

        return $output;
    }

    /**
     * Sort array in SQL-like method
     *
     * @param array $array Input array to be sorted
     * @param string $column Column to order by
     * @param string $direction Optional direction - ASC or DESC
     * @param string $sortingFunction Optional sorting function name, asort by default
     */

    public function orderBy($array, $column, $direction='ASC', $sortingFunction='asort')
    {
        $sorter=array();
        $ret=array();

        reset($array);

        foreach ($array as $ii => $va)
        {
            $sorter[$ii]=$va[$column];
        }

        $sortingFunction($sorter);

        foreach ($sorter as $ii => $va)
        {
            $ret[$ii]=$array[$ii];
        }

        $array = $ret;

        if (strtolower($direction) == 'desc')
        {
            $array = array_reverse($array);
        }

        return $array;
    }

    /**
     * Search in array for specified keywords
     *
     * @param array $inputData Two dimensional array with data
     * @param string $queryString Query string
     * @param array $fields Optional list of fields to perform search on
     */

    public function filterData($inputData, $queryString, $fields=null)
    {
        foreach ($inputData as $rowID => $data)
        {
            $found = False;

            foreach ($data as $key => $value)
            {
                $cols++;

                if ($fields)
                {
                    if (!in_array($key, $fields))
                        continue;
                }

                if (!is_string($value))
                    $value = print_r_html($value, true);

                if (strpos($value, $queryString) !== False)
                {
                    $found = True;
                }
            }

            if (!$found)
                unset($inputData[$rowID]);
        }

        return $inputData;
    }

    /**
     * Get current query string
     *
     * @return string Current query string
     */

    public function getQuery()
    {
        if (self::$searchBars[$this->barName]['method'] == 'POST')
        {
            self::$searchBars[$this->barName]['query'] = $_POST['query'];
        } else {
            self::$searchBars[$this->barName]['query'] = $_GET['query'];
        }

        return self::$searchBars[$this->barName]['query'];
    }

    /**
      * Add an icon to icons bar
      *
      * @param string $src Image address (full), {$PANTHERA_URL} can be used here
      * @param string $link Link address
      * @param string $popup Link to open in a popup window
      * @param string $alt Alternative text to insert int alt attribute
      * @return mixed
      * @author Damian Kęska
      */

    public function addIcon($src, $link=False, $popup=False, $alt=False)
    {
        $icon = array('icon' => $src);

        if ($link)
            $icon['link'] = pantheraUrl($link);

        if ($popup)
            $icon['popup'] = $popup;

        if ($popup)
            $icon['popup'] = $popup;

        if ($alt)
            $icon['alt'] = $alt;

        self::$searchBars[$this->barName]['icons'][] = pantheraUrl($icon);
        return True;
    }

    /**
      * Description of a function
      *
      * @param string $id This will be used to identify setting, also a name of POST parameter
      * @param string $title Title to show user
      * @param string $type Can be a checkbox, text or select
      * @param string $value Default value
      * @param bool $active
      * @return mixed
      * @author Damian Kęska
      */

    public function addSetting($id, $title, $type, $value, $active=False)
    {
        $type = strtolower($type); // avoid mistakes

        if (!in_array($type, array('checkbox', 'text', 'select')))
        {
            throw new Exception('Unsupported setting type "' .$type. '" in uiSearchbar id="' .$this->barName. '"', 8795);
        }

        $setting = array(
            'id' => $id,
            'title' => $title,
            'type' => $type,
            'value' => $value,
            'active' => (bool)$active,
        );

        self::$searchBars[$this->barName]['settings'][$id] = $setting;

        return True;
    }

    /**
     * Return list of settings fields
     *
     * @return array Array with settings, every row should contain id, title, type, value, active fields
     */

    public function getSettings()
    {
        return self::$searchBars[$this->barName]['settings'];
    }

    /**
      * Set query string in text field
      *
      * @param string $query string
      * @return void
      * @author Damian Kęska
      */

    public function setQuery($query)
    {
        self::$searchBars[$this->barName]['query'] = $query;
    }

    /**
      * Set HTTP method
      *
      * @param string $method Can be GET, POST or HEAD
      * @return void
      * @author Damian Kęska
      */

    public function setMethod($method)
    {
        if (!in_array($method, array('GET', 'POST', 'HEAD')))
        {
            throw new Exception('Unsupported method type "' .$method. '" in uiSearchBar id="' .$this->barName. '"');
        }

        self::$searchBars[$this->barName]['formMethod'] = $method;
    }

    /**
      * Set HTTP address
      *
      * @param string $address Address to destination script, accepts Panthera URLs
      * @return void
      * @author Damian Kęska
      */

    public function setAddress($address)
    {
        self::$searchBars[$this->barName]['formAction'] = pantheraUrl($address);
    }

    /**
      * Navigate to that page instead of sending ajax request
      *
      * @param bool $value
      * @return void
      * @author Damian Kęska
      */

    public function navigate($value)
    {
        self::$searchBars[$this->barName]['navigate'] = (bool)$value;
    }

    /**
      * Send all search bars to template
      *
      * @return void
      * @author Damian Kęska
      */

    public static function applyToTemplate()
    {
        global $panthera;
        if ($panthera -> logging -> debug)
            $panthera -> logging -> output ('Adding ui.Searchbars to template: ' .json_encode(self::$searchBars), 'pantheraAdminUI');

        $panthera -> template -> push('uiSearchbars', self::$searchBars);
    }
}