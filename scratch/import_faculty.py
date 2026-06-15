import json
import os
import shutil
import re

# Directory setups
source_images_dir = "faculty"
dest_images_dir = "uploads/faculty"
os.makedirs(dest_images_dir, exist_ok=True)

# Load parsed faculty json
with open("faculty.json", "r", encoding="utf-8") as f:
    faculty_list = json.load(f)

print(f"Loaded {len(faculty_list)} records from JSON.")

sql_inserts = []
imported_count = 0

for idx, f in enumerate(faculty_list):
    name = f.get("Full Name", "").strip()
    designation = f.get("Designation", "").strip()
    qualification = f.get("Education Qualification", "").strip() if f.get("Education Qualification") else ""
    subject = f.get("Subject", "").strip() if f.get("Subject") else ""
    experience = f.get("Professional Experience", "").strip() if f.get("Professional Experience") else ""
    expertise = f.get("Areas of Expertise", "").strip() if f.get("Areas of Expertise") else ""
    edu_meaning = f.get("What does Education mean to you ?", "").strip() if f.get("What does Education mean to you ?") else ""
    philosophy = f.get("What is your Teaching Philosophy ?", "").strip() if f.get("What is your Teaching Philosophy ?") else ""
    message = f.get("What message would you like to share with the Students ?", "").strip() if f.get("What message would you like to share with the Students ?") else ""
    quote = f.get("A quote or motto that inspires you ?", "").strip() if f.get("A quote or motto that inspires you ?") else ""
    
    # Process profile picture
    orig_pic_path = f.get("Profile Picture")
    db_image_path = None
    
    if orig_pic_path:
        # Extract filename (handle both backslash and forward slash)
        raw_filename = os.path.basename(orig_pic_path)
        # Search file inside faculty folder
        src_file_path = os.path.join(source_images_dir, raw_filename)
        
        if os.path.exists(src_file_path):
            # Clean filename: lowercase, replace spaces and special chars with underscores
            clean_filename = raw_filename.lower()
            clean_filename = re.sub(r'[^a-z0-9\._\-]', '_', clean_filename)
            clean_filename = re.sub(r'_+', '_', clean_filename)
            
            dest_file_path = os.path.join(dest_images_dir, clean_filename)
            shutil.copy2(src_file_path, dest_file_path)
            db_image_path = f"uploads/faculty/{clean_filename}"
        else:
            print(f"Warning: Image file not found: {src_file_path}")
            
    # Prepare SQL values (escape single quotes for SQL)
    def sql_escape(val):
        if val is None:
            return "NULL"
        escaped = val.replace("'", "''")
        return f"'{escaped}'"
        
    sql_name = sql_escape(name)
    sql_designation = sql_escape(designation)
    sql_qualification = sql_escape(qualification)
    sql_subject = sql_escape(subject)
    sql_experience = sql_escape(experience)
    sql_expertise = sql_escape(expertise)
    sql_edu_meaning = sql_escape(edu_meaning)
    sql_philosophy = sql_escape(philosophy)
    sql_message = sql_escape(message)
    sql_quote = sql_escape(quote)
    sql_image = sql_escape(db_image_path)
    sort_order = idx + 1
    
    insert_stmt = f"({sql_name}, {sql_designation}, {sql_qualification}, {sql_subject}, {sql_experience}, {sql_expertise}, {sql_edu_meaning}, {sql_philosophy}, {sql_message}, {sql_quote}, {sql_image}, {sort_order})"
    sql_inserts.append(insert_stmt)
    imported_count += 1

# Write SQL file
with open("faculty_seeds.sql", "w", encoding="utf-8") as f_sql:
    f_sql.write("INSERT INTO `faculty` (`name`, `designation`, `qualification`, `subject`, `experience`, `expertise`, `meaning_of_education`, `teaching_philosophy`, `student_message`, `quote`, `image_path`, `sort_order`) VALUES\n")
    f_sql.write(",\n".join(sql_inserts) + ";\n")

print(f"Successfully processed {imported_count} faculty members. SQL seeds written to faculty_seeds.sql")
