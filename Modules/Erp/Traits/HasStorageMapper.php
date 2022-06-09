<?php

namespace Modules\Erp\Traits;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Services\Pipe;
use Modules\Erp\Entities\ErpImport;
use Modules\Erp\Entities\ErpImportDetail;
use Modules\Erp\Jobs\FtpToStorage;

trait HasStorageMapper
{
    public $erp_folder = "ERP-Product-Images";

    public function storeFromFtpImage(): void
    {
        try
        {
            $ftp_directories = Storage::disk("ftp")->directories();
            $ftp_files = Storage::disk("ftp")->files("/{$ftp_directories[0]}");

            // Filter files that are image and not already in local storage
            $ftp_files = array_filter($ftp_files, function ($file) {
                $file_is_image = Str::contains($file, [".jpg", ".jpeg", ".png", ".bmp"]);
                $file_does_not_already_exist = !Storage::exists("{$this->erp_folder}/{$file}");

                if (!$file_does_not_already_exist) {
                    $remote_hash = md5(Storage::disk("ftp")->size($file));
                    $local_hash = md5(Storage::size("{$this->erp_folder}/{$file}"));

                    $file_does_not_already_exist = $remote_hash !== $local_hash;
                }

                return $file_is_image && $file_does_not_already_exist;
            });

            foreach ($ftp_files as $file) {
                FtpToStorage::dispatch($file);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function storeFromLocalImage(): void
    {
        try
        {
            // Filter Images only
            $files = array_filter(Storage::files("/{$this->erp_folder}/final_images"), fn ($file) => Str::contains($file, [".jpg", ".jpeg", ".png", ".bmp"]));

            $hases = [];
            $files = array_map(function ($file) use (&$hases) {
                $file_data = $this->generateFileData($file);
                $hases[] = $file_data["hash"];

                return $file_data;
            }, $files);

            // Filter non-existing Images only
            $existing_hashes = ErpImportDetail::whereIn("hash", $hases)->get()->pluck("hash")->toArray();
            $files = array_filter($files, fn ($file) => !in_array($file["hash"], $existing_hashes));

            $chunked = array_chunk($files, 50);
            foreach ($chunked as $chunk) {
                ErpImportDetail::insert($chunk);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function getStorageFiles(): array
    {
        try
        {
            $files = array_filter(Storage::files("/{$this->erp_folder}/final_images"), function ($file) {
                return Str::contains($file, [".jpg", ".jpeg", ".png", ".bmp"]);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $files;
    }

    public function getImageData(): Collection
    {
        try
        {
            $files = $this->getStorageFiles();
            $images = [];
            foreach ($files as $file) {
                $images[] = $this->generateFileData($file, function ($file_info) {
                    return $file_info;
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return collect($images);
    }

    private function generateFileData(string $file, ?callable $callback = null): array
    {
        try
        {
            $taken = new Pipe($file);
            $file_arr = $taken->pipe($this->explode("/", $taken->value))
                ->pipe($this->array_reverse($taken->value)[0])
                ->pipe($this->explode(".", $taken->value)[0])
                ->pipe($this->explode("_", $taken->value))
                ->value;

            $image_taken = new Pipe($file);
            $image = $image_taken->pipe($this->explode("/", $image_taken->value))
                ->pipe($this->array_reverse($image_taken->value)[0])
                ->value;

            $file_info = [
                "sku" => $file_arr[0],
                "color_code" => $file_arr[1],
                "image_type" =>  array_key_exists(2, $file_arr) ? $file_arr[2] : "a",
                "url" => $file,
                "image" => $image,
            ];
            if (!$callback) {
                $erp_import_id = ErpImport::whereType("productImages")->first()->id;
                $hash = md5($erp_import_id.$file_info["sku"].json_encode($file_info));
                $file_data = [
                    "erp_import_id" => $erp_import_id,
                    "sku" => $file_info["sku"],
                    "value" => json_encode($file_info),
                    "hash" => $hash,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            } else {
                $file_data = (array) $callback($file_info);
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $file_data;
    }

    private function explode(string $seperator, string $value): mixed
    {
       return explode($seperator, $value);
    }

    public function array_reverse(array $value): mixed
    {
        return array_reverse($value);
    }

    public function transferFtpToLocal(string $location): void
    {
        try
        {
            $get_file = Storage::disk("ftp")->get($location);
            Storage::put("{$this->erp_folder}/{$location}", $get_file);
            $this->storeFileToDb($location);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function storeFileToDb(string $location): void
    {
        try
        {
            $file_data = $this->generateFileData($location);
            $chunked = array_chunk($file_data, 50);
            foreach ($chunked as $chunk) {
                ErpImportDetail::insert($chunk);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
