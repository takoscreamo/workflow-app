<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowModel extends Model
{
    protected $table = 'workflows';

    protected $fillable = [
        'name',
        'input_type',
        'output_type',
        'input_data',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(NodeModel::class, 'workflow_id');
    }
}
