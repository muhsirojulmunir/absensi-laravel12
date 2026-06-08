@extends('layouts.master')
@section('title', 'Add New User')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('super-admin.users.index') }}" class="p-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 text-gray-400 dark:text-blue-400 hover:text-navy-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-navy-900 dark:text-white">Add New User</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl text-sm shadow-sm">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold underline uppercase tracking-wider">Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 ml-7 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('super-admin.users.store') }}" method="POST" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
        @csrf
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Basic Information</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-gray-50 dark:bg-slate-900 border @error('name') border-red-500 @else border-gray-200 dark:border-slate-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="John Doe">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required class="w-full bg-gray-50 dark:bg-slate-900 border @error('username') border-red-500 @else border-gray-200 dark:border-slate-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="john_doe">
                        @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-gray-50 dark:bg-slate-900 border @error('email') border-red-500 @else border-gray-200 dark:border-slate-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="john@jmn.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full bg-gray-50 dark:bg-slate-900 border @error('password') border-red-500 @else border-gray-200 dark:border-slate-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="Min. 8 characters">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Workplace Info -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Workplace Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white font-mono font-bold" placeholder="Auto-generated...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Role</label>
                        <select name="role_id" id="role_id" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white cursor-pointer">
                            <option value="" class="dark:bg-slate-900">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" data-slug="{{ $role->slug }}" class="dark:bg-slate-900">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Division</label>
                        <select name="division_id" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white cursor-pointer">
                            <option value="" class="dark:bg-slate-900">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" class="dark:bg-slate-900">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="location_wrapper" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Lokasi Counter (Khusus Ramayana)</label>
                        <select name="location_id" id="location_id" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white cursor-pointer">
                            <option value="" class="dark:bg-slate-900">Pilih Lokasi Counter</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" class="dark:bg-slate-900">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="space-y-4 pt-4 border-t border-gray-50 dark:border-slate-700">
                <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Additional Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Position</label>
                        <input type="text" name="position" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="Senior Developer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Phone Number</label>
                        <input type="text" name="phone" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="0812...">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Home Address</label>
                    <textarea name="address" rows="3" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="Full address..."></textarea>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-slate-900/50 p-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-slate-700">
            <a href="{{ route('super-admin.users.index') }}" class="px-6 py-2.5 text-sm font-bold text-gray-500 dark:text-slate-400 hover:text-navy-900 dark:hover:text-white transition-colors">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-900/20 transition-all active:scale-[0.98]">
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
