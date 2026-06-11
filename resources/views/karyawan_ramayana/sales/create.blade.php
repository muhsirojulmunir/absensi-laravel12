@extends('layouts.master')
@section('title', 'Input Penjualan')
@section('content')

<!-- TomSelect CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<!-- Custom Style for TomSelect to match Tailwind / Dark Mode -->
<style>
    .ts-control {
        border-radius: 0.75rem !important; /* rounded-xl */
        border-color: #cbd5e1 !important; /* border-slate-300 */
        padding: 0.75rem 1rem !important; /* p-3 */
        background-color: #f8fafc !important; /* bg-slate-50 */
        font-weight: 500 !important;
        font-family: inherit !important;
        font-size: 0.875rem !important;
    }
    .ts-dropdown {
        border-radius: 0.75rem !important;
        overflow: hidden;
        border-color: #cbd5e1 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        font-size: 0.875rem !important;
    }
    .dark .ts-control {
        background-color: #1e293b !important; /* bg-slate-800 */
        border-color: #334155 !important; /* border-slate-700 */
        color: #fff !important;
    }
    .dark .ts-dropdown {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #fff !important;
    }
    .dark .ts-dropdown .option {
        color: #fff !important;
    }
    .dark .ts-dropdown .option:hover, .dark .ts-dropdown .option.active {
        background-color: #334155 !important;
        color: #fff !important;
    }
    .dark .ts-control input {
        color: #fff !important;
    }
    .ts-control > input { display: inline-block !important; }
</style>

