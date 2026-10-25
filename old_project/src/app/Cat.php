<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Ramsey\Uuid\Uuid;

class Cat extends Model
{
    use Notifiable;

    const METADATA_KEYS = [
        'cemetery_location_name',
        'cemetery_location_address',
        'cemetery_location_latitude',
        'cemetery_location_longitude',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'full_name',
        'gender_id', 
        'titles_before_name', 'titles_after_name', 'breed',
        'ems_color', 'chip_number', 'genetic_tests', 'dob',
        'sire_id', 'dam_id',
        'breeding_station', 'country_code', 'alternative_name', 
        'print_name_r1', 'print_name_r2', 'dod', 'original_reg_num', 
        'last_reg_num', 'reg_num_2', 'reg_num_3', 'notes', 'breeder', 
        'current_owner', 'country_of_origin', 'country', 
        'ownership_notes', 'personal_info', 'genetic_tests_file', 'photo', 'vet_confirmation'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $appends = [
        'gender',
    ];

    protected $casts = [
        'couples.pivot.id'  => 'string',
        'wifes.pivot.id'    => 'string',
        'husbands.pivot.id' => 'string',
    ];

    // protected $keyType = 'string';

    public function getGenderAttribute()
    {
        return $this->gender_id == 1 ? trans('app.male_code') : trans('app.female_code');
    }

    public function setSire(Cat $sire)
    {
        if ($sire->gender_id == 1) {

            if ($sire->exists == false) {
                $sire->save();
            }

            $this->sire_id = $sire->id;
            $this->save();

            return $sire;
        }

        return false;
    }

    public function setDam(Cat $dam)
    {
        if ($dam->gender_id == 2) {

            if ($dam->exists == false) {
                $dam->save();
            }

            $this->dam_id = $dam->id;
            $this->save();

            return $dam;
        }

        return false;
    }

    public function sire()
    {
        return $this->belongsTo(Cat::class);
    }

    public function dam()
    {
        return $this->belongsTo(Cat::class);
    }

    public function findBreedName() {
        $foundBreed = Breed::where('breed', '=', $this->breed)->first();
        if ($foundBreed instanceof \Illuminate\Database\Eloquent\Model) {
            return $foundBreed->name;
        }
        return 'Not in database';
    }

    public function findEMS() {
        $foundBreed = Breed::where('breed', '=', $this->breed)->first();
        if ($foundBreed instanceof \Illuminate\Database\Eloquent\Model) {
            $foundEMS = Ems::where('breed_id', '=', $foundBreed->id)->where('ems', '=', $this->ems_color)->first();
            if ($foundEMS instanceof \Illuminate\Database\Eloquent\Model) {
                return $foundEMS->english;
            }
        }
        return 'Not in database';
    }

    public function childs()
    {
        if ($this->gender_id == 2) {
            return $this->hasMany(Cat::class, 'dam_id');
        }

        return $this->hasMany(Cat::class, 'sire_id');
    }

    public function profileLink($type = 'profile')
    {
        $type = ($type == 'chart') ? 'chart' : 'show';
        return link_to_route('cats.'.$type, $this->full_name, [$this->id]);
    }

    public function l($generations = 5)
    {
        return link_to_route('cats.tree', $this->titles_before_name . ' ' . $this->full_name . ' ' . $this->titles_after_name, [$this->id, $generations], ['title' => $this->full_name.' ('.$this->gender.')']);
    }

    public function dob() {
        if ($this->dob == "1111-11-11") {
            return "";
        } else {
            return $this->dob;
        }
    }

    public function sireLink()
    {
        return $this->sire_id ? link_to_route('cats.show', $this->sire->full_name, [$this->sire_id]) : null;
    }

    public function damLink()
    {
        return $this->dam_id ? link_to_route('cats.show', $this->dam->full_name, [$this->dam_id]) : null;
    }

    public function s()
    {
        return $this->sire_id ? $this->sire : null;
    }

    public function d()
    {
        return $this->dam_id ? $this->dam : null;
    }

    public function id()
    {
        return $this->id != "1" ? $this->id : "";
    }

    /**
     * Get Breed
     *
     * @param int $genderId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBreed()
    {
        return Breed::select('breed','id')->where('breed',$this->breed)->pluck('id')->first();
    }

    public function titles_before_name() {
        return $this->titles_before_name ? $this->titles_before_name : "";
    }

    public function titles_after_name() {
        return $this->titles_after_name ? $this->titles_after_name : "";
    }

    public function full_name() {
        return $this->full_name;
    }

    public function wifes()
    {
        return $this->belongsToMany(Cat::class, 'couples', 'husband_id', 'wife_id')->using('App\CouplePivot')->withPivot(['id'])->withTimestamps()->orderBy('marriage_date');
    }

    public function addWife(Cat $wife, $marriageDate = null)
    {
        if ($this->gender_id == 1 && !$this->hasBeenMarriedTo($wife)) {
            $this->wifes()->save($wife, [
                'id'            => Uuid::uuid4()->toString(),
                'marriage_date' => $marriageDate,
            ]);
            return $wife;
        }

        return false;
    }

    public function husbands()
    {
        return $this->belongsToMany(Cat::class, 'couples', 'wife_id', 'husband_id')->using('App\CouplePivot')->withPivot(['id'])->withTimestamps()->orderBy('marriage_date');
    }

    public function addHusband(Cat $husband, $marriageDate = null)
    {
        if ($this->gender_id == 2 && !$this->hasBeenMarriedTo($husband)) {
            $this->husbands()->save($husband, [
                'id'            => Uuid::uuid4()->toString(),
                'marriage_date' => $marriageDate,
            ]);
            return $husband;
        }

        return false;
    }

    public function hasBeenMarriedTo(Cat $cat)
    {
        return $this->couples->contains($cat);
    }

    public function couples()
    {
        if ($this->gender_id == 1) {
            return $this->belongsToMany(Cat::class, 'couples', 'husband_id', 'wife_id')->using('App\CouplePivot')->withPivot(['id'])->withTimestamps()->orderBy('marriage_date');
        }

        return $this->belongsToMany(Cat::class, 'couples', 'wife_id', 'husband_id')->using('App\CouplePivot')->withPivot(['id'])->withTimestamps()->orderBy('marriage_date');
    }

    public function marriages()
    {
        if ($this->gender_id == 1) {
            return $this->hasMany(Couple::class, 'husband_id')->orderBy('marriage_date');
        }

        return $this->hasMany(Couple::class, 'wife_id')->orderBy('marriage_date');
    }

    public function siblings()
    {
        if (is_null($this->sire_id) && is_null($this->dam_id) && is_null($this->parent_id)) {
            return collect([]);
        }

        return Cat::where('id', '!=', $this->id)
            ->where(function ($query) {
                if (!is_null($this->sire_id)) {
                    $query->where('sire_id', $this->sire_id);
                }

                if (!is_null($this->dam_id)) {
                    $query->orWhere('dam_id', $this->dam_id);
                }

                if (!is_null($this->parent_id)) {
                    $query->orWhere('parent_id', $this->parent_id);
                }

            })
            ->get();
    }

    public function parent()
    {
        return $this->belongsTo(Couple::class);
    }

    public function manager()
    {
        return $this->belongsTo(Cat::class);
    }

    public function getAgeAttribute()
    {
        $ageDetail = null;
        $yearOnlySuffix = Carbon::now()->format('-m-d');

        if ($this->dob) {
            $ageDetail = Carbon::parse($this->dob)->diffInYears();
        }

        return $ageDetail;
    }

    public function getAgeDetailAttribute()
    {
        $ageDetail = null;
        $yearOnlySuffix = Carbon::now()->format('-m-d');

        if ($this->dob) {
            $ageDetail = Carbon::parse($this->dob)->timespan();
        }

        return $ageDetail;
    }

    public function getAgeStringAttribute()
    {
        return '<div title="'.$this->age_detail.'">'.$this->age.' '.trans_choice('cat.age_years', $this->age).'</div>';
    }

    public function getBirthdayAttribute()
    {
        if (!$this->dob) {
            return;
        }

        $birthdayDate = date('Y').substr($this->dob, 4);
        $birthdayDateClass = Carbon::parse($birthdayDate);

        if (Carbon::parse(date('Y-m-d').' 00:00:00')->gt($birthdayDateClass)) {
            return $birthdayDateClass->addYear();
        }

        return $birthdayDateClass;
    }

    public function getBirthdayRemainingAttribute()
    {
        if ($this->dob) {
            return Carbon::now()->diffInDays($this->birthday, false);
        }
    }

    public function metadata()
    {
        return $this->hasMany(CatMetadata::class, 'cat_id', 'id');
    }

    public function getMetadata($key = null, $defaultValue = null)
    {
        $metadata = $this->metadata;

        if (is_null($key)) {
            $metadataCollection = [];
            foreach ($metadata as $metaKey => $metaValue) {
                $metadataCollection[$metaKey] = $metaValue;
            }

            return collect($metadataCollection);
        }

        $meta = $metadata->filter(function ($meta) use ($key) {
            return $meta->key == $key;
        })->first();

        if ($meta) {
            return $meta->value;
        }

        return $defaultValue;
    }
}
