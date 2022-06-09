<?php

namespace Modules\SizeChart\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\SizeChart\Entities\SizeChartContent;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Arr;
use Modules\Core\Traits\FileManager;

class SizeChartContentRepository extends BaseRepository
{
    use FileManager;
    protected  $folder_path;
    protected $sizechart_image_path;

    public function __construct()
    {
        $this->model = new SizeChartContent();
        $this->model_key = "sizeChartContent";
        $this->rules = [
            "title" => "required|string",
            "type" => "required|in:" . implode(",", SizeChartContent::$content_types),
            "content" => 'required',
            "slug" => "required|unique:size_chart_contents,slug,.$this->model->id"
        ];
        $this->folder_path = storage_path('app/public/');
        $this->sizechart_image_path = 'images/sizechart/';
    }

    public function updateOrCreate(array $attribute, object $parent): void
    {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.sync.before");
        try
        {
            $size_chart_contents = [];
            if (is_array($attribute))
            {
                if (($attribute["type"] == "image") && !file_exists($attribute["content"])) {
                    throw ValidationException::withMessages(["Size chart content" => "Invalid Content Type"]);
                }

                if (($attribute["type"] == "editor") && file_exists($attribute["content"])) {
                    throw ValidationException::withMessages(["Size chart content" => "Invalid Content Type"]);
                }

                if (($attribute["type"] == "image") && file_exists($attribute["content"])) {
                    $attribute["content"] = $this->uploadImage($attribute["content"]);
                }

                $data = [
                    "size_chart_id" => $parent->id,
                    "title" => $attribute["title"],
                    "slug" => $attribute["slug"],
                    "content" => json_encode($attribute["content"]),
                    "type" => $attribute["type"],
                ];

                $exist = $this->model->where($data)->first();
                if ($exist) {
                    $size_chart_contents[] = $exist;
                }
                $size_chart_contents[] = $this->create($data);
            }

            $parent->size_chart_contents()->whereNotIn('id', array_filter(Arr::pluck($size_chart_contents, 'id')))->delete();
        }
        catch (Exception $exception){
            DB::rollBack();
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.sync.after", $size_chart_contents);
        DB::commit();
    }

    public function uploadImage($img): string
    {
        try {
            $this->createFolderIfNotExist($this->folder_path);
            $fileName = $this->getFileName($img);
            $img->move($this->folder_path.$this->sizechart_image_path, $fileName);
        }
        catch (Exception $exception) {
            throw $exception;
        }

        return $fileName;
    }

    public function removeSizeChartImage(int $id): bool
    {
        try {
            $sizeChartContents = $this->model->where("size_chart_id", $id)->whereType("image")->select("content", "type")->get();
            if (isset($sizeChartContents) && $sizeChartContents->count() > 0) {
                foreach ($sizeChartContents as $image) {
                    if (file_exists($this->folder_path.json_decode($image->content))) {
                        unlink($this->folder_path.json_decode($image->content));
                    }
                }
            }
            $this->model()->delete($id);
        }
        catch (Exception $exception) {
            throw $exception;
        }

        return false;
    }
}
