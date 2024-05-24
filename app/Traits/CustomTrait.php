<?php
/**
 * CustomTrait trait
 *
 * @category CustomTrait
 * @author   Codal <developer@codal.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://local.legionmedia.com/
 */

namespace App\Traits;

use App\Models\TimeZone;
use Carbon\Carbon;

/**
 * CustomTrait trait
 *
 * @category CustomTrait
 * @author   Codal <developer@codal.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://local.legionmedia.com/
 */
trait CustomTrait
{
    public $customFieldsGet = ['encryptable', 'convertTime'];
    public $customFieldsSet = ['encryptable', 'convertTime'];
    /**
     * If the attribute is in the -
     * 1. encryptable array in model then decrypt it. used while fetching from db
     * 2. convertTime array in model then convert it to currently logged in user's timezone
     * @param  $key
     *
     * @return $value
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (!empty($value)) {
            foreach ($this->customFieldsGet as $field) {
                if (isset($this->$field) && in_array($key, $this->$field)) {
                    switch ($field) {
                        case 'encryptable':
                            $value = is_null($value) ? $value : decrypt($value);
                            break;
                        case 'convertTime':
                            $timezone = auth()->user()->timezone ?? config('app.timezone');
                            $timeZoneData = TimeZone::find($timezone);
                            if ($timeZoneData) {
                                $timezone = $timeZoneData->timezone_value;
                            }
                            $value = Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC')
                                ->setTimezone($timezone);
                            if (isset($this->humanReadable) && in_array($key, $this->humanReadable)) {
                                $this->attributes['human_' . $key] = $value->diffForHumans(null, true) . ' Ago';
                            }
                            $value = $value->format('Y-m-d H:i:s');
                            break;
                    }
                }
            }
        }
        return $value;
    }

    /**
     * If the attribute is in the -
     * 1. encryptable array in model then encrypt it. used while storing in db
     *
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        foreach ($this->customFieldsSet as $field) {
            if (isset($this->$field) && in_array($key, $this->$field)) {
                switch ($field) {
                    case 'encryptable':
                        $value = is_null($value) ? $value : encrypt($value);
                        break;
                    case 'convertTime':
                        if ($value) {
                            $value = Carbon::createFromFormat(
                                'Y-m-d H:i:s',
                                $value,
                                date_default_timezone_get()
                            )->setTimezone('UTC');
                        }
                        break;
                }
            }
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * When need to make sure that we iterate through
     * all the keys.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($this->customFieldsGet as $field) {
            if (isset($this->$field)) {
                foreach ($this->$field as $key) {
                    if (!empty($attributes[$key])) {
                        switch ($field) {
                            case 'encryptable':
                                $attributes[$key] = decrypt($attributes[$key]);
                                break;
                            case 'convertTime':
                                $timezone = auth()->user()->timezone ?? config('app.timezone');
                                $timeZoneData = TimeZone::find($timezone);
                                if ($timeZoneData) {
                                    $timezone = $timeZoneData->timezone_value;
                                }
                                $attributes[$key] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes[$key], 'UTC')
                                    ->setTimezone($timezone);
                                if (isset($this->humanReadable) && in_array($key, $this->humanReadable)) {
                                    $attributes['human_'.$key] = $attributes[$key]->diffForHumans(null, true) . ' Ago';
                                }
                                $attributes[$key] = $attributes[$key]->format('Y-m-d H:i:s');
                                break;
                        }
                    }
                }
            }
        }

        return $attributes;
    }
}
