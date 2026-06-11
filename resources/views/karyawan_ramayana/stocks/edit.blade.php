@extends('layouts.master')
@section('title', 'Edit Stok Counter')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('karyawan_ramayana.stocks.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Edit Stok: {{ $sku }}</h2>
    </div>

    @if($errors->any())
        <div class="bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('karyawan_ramayana.stocks.update-catalog') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden" x-data="editStockHandler()">
        @csrf
        @method('PUT')
        <div class="p-6 md:p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- SKU -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Produk (SKU)</label>
                    <input type="text" name="sku" value="{{ $sku }}" readonly
                        class="w-full bg-slate-100 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl p-3 cursor-not-allowed">
                </div>
                
                <!-- No warna anymore -->
            </div>

            <!-- Grid Input Qty (Pasang) -->
            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SISA STOK (PASANG) SAAT INI</h3>
                    
                    <!-- Form Tambah Size Baru -->
                    <div class="flex items-center gap-2">
                        <input type="number" x-model.number="newSize" placeholder="Size (cth: 38)" 
                            class="w-28 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-2.5 py-1 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-semibold"
                            @keydown.enter.prevent="addSize()">
                        <button type="button" @click="addSize()" 
                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-colors">
                            + Size
                        </button>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem;">
                    <template x-for="size in sortedSizes()" :key="size">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-center shadow-sm flex flex-col items-center justify-center">
                            <label :for="'size_' + size" class="block text-[12px] font-black text-blue-600 dark:text-blue-400 mb-1" x-text="size"></label>
                            <input type="number" :id="'size_' + size" :name="'sizes[' + size + ']'" x-model.number="quantities[size]" min="0" inputmode="numeric" pattern="[0-9]*"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-center rounded focus:ring-2 focus:ring-blue-500 p-1 font-bold text-base transition-colors h-10" placeholder="0">
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ringkasan Total -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <div class="text-left">
                    <p class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Stok Terbaru</p>
                    <p class="text-2xl font-black text-blue-700 dark:text-blue-400">
                        <span x-text="calculateTotal()"></span> PSG
                    </p>
                </div>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-lg flex items-center gap-2">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function editStockHandler() {
        const initialSizes = @json($sizes);
        return {
            quantities: initialSizes,
            newSize: '',
            sortedSizes() {
                return Object.keys(this.quantities)
                    .map(Number)
                    .filter(n => !isNaN(n))
                    .sort((a, b) => a - b);
            },
            addSize() {
                const size = parseInt(this.newSize);
                if (size && !isNaN(size) && size > 0) {
                    if (this.quantities[size] === undefined) {
                        this.quantities[size] = 0;
                    }
                    this.newSize = '';
                }
            },
            calculateTotal() {
                let total = 0;
                for (let key in this.quantities) {
                    if (this.quantities.hasOwnProperty(key)) {
                        total += parseInt(this.quantities[key]) || 0;
                    }
                }
                return total;
            }
        }
    }
</script>
@endpush
@endsection
