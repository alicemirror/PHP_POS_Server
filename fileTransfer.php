<?php

    const DS = '/';

    // Include the config only once
    require_once( dirname(__FILE__) . DS.'clic.php' );

    const POST_FILE ='file';
    const POST_PATH ='store';
    const POST_OVERWRITE ='overwrite';
    const POST_TRANSFER ='transfer';

    $upload_file = $_REQUEST[POST_FILE];
    $upload_path = getPath($_REQUEST[POST_PATH]);
    $overwrite = $_REQUEST[POST_OVERWRITE];
    $transfer = $_REQUEST[POST_TRANSFER];

    $full_path = $upload_path . $upload_file;
    
    if ($transfer == 'up') {
        if (!is_dir($upload_path)) {
            mkdir($upload_path);
        }        
        uploadFile($full_path, $overwrite);
    }
    elseif ($transfer == 'down') {
        sendFile($full_path);
    }
    
    function uploadFile($path, $overwrite) {
        $putdata = fopen("php://input", "r");

        // x Write only. Creates a new file. Returns FALSE and an error if file already exists)
        // w Write only. Opens and clears the contents of file; or creates a new file if it doesn't exist
        if ($overwrite) {
            $fp = fopen($path, "w");
        }
        else {
            if (!is_file($path)) {
                $fp = fopen($path, "x");
            }
            else {
                echo ('Error 1: File already exists. Try setting overwrite parameter to 1.');    
                fclose($putdata);
                exit;
            }
        }

        while ($data = fread($putdata, 1024)) {
            fwrite($fp, $data);
        }
        
        fclose($fp);
        fclose($putdata);

        // ensure the given file has been uploaded
        if (!isset($path)) {
            echo('Error 2: Failed Upload');
        }
        else {
            echo('Error 0: Upload success.');
        }
    }
    
     /************************************************ 
    * @path the path of the file to be downloaded
    * @return nothing
    */
    function sendFile($path){
        $xar=explode("/", $path);
        $file_name = end($xar);
        $file_size = (string)filesize($path);
        if (hasValidProperties($path, $file_size)) {
            header("Content-Type: application/text ;   name=" . $file_name);
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $file_size);
            header("Content-Disposition: inline; filename=" . $file_name);
            header("Expires: 0");
            header("Cache-Control: no-cache, must-revalidate");
            readfile($path);
        }
    }

    /**********************************
    * @return SUCCESS, FILE_SIZE_EXCEDE or FILE_TYPE_NOT_SUPPORTED contants
    */
    function hasValidProperties($file, $file_size){

        $MAX_FILE_SIZE = 1200000;

        // limit maximum file size
        if( $file_size > $MAX_FILE_SIZE){
            echo("Error 4: The file is too big: Max size is " . $MAX_FILE_SIZE . " Kb, but file size is " . $file['size'] / 1024 . " Kb.");
            return false;
        } 

        return true;
    }
?> 