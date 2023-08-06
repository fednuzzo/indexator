<?php

/**
 * @param string $path
 * @return string[]
 * @throws Exception
 */
function getFilesRecursive(string $path): array {
    if (!($handle = opendir($path))) {
        throw new Exception("Can't open directory {$path}");
    }

    $toSkip = [".", ".."];
    $files = [];
    while ($curr = readdir($handle)) {
        if (in_array($curr, $toSkip)) continue;
        if ($curr[0] === ".") continue;

        $filePath = "{$path}/{$curr}";

        if (is_dir($filePath)) {
            $subDirFiles = getFilesRecursive($filePath);
            foreach($subDirFiles as $subDirFile) {
                $files[] = $subDirFile;
            }
        } else {
            $files[] = $filePath;
        }
    }

    return $files;
}

try {
    $files = getFilesRecursive(".");
    $response = [];
    foreach($files as $file) {
        $info = pathinfo($file);
        $curr = [
            'filepath' => $file,
            'dirname' => $info['dirname'],
            'basename' => $info['basename'],
            'extension' => $info['extension'],
            'filename' => $info['filename'],
            'size' => filesize($file)
        ];

        $response[] = $curr;
    }

    $json = json_encode($response, JSON_PRETTY_PRINT);
    print($json);
    exit;
}
catch (Exception $e) {
    print($e->getMessage());
}