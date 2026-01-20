<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TagService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all tags with caching (tags are shared across all users)
     */
    public function getUserTags(?int $userId = null): Collection
    {
        return Cache::remember(
            $this->getCacheKey(),
            self::CACHE_TTL,
            fn () => Tag::select(['id', 'name', 'color'])
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Invalidate tags cache
     */
    public function invalidateCache(): void
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * Create a new tag and invalidate cache
     */
    public function createTag(array $data): Tag
    {
        $tag = Tag::create([
            'name' => $data['name'],
            'color' => $data['color'] ?? '#3B82F6',
        ]);

        $this->invalidateCache();

        return $tag;
    }

    /**
     * Update a tag and invalidate cache
     */
    public function updateTag(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        $this->invalidateCache();

        return $tag->fresh();
    }

    /**
     * Delete a tag and invalidate cache
     */
    public function deleteTag(Tag $tag): bool
    {
        $deleted = $tag->delete();

        if ($deleted) {
            $this->invalidateCache();
        }

        return $deleted;
    }

    /**
     * Get cache key for tags
     */
    private function getCacheKey(): string
    {
        return 'all_tags';
    }
}
