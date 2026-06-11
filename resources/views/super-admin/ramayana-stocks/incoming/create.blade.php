@extends('layouts.master')
@section('title', 'Input Barang Masuk - ' . ($user->location->name ?? $user->name))
@section('content')

<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="space-y-6" x-data="incomingStockForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('super-admin.ramayana-stocks.show', $user->id) }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-blue-600 mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Kartu Stok
            </a>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Input Barang Masuk</h1>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-2">
                {{ $user->name }} - {{ $user->location->name ?? 'Belum Ada Lokasi' }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-xl font-medium text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('super-admin.ramayana-stocks.incoming.store', $user->id) }}" method="POST" id="incoming-form" class="space-y-6">
        @csrf

        <!-- Card Informasi Surat Jalan -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Informasi Penerimaan</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tanggal DO / Surat Jalan <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Keterangan / Nomor DO (Opsional)</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="Contoh: DO No. 1234 / Kiriman Pusat"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors">
                </div>
            </div>
        </div>

        <!-- Card Daftar Barang -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Daftar Barang Masuk</h3>
                <div class="text-sm font-bold text-slate-500 dark:text-slate-400">
                    Total Qty: <span class="text-blue-600 dark:text-blue-400 text-xl" x-text="totalQty()"></span>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-4" id="items-container">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="flex flex-col md:flex-row gap-4 items-end p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                            <!-- Hidden inputs for validation & storing extra data -->
                            <input type="hidden" :name="'items['+index+'][sku]'" x-model="item.sku">
                            <input type="hidden" :name="'items['+index+'][kode_barang]'" x-model="item.kode_barang">
                            <input type="hidden" :name="'items['+index+'][size]'" x-model="item.size">
                            <!-- removed warna -->
                            <input type="hidden" :name="'items['+index+'][satuan]'" x-model="item.satuan">
                            
                            <div class="w-full md:w-3/5">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">Pilih Barang</label>
                                <div class="relative" x-init="initSelect2($el, index)">
                                    <select class="sku-select w-full" style="width: 100%;" required>
                                        <option value="">Pilih SKU Barang...</option>
                                        @foreach($existingSkus as $sku)
                                            <option value="{{ $sku->sku }}" 
                                                    data-kode="{{ $sku->kode_barang }}"
                                                    data-size="{{ $sku->size }}"
                                                    data-satuan="{{ $sku->satuan }}">
                                                {{ $sku->kode_barang ? '['.$sku->kode_barang.'] ' : '' }}{{ $sku->sku }}{{ $sku->size ? ' ('.$sku->size.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="w-full md:w-1/5">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">Qty Masuk</label>
                                <div class="relative">
                                    <input type="number" :name="'items['+index+'][qty]'" x-model="item.qty" min="1" required
                                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 p-2.5 transition-colors font-bold text-center">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-sm font-semibold" x-text="item.satuan || 'PSG'"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-auto flex justify-end">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                    class="p-2.5 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors" title="Hapus Baris">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6">
                    <button type="button" @click="addItem()" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:hover:bg-slate-700 rounded-xl text-sm font-bold transition-all border border-slate-200 dark:border-slate-600 border-dashed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Baris Barang
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('super-admin.ramayana-stocks.show', $user->id) }}" class="px-6 py-3 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl font-bold transition-colors">Batal</a>
            <button type="submit" onclick="return confirm('Pastikan data barang masuk sudah benar. Lanjutkan menyimpan?');" class="px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-colors">
                Simpan Barang Masuk
            </button>
        </div>
    </form>
</div>

<style>
    /* Styling for Select2 to match Tailwind */
    .select2-container .select2-selection--single {
        height: 46px;
        border: 1px solid #cbd5e1; /* slate-300 */
        border-radius: 0.5rem; /* rounded-lg */
        display: flex;
        align-items: center;
        background-color: #fff;
    }
    .dark .select2-container .select2-selection--single {
        background-color: #1e293b; /* slate-800 */
        border-color: #475569; /* slate-600 */
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a; /* slate-900 */
        font-weight: 500;
        padding-left: 0.75rem;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f8fafc; /* slate-50 */
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
        right: 8px;
    }
    .select2-dropdown {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    .dark .select2-dropdown {
        background-color: #1e293b;
        border-color: #475569;
    }
    .select2-container--default .select2-results__option--selected {
        background-color: #e2e8f0;
    }
    .dark .select2-container--default .select2-results__option--selected {
        background-color: #334155;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #3b82f6; /* blue-500 */
    }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('incomingStockForm', () => ({
        items: [
            { id: Date.now(), sku: '', kode_barang: '', size: '', satuan: 'PSG', qty: 1 }
        ],
        
        addItem() {
            this.items.push({ 
                id: Date.now(), 
                sku: '', 
                kode_barang: '', 
                size: '', 
                satuan: 'PSG', 
                qty: 1 
            });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        
        totalQty() {
            return this.items.reduce((sum, item) => sum + (parseInt(item.qty) || 0), 0);
        },

        initSelect2(el, index) {
            // Karena Select2 mengubah DOM secara manual, kita inisiasi di dalam element setelah dirender Alpine
            setTimeout(() => {
                const selectEl = $(el).find('select');
                selectEl.select2({
                    placeholder: "Pilih SKU Barang...",
                    allowClear: true
                });

                selectEl.on('select2:select', (e) => {
                    const data = e.params.data.element.dataset;
                    this.items[index].sku = e.params.data.id;
                    this.items[index].kode_barang = data.kode || '';
                    this.items[index].size = data.size || '';
                    this.items[index].satuan = data.satuan || 'PSG';
                });

                selectEl.on('select2:unselect', (e) => {
                    this.items[index].sku = '';
                    this.items[index].kode_barang = '';
                    this.items[index].size = '';
                    this.items[index].satuan = 'PSG';
                });
            }, 100);
        }
    }));
});
</script>
@endsection
