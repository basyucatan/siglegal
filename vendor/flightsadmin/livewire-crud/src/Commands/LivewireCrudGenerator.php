<?php

namespace Flightsadmin\LivewireCrud\Commands;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class LivewireCrudGenerator extends LivewireGeneratorCommand
{
	protected $filesystem;
    protected $stubDir;
    protected $argument;
    private $replaces = [];
    protected bool $useCards = false;
    protected $signature = 'crud:generate {name : Table name}
        {--cards : Generar vista en cards}';
    protected $description = 'Generate Livewire Component and CRUD operations';
    public function handle()
    {
        $this->table = $this->getNameInput();
        $this->useCards = $this->option('cards');
        if (!$this->tableExists()) {
            $this->error("`{$this->table}` table not exist");

            return false;
        }
        $this->name = $this->_buildClassName();
           $this->buildModel()
				->buildViews();
        $this->filesystem = new Filesystem;
        $this->argument = $this->getNameInput();
        $routeFile = base_path('routes/web.php');
        $routeContents = $this->filesystem->get($routeFile);
        $routeItemStub = "\tRoute::view('" . $this->getNameInput() . "', 'livewire." . $this->getNameInput() . ".index');";
		$routeItemHook = '//Route Hooks - Do not delete//';

        if (!Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
        }		
        $layoutFile = 'resources/views/layouts/app.blade.php';
        $layoutContents = $this->filesystem->get($layoutFile);
        $navItemStub = "\t\t\t\t\t\t<li class=\"nav-item\">
                            <a href=\"{{ url('/".$this->getNameInput()."') }}\" class=\"nav-link\"><i class=\"bi-house text-info\"></i> ". ucfirst($this->getNameInput()) ."</a> 
                        </li>";
        $navItemHook = '<!--Nav Bar Hooks - Do not delete!!-->';
        if (!Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($layoutFile, $newContents);
            $this->warn('Nav link inserted: <info>' . $layoutFile . '</info>');
        }
        $this->info('');
        $this->info('Livewire Component & CRUD Generated Successfully.');

        return true;
    }
    protected function buildModel()
    {
        $modelPath = $this->_getModelPath($this->name);
        $livewirePath = $this->_getLivewirePath($this->name);
        if (
            $this->files->exists($livewirePath) &&
            $this->ask(
                "Livewire Component " . Str::studly(Str::singular($this->table)) . " Component Already exist. Do you want overwrite (y/n)?",
                'y'
            ) == 'n'
        ) {
            return $this;
        }
        $casts = [];
        foreach ($this->getColumns() as $col) {
            if ($col->Field === 'adicionales') {
                $casts[] = "'{$col->Field}' => 'array'";
            }
        }
        $castsString = '';
        if (!empty($casts)) {
            $castsString = "protected \$casts = [\n        " . implode(",\n        ", $casts) . "\n    ];";
        }
        $replace = array_merge($this->buildReplacements(),$this->modelReplacements(),['{{casts}}' => $castsString]);
        $modelTemplate = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub('Model')
        );
        $livewireTemplate = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub('Livewire')
        );
        $this->warn('Creating: <info>Livewire Component...</info>');
        $this->write($livewirePath, $livewireTemplate);
        $this->warn('Creating: <info>Model...</info>');
        $this->write($modelPath, $modelTemplate);
        return $this;
    }
    protected function buildViews()
    {
        $this->warn('Creating:<info> Views ...</info>');
        $viewStub = $this->useCards
            ? 'views/view-cards'
            : 'views/view-tabla';
        $tableHead = "\n";
        $tableBody = "\n";
        $cardBody = "\n";
        $viewRows = "\n";
        $form = "\n";
        $type = null;
        $cardParts = [];
        foreach ($this->getFilteredColumns() as $column) {
            $title = Str::title(str_replace('_', ' ', $column));

            $tableHead .= "\t\t\t\t". $this->getHead($title);
            $tableBody .= "\t\t\t\t". $this->getBody($column);
            $form .= $this->getField($title, $column, 'form-field') . "\n";
            $cardParts[] = "{{ \$row->{$column} }}";
        }
		$cardBody = implode(' | ', $cardParts);
		foreach ($this->getColumns() as $values) {
			$type = "text";
		}
        $replace = array_merge($this->buildReplacements(), [
            '{{tableHeader}}' => $tableHead,
            '{{tableBody}}' => $tableBody,
            '{{cardBody}}'   => $cardBody,
            '{{viewRows}}' => $viewRows,
            '{{form}}' => $form,
            '{{useCards}}' => $this->useCards ? 'true' : 'false',
            '{{type}}' => $type,
        ]);
        $this->buildLayout();
        foreach (['view', 'index', 'modals'] as $view) {
            $viewTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub($viewStub)
            );
            $this->write($this->_getViewPath('view'), $viewTemplate);
            $indexTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub('views/index')
            );
            $this->write($this->_getViewPath('index'), $indexTemplate);
            $modalsTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub('views/modals')
            );
            $this->write($this->_getViewPath('modals'), $modalsTemplate);

        }
        return $this;
    }
    private function _buildClassName()
    {
        return Str::studly(Str::singular($this->table));
    }
	private function replace($content)
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        return $content;
    }
}