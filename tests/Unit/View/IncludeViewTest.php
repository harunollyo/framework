<?php

namespace Framework\Tests\Unit\View;

use Framework\Tests\Unit\TestCase;
use Framework\View\TemplateEngine;
use Framework\View\View;
use Framework\View\ViewContext;
use RuntimeException;

use function Framework\app;
use function Framework\include_view;

class IncludeViewTest extends TestCase
{
    /**
     * Temporary views directory.
     *
     * @var string
     */
    protected $views;

    protected function setUp(): void
    {
        parent::setUp();

        $this->views = sys_get_temp_dir() . '/framework-include-view-' . uniqid();
        mkdir($this->views . '/shop', 0777, true);

        $app = $this->bootstrap_application();
        $app->use_view_path($this->views);
        $app->instance(TemplateEngine::class, new TemplateEngine());
        $app->instance(ViewContext::class, new ViewContext());
    }

    protected function tearDown(): void
    {
        app(ViewContext::class)->clear();
        $this->remove_directory($this->views);
        parent::tearDown();
    }

    public function test_include_view_renders_partial_with_view_data(): void
    {
        file_put_contents(
            $this->views . '/shop/filter.php',
            '<?php echo implode(",", \Framework\view_data("categories"));'
        );

        ob_start();
        include_view('shop.filter', ['categories' => ['a', 'b']]);
        $output = (string) ob_get_clean();

        $this->assertSame('a,b', $output);
        $this->assertNull(app(ViewContext::class)->get_active());
    }

    public function test_include_view_does_not_inherit_parent_keys(): void
    {
        file_put_contents(
            $this->views . '/shop/products.php',
            '<?php \Framework\include_view("shop.filter", ["categories" => ["x"]]); echo "-"; echo \Framework\view_data("products");'
        );
        file_put_contents(
            $this->views . '/shop/filter.php',
            '<?php echo \Framework\view_data("categories")[0]; echo \Framework\view_data("products", "none");'
        );

        $context = app(ViewContext::class);
        $context->prepare(
            new View('shop.products', ['products' => 'list']),
            'shop.products',
            $this->views . '/shop/products.php'
        );

        ob_start();
        require $this->views . '/shop/products.php';
        $output = (string) ob_get_clean();

        $this->assertSame('xnone-list', $output);
    }

    public function test_include_view_merges_shared_data(): void
    {
        app(TemplateEngine::class)->share('site', 'demo');

        file_put_contents(
            $this->views . '/shop/filter.php',
            '<?php echo \Framework\view_data("site"); echo "-"; echo \Framework\view_data("n");'
        );

        ob_start();
        include_view('shop.filter', ['n' => '1']);
        $output = (string) ob_get_clean();

        $this->assertSame('demo-1', $output);
    }

    public function test_include_view_throws_when_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View [shop.missing] not found.');

        include_view('shop.missing', []);
    }

    protected function remove_directory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                $this->remove_directory($path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
