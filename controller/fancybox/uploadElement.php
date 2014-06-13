<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 05/06/14
 * Time: 12:36
 */

header('Content-Type: text/html; charset=utf-8');


$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
?>

<script type="text/javascript">
(function () {
    "use strict";

    var file_id = 1, drop_zone;
    document.getElementById('drop_zone').onclick = function () {
    document.getElementById('files').click();

    return false;
    };

    if ((typeof File !== 'undefined') && !File.prototype.slice) {
    if(File.prototype.webkitSlice) {
    File.prototype.slice = File.prototype.webkitSlice;
    }

    if(File.prototype.mozSlice) {
    File.prototype.slice = File.prototype.mozSlice;
    }
    }

    if (!window.File || !window.FileReader || !window.FileList || !window.Blob || !File.prototype.slice) {
    alert('File APIs are not fully supported in this browser. Please use latest Mozilla Firefox or Google Chrome.');
    }

    function hash_file(file, workers){
       console.log(file);
    var i, buffer_size, block, threads, reader, blob, handle_hash_block, handle_load_block;

    handle_load_block = function (event) {
    for( i = 0; i < workers.length; i += 1) {
    threads += 1;
    workers[i].postMessage({
    'message' : event.target.result,
    'block' : block
    });
    }
    };
    handle_hash_block = function (event) {
    threads -= 1;

    if(threads === 0) {
    if(block.end !== file.size) {
    block.start += buffer_size;
    block.end += buffer_size;

    if(block.end > file.size) {
    block.end = file.size;
    }
    reader = new FileReader();
    reader.onload = handle_load_block;
    blob = file.slice(block.start, block.end);

    reader.readAsArrayBuffer(blob);
    }
    }
    };
    buffer_size = 64 * 16 * 1024;
    block = {
    'file_size' : file.size,
    'start' : 0
    };

    block.end = buffer_size > file.size ? file.size : buffer_size;
    threads = 0;

    for (i = 0; i < workers.length; i += 1) {
    workers[i].addEventListener('message', handle_hash_block);
    }
    reader = new FileReader();
    reader.onload = handle_load_block;
    blob = file.slice(block.start, block.end);

    reader.readAsArrayBuffer(blob);
    }

    function handle_worker_event(id) {
    return function (event) {
    if (event.data.result) {
    $("#" + id).parent().html(event.data.result);
    } else {
    $("#" + id + ' .bar').css('width', event.data.block.end * 100 / event.data.block.file_size + '%');
    }
    };
    }

    function handle_file_select(event) {
    event.stopPropagation();
    event.preventDefault();

    var i, output, files, file, workers, worker;
    files = event.dataTransfer ? event.dataTransfer.files : event.target.files;
    output = [];

    for (i = 0; i < files.length; i += 1) {
    file = files[i];
    workers = [];

    output.push('<tr><td class="span12"><strong>', file.name, '</strong></td><td> (', file.type || 'n/a', ') - ', file.size, ' bytes</td></tr>');

//    if (document.getElementById('hash_md5').checked) {
//    output.push('<tr>', '<td>MD5</td><td> <div class="progress progress-striped active" style="margin-bottom: 0px" id="md5_file_hash_', file_id, '"><div class="bar" style="width: 0%;"></div></div></td></tr>');
//    worker = new Worker('/js/calculator/calculator.worker.md5.js');
//    worker.addEventListener('message', handle_worker_event('md5_file_hash_' + file_id));
//    workers.push(worker);
//    }


    output.push('<tr>', '<td>SHA1</td><td> <div class="progress progress-striped active" style="margin-bottom: 0px" id="sha1_file_hash_', file_id, '"><div class="bar" style="width: 0%;"></div></div></td></tr>');
    worker = new Worker('../Nestbox/content/js/calculatorSha1.js');
    worker.addEventListener('message', handle_worker_event('sha1_file_hash_' + file_id));
    workers.push(worker);


//    if (document.getElementById('hash_sha256').checked) {
//    output.push('<tr>', '<td>SHA256</td><td> <div class="progress progress-striped active" style="margin-bottom: 0px" id="sha256_file_hash_', file_id, '"><div class="bar" style="width: 0%;"></div></div></td></tr>');
//    worker = new Worker('/ /js/calculator/calculator.worker.sha256.js');
//    worker.addEventListener('message', handle_worker_event('sha256_file_hash_' + file_id));
//    workers.push(worker);
//    }

    hash_file(file, workers);
    file_id += 1;

    }

    document.getElementById('list').innerHTML = '<table class="table table-striped table-hover">' + output.join('') + '</table>' + document.getElementById('list').innerHTML;
    }

    function handle_drag_over(event) {
    event.stopPropagation();
    event.preventDefault();
    }

    drop_zone = document.getElementById('drop_zone');

    drop_zone.addEventListener('dragover', handle_drag_over, false);
    drop_zone.addEventListener('drop', handle_file_select, false);

    document.getElementById('files').addEventListener('change', handle_file_select, false);
}());

