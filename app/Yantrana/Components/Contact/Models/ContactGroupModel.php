<?php
/**
* ContactGroup.php - Model file
*
* This file is part of the Contact component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Contact\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ContactGroupModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'contact_groups';

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
    ];

    /**
     * Get the contacts in this group
     *
     * @return HasManyThrough
     */
    public function contacts(): HasManyThrough
    {
        return $this->hasManyThrough(
            ContactModel::class,
            GroupContactModel::class,
            'contact_groups__id', // Foreign key on group_contacts table
            '_id', // Foreign key on contacts table
            '_id', // Local key on contact_groups table
            'contacts__id' // Local key on group_contacts table
        );
    }
}
