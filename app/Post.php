<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'body_content', 'user_id',
    ];
    // Inverse Relationship
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    // PostFiles OneToMany Relationship
    public function postFiles(){
        return $this->hasMany('App\PostFile', 'post_id');
    }
}
