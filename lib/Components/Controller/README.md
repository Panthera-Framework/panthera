Controllers
===========

Controllers have to belong to a package, to create a package please use `deploy Create/Package YourPackageNameHere`
To create a new controller there is a tool which have syntax:

```bash
deploy Create/Controller MyControllerName --package YourPackageNameHere --route "/example,[:variable]"
```

After that it's good to refresh routing cache:

```bash
deploy Build/Routing/Cache
```

### Structure

> YourPackageNameHere/
>     Controllers/
>         MyControllerNameController/
>             MyControllerNameController.php
>             controller.yml

`controller.yml` contains meta information, at this time it stores only routing information.
`Build/Routing/Cache` task is building routing cache from indexed controller.yml files from multiple packages

Controller should implement a BaseControllerInterface interface (or just extend BaseFrameworkController) and defaultAction.
`function defaultAction()` should implement a default logic when entering a controller without action parameter set.
 
#### Actions

As pointed before - defaultAction() is a main action for our controller, we can have more actions like "add", "edit" in our controller.
To create an action you have to add a new method:

```php
protected function editAction()
{
    // i will be executed when you type in the url: ?action=edit
}
```

Of course change "edit" to your action name.

### Returning values

Controller component currently has classes Response and ResponseText.
`ResponseText` is returning plain text, like you would use "echo" or "print".
Second class - `Response` is rendering a template, or it could be used as API provider when second argument is `null`.

Example:
```php
protected function editAction()
{
    return new ResponseText('Sorry, this is not implemented yet');
}

protected function deleteAction()
{
    return new Response([
        'testVarName' => 123, // this will be passed as {$testVarName} to template (if using RainTPL4)
    ], 'MyTemplateName.tpl'); // template name from the package eg. /YourPackageNameHere/Templates/MyTemplateName.tpl - hint: a template could include files from other packages
}
```

### API, returning JSON and YAML

To return a JSON or YAML you need to pass "__returnType" with value `json` or `yaml`
also your action method should have a `@API` tag in PHPDoc comment that is giving know that it could return a serialized content.

```php
/**
 * In JSON this function should return {'testVarName': 123}
 *
 * @API
 */
protected function deleteAction()
{
    return new Response([
        'testVarName' => 123, // this will be passed as {$testVarName} to template (if using RainTPL4)
    ], 'MyTemplateName.tpl'); // template name from the package eg. /YourPackageNameHere/Templates/MyTemplateName.tpl - hint: a template could include files from other packages
}
```

#### Securing objects from exposing data to public

Please make sure that all objects returned via API does not expose secrets.
Every class could implement a `magic method` that **is called by `Response` to get public representation of object.**

```php
<?php
class UserEntity
{
    /**
     * Controller's magic method that exposes external interface to public
     *
     * @Magic
     */
    public function __exposePublic()
    {
        return [
            'id'    => $this->userId,
            'name'  => $this->getName(),
            'login' => $this->userLogin,
            
            // there is no passwd here, huh?
        ];
    }
}
```