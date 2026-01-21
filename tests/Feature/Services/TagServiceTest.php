<?php

namespace Tests\Feature\Services;

use App\Models\Tag;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $tagService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = app(TagService::class);
    }

    // ==================== Get User Tags ====================

    public function test_get_user_tags_returns_all_tags(): void
    {
        Tag::factory()->count(3)->create();

        $tags = $this->tagService->getUserTags();

        $this->assertCount(3, $tags);
    }

    public function test_get_user_tags_returns_tags_ordered_by_name(): void
    {
        Tag::factory()->create(['name' => 'Zebra']);
        Tag::factory()->create(['name' => 'Apple']);
        Tag::factory()->create(['name' => 'Banana']);

        $tags = $this->tagService->getUserTags();

        $this->assertEquals('Apple', $tags->first()->name);
        $this->assertEquals('Zebra', $tags->last()->name);
    }

    public function test_get_user_tags_only_selects_required_fields(): void
    {
        Tag::factory()->create();

        $tags = $this->tagService->getUserTags();
        $tag = $tags->first();

        $this->assertArrayHasKey('id', $tag->toArray());
        $this->assertArrayHasKey('name', $tag->toArray());
        $this->assertArrayHasKey('color', $tag->toArray());
    }

    public function test_get_user_tags_uses_cache(): void
    {
        Tag::factory()->create(['name' => 'Original']);

        // First call - loads from database
        $tags = $this->tagService->getUserTags();
        $this->assertCount(1, $tags);

        // Create new tag
        Tag::factory()->create(['name' => 'New']);

        // Second call - should return cached version
        $tags = $this->tagService->getUserTags();
        $this->assertCount(1, $tags);
        $this->assertEquals('Original', $tags->first()->name);
    }

    public function test_get_user_tags_accepts_optional_user_id(): void
    {
        Tag::factory()->count(2)->create();

        $user = User::factory()->create();

        $tags = $this->tagService->getUserTags($user->id);

        $this->assertCount(2, $tags);
    }

    // ==================== Create Tag ====================

    public function test_create_tag_creates_tag_in_database(): void
    {
        $data = [
            'name' => 'Bills',
            'color' => '#FF0000',
        ];

        $tag = $this->tagService->createTag($data);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertDatabaseHas('tags', [
            'name' => 'Bills',
            'color' => '#FF0000',
        ]);
    }

    public function test_create_tag_uses_default_color_when_not_provided(): void
    {
        $data = ['name' => 'Shopping'];

        $tag = $this->tagService->createTag($data);

        $this->assertEquals('#3B82F6', $tag->color);
    }

    public function test_create_tag_invalidates_cache(): void
    {
        // Populate cache
        Tag::factory()->create();
        $this->tagService->getUserTags();

        // Create new tag
        $this->tagService->createTag(['name' => 'New Tag']);

        // Cache should be invalidated, so we get fresh data
        $tags = $this->tagService->getUserTags();
        $this->assertCount(2, $tags);
    }

    // ==================== Update Tag ====================

    public function test_update_tag_updates_fields(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Old Name',
            'color' => '#000000',
        ]);

        $updated = $this->tagService->updateTag($tag, [
            'name' => 'New Name',
            'color' => '#FFFFFF',
        ]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('#FFFFFF', $updated->color);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
            'color' => '#FFFFFF',
        ]);
    }

    public function test_update_tag_returns_fresh_instance(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original']);

        $updated = $this->tagService->updateTag($tag, ['name' => 'Updated']);

        $this->assertNotSame($tag, $updated);
        $this->assertEquals('Updated', $updated->name);
    }

    public function test_update_tag_invalidates_cache(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original']);

        // Populate cache
        $this->tagService->getUserTags();

        // Update tag
        $this->tagService->updateTag($tag, ['name' => 'Updated']);

        // Cache should be invalidated
        $tags = $this->tagService->getUserTags();
        $this->assertEquals('Updated', $tags->first()->name);
    }

    // ==================== Delete Tag ====================

    public function test_delete_tag_removes_from_database(): void
    {
        $tag = Tag::factory()->create();

        $result = $this->tagService->deleteTag($tag);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_delete_tag_invalidates_cache(): void
    {
        $tag1 = Tag::factory()->create(['name' => 'Keep']);
        $tag2 = Tag::factory()->create(['name' => 'Delete']);

        // Populate cache
        $this->tagService->getUserTags();

        // Delete tag
        $this->tagService->deleteTag($tag2);

        // Cache should be invalidated
        $tags = $this->tagService->getUserTags();
        $this->assertCount(1, $tags);
        $this->assertEquals('Keep', $tags->first()->name);
    }

    public function test_delete_tag_only_invalidates_cache_when_successful(): void
    {
        $tag = Tag::factory()->create();

        // Populate cache
        $this->tagService->getUserTags();
        $this->assertTrue(Cache::has('all_tags'));

        // Delete successfully
        $this->tagService->deleteTag($tag);

        // Cache should be cleared
        $this->assertFalse(Cache::has('all_tags'));
    }

    // ==================== Cache Management ====================

    public function test_invalidate_cache_clears_cache(): void
    {
        Tag::factory()->create();

        // Populate cache
        $this->tagService->getUserTags();
        $this->assertTrue(Cache::has('all_tags'));

        // Invalidate cache
        $this->tagService->invalidateCache();

        // Cache should be cleared
        $this->assertFalse(Cache::has('all_tags'));
    }

    public function test_cache_key_is_consistent(): void
    {
        Tag::factory()->create(['name' => 'Test']);

        // First call
        $this->tagService->getUserTags();
        $this->assertTrue(Cache::has('all_tags'));

        // Second call should use same cache key
        $cached = Cache::get('all_tags');
        $this->assertCount(1, $cached);
        $this->assertEquals('Test', $cached->first()->name);
    }
}
