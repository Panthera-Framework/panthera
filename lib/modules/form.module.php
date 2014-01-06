<?php
/**
  * User registration module
  *
  * @package Panthera\modules\userregistration
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

abstract class validableForm
{
    protected $panthera;
    protected $forceEnable = False;
    protected $source = array();
    public $disabledFields = array(); // eg. array('__register_login')
    public $fieldsSettings = array();
    public $formName = 'form';
    public $formTemplateDisabled = 'formTemplate.closed.tpl';
    public $formTemplateEnabled = 'formTemplate.tpl'; 
    protected $fieldsList = array(); // eg. array('login', 'password', 'mail')
    
    /*
     * Default constructor
     * 
     * @param array $source Source array eg. $_POST
     * @param array $forceEnable Force enable form even if it's disabled via config or something other (see: formEnabled() method)
     */
    
    public function __construct($source=Null, $forceEnable=False)
    {
        global $panthera;
        $this -> panthera = $panthera;
        $this -> forceEnable = (bool)$forceEnable;
        
        if ($source === Null)
        {
            $source = $_POST;
        }

        // clear form prefix from all fields        
        $newSource = array();
        foreach ($source as $field => $value)
            $newSource[str_replace('__' .$this->formName. '_', '', $field)] = $value;
        
        $this -> source = $newSource;
    }

    /*
     * Is user posting a form now?
     * 
     * @return bool
     */
    
    public function isPostingAForm()
    {
        if (isset($this->source['submit']))
        {
            return True;
        }
    }
    
    /*
     * This function is to be extended, it's doing nothing
     * 
     * Note: After forking this function please execute it's parent by using parent::validateForm() at the end of forked function
     * Useful functions for form validation: strip_tags, filter_var, trim, ltrim, rtrim, htmlspecialchars
     * 
     * @return bool|array Returns False or Array when there is any error, True when form was validated correctly
     */
    
    protected function _processFormValidation()
    {
        return True;
    }
    
    /*
     * Don't touch this function as it's submiting validation results to template, please take a look at _processFormValidation() method
     * 
     * @return bool|array
     */
    
    public function validateForm()
    {
        if (!$this->forceEnable and !$this->formEnabled())
        {
            $this -> panthera -> logging -> output('This form is disabled, cannot validate', 'form');
            return False;
        }
        
        $this -> panthera -> logging -> setTimer();
        $result = $this -> _processFormValidation();
        $this -> panthera -> logging -> output('Standard validation finished, results: ' .json_encode($result), 'form');
        
        // additional fields
        $additionalFields = $this->validateAdditionalFields();
        
        if (!$additionalFields or is_array($additionalFields))
        {
            $this -> panthera -> template -> push('formValidation', $additionalFields);
            return $additionalFields;
        }
        
        $this -> panthera -> logging -> output('Scanning for validation methods eg. _processField_password', 'register');
        
        // generic fields validation
        foreach ($this->fieldsList as $field)
        {
            $methodName = '_processField_' .$field;
            
            if (method_exists($this, $methodName))
            {
                $r = $this->$methodName();
                
                if ($r !== True and $r !== Null)
                {
                    return $r;
                }
            }
        }
        
        $this -> panthera -> template -> push('formValidation', $result);
        return $result;
    }
    
    /*
     * Dummy function to be forked
     * 
     * @return bool
     */
    
    public function validateAdditionalFields()
    {
        return True;
    }
    
    /*
     * Check if form is enabled, here can be a simple configuration check placed
     * 
     * @return bool
     */
    
    public function formEnabled()
    {
        return False; // can be eg. (bool)$this -> panthera -> config -> getKey('register.open', 0, 'bool')
    }
    
    /*
     * Display a form template
     * 
     * @return null
     */
    
    public function displayForm()
    {
        if (!$this->forceEnable and !$this->formEnabled())
        {
            $this -> panthera -> logging -> output('This form is disabled, cannot display', 'form');
            $this -> panthera -> template -> display($this->formTemplateDisabled); // template for disabled form
            pa_exit();
        }
        
        $this -> panthera -> logging -> output('Displaying form, name=' .$this->formName, 'form');
        $this -> panthera -> template -> push('disabledFields', $this->disabledFields);
        $this -> panthera -> template -> push('registrationFields', $this->source);
        $this -> panthera -> template -> display($this->formTemplateEnabled);
        pa_exit();
    }
}