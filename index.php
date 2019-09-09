<?php
// édition de fichier .html ou .txt
if ((isset($_POST['dir']) && !empty($_POST['dir'])) && (isset($_POST['file']) && !empty($_POST['file']))) {
    if (isset($_POST['contenu']) && !empty($_POST['contenu'])) {
        $fileEdit = $_POST['dir'] . '/' . $_POST['file'];
        $file = fopen($fileEdit, "w");
        fwrite($file, trim($_POST['contenu']));
        fclose($file);
    }
    header("HTTP/1.1 303 See Other");
    header('Location: index.php?dir='.$_POST['dir']);
}

// suppression récursive des éléments d'un répertoire
function deleteFolder(string $dir) {
    $dir_delete = opendir($dir);
    while (($file = readdir($dir_delete)) !== false) {
         if (!in_array($file, array('.', '..'))) {
            if (is_dir($dir . '/' . $file)) {
                deleteFolder($dir . '/' . $file);
            } else {
               unlink($dir . '/' . $file);
            }
        }
    }
    closedir($dir_delete);
    rmdir($dir);
}
// --

// Demande de suppression
if (isset($_GET['delete']) && isset($_GET['dir'])) {
    $deleteFileOrFolder = $_GET['dir'] . '/' .$_GET['delete'];
    if (is_dir($deleteFileOrFolder)) {
        // suppression récursive fichiers et dossiers
        deleteFolder($deleteFileOrFolder);
    } else {
        // suppression du fichier
        if (file_exists($deleteFileOrFolder)) {
            unlink($deleteFileOrFolder);
        }
    }
    header("HTTP/1.1 303 See Other");
    header('Location: index.php?dir='.$_GET['dir']);
}


// liste répertoires et fichiers du dossier 'files' ou du dossier passé en paramètre
$dir = "files";
if (isset($_GET['dir']) && !empty($_GET['dir'])) {
    $dir = $_GET['dir'];
}

$listFiles = [];

if (is_dir($dir)) {
    $dir_current = opendir($dir);
    while (($file = readdir($dir_current)) !== false) {
        if (!in_array($file, array('.', '..'))) {
            if (is_dir($dir . '/' . $file)) {
                $listFiles[] = array('name' => $file, 'folder' => $dir, 'type' => 'folder');
            } else {
                $listFiles[] = array('name' => $file, 'folder' => $dir, 'type' => 'file');
            }
         }
    }
    closedir($dir_current);
}

include('inc/head.php');

?>

<p>
    <a href="/" >Home</a>
</p>

<?php
if (isset($listFiles) && count($listFiles) > 0) :
    foreach ($listFiles as $file) :
        $url = '?dir=' . $file['folder'] . '/' . $file['name'];
        $urlDelete = '?dir=' . $file['folder'] . '&amp;delete=' . $file['name'];

        if ($file['type'] == "file") {
            $extention = new SplFileInfo($file['name']);
            if ($extention->getExtension() == 'jpg') {
                $url = '';
            } else {
                $url = '?dir='.$file['folder'].'&amp;edit='.$file['name'];
            }
        }
        if ($url != '') :
    ?>

    <p>
        <a href="<?= $url ?>" >
            <img src="assets/images/<?= $file['type'] ?>.png" alt="" />
            <?= $file['name'] ?>
        </a>
        <a href="<?= $urlDelete ?>">
            <img src="assets/images/rubbish-bin.png" alt="" />
        </a>
    </p>

    <?php else: ?>

    <p>
       <img src="assets/images/<?= $file['type'] ?>.png" alt="" />
            <?= $file['name'] ?>
        <a href="<?= $urlDelete ?>">
            <img src="assets/images/rubbish-bin.png" alt="" />
        </a>
    </p>

    <?php
        endif;
    endforeach;

    if (isset($_GET['edit']) && !empty($_GET['edit'])) :
        $file = $_GET['dir'] . '/' . $_GET['edit'];
        $contenu = file_get_contents($file);
    ?>
        <form method="post" action="">
            <input type="hidden" name="dir" value="<?=$_GET['dir'];?>" />
            <input type="hidden" name="file" value="<?=$_GET['edit'];?>" />
            <label>Edit file</label>
            <textarea name="contenu" style="width:100%;height:200px;"><?php echo $contenu; ?></textarea><br/>
            <input type="submit" value="Save" />
        </form>
    <?php
    endif;
endif;

include('inc/foot.php');
?>