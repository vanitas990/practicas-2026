<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Listar gastos
     */
    public function index(Request $request)
    {
        $query = Expense::with('creator');

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        if ($request->has('period')) {
            switch ($request->period) {
                case 'today':
                    $query->today();
                    break;
                case 'this_month':
                    $query->thisMonth();
                    break;
                case 'this_year':
                    $query->thisYear();
                    break;
            }
        }

        $sortBy = $request->get('sort_by', 'expense_date');
        $sortOrder = $request->get('sort_order', 'desc');

        $expenses = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $expenses
        ]);
    }

    /**
     * Crear gasto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description'     => 'required|string|max:255',
            'category'        => 'required|string|max:100',
            'amount_usd'      => 'nullable|numeric|min:0',
            'amount_pen'      => 'nullable|numeric|min:0',
            'exchange_rate'   => 'nullable|numeric|min:0',
            'payment_method'  => 'required|in:efectivo,tarjeta,transferencia,cheque',
            'payment_status'  => 'nullable|in:paid,pending',
            'supplier'        => 'nullable|string|max:255',
            'invoice_number'  => 'nullable|string|max:100',
            'expense_date'    => 'required|date',
            'notes'           => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Debe existir al menos un monto
        if (!$request->amount_usd && !$request->amount_pen) {
            return response()->json([
                'success' => false,
                'message' => 'Debe ingresar al menos un monto (USD o PEN)'
            ], 422);
        }

        // Crear gasto (created_by seguro aunque no haya login)
        $expense = Expense::create(array_merge(
            $validator->validated(),
            ['created_by' => auth()->id() ?? null]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Gasto registrado exitosamente',
            'data' => $expense
        ], 201);
    }

    /**
     * Ver gasto
     */
    public function show($id)
    {
        $expense = Expense::with('creator')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $expense
        ]);
    }

    /**
     * Actualizar gasto
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'description'    => 'sometimes|required|string|max:255',
            'category'       => 'sometimes|required|string|max:100',
            'amount_usd'     => 'nullable|numeric|min:0',
            'amount_pen'     => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:paid,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $expense->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Gasto actualizado exitosamente',
            'data' => $expense
        ]);
    }

    /**
     * Eliminar gasto
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto eliminado exitosamente'
        ]);
    }

    /**
     * Resumen de gastos
     */
    public function summary(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $query = Expense::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'this_month':
                $query->thisMonth();
                break;
            case 'this_year':
                $query->thisYear();
                break;
        }

        $expenses = $query->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'data' => [
                'total' => $expenses->sum('amount_pen'),
                'paid' => $expenses->where('payment_status', 'paid')->sum('amount_pen'),
                'pending' => $expenses->where('payment_status', 'pending')->sum('amount_pen'),
                'by_category' => $expenses->groupBy('category')->map(fn ($g) => [
                    'count' => $g->count(),
                    'total' => $g->sum('amount_pen'),
                ]),
                'by_payment_method' => $expenses->groupBy('payment_method')->map(fn ($g) => [
                    'count' => $g->count(),
                    'total' => $g->sum('amount_pen'),
                ]),
            ]
        ]);
    }

    /**
     * Categorías de gastos
     */
    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'compra_inventario' => 'Compra de Inventario',
                'operativo' => 'Gastos Operativos',
                'salarios' => 'Salarios',
                'servicios' => 'Servicios (Luz, Agua, Internet)',
                'impuestos' => 'Impuestos',
                'alquiler' => 'Alquiler',
                'marketing' => 'Marketing y Publicidad',
                'mantenimiento' => 'Mantenimiento',
                'transporte' => 'Transporte',
                'otros' => 'Otros',
            ]
        ]);
    }
}
