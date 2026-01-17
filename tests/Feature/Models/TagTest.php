<?php

namespace Tests\Feature\Models;

use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_has_many_transactions(): void
    {
        $tag = Tag::factory()->create();
        $transactions = Transaction::factory()->count(3)->create();

        $tag->transactions()->attach($transactions->pluck('id'));

        $this->assertCount(3, $tag->transactions);
        $this->assertInstanceOf(Transaction::class, $tag->transactions->first());
    }

    public function test_get_color_with_default_returns_default_when_null(): void
    {
        $tagWithColor = Tag::factory()->create(['color' => '#FF0000']);
        $tagWithoutColor = Tag::factory()->create(['color' => null]);

        $this->assertEquals('#FF0000', $tagWithColor->getColorWithDefault());
        $this->assertEquals('#6B7280', $tagWithoutColor->getColorWithDefault());
    }

    public function test_get_transaction_count_returns_correct_count(): void
    {
        $tag = Tag::factory()->create();
        $transactions = Transaction::factory()->count(5)->create();

        $tag->transactions()->attach($transactions->pluck('id'));

        $this->assertEquals(5, $tag->getTransactionCount());
    }

    public function test_get_transaction_count_returns_zero_when_no_transactions(): void
    {
        $tag = Tag::factory()->create();

        $this->assertEquals(0, $tag->getTransactionCount());
    }
}
