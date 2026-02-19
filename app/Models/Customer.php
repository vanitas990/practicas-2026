<?php

namespace App\Models;
use App\Http\Controllers\VehicleController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'city',
        'district',
        'birth_date',
        'customer_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con vehículos
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Relación con ventas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación con registros de servicio
     */
    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    /**
     * Obtener total de compras
     */
    public function getTotalPurchasesAttribute()
    {
        return $this->sales()->where('status', 'completed')->sum('total_pen');
    }

    /**
     * Obtener total de servicios
     */
    public function getTotalServicesAttribute()
    {
        return $this->serviceRecords()->where('status', 'completed')->sum('total_pen');
    }

    /**
     * Obtener nombre completo con tipo de documento
     */
    public function getFullIdentificationAttribute()
    {
        return "{$this->document_type}: {$this->document_number} - {$this->name}";
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar clientes
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('document_number', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    /**
     * Verificar si es cliente frecuente (más de 5 compras)
     */
    public function isFrequentCustomer()
    {
        return $this->sales()->where('status', 'completed')->count() >= 5;
    }

    /**
     * Obtener última compra
     */
    public function getLastPurchase()
    {
        return $this->sales()->where('status', 'completed')->latest('sale_date')->first();
    }
}