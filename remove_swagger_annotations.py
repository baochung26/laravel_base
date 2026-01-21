#!/usr/bin/env python3
import re

files = [
    'app/Http/Controllers/Api/V1/UserController.php',
    'app/Http/Controllers/Api/V1/ProfileController.php',
    'app/Http/Controllers/Api/V1/PasswordController.php',
    'app/Http/Controllers/Api/V1/RolePermissionController.php',
]

for file_path in files:
    try:
        with open(file_path, 'r') as f:
            content = f.read()
        
        # Remove all comment blocks that contain @OA\ anywhere
        # This regex matches /** ... */ blocks that contain @OA\
        pattern = r'/\*\*[^*]*\*+(?:[^/*][^*]*\*+)*/\s*\n'
        matches = re.finditer(pattern, content, re.DOTALL)
        
        new_content = content
        for match in reversed(list(matches)):  # Reverse to maintain positions
            comment_block = match.group(0)
            if '@OA\\' in comment_block or 'OA\\' in comment_block:
                new_content = new_content[:match.start()] + new_content[match.end():]
        
        # Also remove any remaining lines with @OA\ in doc comments
        lines = new_content.split('\n')
        result_lines = []
        in_oa_comment = False
        
        for i, line in enumerate(lines):
            # Check if line starts a comment with @OA\
            if '/**' in line and ('@OA\\' in line or 'OA\\' in line):
                in_oa_comment = True
                continue
            
            # If we're in an OA comment block
            if in_oa_comment:
                # Check if this line contains @OA\ or OA\
                if '@OA\\' in line or 'OA\\' in line:
                    continue
                # Check if this is the end of the comment
                if '*/' in line:
                    in_oa_comment = False
                    continue
                # Skip all lines in the OA comment block
                continue
            
            # Check if this is a comment line with @OA\
            if re.match(r'\s*\*\s*@OA\\', line) or re.match(r'\s*\*\s*OA\\', line):
                continue
            
            # Check if this line contains @OA\ in a comment
            if re.search(r'/\*\*.*@OA\\', line) or re.search(r'\*.*@OA\\', line):
                continue
            
            result_lines.append(line)
        
        # Write back
        with open(file_path, 'w') as f:
            f.write('\n'.join(result_lines))
        
        print(f"Processed: {file_path}")
    except Exception as e:
        print(f"Error processing {file_path}: {e}")
