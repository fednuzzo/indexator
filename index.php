<?php

/**
 * @param string $path
 * @param string[] $files
 * @param string[] $dirs
 * @throws Exception
 */
function getFilesRecursive(string $path, array &$files, array &$dirs) {
    if (!($handle = opendir($path))) {
        throw new Exception("Can't open directory {$path}");
    }

    $toSkip = [".", ".."];
    while ($curr = readdir($handle)) {
        if (in_array($curr, $toSkip)) continue;
        if ($curr[0] === ".") continue;

        $filePath = "{$path}/{$curr}";

        if (is_dir($filePath)) {
            $dirs[] = $filePath;
            $subFiles = [];
            $subDirs = [];
            getFilesRecursive($filePath, $subFiles, $subDirs);
            foreach($subFiles as $subFile) {
                $files[] = $subFile;
            }
            foreach($subDirs as $subDir) {
                $dirs[] = $subDir;
            }
        } else {
            $files[] = $filePath;
        }
    }
}

try {
    $files = [];
    $dirs = [];
    getFilesRecursive(".", $files, $dirs);
    $response = [
        'files' => [],
        'dirs' => $dirs
    ];
    foreach($files as $file) {
        $info = pathinfo($file);
        $curr = [
            'filepath' => $file,
            'dirname' => $info['dirname'],
            'basename' => $info['basename'],
            'extension' => $info['extension'],
            'filename' => $info['filename'],
            'size' => filesize($file),
            'checksum' => md5_file($file)
        ];

        $response['files'][] = $curr;
    }

    $json = json_encode($response, JSON_PRETTY_PRINT);
    http_response_code(200);
    header('Content-type: application/json');
    print($json);
    exit;
}
catch (Exception $e) {
    print($e->getMessage());
}