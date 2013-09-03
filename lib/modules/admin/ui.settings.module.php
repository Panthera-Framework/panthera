<?php
/**
  * Admin UI: Configuration tool
  * 
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Admin UI: Configuration tool
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */
  
class uiSettings
{
    protected $settingsList = array();
    protected $options = array(
        'languageSelector' => False
    );
    protected $panthera;
    
    /**
      * Constructor
      *
      * @param string $section Optional configuration section to load for this configuration page
      * @author Damian Kęska
      */
    
    public function __construct($section='')
    {
        global $panthera;
        $this->panthera = $panthera;
    
        if ($section)
        {
            $panthera -> config -> loadOverlay($section);
        }
        
        $panthera -> add_option('template.display', array($this, 'applyToTemplate'));
    }
    
    /**
      * Add new setting to list
      * Please note that "." character will be replaced with "_-_" because of compatibility with RainTPL template engine
      *
      * @param string $setting Name
      * @return void 
      * @author Damian Kęska
      */
    
    public function add($setting, $label, $validator='', $value=null)
    {
        $fKey = $this->filter($setting);
        
        if ($value == null)
            $value = $this->panthera->config->getKey($setting);
        
        $this->settingsList[$fKey] = array(
            'value' => $value,
            'label' => $label, 
            'validator' => $validator,
            'key' => $setting,
            'customSaveHandler' => null,
            'description' => '',
            'type' => 'string',
            'hide' => false
        );
    }
    
    /**
      * Enable or disable language selector
      *
      * @param bool $value
      * @return void 
      * @author Damian Kęska
      */
    
    public function languageSelector($value=False)
    {
        $this->options['defaultLanguage'] = pantheraLocale::getFromOverride(@$_GET['language']);
        $this->options['languages'] = $this->panthera->locale->getLocales();
        $this->options['languageSelector'] = (bool)$value;
    }
    
    /**
      * Set field type
      *
      * @param string $field
      * @param string $type eg. multipleboolselect, string, select, packaged
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function setFieldType ($field, $type)
    {
        if (!isset($this->settingsList[$this->filter($field)]))
        {
            return false;
        }

        if (!in_array($type, array('string', 'multipleboolselect', 'select', 'packaged')))
        {
            return false;
        }
        
        if ($type == 'packaged')
        {
            $values = $this->panthera -> config -> getKey($field);
            
            foreach ($values as $key => $value)
            {
                if (!$key)
                    continue;
                    
                if (is_array($value))
                {
                    $this -> add ('w_' .$key, $key, '', '');
                    $this->settingsList['w_' .$key]['separator'] = True;
                
                    foreach ($value as $subKey => $subValue)
                    {
                        // field -> key -> subkey
                        $this -> add('__p_' .$field. '__f_' .$key. '__f_' .$subKey, $subKey, '', $subValue);
                        $this -> setDescription('__p_' .$field. '__f_' .$key. '__f_' .$subKey, $key);
                    }
                    
                    continue;
                }
                
                $this->add('__p_' .$field. '__f_' .$key, $key, '', $value);
            }
            
            $this->settingsList[$this->filter($field)]['separator'] = True;
            //$this->settingsList[$this->filter($field)]['hide'] = True;
        }
        
        $this->settingsList[$this->filter($field)]['type'] = $type;
        return True;
    
    }
    
    /**
      * Replace conflicting characters
      *
      * @param string $string
      * @param bool $unFilter
      * @return string 
      * @author Damian Kęska
      */
    
    protected function filter($string, $unFilter=False)
    {
        if ($unFilter)
            return str_ireplace('_-_', '.', $string);
        
        return str_ireplace('.', '_-_', $string);
    }
    
    /**
      * Describe a key
      *
      * @param string $field
      * @param string $description
      * @return bool|null 
      * @author Damian Kęska
      */
    
    public function setDescription($field, $description)
    {
        if (isset($this->settingsList[$this->filter($field)]))
        {
            $this->settingsList[$this->filter($field)]['description'] = $description;
            return True;
        }
    }
    
    /**
      * Handle input variables and save them
      *
      * @param string $input
      * @return bool|string 
      * @author Damian Kęska
      */
    
    public function handleInput($input='')
    {
        // set POST as default input source
        if ($input == '')
        {
            $input = $_POST;
        }
        
        $packaged = array();
        
        //if (isset($input[key($this->settingsList)]))
        //{
            foreach ($input as $key => $value)
            {
                if (!isset($this->settingsList[$key]))
                {
                    continue;
                }
                
                $i++;

                $rKey = $this->filter($key, True);
                
                if ($this->settingsList[$key]['customSaveHandler'])
                {
                    try {
                        $value = $this->settingsList[$key]['customSaveHandler']('save', $rKey, $value);
                    } catch (Exception $e) {
                        return array(
                            'message' => array($e->getCode(), $e -> getMessage()), 
                            'field' => $key, 
                            'fieldTitle' => $this->settingsList[$key]['label']
                        );
                    }
                }
                
                if (is_object($this->settingsList[$key]['validator']))
                {
                    if (!$this->settingsList[$key]['validator'] -> match($value))
                    {
                        return array(
                            'message' => $this->settingsList[$key]['validator'] -> getErrorCode(), 
                            'field' => $key, 
                            'fieldTitle' => $this->settingsList[$key]['label']
                        );
                    }
                }
                
                if ($this->settingsList[$key]['customSaveHandler'])
                {
                    $this->settingsList[$key]['value'] = $this->settingsList[$key]['customSaveHandler']('get', $key, '');
                } else {
                    $this->settingsList[$key]['value'] = $value; // update cache
                }
                
                if (substr($rKey, 0, 4) == '__p_')
                {
                    $exp = explode('__f_', substr($rKey, 4));
                    
                    if (!isset($packaged[$exp[0]]))
                    {
                        $packaged[$exp[0]] = array();
                    }
                    
                    if (is_numeric($value))
                        $value = intval($value);
                        
                    if (count($exp) > 1)
                    {
                        $packaged[$exp[0]][$exp[1]][$exp[2]] = $value;                         
                    } else {
                        $packaged[$exp[0]][$exp[1]] = $value; 
                    }
                    
                    continue;
                }
                
                $this->panthera->config->setKey($rKey, $value);
            }
            
            foreach ($packaged as $field => $values)
            {
                $this -> panthera -> config -> setKey($field, $values);           
            }
            
            if ($i > 0)
            {
                return True;
            }
            
            return False;
        //} else {
        //    return False;
        //}
    }
    
