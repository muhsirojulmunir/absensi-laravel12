@extends('layouts.master')

@section('title', 'Scanner Dokumen HRD')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6" x-data="scannerApp()" x-init="init()">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-2">
            <div class="space-y-1">
                <h1
                    class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-blue-500/10">
                        📸
                    </div>
                    <span>Scanner Dokumen HRD</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Scan banyak halaman laporan kertas
                    sekaligus dan gabungkan menjadi PDF berkualitas tinggi.</p>
            </div>
        </div>

        {{-- STEP 1: SELECT SOURCE / CAPTURE --}}
        <div x-show="step === 'source'"
            class="bg-white dark:bg-dark-card border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-md font-bold text-slate-800 dark:text-slate-200">
                    1. Pilih Sumber Gambar <span x-show="pages.length > 0"
                        class="text-xs font-normal text-slate-500">(Menambahkan ke dokumen yang sudah ada)</span>
                </h2>
                <button x-show="pages.length > 0" type="button" @click="step = 'preview'"
                    class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900 active:scale-95 transition-all cursor-pointer">
                    Kembali ke Preview ✕
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Camera Card --}}
                <div
                    class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-6 flex flex-col items-center justify-center text-center group hover:border-blue-500/50 transition-colors">
                    <div
                        class="w-16 h-16 bg-blue-50 dark:bg-blue-950/40 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">Gunakan Kamera HP / Laptop</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 max-w-[240px]">Ambil foto laporan kertas
                        secara langsung menggunakan kamera internal perangkat Anda.</p>
                    <button type="button" @click="startCamera()"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all active:scale-95 uppercase tracking-wider cursor-pointer">
                        Buka Kamera
                    </button>
                </div>

                {{-- Upload Card --}}
                <div
                    class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-6 flex flex-col items-center justify-center text-center group hover:border-blue-500/50 transition-colors">
                    <div
                        class="w-16 h-16 bg-blue-50 dark:bg-blue-950/40 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">Unggah Beberapa File Foto
                        sekaligus</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 max-w-[240px]">Unggah beberapa foto sekaligus
                        dari galeri perangkat Anda (JPEG, PNG).</p>

                    <label
                        class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-semibold text-xs rounded-xl shadow-sm transition-all active:scale-95 uppercase tracking-wider cursor-pointer">
                        Pilih File Foto
                        <input type="file" accept="image/*" class="hidden" multiple @change="handleFileUpload($event)">
                    </label>
                </div>
            </div>

            {{-- Camera Modal --}}
            <div x-show="showCameraModal" style="display:none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="stopCamera()"></div>
                <div
                    class="bg-white dark:bg-dark-card rounded-2xl shadow-xl relative z-10 w-full max-w-lg overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col">
                    <div
                        class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm">Ambil Foto Halaman</h3>
                        <button type="button" @click="stopCamera()"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                    </div>
                    <div class="relative bg-black aspect-[3/4] flex items-center justify-center">
                        <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                        {{-- Guide Overlay --}}
                        <div
                            class="absolute inset-8 border-2 border-dashed border-white/40 rounded-xl pointer-events-none flex items-center justify-center">
                            <span
                                class="text-[10px] text-white/50 bg-black/40 px-3 py-1 rounded-full uppercase tracking-wider font-bold">Posisikan
                                Kertas di Sini</span>
                        </div>
                    </div>
                    <div
                        class="p-5 flex justify-center bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="capturePhoto()"
                            class="w-14 h-14 bg-red-600 hover:bg-red-700 rounded-full border-4 border-white dark:border-slate-800 flex items-center justify-center shadow-lg active:scale-90 transition-all cursor-pointer">
                            <div class="w-5 h-5 bg-white rounded-full"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: CROP & WARP PERSPECTIVE (QUEUE RUNNER) --}}
        <div x-show="step === 'crop'" style="display:none;"
            class="bg-white dark:bg-dark-card border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm transition-all duration-300">
            <h2 class="text-md font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center justify-between">
                <span>2. Atur Sudut Halaman: <span class="text-blue-600" x-text="currentQueueItemName"></span></span>
                <span class="text-xs font-normal text-slate-500" x-show="rawQueue.length > 0">Sisa antrean: <span
                        x-text="rawQueue.length"></span> foto</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Geser ke-4 lingkaran biru tepat di sudut-sudut kertas
                laporan Anda untuk meluruskan kertas.</p>

            {{-- Cropping Container --}}
            <div
                class="flex justify-center bg-slate-100 dark:bg-slate-950 p-4 rounded-2xl overflow-hidden relative min-h-[300px]">
                <div class="relative inline-block max-w-full select-none" x-ref="cropContainer">
                    <img :src="imageSrc" class="max-h-[480px] w-auto max-w-full pointer-events-none rounded-lg"
                        @load="onImageLoaded($event)" x-ref="cropImg">

                    {{-- Interactive Overlay (SVG Lines) --}}
                    <svg class="absolute inset-0 pointer-events-none w-full h-full" x-ref="svgOverlay">
                        <polygon :points="polygonPoints" fill="rgba(59, 130, 246, 0.15)" stroke="#3b82f6"
                            stroke-width="2.5" />
                    </svg>

                    {{-- Draggable Corner Handles --}}
                    <template x-for="(pt, idx) in corners">
                        <div class="absolute w-8 h-8 -ml-4 -mt-4 bg-blue-500/20 hover:bg-blue-500/40 rounded-full flex items-center justify-center cursor-move touch-none z-20"
                            :style="'left: ' + (pt.x * containerW) + 'px; top: ' + (pt.y * containerH) + 'px;'"
                            @pointerdown="startDrag($event, idx)">
                            <div
                                class="w-3.5 h-3.5 bg-blue-600 border-2 border-white rounded-full shadow-md shadow-blue-500/30">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
                <div class="flex items-center gap-2">
                    <button type="button" @click="rotateImage()"
                        class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900 active:scale-95 transition-all cursor-pointer">
                        Putar 90° 🔄
                    </button>
                    <button type="button" @click="warpDefaultAndSaveCurrent()"
                        class="px-4 py-2 border border-slate-200 dark:border-slate-850 text-slate-600 dark:text-slate-400 font-semibold text-xs rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900 active:scale-95 transition-all cursor-pointer">
                        Lewati Pemotongan (Gunakan Default)
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button x-show="rawQueue.length > 0" type="button" @click="warpAllRemaining()"
                        class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow-sm transition-all active:scale-95 cursor-pointer">
                        Proses Semua Sisa Otomatis ⚡
                    </button>
                    <button type="button" @click="warpDocument()"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all active:scale-95 uppercase tracking-wider cursor-pointer">
                        Luruskan & Simpan Halaman ✨
                    </button>
                </div>
            </div>
        </div>

        {{-- STEP 3: PREVIEW & MANAGE MULTI-PAGE DOCUMENT --}}
        <div x-show="step === 'preview'" style="display:none;"
            class="bg-white dark:bg-dark-card border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm transition-all duration-300">

            <div style="display: flex; flex-wrap: wrap; gap: 2rem; width: 100%;">

                {{-- LEFT SIDEBAR: Page List & PDF settings --}}
                <div style="flex: 0 0 320px; max-width: 100%; min-width: 280px;" class="space-y-6">

                    <div>
                        <h3
                            class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 uppercase tracking-wider text-[11px] text-slate-400">
                            Pengaturan Berkas</h3>
                        <div
                            class="space-y-4 bg-slate-50 dark:bg-slate-900/60 p-4 border border-slate-100 dark:border-slate-800/80 rounded-2xl">
                            <div class="space-y-1.5">
                                <label
                                    class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Nama
                                    File PDF</label>
                                <input type="text" x-model="pdfName"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-blue-500 rounded-xl text-slate-900 dark:text-slate-100 px-3 py-2 text-xs font-semibold transition-all shadow-sm">
                            </div>

                            <button type="button" @click="downloadPDF()"
                                class="w-full inline-flex justify-center items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs py-3 rounded-xl shadow-md hover:shadow-lg transition-all uppercase tracking-widest cursor-pointer">
                                <span>Download PDF (<span x-text="pages.length"></span> Halaman) 📄</span>
                            </button>
                        </div>
                    </div>

                    {{-- Pages Thumbnails Panel --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px] text-slate-400">
                                Halaman Dokumen</h3>
                            <button type="button" @click="resetToSource()"
                                class="text-xs text-blue-600 hover:underline font-bold">+ Tambah Halaman</button>
                        </div>

                        <div class="space-y-2 max-h-[380px] overflow-y-auto pr-2" style="scrollbar-width: thin;">
                            <template x-for="(page, idx) in pages" :key="page.id">
                                <div @click="selectPage(idx)"
                                    :class="currentPageIndex === idx ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/40'"
                                    class="flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-colors relative group">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-12 bg-slate-100 dark:bg-slate-950 rounded overflow-hidden flex items-center justify-center border border-slate-200/50 dark:border-slate-900">
                                            <img :src="page.finalImageData" class="object-cover w-full h-full">
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200"
                                                x-text="'Halaman ' + (idx + 1)"></h4>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold"
                                                x-text="page.filter === 'magic' ? 'Magic Color' : (page.filter === 'bw' ? 'B&W' : 'Original')">
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click.stop="deletePage(idx)"
                                        class="w-6 h-6 bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-950/40 text-slate-400 hover:text-red-500 rounded-lg flex items-center justify-center transition-colors">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- RIGHT WORKSPACE: Filter adjustments for active page --}}
                <div style="flex: 1 1 450px;" class="space-y-6">

                    <div x-show="pages[currentPageIndex]" class="space-y-4">

                        {{-- Active Page Preview --}}
                        <div
                            class="flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-950 p-4 rounded-2xl overflow-hidden relative min-h-[320px] border border-slate-200/60 dark:border-slate-850">
                            <canvas x-ref="previewCanvas"
                                class="max-h-[460px] w-auto max-w-full shadow-lg rounded border border-white/10"></canvas>

                            <div
                                class="absolute bottom-4 left-4 bg-slate-900/80 backdrop-blur text-white text-[10px] font-bold px-3 py-1.5 rounded-lg uppercase tracking-wider">
                                Halaman Aktif: <span x-text="currentPageIndex + 1"></span> dari <span
                                    x-text="pages.length"></span>
                            </div>
                        </div>

                        {{-- Filters & Re-crop Controls --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900/60 p-4 border border-slate-100 dark:border-slate-800/80 rounded-2xl">
                            <div>
                                <span
                                    class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-2.5">PILIH
                                    FILTER HALAMAN INI</span>
                                <div class="flex gap-2">
                                    <button type="button" @click="applyFilter('original')"
                                        :class="pages[currentPageIndex]?.filter === 'original' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'"
                                        class="px-4 py-2 rounded-xl text-center text-xs font-bold transition-all cursor-pointer">
                                        Asli
                                    </button>
                                    <button type="button" @click="applyFilter('magic')"
                                        :class="pages[currentPageIndex]?.filter === 'magic' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'"
                                        class="px-4 py-2 rounded-xl text-center text-xs font-bold transition-all cursor-pointer">
                                        Magic Color
                                    </button>
                                    <button type="button" @click="applyFilter('bw')"
                                        :class="pages[currentPageIndex]?.filter === 'bw' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'"
                                        class="px-4 py-2 rounded-xl text-center text-xs font-bold transition-all cursor-pointer">
                                        Hitam Putih
                                    </button>
                                </div>
                            </div>

                            <button type="button" @click="recropActivePage()"
                                class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 active:scale-95 transition-all cursor-pointer">
                                Atur Ulang Sudut 📐
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Offscreen Hidden Processing Canvases --}}
        <canvas x-ref="rawCanvas" class="hidden"></canvas>
        <canvas x-ref="warpedCanvas" class="hidden"></canvas>
    </div>

    {{-- Load jsPDF from CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        function scannerApp() {
            return {
                step: 'source', // 'source', 'crop', 'preview'
                showCameraModal: false,
                stream: null,

                // Multi-page state
                pages: [], // array of objects: { id, name, rawImage, rotation, corners, warpedImage, finalImageData, filter }
                currentPageIndex: 0,

                // Upload queue state
                rawQueue: [], // queue of raw { name, src } uploaded/captured files
                currentQueueItemName: '',

                // Active page editing cache
                imageSrc: '',
                corners: [
                    { x: 0.15, y: 0.15 },
                    { x: 0.85, y: 0.15 },
                    { x: 0.85, y: 0.85 },
                    { x: 0.15, y: 0.85 }
                ],
                containerW: 100,
                containerH: 100,
                naturalW: 0,
                naturalH: 0,
                dragIdx: null,
                editingPageIndex: null, // index if we are editing an existing page, else null

                pdfName: 'laporan_scan_' + new Date().toISOString().slice(0, 10) + '.pdf',

                init() {
                    // Setup global pointer move/up handlers for dragging handles anywhere on the viewport
                    window.addEventListener('pointermove', (e) => this.handleDrag(e));
                    window.addEventListener('pointerup', () => this.endDrag());

                    // Adjust container display dimensions on window resize
                    window.addEventListener('resize', () => {
                        if (this.step === 'crop') {
                            this.updateContainerDimensions();
                        }
                    });
                },

                get polygonPoints() {
                    return `${this.corners[0].x * this.containerW},${this.corners[0].y * this.containerH} ` +
                        `${this.corners[1].x * this.containerW},${this.corners[1].y * this.containerH} ` +
                        `${this.corners[2].x * this.containerW},${this.corners[2].y * this.containerH} ` +
                        `${this.corners[3].x * this.containerW},${this.corners[3].y * this.containerH}`;
                },

                startCamera() {
                    this.showCameraModal = true;
                    navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 960 } }
                    })
                        .then(s => {
                            this.stream = s;
                            this.$refs.video.srcObject = s;
                        })
                        .catch(err => {
                            console.error("Camera access failed:", err);
                            Swal.fire({
                                title: 'Kamera Gagal Dibuka',
                                text: 'Tidak dapat mengakses kamera. Silakan periksa izin perangkat Anda atau unggah file foto secara langsung.',
                                icon: 'error',
                                confirmButtonColor: '#2563eb'
                            });
                            this.showCameraModal = false;
                        });
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.stream = null;
                    }
                    this.showCameraModal = false;
                },

                capturePhoto() {
                    if (!this.stream) return;

                    const video = this.$refs.video;
                    const canvas = this.$refs.rawCanvas;
                    const ctx = canvas.getContext('2d');

                    const w = video.videoWidth;
                    const h = video.videoHeight;
                    canvas.width = w;
                    canvas.height = h;
                    ctx.drawImage(video, 0, 0, w, h);

                    const dataUrl = canvas.toDataURL('image/jpeg');
                    this.stopCamera();

                    // Add to raw queue
                    this.rawQueue.push({
                        name: 'Foto Kamera ' + (this.pages.length + this.rawQueue.length + 1),
                        src: dataUrl
                    });
                    this.editingPageIndex = null;
                    this.processNextQueueItem();
                },

                async handleFileUpload(event) {
                    const files = event.target.files;
                    if (!files || files.length === 0) return;

                    // Load files to raw queue
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const dataUrl = await this.readFileAsync(file);
                        this.rawQueue.push({
                            name: file.name,
                            src: dataUrl
                        });
                    }

                    this.editingPageIndex = null;
                    this.processNextQueueItem();
                },

                readFileAsync(file) {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = (e) => resolve(e.target.result);
                        reader.readAsDataURL(file);
                    });
                },

                processNextQueueItem() {
                    if (this.rawQueue.length > 0) {
                        const item = this.rawQueue.shift();
                        this.currentQueueItemName = item.name;
                        this.imageSrc = item.src;
                        this.step = 'crop';
                    } else {
                        this.step = 'preview';
                        this.currentPageIndex = this.pages.length - 1;
                        this.$nextTick(() => {
                            this.renderActivePage();
                        });
                    }
                },

                onImageLoaded() {
                    this.updateContainerDimensions();

                    // Auto detect corners on image load using a downsampled offscreen canvas for noise reduction and maximum speed
                    const img = this.$refs.cropImg;

                    const maxDim = 500;
                    let dw = img.naturalWidth;
                    let dh = img.naturalHeight;
                    if (dw > maxDim || dh > maxDim) {
                        const ratio = Math.min(maxDim / dw, maxDim / dh);
                        dw = Math.round(dw * ratio);
                        dh = Math.round(dh * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = dw;
                    canvas.height = dh;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, dw, dh);

                    const imgData = ctx.getImageData(0, 0, dw, dh);
                    this.corners = this.detectPaperCorners(imgData, dw, dh);
                },

                detectPaperCorners(imageData, width, height) {
                    const data = imageData.data;

                    // Helper to extract pixel brightness
                    const getVal = (x, y) => {
                        const idx = (y * width + x) * 4;
                        return (data[idx] + data[idx + 1] + data[idx + 2]) / 3;
                    };

                    const topPoints = [];
                    const bottomPoints = [];
                    const leftPoints = [];
                    const rightPoints = [];

                    // Scan Top edges: look for peak vertical gradient transition (dark desk -> light paper)
                    for (let x = Math.round(width * 0.15); x < width * 0.85; x += 3) {
                        let maxGrad = -1;
                        let bestY = -1;
                        for (let y = Math.round(height * 0.02) + 2; y < height * 0.45 - 2; y++) {
                            const grad = getVal(x, y + 2) - getVal(x, y - 2);
                            if (grad > maxGrad) {
                                maxGrad = grad;
                                bestY = y;
                            }
                        }
                        if (maxGrad > 12) {
                            topPoints.push({ x, y: bestY });
                        }
                    }

                    // Scan Bottom edges: look for peak vertical gradient transition scanning upwards
                    for (let x = Math.round(width * 0.15); x < width * 0.85; x += 3) {
                        let maxGrad = -1;
                        let bestY = -1;
                        for (let y = Math.round(height * 0.98) - 2; y > height * 0.55 + 2; y--) {
                            const grad = getVal(x, y - 2) - getVal(x, y + 2);
                            if (grad > maxGrad) {
                                maxGrad = grad;
                                bestY = y;
                            }
                        }
                        if (maxGrad > 12) {
                            bottomPoints.push({ x, y: bestY });
                        }
                    }

                    // Scan Left edges: horizontal gradient (dark desk -> light paper)
                    for (let y = Math.round(height * 0.15); y < height * 0.85; y += 3) {
                        let maxGrad = -1;
                        let bestX = -1;
                        for (let x = Math.round(width * 0.02) + 2; x < width * 0.45 - 2; x++) {
                            const grad = getVal(x + 2, y) - getVal(x - 2, y);
                            if (grad > maxGrad) {
                                maxGrad = grad;
                                bestX = x;
                            }
                        }
                        if (maxGrad > 12) {
                            leftPoints.push({ x: bestX, y });
                        }
                    }

                    // Scan Right edges: horizontal gradient scanning backwards
                    for (let y = Math.round(height * 0.15); y < height * 0.85; y += 3) {
                        let maxGrad = -1;
                        let bestX = -1;
                        for (let x = Math.round(width * 0.98) - 2; x > width * 0.55 + 2; x--) {
                            const grad = getVal(x - 2, y) - getVal(x + 2, y);
                            if (grad > maxGrad) {
                                maxGrad = grad;
                                bestX = x;
                            }
                        }
                        if (maxGrad > 12) {
                            rightPoints.push({ x: bestX, y });
                        }
                    }

                    // Fit 4 lines: Top (y=mx+c), Bottom (y=mx+c), Left (x=my+c), Right (x=my+c)
                    const lineTop = this.fitLine(topPoints, true);
                    const lineBottom = this.fitLine(bottomPoints, true);
                    const lineLeft = this.fitLine(leftPoints, false);
                    const lineRight = this.fitLine(rightPoints, false);

                    if (lineTop && lineBottom && lineLeft && lineRight) {
                        // Intersect lines to find corners
                        const tl = this.getIntersection(lineTop, lineLeft);
                        const tr = this.getIntersection(lineTop, lineRight);
                        const br = this.getIntersection(lineBottom, lineRight);
                        const bl = this.getIntersection(lineBottom, lineLeft);

                        const validPoint = (pt) => {
                            return pt &&
                                pt.x >= -width * 0.15 && pt.x <= width * 1.15 &&
                                pt.y >= -height * 0.15 && pt.y <= height * 1.15;
                        };

                        if (validPoint(tl) && validPoint(tr) && validPoint(br) && validPoint(bl)) {
                            const clamp = (val, max) => Math.max(0, Math.min(1, val / max));
                            const pts = [
                                { x: clamp(tl.x, width), y: clamp(tl.y, height) },
                                { x: clamp(tr.x, width), y: clamp(tr.y, height) },
                                { x: clamp(br.x, width), y: clamp(br.y, height) },
                                { x: clamp(bl.x, width), y: clamp(bl.y, height) }
                            ];

                            // Shift points 0.5% inwards to ensure desk background ("lebihan") is 100% cropped out, leaving only the clean white paper document.
                            const centerX = 0.5;
                            const centerY = 0.5;
                            const factor = 0.995;

                            return pts.map(pt => {
                                let dx = pt.x - centerX;
                                let dy = pt.y - centerY;
                                return {
                                    x: Math.max(0, Math.min(1, centerX + dx * factor)),
                                    y: Math.max(0, Math.min(1, centerY + dy * factor))
                                };
                            });
                        }
                    }

                    // Fallback: 1% margins (almost full image) to ensure zero text cutoff
                    return [
                        { x: 0.01, y: 0.01 },
                        { x: 0.99, y: 0.01 },
                        { x: 0.99, y: 0.99 },
                        { x: 0.01, y: 0.99 }
                    ];
                },

                fitLine(points, fitYvsX) {
                    const N = points.length;
                    if (N < 5) return null;
                    let sumX = 0, sumY = 0, sumXY = 0, sumX2 = 0;
                    for (let i = 0; i < N; i++) {
                        const x = fitYvsX ? points[i].x : points[i].y;
                        const y = fitYvsX ? points[i].y : points[i].x;
                        sumX += x;
                        sumY += y;
                        sumXY += x * y;
                        sumX2 += x * x;
                    }
                    const denom = (N * sumX2 - sumX * sumX);
                    if (Math.abs(denom) < 1e-5) return null;
                    const m = (N * sumXY - sumX * sumY) / denom;
                    const c = (sumY - m * sumX) / N;

                    // Outlier rejection (filter points deviating > 10 pixels to ensure straight fit)
                    const cleanPoints = [];
                    for (let i = 0; i < N; i++) {
                        const x = fitYvsX ? points[i].x : points[i].y;
                        const y = fitYvsX ? points[i].y : points[i].x;
                        const expectedY = m * x + c;
                        if (Math.abs(y - expectedY) < 10) {
                            cleanPoints.push({ x: fitYvsX ? x : y, y: fitYvsX ? y : x });
                        }
                    }

                    const M = cleanPoints.length;
                    if (M < 3) return { m, c };

                    let sX = 0, sY = 0, sXY = 0, sX2 = 0;
                    for (let i = 0; i < M; i++) {
                        const x = fitYvsX ? cleanPoints[i].x : cleanPoints[i].y;
                        const y = fitYvsX ? cleanPoints[i].y : cleanPoints[i].x;
                        sX += x;
                        sY += y;
                        sXY += x * y;
                        sX2 += x * x;
                    }
                    const d = (M * sX2 - sX * sX);
                    if (Math.abs(d) < 1e-5) return { m, c };
                    return {
                        m: (M * sXY - sX * sY) / d,
                        c: (sY - M * sX) / M
                    };
                },

                getIntersection(line1, line2) {
                    const denom = 1 - line1.m * line2.m;
                    if (Math.abs(denom) < 1e-5) return null;
                    const y = (line1.m * line2.c + line1.c) / denom;
                    const x = line2.m * y + line2.c;
                    return { x, y };
                },

                updateContainerDimensions() {
                    const img = this.$refs.cropImg;
                    if (!img) return;
                    this.containerW = img.clientWidth;
                    this.containerH = img.clientHeight;
                    this.naturalW = img.naturalWidth;
                    this.naturalH = img.naturalHeight;
                },

                rotateImage() {
                    // Create a rotated copy of the raw canvas
                    const rawCanvas = this.$refs.rawCanvas;
                    const tempImg = new Image();
                    tempImg.src = this.imageSrc;

                    tempImg.onload = () => {
                        const ctx = rawCanvas.getContext('2d');
                        const w = tempImg.naturalWidth;
                        const h = tempImg.naturalHeight;

                        rawCanvas.width = h;
                        rawCanvas.height = w;

                        ctx.clearRect(0, 0, rawCanvas.width, rawCanvas.height);
                        ctx.translate(rawCanvas.width / 2, rawCanvas.height / 2);
                        ctx.rotate(90 * Math.PI / 180);
                        ctx.drawImage(tempImg, -w / 2, -h / 2);

                        this.imageSrc = rawCanvas.toDataURL('image/jpeg');
                    };
                },

                startDrag(event, index) {
                    event.preventDefault();
                    this.dragIdx = index;
                    event.target.setPointerCapture(event.pointerId);
                },

                handleDrag(event) {
                    if (this.dragIdx === null) return;

                    const container = this.$refs.cropContainer;
                    if (!container) return;
                    const rect = container.getBoundingClientRect();

                    let x = (event.clientX - rect.left) / rect.width;
                    let y = (event.clientY - rect.top) / rect.height;

                    x = Math.max(0, Math.min(1, x));
                    y = Math.max(0, Math.min(1, y));

                    this.corners[this.dragIdx].x = x;
                    this.corners[this.dragIdx].y = y;
                },

                endDrag() {
                    this.dragIdx = null;
                },

                resetToSource() {
                    this.step = 'source';
                },

                warpDocument() {
                    const img = new Image();
                    img.src = this.imageSrc;
                    img.onload = () => {
                        const pts = this.corners.map(pt => ({
                            x: pt.x * this.naturalW,
                            y: pt.y * this.naturalH
                        }));

                        const widthTop = Math.hypot(pts[1].x - pts[0].x, pts[1].y - pts[0].y);
                        const widthBottom = Math.hypot(pts[2].x - pts[3].x, pts[2].y - pts[3].y);
                        const outW = Math.round(Math.max(widthTop, widthBottom));

                        const heightRight = Math.hypot(pts[2].x - pts[1].x, pts[2].y - pts[1].y);
                        const heightLeft = Math.hypot(pts[3].x - pts[0].x, pts[3].y - pts[0].y);
                        const outH = Math.round(Math.max(heightRight, heightLeft));

                        const maxDim = 1200;
                        let finalW = outW;
                        let finalH = outH;
                        if (outW > maxDim || outH > maxDim) {
                            const ratio = Math.min(maxDim / outW, maxDim / outH);
                            finalW = Math.round(outW * ratio);
                            finalH = Math.round(outH * ratio);
                        }

                        const rawCanvas = this.$refs.rawCanvas;
                        const warpedCanvas = this.$refs.warpedCanvas;

                        rawCanvas.width = this.naturalW;
                        rawCanvas.height = this.naturalH;
                        const rawCtx = rawCanvas.getContext('2d');
                        rawCtx.drawImage(img, 0, 0);

                        warpedCanvas.width = finalW;
                        warpedCanvas.height = finalH;

                        const destPts = [
                            { x: 0, y: 0 },
                            { x: finalW, y: 0 },
                            { x: finalW, y: finalH },
                            { x: 0, y: finalH }
                        ];

                        try {
                            const h = this.solveHomography(pts, destPts);
                            this.applyWarp(rawCanvas, warpedCanvas, h, finalW, finalH);

                            const warpedBase64 = warpedCanvas.toDataURL('image/jpeg');
                            const finalBase64 = this.getFilteredBase64(warpedCanvas, 'magic');

                            if (this.editingPageIndex !== null) {
                                // Replace existing page
                                this.pages[this.editingPageIndex].rawImage = this.imageSrc;
                                this.pages[this.editingPageIndex].corners = JSON.parse(JSON.stringify(this.corners));
                                this.pages[this.editingPageIndex].warpedImage = warpedBase64;
                                this.pages[this.editingPageIndex].finalImageData = finalBase64;
                                this.pages[this.editingPageIndex].filter = 'magic';

                                this.step = 'preview';
                                this.editingPageIndex = null;
                                this.$nextTick(() => {
                                    this.renderActivePage();
                                });
                            } else {
                                // Insert new page
                                this.pages.push({
                                    id: Date.now() + Math.random(),
                                    rawImage: this.imageSrc,
                                    corners: JSON.parse(JSON.stringify(this.corners)),
                                    warpedImage: warpedBase64,
                                    finalImageData: finalBase64,
                                    filter: 'magic'
                                });
                                this.processNextQueueItem();
                            }
                        } catch (e) {
                            console.error("Warp error:", e);
                            Swal.fire({
                                title: 'Gagal Memproses Gambar',
                                text: 'Silakan pastikan 4 sudut telah diatur dengan benar.',
                                icon: 'error',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    };
                },

                async warpDefaultAndSaveCurrent() {
                    // Save this current item using a default crop frame and proceed
                    const defaultResult = await this.warpDefault(this.imageSrc);
                    if (this.editingPageIndex !== null) {
                        this.pages[this.editingPageIndex].rawImage = this.imageSrc;
                        this.pages[this.editingPageIndex].corners = defaultResult.corners;
                        this.pages[this.editingPageIndex].warpedImage = defaultResult.warpedBase64;
                        this.pages[this.editingPageIndex].finalImageData = defaultResult.finalBase64;
                        this.pages[this.editingPageIndex].filter = 'magic';
                        this.editingPageIndex = null;
                        this.step = 'preview';
                        this.$nextTick(() => this.renderActivePage());
                    } else {
                        this.pages.push({
                            id: Date.now() + Math.random(),
                            rawImage: this.imageSrc,
                            corners: defaultResult.corners,
                            warpedImage: defaultResult.warpedBase64,
                            finalImageData: defaultResult.finalBase64,
                            filter: 'magic'
                        });
                        this.processNextQueueItem();
                    }
                },

                async warpAllRemaining() {
                    Swal.fire({
                        title: 'Memproses Antrean...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Process current item
                    const currentRes = await this.warpDefault(this.imageSrc);
                    this.pages.push({
                        id: Date.now() + Math.random(),
                        rawImage: this.imageSrc,
                        corners: currentRes.corners,
                        warpedImage: currentRes.warpedBase64,
                        finalImageData: currentRes.finalBase64,
                        filter: 'magic'
                    });

                    // Process all items in queue
                    while (this.rawQueue.length > 0) {
                        const item = this.rawQueue.shift();
                        const res = await this.warpDefault(item.src);
                        this.pages.push({
                            id: Date.now() + Math.random(),
                            rawImage: item.src,
                            corners: res.corners,
                            warpedImage: res.warpedBase64,
                            finalImageData: res.finalBase64,
                            filter: 'magic'
                        });
                    }

                    Swal.close();
                    this.step = 'preview';
                    this.currentPageIndex = this.pages.length - 1;
                    this.$nextTick(() => {
                        this.renderActivePage();
                    });
                },

                warpDefault(srcBase64) {
                    return new Promise((resolve) => {
                        const img = new Image();
                        img.src = srcBase64;
                        img.onload = () => {
                            const w = img.naturalWidth;
                            const h = img.naturalHeight;

                            // Downsample to max 500px for paper corner detection
                            const maxDimDetect = 500;
                            let dw = w;
                            let dh = h;
                            if (dw > maxDimDetect || dh > maxDimDetect) {
                                const ratio = Math.min(maxDimDetect / dw, maxDimDetect / dh);
                                dw = Math.round(dw * ratio);
                                dh = Math.round(dh * ratio);
                            }
                            const tempCanvasDetect = document.createElement('canvas');
                            tempCanvasDetect.width = dw;
                            tempCanvasDetect.height = dh;
                            const ctxDetect = tempCanvasDetect.getContext('2d');
                            ctxDetect.drawImage(img, 0, 0, dw, dh);
                            const imgDataDetect = ctxDetect.getImageData(0, 0, dw, dh);

                            // Detect paper corners dynamically
                            const detectedNormalizedCorners = this.detectPaperCorners(imgDataDetect, dw, dh);

                            // Map back to natural image scale
                            const pts = detectedNormalizedCorners.map(pt => ({
                                x: pt.x * w,
                                y: pt.y * h
                            }));

                            const widthTop = Math.hypot(pts[1].x - pts[0].x, pts[1].y - pts[0].y);
                            const widthBottom = Math.hypot(pts[2].x - pts[3].x, pts[2].y - pts[3].y);
                            const outW = Math.round(Math.max(widthTop, widthBottom));

                            const heightRight = Math.hypot(pts[2].x - pts[1].x, pts[2].y - pts[1].y);
                            const heightLeft = Math.hypot(pts[3].x - pts[0].x, pts[3].y - pts[0].y);
                            const outH = Math.round(Math.max(heightRight, heightLeft));

                            const maxDim = 1200;
                            let finalW = outW;
                            let finalH = outH;
                            if (outW > maxDim || outH > maxDim) {
                                const ratio = Math.min(maxDim / outW, maxDim / outH);
                                finalW = Math.round(outW * ratio);
                                finalH = Math.round(outH * ratio);
                            }

                            const tempRaw = document.createElement('canvas');
                            tempRaw.width = w;
                            tempRaw.height = h;
                            const rCtx = tempRaw.getContext('2d');
                            rCtx.drawImage(img, 0, 0);

                            const tempWarped = document.createElement('canvas');
                            tempWarped.width = finalW;
                            tempWarped.height = finalH;

                            const destPts = [
                                { x: 0, y: 0 },
                                { x: finalW, y: 0 },
                                { x: finalW, y: finalH },
                                { x: 0, y: finalH }
                            ];

                            const hMatrix = this.solveHomography(pts, destPts);
                            this.applyWarp(tempRaw, tempWarped, hMatrix, finalW, finalH);

                            const finalBase64 = this.getFilteredBase64(tempWarped, 'magic');
                            resolve({
                                corners: detectedNormalizedCorners,
                                warpedBase64: tempWarped.toDataURL('image/jpeg'),
                                finalBase64: finalBase64
                            });
                        };
                    });
                },

                solveHomography(src, dst) {
                    const A = [];
                    const B = [];
                    for (let i = 0; i < 4; i++) {
                        const s = src[i];
                        const d = dst[i];
                        A.push([d.x, d.y, 1, 0, 0, 0, -s.x * d.x, -s.x * d.y]);
                        B.push(s.x);
                        A.push([0, 0, 0, d.x, d.y, 1, -s.y * d.x, -s.y * d.y]);
                        B.push(s.y);
                    }

                    const n = B.length;
                    for (let i = 0; i < n; i++) {
                        let maxRow = i;
                        for (let k = i + 1; k < n; k++) {
                            if (Math.abs(A[k][i]) > Math.abs(A[maxRow][i])) maxRow = k;
                        }
                        const tempA = A[i]; A[i] = A[maxRow]; A[maxRow] = tempA;
                        const tempB = B[i]; B[i] = B[maxRow]; B[maxRow] = tempB;

                        for (let k = i + 1; k < n; k++) {
                            const factor = A[k][i] / A[i][i];
                            B[k] -= factor * B[i];
                            for (let j = i; j < n; j++) {
                                A[k][j] -= factor * A[i][j];
                            }
                        }
                    }

                    const x = new Array(n).fill(0);
                    for (let i = n - 1; i >= 0; i--) {
                        let sum = 0;
                        for (let j = i + 1; j < n; j++) {
                            sum += A[i][j] * x[j];
                        }
                        x[i] = (B[i] - sum) / A[i][i];
                    }

                    return [
                        x[0], x[1], x[2],
                        x[3], x[4], x[5],
                        x[6], x[7], 1.0
                    ];
                },

                applyWarp(srcCanvas, dstCanvas, h, dstW, dstH) {
                    const srcCtx = srcCanvas.getContext('2d');
                    const dstCtx = dstCanvas.getContext('2d');

                    const srcW = srcCanvas.width;
                    const srcH = srcCanvas.height;

                    const srcData = srcCtx.getImageData(0, 0, srcW, srcH);
                    const dstData = dstCtx.createImageData(dstW, dstH);

                    const sPix = srcData.data;
                    const dPix = dstData.data;

                    // Loop every pixel in destination image and project back to source image using Bilinear Interpolation
                    for (let v = 0; v < dstH; v++) {
                        for (let u = 0; u < dstW; u++) {
                            const den = h[6] * u + h[7] * v + 1.0;
                            const x = (h[0] * u + h[1] * v + h[2]) / den;
                            const y = (h[3] * u + h[4] * v + h[5]) / den;

                            const dIdx = (v * dstW + u) * 4;

                            if (x >= 0 && x < srcW - 1 && y >= 0 && y < srcH - 1) {
                                const xf = Math.floor(x);
                                const yf = Math.floor(y);
                                const xc = xf + 1;
                                const yc = yf + 1;

                                const dx = x - xf;
                                const dy = y - yf;

                                const w00 = (1 - dx) * (1 - dy);
                                const w10 = dx * (1 - dy);
                                const w01 = (1 - dx) * dy;
                                const w11 = dx * dy;

                                const idx00 = (yf * srcW + xf) * 4;
                                const idx10 = (yf * srcW + xc) * 4;
                                const idx01 = (yc * srcW + xf) * 4;
                                const idx11 = (yc * srcW + xc) * 4;

                                for (let c = 0; c < 4; c++) {
                                    dPix[dIdx + c] = Math.round(
                                        w00 * sPix[idx00 + c] +
                                        w10 * sPix[idx10 + c] +
                                        w01 * sPix[idx01 + c] +
                                        w11 * sPix[idx11 + c]
                                    );
                                }
                            } else {
                                dPix[dIdx] = 255;
                                dPix[dIdx + 1] = 255;
                                dPix[dIdx + 2] = 255;
                                dPix[dIdx + 3] = 255;
                            }
                        }
                    }
                    dstCtx.putImageData(dstData, 0, 0);
                },

                getBackgroundIllumination(imageData, width, height, radius) {
                    const src = imageData.data;
                    const length = src.length;
                    const temp = new Uint8ClampedArray(length);
                    const bg = new Uint8ClampedArray(length);

                    // Separable Box Blur: Horizontal pass
                    for (let y = 0; y < height; y++) {
                        let rSum = 0, gSum = 0, bSum = 0;
                        let count = 0;

                        for (let dx = -radius; dx <= radius; dx++) {
                            const x = Math.max(0, Math.min(width - 1, dx));
                            const idx = (y * width + x) * 4;
                            rSum += src[idx];
                            gSum += src[idx + 1];
                            bSum += src[idx + 2];
                            count++;
                        }

                        for (let x = 0; x < width; x++) {
                            const outIdx = (y * width + x) * 4;
                            temp[outIdx] = rSum / count;
                            temp[outIdx + 1] = gSum / count;
                            temp[outIdx + 2] = bSum / count;

                            const prevX = Math.max(0, x - radius);
                            const nextX = Math.min(width - 1, x + radius + 1);
                            const prevIdx = (y * width + prevX) * 4;
                            const nextIdx = (y * width + nextX) * 4;

                            rSum += src[nextIdx] - src[prevIdx];
                            gSum += src[nextIdx + 1] - src[prevIdx + 1];
                            bSum += src[nextIdx + 2] - src[prevIdx + 2];
                        }
                    }

                    // Separable Box Blur: Vertical pass
                    for (let x = 0; x < width; x++) {
                        let rSum = 0, gSum = 0, bSum = 0;
                        let count = 0;

                        for (let dy = -radius; dy <= radius; dy++) {
                            const y = Math.max(0, Math.min(height - 1, dy));
                            const idx = (y * width + x) * 4;
                            rSum += temp[idx];
                            gSum += temp[idx + 1];
                            bSum += temp[idx + 2];
                            count++;
                        }

                        for (let y = 0; y < height; y++) {
                            const outIdx = (y * width + x) * 4;
                            bg[outIdx] = rSum / count;
                            bg[outIdx + 1] = gSum / count;
                            bg[outIdx + 2] = bSum / count;
                            bg[outIdx + 3] = 255;

                            const prevY = Math.max(0, y - radius);
                            const nextY = Math.min(height - 1, y + radius + 1);
                            const prevIdx = (prevY * width + x) * 4;
                            const nextIdx = (nextY * width + x) * 4;

                            rSum += temp[nextIdx] - temp[prevIdx];
                            gSum += temp[nextIdx + 1] - temp[prevIdx + 1];
                            bSum += temp[nextIdx + 2] - temp[prevIdx + 2];
                        }
                    }
                    return bg;
                },

                getFilteredBase64(canvas, filterType) {
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = canvas.width;
                    tempCanvas.height = canvas.height;
                    const ctx = tempCanvas.getContext('2d');
                    ctx.drawImage(canvas, 0, 0);

                    const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const data = imgData.data;
                    const w = canvas.width;
                    const h = canvas.height;

                    if (filterType === 'magic') {
                        const radius = Math.max(15, Math.round(Math.max(w, h) / 30));
                        const bg = this.getBackgroundIllumination(imgData, w, h, radius);
                        for (let i = 0; i < data.length; i += 4) {
                            for (let c = 0; c < 3; c++) {
                                const idx = i + c;
                                const orig = data[idx];
                                const bgVal = bg[idx];

                                // Divide original by blurred background to flatten illumination
                                let val = (orig / (bgVal + 1)) * 255;

                                // High-contrast S-curve stretch to remove paper grain and darken text
                                if (val > 235) {
                                    val = 255;
                                } else if (val < 190) {
                                    val = val * 0.45; // significantly darken text to make it deep and clear
                                } else {
                                    val = (val - 190) * (255 / 45);
                                }

                                data[idx] = Math.max(0, Math.min(255, Math.round(val)));
                            }
                        }
                        ctx.putImageData(imgData, 0, 0);
                    } else if (filterType === 'bw') {
                        const gData = new Uint8ClampedArray(data.length);
                        for (let i = 0; i < data.length; i += 4) {
                            const gray = Math.round(0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);
                            gData[i] = gray;
                            gData[i + 1] = gray;
                            gData[i + 2] = gray;
                        }
                        const grayImgData = new ImageData(gData, w, h);
                        const radius = Math.max(15, Math.round(Math.max(w, h) / 30));
                        const bg = this.getBackgroundIllumination(grayImgData, w, h, radius);

                        for (let i = 0; i < data.length; i += 4) {
                            const orig = gData[i];
                            const bgVal = bg[i];

                            let val = (orig / (bgVal + 1)) * 255;

                            // Sharp thresholding to match clean CamScanner B&W copier
                            if (val > 230) {
                                val = 255;
                            } else if (val < 185) {
                                val = 0;
                            } else {
                                val = Math.round((val - 185) * (255 / 45));
                            }

                            data[i] = val;
                            data[i + 1] = val;
                            data[i + 2] = val;
                        }
                        ctx.putImageData(imgData, 0, 0);
                    }
                    return tempCanvas.toDataURL('image/jpeg', 0.95);
                },

                selectPage(index) {
                    this.currentPageIndex = index;
                    this.renderActivePage();
                },

                deletePage(index) {
                    this.pages.splice(index, 1);
                    if (this.pages.length === 0) {
                        this.step = 'source';
                    } else {
                        this.currentPageIndex = Math.min(this.currentPageIndex, this.pages.length - 1);
                        this.renderActivePage();
                    }
                },

                recropActivePage() {
                    const page = this.pages[this.currentPageIndex];
                    this.editingPageIndex = this.currentPageIndex;
                    this.currentQueueItemName = 'Sesuaikan Halaman ' + (this.currentPageIndex + 1);
                    this.imageSrc = page.rawImage;
                    this.corners = JSON.parse(JSON.stringify(page.corners));
                    this.step = 'crop';
                },

                applyFilter(type) {
                    if (!this.pages[this.currentPageIndex]) return;
                    this.pages[this.currentPageIndex].filter = type;

                    // Re-apply filter on the base warped image
                    const tempImg = new Image();
                    tempImg.src = this.pages[this.currentPageIndex].warpedImage;
                    tempImg.onload = () => {
                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = tempImg.width;
                        tempCanvas.height = tempImg.height;
                        const tempCtx = tempCanvas.getContext('2d');
                        tempCtx.drawImage(tempImg, 0, 0);

                        const finalBase64 = this.getFilteredBase64(tempCanvas, type);
                        this.pages[this.currentPageIndex].finalImageData = finalBase64;
                        this.renderActivePage();
                    };
                },

                renderActivePage() {
                    const page = this.pages[this.currentPageIndex];
                    if (!page) return;

                    const preview = this.$refs.previewCanvas;
                    const pCtx = preview.getContext('2d');

                    const img = new Image();
                    img.src = page.finalImageData;
                    img.onload = () => {
                        preview.width = img.width;
                        preview.height = img.height;
                        pCtx.drawImage(img, 0, 0);
                    };
                },

                loadImageAsync(src) {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        img.src = src;
                        img.onload = () => resolve(img);
                        img.onerror = (e) => reject(e);
                    });
                },

                async downloadPDF() {
                    if (this.pages.length === 0) {
                        Swal.fire({
                            title: 'Dokumen Kosong',
                            text: 'Belum ada halaman yang ditambahkan.',
                            icon: 'warning',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Menyusun PDF...',
                        text: 'Mohon tunggu sebentar selagi kami merender halaman berkas.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const { jsPDF } = window.jspdf;
                    let pdf = null;

                    try {
                        for (let i = 0; i < this.pages.length; i++) {
                            const page = this.pages[i];
                            const img = await this.loadImageAsync(page.finalImageData);
                            const w = img.width;
                            const h = img.height;
                            const orientation = w > h ? 'landscape' : 'portrait';

                            if (i === 0) {
                                pdf = new jsPDF({
                                    orientation: orientation,
                                    unit: 'px',
                                    format: [w, h]
                                });
                            } else {
                                pdf.addPage([w, h], orientation);
                            }

                            pdf.addImage(page.finalImageData, 'JPEG', 0, 0, w, h);
                        }

                        let filename = this.pdfName.trim();
                        if (!filename.endsWith('.pdf')) {
                            filename += '.pdf';
                        }
                        pdf.save(filename);

                        Swal.fire({
                            title: 'PDF Berhasil Diunduh! 🎉',
                            text: 'Berkas scan ' + filename + ' (' + this.pages.length + ' halaman) telah disimpan ke perangkat Anda.',
                            icon: 'success',
                            confirmButtonColor: '#2563eb'
                        });
                    } catch (e) {
                        console.error("PDF generation failed:", e);
                        Swal.fire({
                            title: 'Unduhan Gagal',
                            text: 'Terjadi kesalahan saat menyusun halaman PDF.',
                            icon: 'error',
                            confirmButtonColor: '#2563eb'
                        });
                    }
                }
            };
        }
    </script>
@endsection