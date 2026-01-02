<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class SliderModel extends Model
{
    protected $table = 'sliders';

    protected $fillable = [
        'image_path',
        'title',
        'description',
        'link',
        'status',
        'order'
    ];
}
