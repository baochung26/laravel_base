#!/usr/bin/env python3
import re

files = [
    'app/Http/Controllers/Api/V1/ProfileController.php',
    'app/Http/Controllers/Api/V1/PasswordController.php',
    'app/Http/Controllers/Api/V1/RolePermissionController.php',
]

for file_path in files:
    try:
        with open(file_path, 'r') as f:
            lines = f.readlines()
        
        new_lines = []
        for i, line in enumerate(lines):
            # Fix methods with 8 spaces indentation to 4 spaces
            if re.match(r'^\s{8}public function', line):
                line = '    ' + line.lstrip()
            new_lines.append(line)
        
        with open(file_path, 'w') as f:
            f.writelines(new_lines)
        
        print(f"Fixed indentation: {file_path}")
    except Exception as e:
        print(f"Error: {e}")
