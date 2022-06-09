<?php

namespace Modules\Core\Traits;


use Illuminate\Support\Str;
use Modules\Core\Exceptions\SlugCouldNotBeGenerated;

//generates slugs for repositories
trait Sluggable
{
    /**
     * @param $title
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function createSlug($title, $id = 0, $slug = null)
    {
        // Slugify
        if(!$slug) {
            $slug = Str::slug($title);
        }
        $original_slug = $slug;

        // Throw Error if slug could not be generated
        if ($slug == "") throw new SlugCouldNotBeGenerated();

        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.
        $allSlugs = $this->getRelatedSlugs($slug, $id);

        // If we haven't used it before then we are all good.
        if (!$allSlugs->contains('slug', $slug)) return $slug;

        //if used,then count them
        $count = $allSlugs->count();

        // Loop through generated slugs
        while ($this->checkIfSlugExist($slug, $id) && $slug != "") {
            $slug = "{$original_slug}-{$count}";
            $count++;
        }

        // Finally return Slug
        return $slug;
    }

    /**
     * Get related slugs
     *
     * @param String $slug
     * @param Int $id
     * @return Collection
     */
    private function getRelatedSlugs($slug, $id = 0)
    {
        return static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")
            ->where('id', '<>', $id)
            ->get();
    }

    /**
     * Check if slug exists
     *
     * @param String $slug
     * @param Int $id
     * @return Boolean
     */
    private  function checkIfSlugExist($slug, $id = 0)
    {
        return static::select('slug')->where('slug', $slug)
            ->where('id', '<>', $id)
            ->exists();
    }
}
