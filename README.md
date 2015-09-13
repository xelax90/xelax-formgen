# Xelax Formgen
ZF2 Form/Fieldset generation for doctrine entities

## Usage
* Navigate to your zf2 project directory
* Run ```php public/index.php formgen generate``` to generate forms and fieldsets for your modules located in the module directory
* Run ```php public/index.php formgen generate --module=YourModuleName``` to specify a module anywhere in your project
* Run ```php public/index.php formgen generate --entity=YourModuleName\Entity\SomeEntity``` to generate form and fieldset for one specific entity in your project
* The generated files can be found in the ```data/formgen``` directory in your project
