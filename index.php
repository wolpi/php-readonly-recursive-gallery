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
    $dir = dir($path);
    #echo "<ul>";
    echo "<div id=\"links\">";
    if (!$isStartDir) {
        $parentDir = dirname($path);
        printDir($parentDir, "..");
    }
    while (false !== ($entry = $dir->read())) {
        // ignore dot files
        $isDotFile = (substr($entry, 0, 1) === ".");
        if (!$isDotFile) {
            $fullPath = $path . "/" . $entry;
            if (is_dir($fullPath)) {
                printDir($fullPath, $entry);
            } elseif (is_file($fullPath)) {
                handleFile($path, $entry, $thumbnail_cache_dir);
            }
        }
    }
    #echo "</ul>";
    echo "</div>";
    $dir->close();
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
                mkdir($thumbDirFullPath, 0777, true);
                createThumb($originalFullPath, $thumbFileFullPath);
            }
            printImgFile($originalFullPath, $thumbFileFullPath);
        }
    }
}

function createThumb($originalFullPath, $thumbFullPath) {
    exec("convert $originalFullPath -resize 64x64\> $thumbFullPath");
}

function printImgFile($originalFullPath, $thumbFullPath) {
    #echo "<li class=\"file\"><a href=\"$originalFullPath\"><img src=\"$thumbFullPath\" /></a></li>";
    echo "<a class=\"file\" href=\"$originalFullPath\" data-dialog>";
    echo "<img src=\"$thumbFullPath\" />";
    echo "</a>";
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