<div class="max-w-4xl mx-auto space-y-6" x-data="salesInputHandler()">
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('karyawan_ramayana.sales.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Input Penjualan Harian</h2>
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

    <form action="{{ route('karyawan_ramayana.sales.store') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        @csrf
        <div class="p-6 md:p-8 space-y-8">
            <!-- Tanggal Input -->
            <div class="max-w-sm">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tanggal Penjualan <span class="text-red-500">*</span></label>
                <input type="date" name="date" required x-model="date"
                    class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors dark:[color-scheme:dark]">
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-widest">Detail Terjual</h3>
                    <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-blue-50/50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-black uppercase tracking-widest rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors border border-blue-200 dark:border-blue-800">
                        + Tambah Baris
                    </button>
                </div>

                <!-- Daftar Input Dinamis -->
                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="grid grid-cols-12 gap-4 items-start bg-slate-50 dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 relative">
                            <!-- Tombol Hapus (Muncul jika > 1) -->
                            <button type="button" x-show="items.length > 1" @click="removeItem(item.id)" class="absolute -right-2 -top-2 w-6 h-6 bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-900 transition-colors border border-red-200 dark:border-red-800 shadow-sm z-10">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <!-- Pilihan Produk (Dropdown) -->
                            <div class="col-span-12 md:col-span-6 relative">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Pilih Produk <span class="text-red-500">*</span></label>
                                <!-- Kita hanya pakai element select yang dikendalikan oleh TomSelect -->
                                <select :name="`items[${index}][product_key]`" required class="w-full product-select">
                                    <option value="">Ketik untuk mencari produk...</option>
                                    @foreach($availableStocks as $stock)
                                        @php
                                            $key = $stock->sku . '|' . $stock->size . '|' . $stock->satuan;
                                            $label = $stock->sku;
                                            if ($stock->size) $label .= " (Size " . $stock->size . ")";
                                            $label .= " — Sisa Stok: " . $stock->total_qty . " " . ($stock->satuan ?? 'PSG');
                                        @endphp
                                        <option value="{{ $key }}" data-qty="{{ $stock->total_qty }}" data-satuan="{{ $stock->satuan ?? 'PSG' }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Qty -->
                            <div class="col-span-6 md:col-span-3 relative">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Qty (<span x-text="item.satuan"></span>) <span class="text-red-500">*</span></label>
                                <input type="number" :name="`items[${index}][qty]`" x-model.number="item.qty" required min="1"
                                    placeholder="1"
                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 text-sm transition-colors">
                                <p class="text-[10px] mt-1 font-semibold" :class="item.maxQty > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" x-text="item.product_key !== '' ? 'Sisa stok saat ini: ' + item.maxQty : 'Pilih produk dulu'"></p>
                            </div>

                            <!-- Nominal -->
                            <div class="col-span-6 md:col-span-3">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Harga Total (Rp) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-semibold">Rp</span>
                                    <input type="text" :name="`items[${index}][nominal]`" x-model="item.nominal" @input="formatNominal(index, $event.target.value)" required
                                        placeholder="200.000" inputmode="numeric"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 pl-10 p-3 text-sm transition-colors">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Ringkasan Total -->
                <div class="mt-6 flex justify-end">
                    <div class="bg-blue-50 dark:bg-blue-900/20 px-6 py-4 rounded-xl border border-blue-100 dark:border-blue-900/30 text-right">
                        <p class="text-xs font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Total Nilai Penjualan</p>
                        <p class="text-xl font-black text-blue-700 dark:text-blue-300">
                            Rp <span x-text="calculateTotal()"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0">
                    Simpan Data Penjualan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- TomSelect JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    function salesInputHandler() {
        return {
            date: '{{ date('Y-m-d') }}',
            items: [
                { id: Date.now(), product_key: '', qty: 1, maxQty: 0, nominal: '', satuan: 'PSG' }
            ],
            
            init() {
                // Initialize TomSelect for the first item on load
                this.$nextTick(() => {
                    this.initTomSelect(document.querySelector('.product-select'), 0);
                });
            },
            
            addItem() {
                let newIndex = this.items.length;
                this.items.push({ id: Date.now(), product_key: '', qty: 1, maxQty: 0, nominal: '', satuan: 'PSG' });
                // Initialize TomSelect for the newly added select element
                this.$nextTick(() => {
                    let selects = document.querySelectorAll('.product-select');
                    let newSelect = selects[selects.length - 1];
                    this.initTomSelect(newSelect, newIndex);
                });
            },
            
            removeItem(id) {
                // Hapus instance TomSelect jika perlu (opsional karena elemen dihapus)
                this.items = this.items.filter(item => item.id !== id);
                // Kita perlu rebuild TomSelect indexes? 
                // Tidak perlu karena x-for mengikat model, tapi index parameter di TomSelect onChange mungkin bergeser.
                // Pendekatan lebih aman: biarkan index Alpine mereset, kita gunakan object state tracking di TomSelect onChange.
            },
            
            formatNominal(index, value) {
                let num = value.replace(/[^0-9]/g, '');
                if(num) {
                    this.items[index].nominal = new Intl.NumberFormat('id-ID').format(num);
                } else {
                    this.items[index].nominal = '';
                }
            },
            
            calculateTotal() {
                let total = 0;
                this.items.forEach(item => {
                    let num = parseInt(item.nominal.replace(/[^0-9]/g, '')) || 0;
                    // Harga Total tidak dikalikan qty lagi di sini karena "Harga Total" sudah diminta pengguna sebagai total keseluruhan baris.
                    // Namun jika sebelumnya qty * nominal, maka kita kalikan. Kita abaikan perkalian sesuai design lama? 
                    // Design lama: total += (num * qty). Mari kita biarkan num * 1 agar sesuai jika nominal = Total Harga per baris.
                    // User mengisi "Harga Jual (Rp)", kita anggap harga SATUAN atau TOTAL? Label saya ubah jadi "Harga Total (Rp)"
                    total += num;
                });
                return new Intl.NumberFormat('id-ID').format(total);
            },
            
            initTomSelect(el, itemIndex) {
                let self = this;
                new TomSelect(el, {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Ketik untuk mencari produk...',
                    onChange: function(value) {
                        let itemObj = self.items.find(i => i.product_key === value || i.product_key === '');
                        let index = Array.from(document.querySelectorAll('.product-select')).indexOf(el);
                        
                        if(index > -1 && self.items[index]) {
                            self.items[index].product_key = value;
                            if(value) {
                                let realOption = Array.from(el.options).find(o => o.value === value);
                                if(realOption) {
                                    self.items[index].maxQty = parseInt(realOption.dataset.qty) || 0;
                                    self.items[index].satuan = realOption.dataset.satuan || 'PSG';
                                } else {
                                    self.items[index].maxQty = 0;
                                    self.items[index].satuan = 'PSG';
                                }
                            } else {
                                self.items[index].maxQty = 0;
                                self.items[index].satuan = 'PSG';
                            }
                        }
                    }
                });
            }
        }
    }
</script>

@endsection
