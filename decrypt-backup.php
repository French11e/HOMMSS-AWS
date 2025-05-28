<?php
/**
 * HOMMSS Backup Decryption Script
 * Decrypt .zip.enc files to regular .zip files
 */

if ($argc < 2) {
    echo "Usage: php decrypt-backup.php <encrypted-file.zip.enc> [password]\n";
    echo "Example: php decrypt-backup.php backup.zip.enc\n";
    exit(1);
}

$encryptedFile = $argv[1];
$password = $argv[2] ?? 'default-password'; // Use your backup password

if (!file_exists($encryptedFile)) {
    echo "Error: File '{$encryptedFile}' not found.\n";
    exit(1);
}

// Output file (remove .enc extension)
$decryptedFile = str_replace('.enc', '', $encryptedFile);

echo "HOMMSS Backup Decryption\n";
echo "========================\n";
echo "Input:  {$encryptedFile}\n";
echo "Output: {$decryptedFile}\n";
echo "Decrypting...\n";

try {
    // Read encrypted data
    $encryptedData = file_get_contents($encryptedFile);
    
    if ($encryptedData === false) {
        throw new Exception("Failed to read encrypted file");
    }
    
    // Decrypt using AES-256-CBC
    $algorithm = 'AES-256-CBC';
    $iv = str_repeat('0', 16); // Same IV used in encryption
    
    $decryptedData = openssl_decrypt($encryptedData, $algorithm, $password, 0, $iv);
    
    if ($decryptedData === false) {
        throw new Exception("Decryption failed. Check password.");
    }
    
    // Write decrypted data
    $result = file_put_contents($decryptedFile, $decryptedData);
    
    if ($result === false) {
        throw new Exception("Failed to write decrypted file");
    }
    
    $originalSize = strlen($encryptedData);
    $decryptedSize = strlen($decryptedData);
    
    echo "Decryption successful!\n";
    echo "Original size: " . formatBytes($originalSize) . "\n";
    echo "Decrypted size: " . formatBytes($decryptedSize) . "\n";
    echo "You can now extract: {$decryptedFile}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
