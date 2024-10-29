<?php
// Path to the current directory
/*$dir = __DIR__;

// Open the directory
if (is_dir($dir)) {
    // Scan all files in the directory
    $files = scandir($dir);

    foreach ($files as $file) {
        // Check if the file has a .bin extension
        if (pathinfo($file, PATHINFO_EXTENSION) === 'bin') {
            // Get the file's name without the extension
            $filename = pathinfo($file, PATHINFO_FILENAME);

            // Define the new file name with the .gltfdata extension
            $new_filename = $filename . '.gltfdata';

            // Rename the file
            if (rename($dir . '/' . $file, $dir . '/' . $new_filename)) {
                echo "Renamed: $file to $new_filename\n";
            } else {
                echo "Error renaming: $file\n";
            }
        }
    }
} else {
    echo "Directory does not exist.";
}
*/