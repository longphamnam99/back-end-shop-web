<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class CustomMakeControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:controller:api
                            {name : The name of the controller}
                            {--resource : Generate a resource controller class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('resource')) {
            return __DIR__ . '/stubs/controller.stub';
        }

        return __DIR__ . '/stubs/controller.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers\api';
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        $controllerClass = $this->parseClassName($name);
        $routeResource = Str::kebab(Str::plural($controllerClass));
        $routeResource = str_replace('_controllers', '', $routeResource);

        // Get the contents of the controller stub
        $useStatement = "use App\Http\Controllers\api\\$controllerClass;";
        $stub = str_replace('DummyUseStatement', $useStatement, $stub);

        // Get the contents of the api.php file
        $filePath = base_path('routes/api.php');
        $fileContents = File::get($filePath);
        $fileContents = str_replace('use Illuminate\Support\Facades\Route;', "use Illuminate\Support\Facades\Route;\nuse App\Http\Controllers\api\\$controllerClass;", $fileContents);

        // Find the position of the middleware group
        $middlewareGroupPosition = strpos($fileContents, "Route::middleware(['api'])->group(function () {");

        // Find the position of the closing bracket of the middleware group
        $closingBracketPosition = strpos($fileContents, "});", $middlewareGroupPosition);

        // Check if the route already exists within the middleware group
        $existingRoute = "Route::resource('$routeResource', $controllerClass::class);";
        $existingRoutePosition = strpos($fileContents, $existingRoute, $middlewareGroupPosition);

        if ($existingRoutePosition === false || $existingRoutePosition > $closingBracketPosition) {
            // Insert the new route inside the middleware group
            $newRoute = "    Route::resource('$routeResource', $controllerClass::class);" . PHP_EOL;
            $updatedContents = substr_replace($fileContents, $newRoute, $closingBracketPosition, 0);
            File::put($filePath, $updatedContents);
        }

        return str_replace('DummyNamespace', $this->getNamespace($name), $stub);
    }

    /**
     * Append the route content to the specified file.
     *
     * @param  string  $filePath
     * @param  string  $content
     * @return void
     */
    protected function appendRouteToFile($filePath, $content)
    {
        $routeContent = file_get_contents($filePath);
        $routeContent .= PHP_EOL . $content;

        file_put_contents($filePath, $routeContent);
    }

    /**
     * Parse the class name from the given controller name.
     *
     * @param  string  $name
     * @return string
     */
    protected function parseClassName($name)
    {
        return str_replace('Controller', '', class_basename($name));
    }
}
