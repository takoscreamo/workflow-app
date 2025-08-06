<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Node extends Model
{
    protected $fillable = [
        'workflow_id',
        'node_type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    /**
     * ノードが属するワークフローを取得
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
