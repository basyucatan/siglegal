<?php

namespace Flightsadmin\LivewireCrud\Commands;

use Flightsadmin\LivewireCrud\ModelGenerator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
abstract class LivewireGeneratorCommand extends Command
{
    protected $files;
    protected $unwantedColumns = [
        'id',
        'password',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $table = null;
    protected $name = null;
    private $tableColumns = null;
    protected $modelNamespace = 'App\Models';
    protected $controllerNamespace = 'App\Http\Controllers';
    protected $livewireNamespace = 'App\Livewire';
    protected $layout = 'layouts.app';
    protected $options = [];
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
        $this->unwantedColumns = config('livewire-crud.model.unwantedColumns', $this->unwantedColumns);
        $this->modelNamespace = config('crud.model.namespace', $this->modelNamespace);
        $this->controllerNamespace = config('livewire-crud.controller.namespace', $this->controllerNamespace);
        $this->livewireNamespace = config('livewire-crud.livewire.namespace', $this->livewireNamespace);
        $this->layout = config('livewire-crud.layout', $this->layout);
    }
    abstract protected function buildModel();
    abstract protected function buildViews();
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }
    protected function write($path, $content)
    {
        $this->files->put($path, $content);
    }
    protected function getStub($type, $content = true)
    {
        $stub_path = config('livewire-crud.stub_path', 'default');
        if ($stub_path == 'default') {
            $stub_path = __DIR__ . '/../stubs/';
        }

        $path = Str::finish($stub_path, '/') . "{$type}.stub";

        if (!$content) {
            return $path;
        }

        return $this->files->get($path);
    }
    private function _getSpace($no = 1)
    {
        $tabs = '';
        for ($i = 0; $i < $no; $i++) {
            $tabs .= "\t";
        }

        return $tabs;
    }
    protected function _getMigrationPath($name)
    {
        return base_path("database/migrations/" . date('Y-m-d_His') . "_create_" . Str::lower(Str::plural($name)) . "_table.php");
    }
    protected function _getFactoryPath($name)
    {
        return base_path("database/factories/{$name}Factory.php");
    }
    protected function _getLivewirePath($name)
    {
        return app_path($this->_getNamespacePath($this->livewireNamespace) . "{$name}s.php");
    }
    protected function _getModelPath($name)
    {
        return $this->makeDirectory(app_path($this->_getNamespacePath($this->modelNamespace) . "{$name}.php"));
    }
    private function _getNamespacePath($namespace)
    {
        $str = Str::start(Str::finish(Str::after($namespace, 'App'), '\\'), '\\');

        return str_replace('\\', '/', $str);
    }
    private function _getLayoutPath()
    {
        return $this->makeDirectory(resource_path("/views/layouts/app.blade.php"));
    }
    protected function _getViewPath($view)
    {
        $name = Str::kebab($this->name);

        return $this->makeDirectory(resource_path("/views/livewire/{$name}s/{$view}.blade.php"));
    }
    protected function buildReplacements()
    {
        return [
            '{{layout}}' => $this->layout,
            '{{modelName}}' => $this->name,
            '{{modelTitle}}' => Str::title(Str::snake($this->name, ' ')),
            '{{modelNamespace}}' => $this->modelNamespace,
            '{{controllerNamespace}}' => $this->controllerNamespace,
            '{{modelNamePluralLowerCase}}' => Str::camel(Str::plural($this->name)),
            '{{modelNamePluralUpperCase}}' => ucfirst(Str::plural($this->name)),
            '{{modelNameLowerCase}}' => Str::camel($this->name),
            '{{modelRoute}}' => $this->options['route'] ?? Str::kebab(Str::plural($this->name)),
            '{{modelView}}' => Str::kebab($this->name),
        ];
    }
    protected function getField($title, $column, $type = 'form-field')
    {
        $replace = array_merge($this->buildReplacements(), [
            '{{title}}' => $title,
            '{{column}}' => $column,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub("views/{$type}")
        );
    }
    protected function getHead($title)
    {
        $replace = array_merge($this->buildReplacements(), [
            '{{title}}' => $title,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->_getSpace(4) . '<th>{{title}}</th>' . "\n"
        );
    }
    protected function getBody($column)
    {
        $replace = array_merge($this->buildReplacements(), [
            '{{column}}' => $column,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->_getSpace(4) . '<td>{{ $row->{{column}} }}</td>' . "\n"
        );
    }
    protected function buildLayout(): void
    {
        if (!(view()->exists($this->layout))) {

            $this->info('Creating Layout ...');

            if ($this->layout == 'layouts.app') {
                $this->files->copy($this->getStub('layouts/app', false), $this->_getLayoutPath());
            } else {
                throw new \Exception("{$this->layout} layout not found!");
            }
        }
    }
    protected function getColumns()
    {
        if (empty($this->tableColumns)) {
            $this->tableColumns = DB::select('SHOW COLUMNS FROM ' . $this->table);
        }

        return $this->tableColumns;
    }
    protected function getFilteredColumns()
    {
        $unwanted = $this->unwantedColumns;
        $columns = [];

        foreach ($this->getColumns() as $column) {
            $columns[] = $column->Field;
        }

        return array_filter($columns, function ($value) use ($unwanted) {
            return !in_array($value, $unwanted);
        });
    }
    protected function modelReplacements()
    {
        $properties = '';
        $rulesArray = [];
        $softDeletesNamespace = $softDeletes = '';
        foreach ($this->getColumns() as $value) {

            if ($value->Null == 'NO') {
                $rulesArray[$value->Field] = 'required';
            }
            if ($value->Field == 'deleted_at') {
                $softDeletesNamespace = "use Illuminate\Database\Eloquent\SoftDeletes;\n";
                $softDeletes = "use SoftDeletes;\n";
            }
        }
        $rules = function () use ($rulesArray) {
            $rules = '';
            $rulesArray = Arr::except($rulesArray, $this->unwantedColumns);
            foreach ($rulesArray as $col => $rule) {
                $rules .= "\n\t\t'{$col}' => '{$rule}',";
            }
            return $rules;
        };
        $fillable = function () {
            $filterColumns = $this->getFilteredColumns();
            foreach ($this->getColumns() as $col) {
                if ($col->Field === 'adicionales' && !in_array('adicionales', $filterColumns)) {
                    $filterColumns[] = 'adicionales';
                }
            }
            array_walk($filterColumns, function (&$value) {
                $value = "'" . $value . "'";
            });
            return implode(',', $filterColumns);
        };        
        $updatefield = function () {
            $filterColumns = $this->getFilteredColumns();
            $jsonColumns = [];
            foreach ($this->getColumns() as $col) {
                $type = strtolower($col->Type);

                if (str_contains($type, 'json') || str_contains($type, 'longtext')) {
                    $jsonColumns[] = $col->Field;
                }
            }
            $filterColumns = array_diff($filterColumns, $jsonColumns);
            array_walk($filterColumns, function (&$value) {
                $value = "$" . $value;
            });
            return implode(', ', $filterColumns);
        };
        $resetfields = function () {
            $filterColumns = $this->getFilteredColumns();
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\$this->" . $value . " = null";
                $value .= ";";
            });
            return implode('', $filterColumns);
        };
        $addfields = function () {
            $filterColumns = $this->getFilteredColumns();
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\t\t'" . $value . "' => \$this-> " . $value;
            });
            return implode(',', $filterColumns);
        };
        $keyWord = function () {
            $filterColumns = $this->getFilteredColumns();
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\t\t\t\t->orWhere('" . $value . "', 'LIKE', \$keyWord)";
            });
            return implode('', $filterColumns);
        };
        $factoryfields = function () {
            $filterColumns = $this->getFilteredColumns();
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\t'" . $value . "' => fake()->name(),";
            });
            return implode('', $filterColumns);
        };
        $editfields = function () {
            $filterColumns = $this->getFilteredColumns();
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\$this->" . $value . " = \$record-> " . $value . ";";
            });
            return implode('', $filterColumns);
        };
        list($relations, $properties) = (new ModelGenerator($this->table, $properties, $this->modelNamespace))->getEloquentRelations();
        $arrayProperties = '';
        foreach ($this->getColumns() as $col) {
        $type = strtolower($col->Type);
        $isJsonLike = str_contains($type, 'json') || str_contains($type, 'longtext');
        if ($isJsonLike) {
            $arrayProperties .= "\n\tpublic \${$col->Field} = [];";
        }
        }
        return [
            '{{fillable}}' => $fillable(),
            '{{updatefield}}' => $updatefield(),
            '{{resetfields}}' => $resetfields(),
            '{{editfields}}' => $editfields(),
            '{{addfields}}' => $addfields(),
            '{{factory}}' => $factoryfields(),
            '{{rules}}' => $rules(),
            '{{search}}' => $keyWord(),
            '{{relations}}' => $relations,
            '{{properties}}' => $properties,
            '{{arrayProperties}}' => $arrayProperties,
            '{{softDeletesNamespace}}' => $softDeletesNamespace,
            '{{softDeletes}}' => $softDeletes,
        ];
    }
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the table'],
        ];
    }
    protected function tableExists()
    {
        return Schema::hasTable($this->table);
    }
}
