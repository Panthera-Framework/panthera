<?php
/**
 * Users and groups configuration
 * 
 * @package Panthera\installer
 * @author Damian Kęska
 * @license GNU Lesser General Public License 3, see license.txt
 */

/**
 * Users and groups configuration
 * 
 * @package Panthera\installer
 * @author Damian Kęska
 */

class groupsInstallerControllerSystem extends installerController
{
    /**
     * Main function to display everything
     * 
     * @feature installer.groups null Install additional routes
     * 
     * @author Damian Keska
     * @return null
     */
    
    public function display()
    {
        $this -> getFeature('installer.groups');
        
        // create default groups
        pantheraGroup::create('admin', 'Administration');
        pantheraGroup::create('root', 'Root Administrator');
        pantheraGroup::create('users', 'Users');
        pantheraGroup::create('contentadmin', 'Content Administrator');
        
        $groups = array('admin', 'root', 'users', 'contentadmin');
        
        // add administration permissions to admin group
        $adminGroup = new pantheraGroup('name', 'admin');
        $adminGroup -> acl -> set('admin', true);
        $adminGroup -> acl -> save();
        
        // add root permissions to root group
        $g = new pantheraGroup('name', 'admin');
        $g -> acl -> set('admin', true);
        $g -> acl -> set('superuser', true);
        $g -> acl -> save();
        
        // content administrator
        $g = new pantheraGroup('name', 'contentadmin');
        $g -> acl -> set('admin.settings.site', true);
        $g -> acl -> set('admin.templates', true);
        $g -> acl -> set('admin.settigs.register', true);
        $g -> acl -> set('admin.settings.pager', true);
        $g -> acl -> set('admin.controllers.palogin.settings', true);
        $g -> acl -> set('admin.settings.mce', true);
        $g -> acl -> set('admin.settings.maintenance', true);
        $g -> acl -> set('admin.newsletter', true);
        $g -> acl -> set('admin.settings.facebook', true);
        $g -> acl -> set('admin.dash', true);
        $g -> acl -> set('admin.custompages.settings', true);
        $g -> acl -> set('admin.system.cache', true);
        $g -> acl -> set('admin.systeminfo', true);
        $g -> acl -> set('custompages.management', true);
        $g -> acl -> set('admin.dash.managewidgets', true);
        $g -> acl -> set('admin.databases', true);
        $g -> acl -> set('admin.newsletter.management', true);
        $g -> acl -> set('admin.accesspanel', true);
        $g -> acl -> save();
        
        $this -> installer -> enableNextStep();
        $this -> panthera -> template -> push('spinnerStepMessage', localize('Creating default groups...', 'installer'));
        $this -> panthera -> template -> push('spinnerStepTable', $groups);
        $this -> installer -> template = 'spinnerStep';
    }
}