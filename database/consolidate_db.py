import os
import re

# Configuration
base_dir = r'd:\APLIKASI\xampp_lite_8_4\www\e-TU\database'
output_file = os.path.join(base_dir, 'database_consolidated.sql')

# Files to exclude from processing (we handle schema.sql specially)
exclude_files = ['schema.sql', 'database_consolidated.sql', 'rollback_identity_fields.sql']

# Tables to remove from schema.sql
tables_to_remove = ['users', 'roles', 'kelas', 'template_surat']

def read_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        return f.read()

def filter_schema(content):
    # Remove CREATE TABLE statements
    for table in tables_to_remove:
        # Pattern for CREATE TABLE `table` ... );
        # Handles potential IF NOT EXISTS and multiline content
        # We need to be careful. The previous regex might miss if there are comments or weird formatting.
        # But looking at the schema.sql content, it's pretty standard.
        pattern = re.compile(rf'CREATE TABLE (IF NOT EXISTS )?`{table}`.*? ENGINE=InnoDB.*?;', re.DOTALL | re.IGNORECASE)
        content = pattern.sub('', content)
        
        # Remove INSERT INTO statements
        insert_pattern = re.compile(rf'INSERT INTO `{table}`.*?;', re.DOTALL | re.IGNORECASE)
        content = insert_pattern.sub('', content)
    
    return content

def main():
    print(f"Consolidating files in {base_dir}...")
    
    final_content = []
    
    # Header
    final_content.append("-- CONSOLIDATED DATABASE SCRIPT")
    final_content.append("SET FOREIGN_KEY_CHECKS=0;")
    final_content.append("START TRANSACTION;")
    final_content.append("")
    
    # 1. Process schema.sql
    schema_path = os.path.join(base_dir, 'schema.sql')
    if os.path.exists(schema_path):
        print("Processing schema.sql...")
        schema_content = read_file(schema_path)
        filtered_schema = filter_schema(schema_content)
        final_content.append("-- FROM schema.sql (Filtered)")
        final_content.append(filtered_schema)
        final_content.append("")
    
    # Get all other SQL files
    all_files = [f for f in os.listdir(base_dir) if f.endswith('.sql') and f not in exclude_files]
    
    # Group files
    create_files = [f for f in all_files if f.startswith('create_')]
    add_files = [f for f in all_files if f.startswith('add_')]
    update_files = [f for f in all_files if f.startswith('update_')]
    migrate_files = [f for f in all_files if f.startswith('migrate_')]
    other_files = [f for f in all_files if f not in create_files + add_files + update_files + migrate_files]
    
    # 2. Append create_*.sql
    for f in create_files:
        print(f"Processing {f}...")
        final_content.append(f"-- FROM {f}")
        final_content.append(read_file(os.path.join(base_dir, f)))
        final_content.append("")

    # 3. Append add_*.sql
    for f in add_files:
        print(f"Processing {f}...")
        final_content.append(f"-- FROM {f}")
        final_content.append(read_file(os.path.join(base_dir, f)))
        final_content.append("")

    # 4. Append update_*.sql
    for f in update_files:
        print(f"Processing {f}...")
        final_content.append(f"-- FROM {f}")
        final_content.append(read_file(os.path.join(base_dir, f)))
        final_content.append("")
        
    # 5. Append migrate_*.sql
    for f in migrate_files:
        print(f"Processing {f}...")
        final_content.append(f"-- FROM {f}")
        final_content.append(read_file(os.path.join(base_dir, f)))
        final_content.append("")

    # 6. Append others
    for f in other_files:
        print(f"Processing {f}...")
        final_content.append(f"-- FROM {f}")
        final_content.append(read_file(os.path.join(base_dir, f)))
        final_content.append("")

    # Footer
    final_content.append("COMMIT;")
    final_content.append("SET FOREIGN_KEY_CHECKS=1;")
    
    # Write output
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(final_content))
    
    print(f"Created {output_file}")

if __name__ == "__main__":
    main()
