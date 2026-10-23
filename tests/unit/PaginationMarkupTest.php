<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The Blade pages (payments, sub-charges, balance, member lists) style their
 * pagination with Bootstrap. Laravel 8 switched the default paginator markup to
 * Tailwind, which would leave those links unstyled while every page still
 * returns 200 — so the route smoke test can't see it. Pin the Bootstrap markup.
 */
class PaginationMarkupTest extends TestCase
{
    #[Test]
    public function length_aware_pagination_renders_bootstrap_markup()
    {
        $paginator = new LengthAwarePaginator(range(1, 10), 30, 10, 2, ['path' => '/payments']);

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('page-item', $html);
        $this->assertStringContainsString('page-link', $html);
    }

    #[Test]
    public function simple_pagination_renders_bootstrap_markup()
    {
        $paginator = new Paginator(range(1, 11), 10, 1, ['path' => '/payments']);

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('page-item', $html);
    }
}
