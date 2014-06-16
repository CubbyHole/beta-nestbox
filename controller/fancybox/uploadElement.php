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

<div id="utils_fancybox">
    <div id="imageClose">
        <img src="./content/img/icon_close_box.png" onclick="closeBoxAndReload();"/>
    </div>
</div>
    <div class="contain">
        <div class="upload_form_cont">
            <div id="dropArea">Drop area</div>
            <div class="infos">
                <div><input type="hidden" id="url" value="http://localhost/Nestbox/controller/actions/uploadElement.php"/></div>
                <h2>File :</h2>
                <div id="result"></div>
                <canvas width="500" height="20"></canvas>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        function uploadElement() {
            var data = 'destination='+$("#destination").select().val()+'&file='+$("#fileExplorer").select().val();
            jQuery.ajax({
                type: 'POST',
                url: './controller/actions/uploadElement.php',
                data: data
            }).success(function(msg){
                    $("#results").html(msg);
                    var reg = /(successfully)/;
                    if(reg.test(msg) == true)
                    {
                        $("#submitUpload").css({
                            'display':'none'
                        });
                        $("#results").css({
                            'color':'green'
                        });
                    }
                });
        }

        // variables
        var dropArea = document.getElementById('dropArea');
        var canvas = document.querySelector('canvas');
        var context = canvas.getContext('2d');
        var count = document.getElementById('count');
        var destinationUrl = document.getElementById('url');
        var result = document.getElementById('result');
        var list = [];
        var totalSize = 0;
        var totalProgress = 0;

        // main initialization
        (function(){

            // init handlers
            function initHandlers() {
                dropArea.addEventListener('drop', handleDrop, false);
                dropArea.addEventListener('dragover', handleDragOver, false);
            }

            // draw progress
            function drawProgress(progress) {
                context.clearRect(0, 0, canvas.width, canvas.height); // clear context

                context.beginPath();
                context.strokeStyle = '#4B9500';
                context.fillStyle = '#4B9500';
                context.fillRect(0, 0, progress * 500, 20);
                context.closePath();

                // draw progress (as text)
                context.font = '16px Verdana';
                context.fillStyle = '#000';
                context.fillText('Progress: ' + Math.floor(progress*100) + '%', 50, 15);
            }

            // drag over
            function handleDragOver(event) {
                event.stopPropagation();
                event.preventDefault();

                dropArea.className = 'hover';
            }

            // drag drop
            function handleDrop(event) {
                event.stopPropagation();
                event.preventDefault();

                processFiles(event.dataTransfer.files);
            }

            // process bunch of files
            function processFiles(filelist) {
                if (!filelist || !filelist.length || list.length) return;

                totalSize = 0;
                totalProgress = 0;
                result.textContent = '';

                for (var i = 0; i < filelist.length && i < 1; i++) {
                    list.push(filelist[i]);
                    totalSize += filelist[i].size;
                }
                uploadNext();
            }

            // upload file
            function uploadFile(file, status) {

                // prepare XMLHttpRequest
                var xhr = new XMLHttpRequest();
                xhr.open('POST', destinationUrl.value);
                xhr.onload = function() {
                    result.innerHTML += this.responseText;
                    handleComplete(file.size);
                };
                xhr.onerror = function() {
                    result.textContent = this.responseText;
                    handleComplete(file.size);
                };
                xhr.upload.onprogress = function(event) {
                    handleProgress(event);
                }
                xhr.upload.onloadstart = function(event) {
                }

                // prepare FormData
                var formData = new FormData();
                formData.append('file', file);
                xhr.send(formData);
                $("#uploadFile").empty();
            }

            // upload next file
            function uploadNext() {
                if (list.length) {
                    dropArea.className = 'uploading';

                    var nextFile = list.shift();
                    if (nextFile.size >= 209715200) { // 200 Mo
                        result.innerHTML += '<div class="f">Too big file (max filesize exceeded)</div>';
                        handleComplete(nextFile.size);
                    } else {
                        uploadFile(nextFile, status);
                    }
                } else {
                    dropArea.className = '';
                }
            }

            initHandlers();
        })();


        $("#fileExplorer").change(function()
        {
            $(".contain").css
            ({
                'display':'none'
            })
            $("#formUpload").css
            ({
                'display':'none'
            })
            $("#formBasicUpload").css
            ({
                'display':'inline',
                'text-align':'center'
            })
        });
    </script>
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


function cmp($a,$b)
{
    return strcmp($a, $b);
}


?>
    <!-- Formulaire pour l'upload basique -->
    <form id="submitBasicUpload" method="POST" enctype="multipart/form-data">
    <div id="uploadFile" style="margin: 50px 0 0 50px;text-align: center"><input style="margin-bottom: 80px;" type="file" name="fileExplorer" id="fileExplorer">
    <?php
        echo '<div id="formBasicUpload" style="display: none;"><label name="chooseDestination">Select a destination: &nbsp</label>';
            echo '<select name="destination" id="destination">
                <option>/</option>';

                $elementList = $elementManager->find(array(
                'serverPath'=> new MongoRegex("/^/"),
                'state' => 1,
                'idOwner' => $userId,
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
            <input type="submit" class="btn-success btn" value="Upload" name="uploadBasicElem" id="uploadBasicElem">
            <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" id="cancel"></div></div>
    </form>

    <!--  formulaire pour l'upload drag and drop -->
        <form id="submitUpload" method="POST" enctype="multipart/form-data">
        <?php echo '<input type="hidden" name="currentDirectory" id="directory" value="'.$_GET['dir'].'" readonly>';
        echo '<div id="formUpload"><label name="chooseDestination">Select a destination: &nbsp</label>';
          echo '<select name="destination" id="destination">
                <option>/</option>';

                $elementList = $elementManager->find(array(
                'serverPath'=> new MongoRegex("/^/"),
                'state' => 1,
                'idOwner' => $userId,
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
        <input type="button" class="btn-success btn" onclick="uploadElement();" value="Upload" name="uploadElem" id="uploadElem">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" id="cancel"></div>
        <div id="informationElementToUpload"></div>
    </form>


    <div id="results"></div>

<?php
}
?>

