<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * ワークフローに属するノードを取得
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }
}
