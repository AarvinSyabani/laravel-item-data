<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Item extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'category_id',
        'supplier_id',
    ];

    /**
     * Attributes that should have special protection
     */
    protected $protected = [
        'sku',
        'price'
    ];

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'description', 'sku', 'price', 'stock', 
                'category_id', 'supplier_id', 'category.name', 'supplier.name'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Log when stock is updated
        static::updating(function ($model) {
            if ($model->isDirty('stock')) {
                $oldStock = $model->getOriginal('stock');
                $newStock = $model->stock;
                
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($model)
                    ->log("Stock changed from {$oldStock} to {$newStock}");
            }
        });
        
        // Log when price is updated
        static::updating(function ($model) {
            if ($model->isDirty('price')) {
                $oldPrice = $model->getOriginal('price');
                $newPrice = $model->price;
                
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($model)
                    ->log("Price changed from {$oldPrice} to {$newPrice}");
            }
        });
    }

    /**
     * Get the SKU with a security hash
     */
    public function getSecureSKUAttribute(): string
    {
        return EncryptionHelper::hash($this->sku);
    }

    /**
     * Verify if a provided SKU matches this item
     */
    public function verifySKU(string $sku): bool
    {
        return EncryptionHelper::hashEquals($sku, $this->secure_SKU);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
