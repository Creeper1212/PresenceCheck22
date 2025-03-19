import os

def process_files_in_directory(directory, output_file):
    with open(output_file, 'w', encoding='utf-8') as outfile:
        # Walk through the directory and subdirectories
        for root, dirs, files in os.walk(directory):
            for file in files:
                file_path = os.path.join(root, file)
                
                # Only process files (you can filter out certain file types if needed)
                if os.path.isfile(file_path):
                    try:
                        # Open each file and read its content
                        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                            file_content = f.read()

                        # Write the filename and its content to the output file
                        # We will include both the relative file path and its content
                        relative_path = os.path.relpath(file_path, directory)
                        outfile.write(f"{relative_path}\n{file_content}\n\n")
                        
                    except Exception as e:
                        print(f"Error reading file {file_path}: {e}")
        
    print(f"All files have been processed and written to {output_file}")

if __name__ == "__main__":
    # Get the current directory
    current_directory = os.getcwd()
    
    # Define the output file
    output_filename = "combined_file_contents.txt"
    
    # Process files
    process_files_in_directory(current_directory, output_filename)
