<?php

namespace Modules\Core\Traits;

use Exception;
use Illuminate\Support\Str;

trait Slugify
{
    public string $slug_field = "slug";

    public function slugify(string $slugify): string
    {
        try
        {
            $slug = Str::slug($slugify);
            $original_slug = $slug;

            $allSlugs = $this->getRelatedSlugs($slug);

            if (!$allSlugs->contains("{$this->slug_field}", $slug)) {
                $count = $allSlugs->count();

                while ($this->checkIfSlugExist($slug) && $slug != "") {
                    $slug = "{$original_slug}-{$count}";
                    $count++;
                }
            }

            return $slug;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $slug;
    }

    private function getRelatedSlugs(string $slugify): object
    {
        try
        {
            $related_slugs = $this->repository
                ->model()->whereRaw("{$this->slug_field} RLIKE ?", ["^{$slugify}(-[0-9]+)?$"])
                ->get();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $related_slugs;
    }

    private function checkIfSlugExist(string $slugify): bool
    {
        try
        {
            $exists = $this->repository
                ->model()->select($this->slug_field)
                ->where($this->slug_field, $slugify)
                ->exists();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $exists;
    }
}
