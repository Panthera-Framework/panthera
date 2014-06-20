<?php
/**
 * Introduction step
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Introduction step
 * 
 * If you want to display a license text in your application installer this step is created to bring this functionality out of the box
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 */

class introductionInstallerControllerSystem extends installerController
{
    // licence.required - use $_POST['licence_agree'] field with value "1" in HTML form
    protected $config = array(
        'licence.required' => array(
            'value' => FALSE, 'type' => 'bool', 'description' => array('Requires user to accept licence or just mark checkbox', 'installer'),
        ),
    );

    /**
     * Main function to display everything
     *
     * @author Damian Keska
     * @return null
     */

    public function display()
    {
        if (!$this->config['licence.required'])
            $this -> installer -> enableNextStep();
        else {
            if (isset($_POST['licence_agree']))
            {
                if (intval($_POST['licence_agree']) === 1)
                {
                    $this -> installer -> enableNextStep();
                    $this -> installer -> goToNextStep();
                }
            } else {
                $this -> panthera -> template -> push('licenceNotAccepted', True);
            }
        }

        //$this -> installer -> setButton('back', False);

        // look for installer/licence.txt file to get licence text from
        if (getContentDir('installer/licence.txt'))
            $this -> panthera -> template -> push('licenceText', file_get_contents(getContentDir('installer/licence.txt')));

        $this -> installer -> template = 'introduction';
    }
}