<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace Pterodactyl\Models;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Validable;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Contracts\CleansAttributes;
use Sofa\Eloquence\Contracts\Validable as ValidableContract;

class ServiceVariable extends Model implements CleansAttributes, ValidableContract
{
    use Eloquence, Validable;

    /**
     * Reserved environment variable names.
     *
     * @var string
     */
    const RESERVED_ENV_NAMES = 'SERVER_MEMORY,SERVER_IP,SERVER_PORT,ENV,HOME,USER,STARTUP,SERVER_UUID,UUID';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_variables';

    /**
     * Fields that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Cast values to correct type.
     *
     * @var array
     */
    protected $casts = [
        'option_id' => 'integer',
        'user_viewable' => 'integer',
        'user_editable' => 'integer',
    ];

    /**
     * @var array
     */
    protected static $applicationRules = [
        'name' => 'required',
        'env_variable' => 'required',
        'rules' => 'required',
    ];

    /**
     * @var array
     */
    protected static $dataIntegrityRules = [
        'option_id' => 'exists:service_options,id',
        'name' => 'string|between:1,255',
        'description' => 'nullable|string',
        'env_variable' => 'regex:/^[\w]{1,255}$/|notIn:' . self::RESERVED_ENV_NAMES,
        'default_value' => 'string',
        'user_viewable' => 'boolean',
        'user_editable' => 'boolean',
        'rules' => 'string',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'user_editable' => 0,
        'user_viewable' => 0,
    ];

    /**
     * Returns the display executable for the option and will use the parent
     * service one if the option does not have one defined.
     *
     * @return bool
     */
    public function getRequiredAttribute($value)
    {
        return $this->rules === 'required' || str_contains($this->rules, ['required|', '|required']);
    }

    /**
     * Return server variables associated with this variable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serverVariable()
    {
        return $this->hasMany(ServerVariable::class, 'variable_id');
    }
}