function uploadElement() {
    var data = 'destination='+$("#destination").select().val()+'&directory='+$("#directory").select().val(); // voir ce qu'on doit récupérer avec Alban
    jQuery.ajax({
        type: 'POST',
        url: './controller/actions/uploadElement.php',
        data: data
    }).success(function(msg){
            $("#results").html(msg);
//                alert(data); 
        });
}
    </script>

<div id="utils_fancybox">
<div id="imageClose">
    <img src="./content/img/icon_close_box.png" onclick="closeBoxAndReload();"/>
</div>
<div id="infosElement">
    <span class="glyphicon glyphicon-info-sign" onclick="elementInformation();"></span>
</div>
</div>

<?php
if( isset($_POST['var']) && !empty($_POST['var']) )
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
    $refElementEmptyDirectory = $refElementManager->findOne(array(
        'code' => '4002',
        'state' => 1
    ));
    if($refElementEmptyDirectory instanceof RefElement)
        $idRefElementEmptyDirectory = $refElementEmptyDirectory->getId();
    else
        return $refElementEmptyDirectory;

    $refElementNotEmptyDirectory = $refElementManager->findOne(array(
        'code' => '4003',
        'state' => 1
    ));
    if($refElementNotEmptyDirectory instanceof RefElement)
        $idRefElementNotEmptyDirectory = $refElementNotEmptyDirectory->getId();
    else
        return $refElementNotEmptyDirectory;

     echo '<div id="elementInformations">
            <p><label name="directory">Current Directory : '.$_GET['dir'].'</label><p>
           </div>';

function cmp($a,$b)
{
    return strcmp($a, $b);
}


?>

    <!--  formulaire pour la création de dossier -->
        <form id="submitUpload" method="POST" enctype="multipart/form-data">
        <?php echo '<input type="hidden" name="currentDirectory" id="directory" value="'.$_GET['dir'].'" readonly>'; ?>

            <div id="drop_zone" class="alert alert-block alert-success pagination-centered">
                <h1 style="text-align: center">Drop files here or click for select</h1>
            </div>
            <div>

<!--                <input type="checkbox" id="hash_sha1" title="Check this to calculate SHA1 file hash" checked/>-->
<!--                SHA1&nbsp;-->

                <div style="float: right">
                    <input type="file" id="files" name="files[]" multiple style="display: none"/>
                </div>
            </div>
            <div id="list"></div>
        <?php
        echo '<div id="formUpload"><label name="chooseDestination">Select a destination: &nbsp</label>';
          echo '<select name="destination" id="destination">
                <option>/</option>';

                $elementList = $elementManager->find(array(
                'serverPath'=> new MongoRegex("/^/"),
                'state' => 1,
                '$or' => array(
                array('idRefElement' => $idRefElementEmptyDirectory),
                array('idRefElement' => $idRefElementNotEmptyDirectory)
                )
                ),
                array(
                'serverPath' => TRUE,
                'name' => TRUE,
                '_id' => FALSE
                ));

                $f = function($array){return $array['serverPath'].$array['name'];};

                $elementList = array_map($f, $elementList);
                $result = array_unique($elementList);

                usort($result, "cmp");
                foreach($result as $element)
                {
                echo '<option>'.$element.'/</option>';
                }
                echo '</select>';
        ?>
        <br /><br />
        <input type="button" class="btn-success btn" onclick="uploadElement();" value="Upload" name="uploadElem">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel"></div>
        <div id="informationElementToUpload"></div>
    </form>
    <div id="results"></div>

<?php
}
?>