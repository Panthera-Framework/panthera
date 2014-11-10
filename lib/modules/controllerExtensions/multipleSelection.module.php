<?php
/**
 * This class implements multiple selection actions
 *
 * eg. In controllers you can enable multiple users selection and button to delete them all.
 * On PHP side add a simple definition to $mSelectionFields and a callback to run "delete" action.
 * Then place for example $this -> handleMSelectionSubmit('users', 'delete'); before data fetching from database in a controller
 *
 * @package Panthera\core\system\controllers
 * @author Damian Kęska
 */

trait multipleSelectionControllerExtension
{
    /**
     * List of available fields
     *
     * @var array $mSelectionFields
     *
     * Example:
     * [
     *     'users' => [
     *         'className' => 'pantheraUser', // use pantheraUser class of pantheraFetchDB type
     *         'idField' => 'id', // identify by `id` column from database,
     *         'useIterator' => true, // use mSelectionIterator to iterate over array, build object and then call a callback function on single object
     *     ];
     * ];
     */

    //protected $mSelectionFields = [

    //];

    /**
     * This function should be called from controller's code before collecting and displaying results data (eg. list of users)
     * It handles validation and callbacks for executing an action on multiple elements
     *
     * @param string $fieldName Field name eg. 'users'
     * @param string $action Action name eg. delete, moveToGroup, setAsActive, unban
     * @param array $options (Optional) Additional options passed to end callback that handles executing action and returning results
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return int Count of affected objects
     */

    public function handleMSelectionSubmit($fieldName, $action, $options=array())
    {
        /**
         * Validate input
         */

        if (!isset($this->mSelectionFields[$fieldName]))
            throw new UnexpectedValueException('$fieldName with value "' .$fieldName. '" not registered in $this->mSelectionFields. Cannot perform a submit.', 1);

        if (isset($_POST[$fieldName]) and is_array($_POST[$fieldName]) and !empty($_POST[$fieldName]))
        {
            /**
             * Select a best method to handle action
             */
            $method = '';

            // check for method eg. $this -> mSelectionDelete_pantheraUser
            if (method_exists($this, 'mSelection' .ucfirst($action). '_' .$this->mSelectionFields[$fieldName]['className']))
                $method = 'mSelection' .ucfirst($action). '_' .$this->mSelectionFields[$fieldName]['className'];

            // check for generic method eg. $this->mSelectionDelete
            elseif (method_exists($this, 'mSelection' .$action))
                $method = 'mSelection' .ucfirst($action);

            if (!$method)
            {
                $this -> panthera -> logging -> output('$action of value "' .$action. '" not found for field "' .$fieldName. '"', 'multipleSelection');

                if (panthera::getInstance()->logging->debug)
                    throw new UnexpectedValueException('$action of value "' .$action. '" not found for field "' .$fieldName. '"', 2);

                return false;
            }

            // use iterator
            if (isset($this->mSelectionFields[$fieldName]['useIterator']) && $this->mSelectionFields[$fieldName]['useIterator'])
                return $this->mSelectionIterator($fieldName, $method, $_POST[$fieldName], $options);

            return $this -> $method($fieldName, $_POST[$fieldName], $options);
        }
    }

    /**
     * Remove multiple selected elements
     *
     * @param $fieldName
     * @param array $objects List of object id's (columns that identifies objects - specified in $mSelectionFields[$fieldName]['idField']
     * @param array $options (Optional) Options supported by this function (not implemented yet, but can be implemented also in a child function)
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return int Count of affected elements
     */

    protected function mSelectionDelete($fieldName, $objects=array(), $options=array())
    {
        if (is_object($fieldName))
            throw new InvalidArgumentException('This function cannot be used as a iterator!', 3);

        if (!$objects)
            return false;

        $removed = 0;

        foreach ($objects as $objectId)
        {
            $object = new $this->mSelectionFields[$fieldName]['className']($this->mSelectionFields[$fieldName]['idField'], $objectId);

            if (method_exists($object, 'delete'))
            {
                $object -> delete();
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Iterate on multiple elements and run a callback function on every of them
     *
     * @param string $fieldName
     * @param callable $callback
     * @param array $objects List of object id's (columns that identifies objects - specified in $mSelectionFields[$fieldName]['idField']
     * @param array $options (Optional) Options supported by this function (not implemented yet, but can be implemented also in a child function)
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return int Count of affected elements
     */

    protected function mSelectionIterator($fieldName, $callback, $objects=array(), $options=array())
    {
        if (!$objects)
            return false;

        $affected = 0;
        $i = 0;
        $count = count($objects);

        foreach ($objects as $objectId)
        {
            $i++;
            $object = new $this->mSelectionFields[$fieldName]['className']($this->mSelectionFields[$fieldName]['idField'], $objectId);

            if ($this->$callback($object, $i, $count, $objects, $options, $fieldName))
            {
                $affected++;
            }
        }

        return $affected;
    }
}