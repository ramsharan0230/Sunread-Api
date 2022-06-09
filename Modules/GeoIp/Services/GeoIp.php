<?php

namespace Modules\GeoIp\Services;

use Exception;
use GeoIp2\Database\Reader;
use Modules\GeoIp\Services\Location;
use Modules\GeoIp\Traits\HasClientIp;
use PharData;
use GeoIp2\Exception\AddressNotFoundException;

class GeoIp {

    use HasClientIp;

    protected $geoip_db_path, $reader, $dbupdate_url, $default_locale;

    public function __construct()
    {
        $this->geoip_db_path = config("geoip.services.maxmind_database.database_path");
        $this->dbupdate_url = config("geoip.services.maxmind_database.update_url");
        $this->default_locale = config("geoip.services.maxmind_database.locales");
        $this->reader = $this->connect();
    }

    public function connect(): mixed
    {
        return new Reader($this->geoip_db_path, ["en"]);
    }

    public function hydrate(array $attributes = []): mixed
    {
        return new Location($attributes);
    }

    public function getGeoLocation(): mixed
    {
        return $this->locate($this->clientIp());
    }

    public function locate(string $ip): mixed
    {
        try
        {
            $data = $this->reader->city($ip);
        
            $attributes = $this->hydrate([
                'ip' => $ip,
                'iso_code' => $data->country->isoCode,
                'country' => $data->country->name,
                'city' => $data->city->name,
                'state' => $data->mostSpecificSubdivision->isoCode,
                'state_name' => $data->mostSpecificSubdivision->name,
                'postal_code' => $data->postal->code,
                'lat' => $data->location->latitude,
                'lon' => $data->location->longitude,
                'timezone' => $data->location->timeZone,
                'continent' => $data->continent->code,
            ]);
        }
        catch (Exception $exception)
        {
            // No other way to handle not found exception.
            if (get_class($exception) == AddressNotFoundException::class) return null;
            throw $exception;
        }

        return $attributes;
    }

    public function update(): string
    {
        try
        {
            if (!$this->geoip_db_path) throw new Exception('Database path not set in config file.'); 

            $this->withTemporaryDirectory(function ($directory) {
                $tarFile = sprintf('%s/maxmind.tar.gz', $directory);
                file_put_contents($tarFile, fopen($this->dbupdate_url, 'r'));

                $archive = new PharData($tarFile);
                $file = $this->findDatabaseFile($archive);
                $relativePath = "{$archive->getFilename()}/{$file->getFilename()}";
                $archive->extractTo($directory, $relativePath);
                file_put_contents($this->geoip_db_path, fopen("{$directory}/{$relativePath}", 'r'));
            });
        }
        catch (Exception  $exception)
        {
            throw $exception;
        }

        return "Database file ({$this->geoip_db_path}) updated.";
    }

    protected function withTemporaryDirectory(callable $callback): void
    {
        try
        {
            $directory = tempnam(sys_get_temp_dir(), 'maxmind');
            if (file_exists($directory)) unlink($directory);
            mkdir($directory);
            $callback($directory);
        }
        finally
        {
            $this->deleteDirectory($directory);
        }
    }

    protected function findDatabaseFile(mixed $archive): mixed
    {
        foreach ($archive as $file) {
            if ($file->isDir()) {
                $data = $this->findDatabaseFile(new PharData($file->getPathName()));
                break;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) === 'mmdb') {
                $data = $file;
                break;
            }
        }
        if (!isset($data)) throw new Exception('Database file could not be found within archive.');
        return $data;
    }

    protected function deleteDirectory(string $directory): mixed
    {
        if (! file_exists($directory)) {
            return true;
        }

        if (! is_dir($directory)) {
            return unlink($directory);
        }

        foreach (scandir($directory) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (! $this->deleteDirectory($directory . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($directory);
    }
    
}
