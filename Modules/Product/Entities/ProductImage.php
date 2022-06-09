<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\HasFactory;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        "product_id",
        "position",
        "path",
        "main_image",
        "small_image",
        "thumbnail",
        "section_background",
        "gallery",
        "background_color",
        "background_size",
        "image_background_color",
    ];

    protected $appends = [ "small_image_url", "thumbnail_url" ];


    public function types(): BelongsToMany
    {
        return $this->belongsToMany(ImageType::class);
    }

    public function getSmallImageUrlAttribute(): ?string
    {
        return $this->getImage("small_image");
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->getImage("thumbnail");
    }

    public function getImage($image_type): ?string
    {
        if ( !$this->path ) return null;
        $image_url = null;

        switch ($image_type){
            case 'small_image':
                $image_url = $this->getPath("small_image");
                break;

            case 'thumbnail':
                $image_url = $this->getPath("thumbnail");
                break;
        }

        return $image_url;
    }

    private function getPath(string $folder): string
    {
        $file_array = $this->getFileNameArray();
        return Storage::url("{$file_array['folder']}/{$folder}/{$file_array['file']}");
    }

    private function getFileNameArray(): array
    {
        $path_array = explode("/", $this->path);
        $file_name = $path_array[count($path_array) - 1];
        unset($path_array[count($path_array) - 1]);

        return [
            "folder" => implode("/", $path_array),
            "file" => $file_name
        ];
    }
}
