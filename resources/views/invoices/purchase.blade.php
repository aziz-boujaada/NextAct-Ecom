<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
        <header class="flex items-start justify-between p-8 bg-gradient-to-r from-purple-700 via-purple-500 to-pink-500 text-white">
            <div>
                <h1 class="text-2xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-indigo-200 via-pink-200 to-indigo-400">NextGestCo</h1>
                <p class="mt-1 text-sm text-white/80">Supplier Invoice</p>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase tracking-wider text-white/80">Invoice</p>
                <p class="text-2xl font-bold">{{ $invoice->invoice_number }}</p>
            </div>
        </header>

        <main class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 border border-purple-100 rounded-xl p-4">
                    <h3 class="text-xs font-bold text-purple-600 uppercase">Supplier</h3>
                    <p class="mt-2 text-sm text-gray-700 font-semibold">{{ $purchase->supplier?->name ?? 'Supplier' }}</p>
                    @if($purchase->supplier?->phone)
                        <p class="text-sm text-gray-500">{{ $purchase->supplier->phone }}</p>
                    @endif
                    @if($purchase->supplier?->address)
                        <p class="text-sm text-gray-500">{{ $purchase->supplier->address }}</p>
                    @endif
                </div>

                <div class="bg-gray-50 border border-purple-100 rounded-xl p-4">
                    <h3 class="text-xs font-bold text-purple-600 uppercase">Invoice Details</h3>
                    <p class="mt-2 text-sm"><strong>Purchase ID:</strong> {{ $purchase->id }}</p>
                    <p class="text-sm"><strong>Date:</strong> {{ $purchase->created_at?->format('M d, Y') }}</p>
                    <p class="text-sm"><strong>Status:</strong> <span class="inline-block px-3 py-1 rounded-full text-white text-xs font-semibold" style="background: linear-gradient(90deg,#6d28d9,#a855f7);">{{ ucfirst($purchase->status) }}</span></p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="min-w-full divide-y">
                    <thead class="bg-gradient-to-r from-purple-700 via-purple-500 to-pink-500 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Reference</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase">Price</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @foreach ($purchase->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->product?->name ?? 'Product' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->product?->reference ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-700">{{ number_format((float) $item->price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">{{ number_format((float) $item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <div class="w-full md:w-1/3 bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-100 rounded-lg p-4">
                    <div class="text-sm text-purple-600 font-semibold">Grand Total</div>
                    <div class="mt-2 text-2xl font-bold text-purple-700">{{ number_format((float) $invoice->total, 2) }}</div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <div class="text-center w-56">
                    <div class="border-t border-gray-300"></div>
                    <div class="mt-3 text-sm font-semibold text-gray-900">Authorized Signature</div>
                    <div class="text-xs text-purple-600 font-semibold">NextGestCo Administration</div>
                </div>
            </div>
        </main>

        <footer class="p-4 text-center bg-gray-50 text-sm text-gray-500">© {{ date('Y') }} <span class="font-semibold text-purple-600">NextGestCo</span></footer>
    </div>
</body>
</html>
