<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{

	protected $table = 'poll';

	protected $with = ['options'];

   	protected $fillable = ['description'];

   	public $timestamps = false;

    /**
     * Get the comments for the blog post.
     */
    public function options()
    {
        return $this->hasMany('App\Option');
    }

}
