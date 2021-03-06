<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace Pterodactyl\Models;

use File;
use Storage;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Validable;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Contracts\CleansAttributes;
use Sofa\Eloquence\Contracts\Validable as ValidableContract;

class Pack extends Model implements CleansAttributes, ValidableContract
{
    use Eloquence, Validable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'packs';

    /**
     * Fields that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'option_id', 'uuid', 'name', 'version', 'description', 'selectable', 'visible', 'locked',
    ];

    /**
     * @var array
     */
    protected static $applicationRules = [
        'name' => 'required',
        'version' => 'required',
        'description' => 'sometimes',
        'selectable' => 'sometimes|required',
        'visible' => 'sometimes|required',
        'locked' => 'sometimes|required',
        'option_id' => 'required',
    ];

    /**
     * @var array
     */
    protected static $dataIntegrityRules = [
        'name' => 'string',
        'version' => 'string',
        'description' => 'nullable|string',
        'selectable' => 'boolean',
        'visible' => 'boolean',
        'locked' => 'boolean',
        'option_id' => 'exists:service_options,id',
    ];

    /**
     * Cast values to correct type.
     *
     * @var array
     */
    protected $casts = [
        'option_id' => 'integer',
        'selectable' => 'boolean',
        'visible' => 'boolean',
        'locked' => 'boolean',
    ];

    /**
     * Parameters for search querying.
     *
     * @var array
     */
    protected $searchableColumns = [
        'name' => 10,
        'uuid' => 8,
        'option.name' => 6,
        'option.tag' => 5,
        'option.docker_image' => 5,
        'version' => 2,
    ];

    /**
     * Returns all of the archived files for a given pack.
     *
     * @param bool $collection
     * @return \Illuminate\Support\Collection|object
     * @deprecated
     */
    public function files($collection = false)
    {
        $files = collect(Storage::files('packs/' . $this->uuid));

        $files = $files->map(function ($item) {
            $path = storage_path('app/' . $item);

            return (object) [
                'name' => basename($item),
                'hash' => sha1_file($path),
                'size' => File::humanReadableSize($path),
            ];
        });

        return ($collection) ? $files : (object) $files->all();
    }

    /**
     * Gets option associated with a service pack.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function option()
    {
        return $this->belongsTo(ServiceOption::class);
    }

    /**
     * Gets servers associated with a pack.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servers()
    {
        return $this->hasMany(Server::class);
    }
}
