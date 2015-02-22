<?php
/**
  * Admin UI: Title bar/toolbar
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Admin UI: Title bar/toolbar
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */

class uiTitlebar
{
    protected $settings = array(
        'title' => '',
        'backButton' => True,
        'icons' => array ('left' => array(), 'right' => array())
    );

    protected $panthera;

    /**
     * Constructor
     *
     * @return null
     */

    public function __construct($title='')
    {
        global $panthera;
        $this -> panthera = $panthera;

        $this->settings['title'] = $title;
        $panthera -> addOption('template.display', array($this, 'applyToTemplate'), 600);
    }

    /**
      * Set toolbar title
      *
      * @param string $title
      * @return void
      * @author Damian Kęska
      */

    public function setTitle($title)
    {
        $this->settings['title'] = $title;
    }

    /**
      * Enable or disable back button
      *
      * @param bool $value
      * @return void
      * @author Damian Kęska
      */

    public function backButton($value)
    {
        $this->settings['backButton'] = (bool)$value;
    }

    /**
     * Add icons to toolbar
     *
     * @param string $icon Link to image
     * @param string $alignment Left or right
     * @param string $href Optional link (To load a link from template configuration file eg. test.tpl:varName - will use varName_href and varName_onclick)
     * @param string $onclick Optional onclick attribute
     * @param string $iconName Optional icon name ($icon will be taken if not specified)
     * @return mixed
     * @author Damian Kęska
     */

    public function addIcon($icon, $alignment='right', $href='', $onclick='', $iconName='')
    {
        if (strpos($href, ':') !== false and strpos($href, '.tpl') !== false and !$onclick)
        {
            $exp = explode(':', $href);

            if (count($exp) == 2)
            {
                $config = $this -> panthera -> template -> getFileConfig($exp[0]);

                if ($config)
                {
                    $config = (array)$config;

                    if (isset($config[$exp[1]. '_href']))
                        $href = $config[$exp[1]. '_href'];

                    if (isset($config[$exp[1]. '_onclick']))
                        $onclick = $config[$exp[1]. '_onclick'];
                }
            }
        }

        if (!$iconName)
            $iconName = $icon;

        $this->settings['icons'][$alignment][$iconName] = array(
            'image' => pantheraUrl($icon),
            'link' => pantheraUrl($href),
            'onclick' => pantheraUrl($onclick),
        );
    }

    /**
     * Get icon by icon or icon & alignment
     *
     * @param string $icon Icon name
     * @param string $alignment Alignment
     * @return array
     */

    public function getIcon($icon, $alignment='')
    {
        if ($alignment)
        {
            if (isset($this->settings['icons'][$alignment][$icon]))
                return $this->settings['icons'][$alignment][$icon];
        } else {
            foreach ($this->settings['icons'] as $alignment)
            {
                if (isset($this->settings['icons'][$alignment][$icon]))
                    return $this->settings['icons'][$alignment][$icon];
            }
        }
    }

    /**
      * Apply everything to template
      *
      * @hook ui.titlebar.applyToTemplate this, settings, moveToPageTitle
      * @return void
      * @author Damian Kęska
      */

    public function applyToTemplate()
    {
        list($_a, $this->settings, $moveToPageTitle) = $this->panthera->executeFilters('ui.titlebar.applyToTemplate', array($this, $this->settings, True));

        if ($this->settings['title'] and $moveToPageTitle)
        {
            $this->panthera->template->setTitle($this->settings['title']);
        }

        $this->panthera->template->push('uiTitlebar', $this->settings);
    }
}