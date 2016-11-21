<?php
include 'config.php'; 
?>
<!doctype html>
<html>
<head>
    <title><?php echo $html_title ?></title>
    <link rel="stylesheet" type="text/css" href="gallery.css" />
    <link rel="stylesheet" href="blueimp-jquery-image-gallery/jquery-ui.css" id="theme">
    <link rel="stylesheet" href="blueimp-jquery-image-gallery/bla/blueimp-gallery.min.css">
</head>
<body>
    <?php
    $pathPara = $_GET['p'];
    $saniPath = sanitizePath($image_start_dirs, $pathPara);
    if ($saniPath != null) {
        printDirContent($saniPath, false, $thumbnail_cache_dir);
    } else {
        foreach ($image_start_dirs as $path) {
            printDirContent($path, true, $thumbnail_cache_dir);
        }
    }
    printBottom();
    ?>
</body>
</html>
<?php
function sanitizePath($image_start_dirs, $path) {
    if (strpos($path, "../")) {
        return null;
    }
    $posOfFirstSlash = strpos($path, "/");
    if ($posOfFirstSlash) {
        $firstDirPart = substr($path, 0, $posOfFirstSlash);
        foreach ($image_start_dirs as $startDir) {
            if ($startDir === $firstDirPart) {
                return $path;
            }
        }
    }
    return null;
}

function printDirContent($path, $isStartDir, $thumbnail_cache_dir) {
    $dirObj = dir($path);
    #echo "<ul>";
    echo "<div id=\"links\">";
    if (!$isStartDir) {
        $parentDir = dirname($path);
        printDir($parentDir, "..");
    }
    $dirs = array();
    $files = array();
    while (false !== ($entry = $dirObj->read())) {
        // ignore dot files
        $isDotFile = (substr($entry, 0, 1) === ".");
        if (!$isDotFile) {
            $fullPath = $path . "/" . $entry;
            if (is_dir($fullPath)) {
                array_push($dirs, $entry);
            } elseif (is_file($fullPath)) {
                array_push($files, $entry);
            }
        }
    }
    sort($dirs);
    sort($files);
    foreach ($dirs as $dir) {
        $fullPath = $path . "/" . $dir;
        printDir($fullPath, $dir);
    }
    foreach ($files as $file) {
        handleFile($path, $file, $thumbnail_cache_dir);
    }
    #echo "</ul>";
    echo "</div>";
    $dirObj->close();
}

function handleFile($path, $fileName, $thumbnail_cache_dir) {
    $lastIndexOfDot = strrpos($fileName, ".");
    if ($lastIndexOfDot) {
        $fileExtension = substr($fileName, $lastIndexOfDot + 1, strlen($fileName));
        $fileExtensionLower = strtolower($fileExtension);
        if ($fileExtensionLower === "jpg" || $fileExtensionLower === "jpeg" || $fileExtensionLower === "png") {
            $thumbDirFullPath = $thumbnail_cache_dir."/".$path;
            $thumbFileFullPath = $thumbDirFullPath."/".$fileName;
            $originalFullPath = $path."/".$fileName;
            if (!file_exists($thumbFileFullPath)) {
                if (!file_exists($thumbDirFullPath)) {
                    mkdir($thumbDirFullPath, 0777, true);
                }
                createThumb($originalFullPath, $thumbFileFullPath);
            }
            printImgFile($originalFullPath, $thumbFileFullPath);
            printImgPrefetch($originalFullPath);
        }
    }
}

function createThumb($originalFullPath, $thumbFullPath) {
    $originalFullPath = escapePathForConvert($originalFullPath);
    $thumbFullPath = escapePathForConvert($thumbFullPath);
    exec("convert $originalFullPath -resize 64x64\> $thumbFullPath");
}

function escapePathForConvert($path) {
    return str_replace(" ", "\\ ", $path);
}

function printImgFile($originalFullPath, $thumbFullPath) {
    #echo "<li class=\"file\"><a href=\"$originalFullPath\"><img src=\"$thumbFullPath\" /></a></li>";
    echo "<a class=\"file\" href=\"$originalFullPath\" data-dialog>";
    echo "<img src=\"$thumbFullPath\" />";
    echo "</a>";
}

function printImgPrefetch($originalFullPath) {
    echo "<link rel=\"prefetch\" href=\"$originalFullPath\" />";
}

function printDir($path, $name) {
    #echo "<li class=\"dir\"><a href=\"?p=$path\">$name</a></li>";
    echo "<div class=\"dir\"><a href=\"?p=$path\">$name</a></div>";
}

function printBottom() {
    // <!-- The dialog widget -->
    echo "<div id=\"blueimp-gallery-dialog\" data-show=\"fade\" data-hide=\"fade\">";
        // <!-- The gallery widget  -->
        echo "<div class=\"blueimp-gallery blueimp-gallery-carousel blueimp-gallery-controls\">";
            echo "<div class=\"slides\"></div>";
            echo "<a class=\"prev\">‹</a>";
            echo "<a class=\"next\">›</a>";
            echo "<a class=\"play-pause\"></a>";
        echo "</div>";
    echo "</div>";
    echo "<script src=\"blueimp-jquery-image-gallery/jquery.min.js\"></script>";
    echo "<script src=\"blueimp-jquery-image-gallery/jquery-ui.min.js\"></script>";
    echo "<script src=\"blueimp-jquery-image-gallery/jquery.blueimp-gallery.min.js\"></script>";
    echo "<script src=\"blueimp-jquery-image-gallery/jquery.image-gallery.min.js\"></script>";
}
?>
