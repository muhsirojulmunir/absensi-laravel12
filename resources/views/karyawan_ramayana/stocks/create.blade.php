@extends('layouts.master')
@section('title', 'Input Stok Masuk')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="stockInputHandler()">
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('karyawan_ramayana.stocks.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Input Stok Masuk Baru</h2>
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

    <form action="{{ route('karyawan_ramayana.stocks.store') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        @csrf
        <div class="p-6 md:p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tanggal Input -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tanggal Stok Masuk <span class="text-red-500">*</span></label>
                    <input type="date" name="date" required x-model="date"
                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors dark:[color-scheme:dark]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- SKU -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Pilih Produk (SKU) <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" required
                        placeholder="Contoh: Zenix"
                        class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors">
                </div>
                
                <!-- Warna input removed -->
            </div>

            <!-- Kategori Ukuran Diganti Custom -->
            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Tentukan Range Ukuran (Size)</label>
                <div class="flex items-end gap-4 max-w-md">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Mulai Size</label>
                        <input type="number" x-model.number="startSize" min="1" placeholder="Contoh: 26"
                            class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Sampai Size</label>
                        <input type="number" x-model.number="endSize" min="1" placeholder="Contoh: 31"
                            class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors">
                    </div>
                    <div>
                        <button type="button" @click="generateSizes" class="px-5 py-3 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                            Buat Kolom
                        </button>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Ketik ukuran awal dan akhir (misal 26 sampai 31), lalu klik Buat Kolom untuk memunculkan kotak isian jumlah.</p>
            </div>

            <!-- Grid Input Qty (Pasang) -->
            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700" x-show="sizes.length > 0" x-cloak>
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wider">JUMLAH PER UKURAN (PASANG)</h3>
                
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem;">
                    <template x-for="size in sizes" :key="size">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-center shadow-sm flex flex-col items-center justify-center">
                            <label :for="'size_'+size" class="block text-[12px] font-black text-blue-600 dark:text-blue-400 mb-1" x-text="size"></label>
                            <input type="number" :id="'size_'+size" :name="'sizes['+size+']'" x-model.number="quantities[size]" min="0" inputmode="numeric" pattern="[0-9]*"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-center rounded focus:ring-2 focus:ring-blue-500 p-1 font-bold text-base transition-colors h-10" placeholder="0">
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ringkasan Total (Opsional, Update Otomatis) -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center" x-show="sizes.length > 0">
                <div class="text-left">
                    <p class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Stok Masuk</p>
                    <p class="text-2xl font-black text-blue-700 dark:text-blue-400">
                        <span x-text="calculateTotal()"></span> PSG
                    </p>
                </div>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center gap-2">
                    Simpan & Kirim Stok
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function stockInputHandler() {
        return {
            date: '{{ \Carbon\Carbon::now()->toDateString() }}',
            startSize: '',
            endSize: '',
            sizes: [],
            quantities: {},
            
            generateSizes() {
                if(!this.startSize || !this.endSize) {
                    alert('Harap isi Mulai Size dan Sampai Size terlebih dahulu!');
                    return;
                }
                
                let start = parseInt(this.startSize);
                let end = parseInt(this.endSize);
                
                if(start > end) {
                    let temp = start;
                    start = end;
                    end = temp;
                    this.startSize = start;
                    this.endSize = end;
                }
                
                // Jika terlalu besar rangenya, batasi
                if((end - start) > 20) {
                    alert('Maksimal rentang ukuran adalah 20 untuk sekali input!');
                    return;
                }
                
                this.sizes = [];
                let newQuantities = {};
                for(let i = start; i <= end; i++) {
                    this.sizes.push(i);
                    newQuantities[i] = 0;
                }
                this.quantities = newQuantities;
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
