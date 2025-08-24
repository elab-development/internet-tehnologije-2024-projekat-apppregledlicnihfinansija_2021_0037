<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;



class TransactionController extends Controller
{
    // GET /api/v1/transactions
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));
        $query = Transaction::query()
            ->with('category')
            ->where('user_id', $request->user()->id);

        // Filtri
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')); // income|expense
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->date('to'));
        }

        // Sort (opciono): ?sort=-date ili ?sort=amount
        $sort = $request->get('sort', '-date');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        if (!in_array($column, ['date','amount','created_at'])) {
            $column = 'date';
        }
        $query->orderBy($column, $direction);

        $page = $query->paginate($perPage);

        // Resource kolekcija (ima links + meta automatski)
        return TransactionResource::collection($page);
    }

    // POST /api/v1/transactions
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'        => ['required','in:income,expense'],
            'amount'      => ['required','numeric','min:0.01'],
            'date'        => ['required','date'],
            'category_id' => ['nullable','exists:categories,id'],
            'description' => ['nullable','string','max:1000'],
        ]);

        $data['user_id'] = $request->user()->id;

        $trx = Transaction::create($data);

        return (new TransactionResource($trx->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/v1/transactions/{transaction}
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return new TransactionResource($transaction->load('category'));
    }

    // PUT/PATCH /api/v1/transactions/{transaction}
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'type'        => ['required','in:income,expense'],
            'amount'      => ['required','numeric','min:0.01'],
            'date'        => ['required','date'],
            'category_id' => ['nullable','exists:categories,id'],
            'description' => ['nullable','string','max:1000'],
        ]);

        $transaction->update($data);

        return new TransactionResource($transaction->load('category'));
    }

    // DELETE /api/v1/transactions/{transaction}
    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $transaction->delete();
        return response()->noContent(); // 204
    }

    // GET /api/v1/categories/{category}/transactions  (dodatna, "nested")
    public function byCategory(Request $request, Category $category)
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        $page = Transaction::with('category')
            ->where('user_id', $request->user()->id)
            ->where('category_id', $category->id)
            ->orderByDesc('date')
            ->paginate($perPage);

        return TransactionResource::collection($page);
    }

    // POST /api/v1/transactions/bulk  (dodatna, "bulk insert")
    public function bulkStore(Request $request)
    {
        $payload = $request->validate([
            'transactions'              => ['required','array','min:1'],
            'transactions.*.type'       => ['required','in:income,expense'],
            'transactions.*.amount'     => ['required','numeric','min:0.01'],
            'transactions.*.date'       => ['required','date'],
            'transactions.*.category_id'=> ['nullable','exists:categories,id'],
            'transactions.*.description'=> ['nullable','string','max:1000'],
        ]);

        $userId = $request->user()->id;

        $created = [];
        DB::transaction(function () use ($payload, $userId, &$created) {
            foreach ($payload['transactions'] as $t) {
                $t['user_id'] = $userId;
                $created[] = Transaction::create($t);
            }
        });

        return TransactionResource::collection(collect($created))->response()->setStatusCode(201);
    }

    public function export(Request $request)
{
    $format = $request->query('format');
    // ako si pogodila /export.pdf rutu, pretpostavi pdf; inače csv
    if (!$format) {
        $format = $request->routeIs('api.transactions.export.pdf') ? 'pdf' : 'csv';
    }

    $query = Transaction::query()
        ->with('category')
        ->where('user_id', $request->user()->id);

    // isti filteri kao u index:
    if ($request->filled('type')) {
        $query->where('type', $request->string('type'));
    }
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->integer('category_id'));
    }
    if ($request->filled('from')) {
        $query->whereDate('date', '>=', $request->date('from'));
    }
    if ($request->filled('to')) {
        $query->whereDate('date', '<=', $request->date('to'));
    }

    $transactions = $query->orderBy('date')->get();

    // ===== PDF =====
    if ($format === 'pdf') {
        $pdf = Pdf::loadView('exports.transactions', [
            'transactions' => $transactions,
            'user'         => $request->user(),
            'generatedAt'  => now(),
            'filters'      => [
                'type'        => $request->input('type'),
                'category_id' => $request->input('category_id'),
                'from'        => $request->input('from'),
                'to'          => $request->input('to'),
            ],
        ])->setPaper('a4', 'portrait');

        $filename = 'transactions_'.now()->format('Y-m-d_H-i').'.pdf';
        return $pdf->download($filename);
    }

    // ===== CSV (stream) =====
    $filename = 'transactions_'.now()->format('Y-m-d_H-i').'.csv';
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Cache-Control'       => 'no-store, no-cache',
    ];

    $callback = function () use ($transactions) {
        $out = fopen('php://output', 'w');
        // UTF-8 BOM (da Excel lijepo čita čćžđš)
        fwrite($out, "\xEF\xBB\xBF");

        // header
        fputcsv($out, ['Date', 'Type', 'Category', 'Amount', 'Description']);

        foreach ($transactions as $t) {
            fputcsv($out, [
                optional($t->date)->format('Y-m-d'),
                $t->type,
                optional($t->category)->name,
                number_format((float)$t->amount, 2, '.', ''),
                $t->description,
            ]);
        }
        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}
}