    public function setFieldSaveHandler($field, $callback)
    {
        $fKey = $this->filter($field);
    
        if (isset($this->settingsList[$fKey]))
        {
            $this->settingsList[$fKey]['customSaveHandler'] = $callback;
            $this->settingsList[$fKey]['value'] = $callback('get', $field, '');
            return True;
        }
    }
    
    /**
      * Apply everything to template
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function applyToTemplate()
    {
        $this->panthera->template->push('uiSettings', $this->options);
        $this->panthera->template->push('variables', $this->settingsList);
    }
}

/**
  * Custom field handler - multilanguage
  *
  * @param string $action
  * @param string $key
  * @param mixed $value
  * @package Panthera\adminUI
  * @return mixed 
  * @author Damian Kęska
  */

function uiSettingsMultilanguageField($action, $key, $value='')
{
    global $panthera;
    $language = $panthera -> locale -> getFromOverride($_GET['language']);

    if ($action == 'save')
    {
        $v = $panthera -> config -> getKey($key);
        $v[$language] = $value;
        return $v;
    } else {
        $v = $panthera -> config -> getKey($key);
        return $v[$language];
    }
}

/**
  * Custom field handler - pantheraUrl (automaticaly parses Panthera URLs)
  *
  * @param string $action
  * @param string $key
  * @param mixed $value
  * @package Panthera\adminUI
  * @return mixed 
  * @author Damian Kęska
  */

function uiSettingsPantheraURLField($action, $key, $value)
{
    global $panthera;

    if ($action == 'save')
    {
        return pantheraUrl($value, True);
    }
    
    return pantheraUrl($panthera->config->getKey($key));
}

/**
  * Custom field handler - multiple bool selection field
  *
  * @param string $action
  * @param string $key
  * @param mixed $value
  * @package Panthera\adminUI
  * @return mixed 
  * @author Damian Kęska
  */

function uiSettingsMultipleSelectBoolField($action, $key, $value)
{
    global $panthera;
    
    if ($action == 'save')
    {
        $newValues = array();
        
        foreach ($panthera -> config -> getKey($key) as $key => $val)
        {
            $newValues[$key] = False;
        
            if (in_array($key, $value))
            {
                $newValues[$key] = True;
            }
        }
        
        return $newValues;
    } else {
        return $panthera->config->getKey($key);
    }
}

/**
  * Custom field handler - URL field (with URL validation)
  *
  * @param string $action
  * @param string $key
  * @param mixed $value
  * @package Panthera\adminUI
  * @return mixed 
  * @author Damian Kęska
  */

function uiSettingsURLField($action, $key, $value)
{
    global $panthera;

    if ($action == 'save')
    {
        $ctx = stream_context_create(array( 
            'http' => array( 
                'timeout' => 5 
                ) 
            ) 
        ); 
    
        if (!file_get_contents($value, 0, $ctx))
        {
            throw new Exception('Cannot connect to selected URL, it\'s propably invalid');
        }
        
        return $value;
    }
    
    return $panthera->config->getKey($key);
}

/**
  * Simple integer range validation class
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */

class integerRange
{
    protected $a = 0;
    protected $b = 0;
    protected $errorMsg = '';
    protected $errorID = '';

    public function __construct($a, $b)
    {
        $this -> a = intval($a);
        $this -> b = intval($b);
    }
    
    public function match($value)
    {
        if ($value < $this -> a)
        {
            $this -> triggerError('The value is too small', 'INT_NOT_ENOUGHT');
            return False;
        }
    
        if ($value > $this -> b)
        {
            $this -> triggerError('The value is out of range', 'INT_OUT_OF_RANGE');
            return False;
        }
        
        return True;
    }
    
    protected function triggerError($str, $id)
    {
        $this -> errorMsg = $str;
        $this -> errorID = $id;
    }
    
    public function getErrorCode()
    {
        return array($this->errorID, $this->errorMsg);
    }
}

/**
  * Simple string validation class
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */


class stringHolder extends integerRange
{
    public function match($value)
    {
        if (strlen($value) < $this -> a)
        {
            $this -> triggerError('Input string is too short', 'STR_TOO_SHORT');
            return False;
        }
        
        if (strlen($value) > $this -> b)
        {
            $this -> triggerError('Input string is too long', 'STR_TOO_LONG');
            return False;
        }
    
        return True;
    } 
}
