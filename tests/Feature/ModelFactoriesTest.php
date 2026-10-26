<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tests\TestCase;

/**
 * Every Eloquent model gets a factory whose defaults make a valid row, so a
 * test can always start from Model::factory()->create(). The models are found
 * by scanning app/, so a new model without a factory fails here.
 */
class ModelFactoriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<class-string<Model>, array{class-string<Model>}>
     */
    public static function models(): array
    {
        $appPath = dirname(__DIR__, 2) . '/app';
        $models = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appPath));
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($appPath) + 1, -4);
            $class = 'BB\\' . str_replace('/', '\\', $relativePath);

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            if ($reflection->isSubclassOf(Model::class) && ! $reflection->isAbstract()) {
                $models[$class] = [$class];
            }
        }

        ksort($models);

        return $models;
    }

    /**
     * @param class-string<Model> $model
     */
    #[Test]
    #[DataProvider('models')]
    public function each_model_has_its_own_factory(string $model)
    {
        $this->assertContains(HasFactory::class, class_uses_recursive($model), "$model doesn't use HasFactory");

        $factory = $model::factory();

        $this->assertInstanceOf(Factory::class, $factory);
        $this->assertSame($model, $factory->modelName());
    }

    /**
     * @param class-string<Model> $model
     */
    #[Test]
    #[DataProvider('models')]
    public function factory_defaults_save_a_valid_row(string $model)
    {
        $record = $model::factory()->create();

        $this->assertModelExists($record);
    }
}
