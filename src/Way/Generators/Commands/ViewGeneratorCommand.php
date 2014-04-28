<?php namespace Way\Generators\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;

class ViewGeneratorCommand extends GeneratorCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a view';

    /**
     * Create directory tree for views,
     * and fire generator
     */
    public function fire()
    {
        $directoryPath = dirname($this->getFileGenerationPath());

        if ( ! File::exists($directoryPath))
        {
            File::makeDirectory($directoryPath, 0777, true);
        }

        parent::fire();
    }

    /**
     * The path where the file will be created
     *
     * @return mixed
     */
    protected function getFileGenerationPath()
    {
        $path = $this->getPathByOptionOrConfig('path', 'view_target_path');
        $viewName = str_replace('.', '/', $this->argument('viewName'));

        return sprintf('%s/%s.blade.php', $path, $viewName);
    }

    /**
     * Fetch the template data
     *
     * @return array
     */
    protected function getTemplateData()
    {
        $data = $this->option("data") ?: [];
        if ($data) {
            $raw = [];
            array_map(function($str) use (&$raw) {
                $str = explode("=", trim($str), 2);
                if (count($str) === 2) {
                    $raw[$str[0]] = $str[1];
                }
            }, explode(",", $data));
            $data = $raw;
        }
        $data['PATH'] = $this->getFileGenerationPath();
        return $data;
    }

    /**
     * Get path to the template for the generator
     *
     * @return mixed
     */
    protected function getTemplatePath()
    {
        if ($path = $this->option('templatePath')) {
            return $path;
        }
        $default = $path = Config::get("generators::config.view_template_path");
        $dir = dirname($path);
        $view_name = explode(".", $this->argument("viewName"));
        $path = implode(DIRECTORY_SEPARATOR, [dirname($path), "views", "{$view_name[count($view_name) - 1]}.txt"]);
        if (file_exists($path)) {
            return $path;
        }
        return $default;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['viewName', InputArgument::REQUIRED, 'The name of the desired view']
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $return = parent::getOptions();
        $return[] = ['data', "d", InputOption::VALUE_REQUIRED, "Additional template data."];
        return $return;
    }

}