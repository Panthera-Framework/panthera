<?php
/**
 * Upload and gallery configuration step
 *
 * @package Panthera\installer
 * @author Damian Kęska
 * @license GNU Lesser General Public License 3, see license.txt
 */

/**
 * Upload and gallery configuration step
 *
 * @package Panthera\installer
 * @author Damian Kęska
 */

class uploadInstallerControllerSystem extends installerController
{
    /**
     * Main function to display everything
     *
     * @feature installer.upload null Install additional routes
     *
     * @author Damian Keska
     * @return null
     */

    public function display()
    {
        $this -> panthera -> importModule('filesystem');

        // generate table log
        $log = array();
        $default = $this -> panthera -> config -> getKey('upload.default.category', 'default', 'string', 'upload');

        $categories = array(
            'gallery' => array(
                'mimes' => 'image',
                'maxfilesize' => 1024*1024*6, // 6 mb
                'title' => array('Gallery', 'gallery'),
            ),

            'avatars' => array(
                'mimes' => 'image',
                'maxfilesize' => 1024*1024*1, // 1 mb
                'title' => array('Avatars', 'avatars'),
            ),

            'default' => array(
                'mimes' => 'all',
                'maxfilesize' => 0, // every size should fit
                'title' => array('Default category', 'upload'),
            ),
        );

        foreach ($categories as $name => $category)
        {
            $test = new uploadCategory('name', $name);

            // skip creating category that already exists
            if ($test -> exists())
                continue;

            $log[] = array(
                localize($category['title'][0], $category['title'][1]),
                $category['mimes'],
                filesystem::bytesToSize($category['maxfilesize']),
            );

            pantheraUpload::createUploadCategory($name, 1, $category['mimes'], serialize($category['title']), $category['maxfilesize'], true);
        }

        $this -> getFeature('installer.upload');

        $this -> installer -> enableNextStep();
        $this -> panthera -> template -> push('spinnerStepMessage', localize('Preconfiguring upload and gallery modules...', 'installer'));
        $this -> panthera -> template -> push('spinnerStepTable', $log);
        $this -> installer -> template = 'spinnerStep';
    }
}