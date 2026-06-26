@extends('layouts.master')
@section('title', 'Add New User')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('super-admin.users.index') }}" 
           class="p-2.5 bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200/60 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Add New User</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Create a new user account with role-based access.</p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-400 px-6 py-4 rounded-2xl text-sm shadow-xs animate-fade-in">
            <div class="flex items-center mb-2 font-semibold">
                <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                </svg>
                <span>Please resolve the following errors:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 ml-7 font-medium text-rose-700 dark:text-rose-350">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('super-admin.users.store') }}" method="POST" 
          class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden">
        @csrf
        <div class="p-6 sm:p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Side: Basic Info -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Basic Information</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border @error('name') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-800 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:bg-white dark:focus:bg-slate-900" 
                                   placeholder="John Doe">
                            @error('name') <p class="text-rose-500 text-xs mt-1.5 pl-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Username</label>
                            <input type="text" name="username" value="{{ old('username') }}" required 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border @error('username') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-800 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:bg-white dark:focus:bg-slate-900" 
                                   placeholder="john_doe">
                            @error('username') <p class="text-rose-500 text-xs mt-1.5 pl-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Email Address (Optional)</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border @error('email') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-800 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:bg-white dark:focus:bg-slate-900" 
                                   placeholder="john@example.com">
                            @error('email') <p class="text-rose-500 text-xs mt-1.5 pl-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required 
                                       class="w-full bg-slate-50 dark:bg-slate-950 border @error('password') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-800 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl pl-4 pr-12 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:bg-white dark:focus:bg-slate-900" 
                                       placeholder="Min. 8 characters">
                                <button type="button" 
                                        onclick="const p=document.getElementById('password'); const isPass=p.type==='password'; p.type=isPass?'text':'password'; this.children[0].classList.toggle('hidden', isPass); this.children[1].classList.toggle('hidden', !isPass);" 
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password') <p class="text-rose-500 text-xs mt-1.5 pl-0.5">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Right Side: Workplace Info -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Workplace Details</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" readonly
                                   class="w-full bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded-xl px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono font-bold outline-none cursor-not-allowed" 
                                   placeholder="Select Role to auto-generate...">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Role</label>
                            <select name="role_id" id="role_id" required 
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 cursor-pointer">
                                <option value="" class="dark:bg-slate-950">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-slug="{{ $role->slug }}" class="dark:bg-slate-950">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Division</label>
                            <select name="division_id" 
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 cursor-pointer">
                                <option value="" class="dark:bg-slate-950">Select Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" class="dark:bg-slate-950">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="location_wrapper" style="display: none;" class="animate-fade-in">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Lokasi Counter (Khusus Ramayana)</label>
                            <select name="location_id" id="location_id" 
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 cursor-pointer">
                                <option value="" class="dark:bg-slate-950">Pilih Lokasi Counter</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" class="dark:bg-slate-950">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Additional Info -->
            <div class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center space-x-2 pb-2">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Additional Information</h2>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Position</label>
                            <input type="text" name="position" value="{{ old('position') }}"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all" 
                                   placeholder="Senior Developer">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all" 
                                   placeholder="0812...">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all" 
                                   placeholder="Jakarta">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all dark:[color-scheme:dark]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Home Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all resize-none" 
                                  placeholder="Full address...">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Footer Actions -->
        <div class="bg-slate-50 dark:bg-slate-950/40 px-6 py-5 flex justify-end items-center space-x-3 border-t border-slate-100 dark:border-slate-800/80">
            <a href="{{ route('super-admin.users.index') }}" 
               class="px-5 py-2.5 text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-md shadow-indigo-900/10 hover:shadow-lg transition-all active:scale-[0.98]">
                Save User
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('role_id').addEventListener('change', function() {
        const roleId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const roleSlug = selectedOption ? selectedOption.getAttribute('data-slug') : '';
        const employeeIdInput = document.getElementById('employee_id');
        const locationWrapper = document.getElementById('location_wrapper');
        
        if (roleSlug === 'karyawan_ramayana') {
            locationWrapper.style.display = 'block';
        } else {
            locationWrapper.style.display = 'none';
            document.getElementById('location_id').value = '';
        }
        
        if (!roleId) {
            employeeIdInput.value = '';
            return;
        }

        employeeIdInput.value = 'Generating...';
        employeeIdInput.classList.add('opacity-50');

        fetch(`/super-admin/users/generate-id/${roleId}`)
            .then(response => response.json())
            .then(data => {
                employeeIdInput.value = data.id;
                employeeIdInput.classList.remove('opacity-50');
            })
            .catch(error => {
                console.error('Error:', error);
                employeeIdInput.value = '';
                employeeIdInput.classList.remove('opacity-50');
            });
    });
</script>
@endpush
