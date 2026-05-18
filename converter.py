import re
import os

files = [
    'resources/views/super-admin/users/index.blade.php',
    'resources/views/super-admin/users/create.blade.php',
    'resources/views/super-admin/users/edit.blade.php',
    'resources/views/super-admin/settings/index.blade.php',
    'resources/views/pic/leave-approvals/index.blade.php',
    'resources/views/pic/employees/index.blade.php',
    'resources/views/hrd/attendance/index.blade.php',
    'resources/views/hrd/attendance/recap.blade.php'
]

replacements = {
    r'bg-\[\#1e293b\]/50': r'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)]',
    r'bg-slate-900/60': r'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)]',
    r'bg-slate-900/50\b': r'bg-blue-50/30',
    r'bg-slate-900\b': r'bg-slate-50 shadow-inner',
    r'bg-slate-800/50\b': r'bg-blue-50/50',
    r'bg-slate-800/30\b': r'bg-blue-50/30',
    r'bg-slate-800/20\b': r'bg-blue-50/20',
    r'bg-slate-800\b': r'bg-blue-50',
    r'bg-slate-950\b': r'bg-white',
    r'bg-white/5\b': r'bg-blue-50',
    r'bg-white/10\b': r'bg-blue-50',
    r'hover:bg-slate-800/30\b': r'hover:bg-blue-50/50',
    r'hover:bg-slate-800/50\b': r'hover:bg-blue-50',
    r'hover:bg-white/\[0\.02\]': r'hover:bg-blue-50/30',
    r'bg-white/\[0\.02\]': r'bg-blue-50/50',
    
    r'border-slate-800/50': r'border-blue-100',
    r'border-slate-800': r'border-blue-100',
    r'border-slate-700/50': r'border-blue-200/50',
    r'border-slate-700': r'border-blue-200',
    r'border-white/5\b': r'border-blue-50',
    r'border-white/10\b': r'border-blue-100',

    r'divide-slate-800/50': r'divide-blue-50',
    r'divide-slate-800': r'divide-blue-100',
    r'divide-white/5\b': r'divide-blue-50',

    r'text-slate-200': r'text-blue-900',
    r'text-slate-300': r'text-blue-900',
    r'text-slate-400': r'text-blue-600/80',
    r'text-slate-500': r'text-blue-500',
    r'text-slate-600': r'text-blue-400',
    
    r'placeholder-slate-700': r'placeholder-blue-300',
    r'placeholder-slate-500': r'placeholder-blue-300',
    r'ring-slate-700': r'ring-blue-200',
    r'ring-slate-800': r'ring-blue-300',
}

def replace_text_white(content):
    content = content.replace('text-white', 'text-blue-950')
    lines = content.split('\n')
    for i, line in enumerate(lines):
        if 'bg-blue-' in line or 'bg-emerald-' in line or 'bg-red-' in line or 'bg-indigo-' in line or 'bg-amber-' in line or 'bg-orange-' in line or 'btn' in line:
            if 'text-blue-950' in line:
                lines[i] = line.replace('text-blue-950', 'text-white')
    return '\n'.join(lines)


for filepath in files:
    if os.path.exists(filepath):
        print(f"Processing: {filepath}")
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        content = replace_text_white(content)
            
        for k, v in replacements.items():
            content = re.sub(k, v, content)
            
        content = content.replace('shadow-2xl', 'shadow-[0_8px_30px_rgb(0,0,0,0.06)]')
        content = content.replace('shadow-xl', 'shadow-[0_8px_30px_rgb(0,0,0,0.04)]')
        content = content.replace('backdrop-blur-2xl', '')
        content = content.replace('backdrop-blur-xl', '')
        content = content.replace('backdrop-blur-lg', '')
        content = content.replace('backdrop-blur-md', '')
        content = content.replace('backdrop-blur-sm', '')
        content = content.replace('\[color-scheme:dark\]', '')
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
print('Done!')
