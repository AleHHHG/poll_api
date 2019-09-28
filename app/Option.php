<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{

	protected $table = 'option';

	protected $fillable = ['description', 'votes'];

	public $timestamps = false;
    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo('App\Poll');
    }
}
