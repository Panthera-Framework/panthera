<?php
/**
 * Get list of all changed files and export to file for comparsion
 *
 * @package Panthera\core\adminUI\debug\syschecksum
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Get list of all changed files and export to file for comparsion
 *
 * @package Panthera\core\adminUI\debug\syschecksum
 * @author Damian Kęska
 */

class syschecksumAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.syschecksum' => array('Checksum of system files'),
    );

    protected $requirements = array(
        'filesystem',
    );

    protected $uiTitlebar = array(
        'Checksum of system files'
    );

    protected $files = array();

    /**
     * Get list of files and directories
     *
     * @return array
     */

    protected function getFiles()
    {
        return filesystem::scandirDeeply(SITE_DIR);
    }

    /**
     * Import uploaded file
     *
     * @param string $file Input file path
     * @param bool $unlink Remove file after read
     * @return array
     */

    protected function importUploadedFile($file, $unlink=True)
    {
        $array = array();

        if(is_file($file))
        {
            if (defined('JSON_PRETTY_PRINT'))
                $array = @json_decode(file_get_contents($file), JSON_PRETTY_PRINT);
            else
                $array = @json_decode(file_get_contents($file));

            if ($unlink)
                @unlink($file);
        }

        return $array;
    }

    /**
     * Export data to file
     *
     * @return null
     */

    protected function exportFile($filesTpl)
    {
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="syschecksum.json"');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        print(json_encode(array(
            'panthera_checksum' => $filesTpl,
        )));

        pa_exit();
    }

    /**
     * Prepare data to be displayed
     *
     * @return array
     */

    public function prepareData()
    {
        $this -> files = $this -> getFiles();
        $filesTpl = array();
        $array = '';

        if(isset($_FILES['syschecksum']))
        {
            $array = $this -> importUploadedFile($_FILES['syschecksum']['tmp_name']);
        }

        foreach ($this->files as $file)
        {
            if(!is_file($file))
                continue;

            $contents = file_get_contents($file);
            $nameSum = md5(str_replace(SITE_DIR, '', $file));
            $sum = md5($contents);
            $bold = False;

            if (is_array($array))
            {
                // check if remote server has the file
                if (isset($array["panthera_checksum"][$nameSum]))
                {
                    // the file has diffirences
                    if ($_POST['method'] == 'sum')
                    {
                        // checksum method
                        if($array["panthera_checksum"][$nameSum]['sum'] != $sum)
                            $bold = True;

                    } elseif ($_POST['method'] == 'time') {
                        // by modification time
                        if($array["panthera_checksum"][$nameSum]['mtime'] != filemtime($file))
                            $bold = True;

                    } else {
                        // checking by file size
                        if($array["panthera_checksum"][$nameSum]['size_bytes'] != strlen($contents))
                            $bold = True;

                    }

                    if (@$_POST['show_only_modified'] == "1")
                    {
                        if ($bold == False)
                           continue;
                    }
                }
            }

            $filesTpl[$nameSum] = array(
                'name' => str_replace(SITE_DIR, '', $file),
                'sum' => $sum,
                'size' => filesystem::bytesToSize(strlen($contents)),
                'time' => date($panthera -> dateFormat, filemtime($file)),
                'mtime' => filemtime($file),
                'bold' => $bold,
                'size_bytes' => strlen($contents),
            );
        }

        // compare results
        if (is_array($array))
        {
            foreach ($array['panthera_checksum'] as $file)
            {
                $filesTpl[$nameSum] = array(
                    'name' => $file['name'],
                    'sum' => $file['sum'],
                    'size' => $file['size'],
                    'time' => $file['time'],
                    'mtime' => $file['mtime'],
                    'bold' => True,
                    'size_bytes' => $file['size_bytes'],
                    'created' => True,
                );
            }
        }

        if (isset($_GET['export']))
        {
            $this -> exportFile($filesTpl);
        }

        return $filesTpl;
    }

    /**
     * Main function
     *
     * @return null
     */

    public function display()
    {
        // requirements
        $this -> panthera -> locale -> loadDomain('debug');
        $filesTpl = $this -> prepareData();

        $this -> panthera -> template -> push('files', $filesTpl);
        return $this -> panthera -> template -> compile('syschecksum.tpl');
    }
}