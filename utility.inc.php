<?php

class FileManagement {
    /**
     * @abstract Classe per gestire lettura, scrittura file, recupero file da remoto
     */


    /**
     * @abstract converte in array un file CSV
     * @return array data
     *  
     */
     public static function csv_to_array($filename='', $log, $delimiter=',', $local = true)
    {
        
        if($local && (!file_exists($filename) || !is_readable($filename))) {
            $log->logFatal('Impossibile recuperare il file: '. $filename);
            return FALSE;
        }
    
        $header = NULL;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * @abstract converte in array un file json
     * @return array data
     *  
     */
    public static function json_to_array($filename='', $log)
    {
        $specificheLog[0] = $filename;
        
        if(!file_exists($filename) || !is_readable($filename)) {
            $log->logFatal('Impossibile recuperare il file: '. $filename);
//            Logger::fatal("Impossibile recuperare il file:", $specificheLog);
            return FALSE;
        }
        $strJsonFileContents = file_get_contents($filename);
        if (!$strJsonFileContents) {
            $log->logFatal('Impossibile decodificare: '. $filename);
            return FALSE;

        }
        $data = json_decode($strJsonFileContents, true);
        $log->logInfo('recuperato il file: '. $filename);

        return $data;
    }
    public static function getFileFromRemoteBolzano($filename='',$log, $delimiter=';')
    {
        $specificheLog[] = $filename;
        $file_headers = @get_headers($filename);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $exists = false;
            $log->logFatal('Impossibile recuperare il file: '. $filename);
//            Logger::fatal("Impossibile recuperare il file:", $specificheLog);
            return false;
        } else {

            $csvData = file_get_contents($filename, FILE_USE_INCLUDE_PATH);
            if (!$csvData) {
                $log->logFatal('Impossibile recuperare il file: '. $filename);
//                Logger::fatal("Impossibile recuperare il file:", $specificheLog);
                return $csvData;
            }
            $log->logInfo('recuperato il file: '. $filename);
//            Logger::info("recuperato il file:", $specificheLog);
            return $csvData;
        }
    }

    public static function convert_utf8($content) 
    {
        return mb_convert_encoding($content, 'UTF-8',
            mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }

    public static function getFileFromRemote($filename='',$log, $delimiter=';')
    {
        $specificheLog[] = $filename;
        $file_headers = @get_headers($filename);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $exists = false;
            $log->logFatal('Impossibile recuperare il file: '. $filename);
//            Logger::fatal("Impossibile recuperare il file:", $specificheLog);
            return false;
        } else {

            $csvData = file_get_contents($filename, FILE_USE_INCLUDE_PATH);
            if (!$csvData) {
                $log->logFatal('Impossibile recuperare il file: '. $filename);
//                Logger::fatal("Impossibile recuperare il file:", $specificheLog);
                return $csvData;
            }
            $log->logInfo('recuperato il file: '. $filename);
//            Logger::info("recuperato il file:", $specificheLog);

            $lines = explode(PHP_EOL, $csvData);

            $header = NULL;
            $data = array();
            $col = array();

            foreach ($lines as $line) {
                $linePulita = str_replace('"', '', $line);
                $row = explode($delimiter,$linePulita);
                if(!$header) {
                    $header = $row;
                } else {
                    if (count($header) == count($row)) {
                        $data[] = array_combine($header, $row);
                    }
                }
            }
            return $data;
        }
    }

    Public static function string2array($stringa, $delimiter=';') {

        $lines = explode(PHP_EOL, $stringa);

        $header = NULL;
        $data = array();
        $col = array();
    
        foreach ($lines as $line) {
            $linePulita = str_replace('"', '', $line);
            $row = explode($delimiter,$linePulita);
            if(!$header) {
                $header = $row;
            } else {
                for ($i=0; $i < count($row); $i++) { 
                    $row[$i] = str_replace(array(PHP_EOL,'\r'), '', $row[$i]);
                }
                unset($col);
                for ($i=0; $i < count($row); $i++) { 
                    $row[$i] = str_replace(array(PHP_EOL,'\r'), '', $row[$i]);
                    $col[$header[$i]] = $row[$i];
                }
                   $data[] = $col;
            }
        }
        return $data;
    }    

    public static function upload_to_dl($file2upload, $url=UPLOAD_URL, $cod_prov, $cod_com, $log) {

        //The name of the field for the uploaded file.
        $uploadFieldName = 'file';

        $postName = basename($file2upload);

        //        curl --location --request POST 'http://10.99.36.78:40525/action/push?path=/dl/prova_upload/test/' --form 'file=@/C: /prova.txt'
        //Initiate cURL
        $ch = curl_init();

//        $escapedAction = curl_escape($ch, UPLOAD_ACTION); //ritorna errore con php 5.3 :/
        $url = $url.UPLOAD_ACTION;
//        $url = $url.$escapedAction;

        $upload_path = DL_PATH.PATH_PROV.'/'.$cod_prov.PATH_COMUNI.$cod_com.'/';

        //Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //Set the HTTP request to POST
        curl_setopt($ch, CURLOPT_POST, true);

        //Tell cURL to return the output as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //If the function curl_file_create exists
        if(function_exists('curl_file_create')){
            //Use the recommended way, creating a CURLFile object.
            $filePath = curl_file_create($file2upload, '', $postName);
        } else{
            //Otherwise, do it the old way.
            //Get the canonicalized pathname of our file and prepend
            //the @ character.
            $filePath = '@' . $file2upload.';filename='.$postName;
//            $value = "@{$this->filename};filename=" . $this->postname;
            //Turn off SAFE UPLOAD so that it accepts files
            //starting with an @
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }

        //Setup our POST fields
        $postFields = array(
            $uploadFieldName => $filePath,
            'path' => $upload_path
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        //Execute the request
        $result = curl_exec($ch);

        //If an error occured, throw an exception
        //with the error message.
        if(curl_errno($ch)){
            $log->logError('Errore: '. curl_error($ch).' Impossibile caricare il file: '. $file2upload);
        } else {
            $log->logNotice('File caricato: '. $file2upload .' in '.$upload_path);
        }
        return $result;

    }

    //public static function upload_generic_to_dl($file2upload, $log, $upload_path=DL_PATH.PATH_PROV.'/', $url=UPLOAD_URL) {
    public static function upload_generic_to_dl($file2upload, $log, $upload_path, $url=UPLOAD_URL) {    

        $uploadFieldName = 'file';

        $postName = basename($file2upload);

        $ch = curl_init();

        $url = $url.UPLOAD_ACTION;

        //Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //Set the HTTP request to POST
        curl_setopt($ch, CURLOPT_POST, true);

        //Tell cURL to return the output as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //If the function curl_file_create exists
        if(function_exists('curl_file_create')){
            //Use the recommended way, creating a CURLFile object.
            $filePath = curl_file_create($file2upload, '', $postName);
        } else{
            //Otherwise, do it the old way.
            //Get the canonicalized pathname of our file and prepend
            //the @ character.
            $filePath = '@' . $file2upload.';filename='.$postName;
//            $value = "@{$this->filename};filename=" . $this->postname;
            //Turn off SAFE UPLOAD so that it accepts files
            //starting with an @
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }

        //Setup our POST fields
        $postFields = array(
            $uploadFieldName => $filePath,
            'path' => $upload_path
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        //Execute the request
        $result = curl_exec($ch);

        //If an error occured, throw an exception
        //with the error message.
        if(curl_errno($ch)){
            $log->logError('Errore: '. curl_error($ch).' Impossibile caricare il file: '. $file2upload);
        } else {
            $log->logNotice('File caricato: '. $file2upload .' in '.$upload_path);
        }
        return $result;

    }
    
    public static function save_object_to_json($jsonObject,$file2write,$log) {

        //encode and output jsonObject
        $specificheLog[0] = $file2write;
        $specificheLog[1] = 'Comune ' . $jsonObject->int->desc_com;

        $path_prov = PATH_PROV;
        $path_Prov_specifico = '/'.$jsonObject->int->cod_prov;
        $path_comune = PATH_COMUNI;
        $path_comune_specifico = $jsonObject->int->cod_com;

        if (!file_exists(CONV_DIR)) {
            mkdir(CONV_DIR, 0777, true);
        }
        if (!file_exists(CONV_DIR.$path_prov)) {
            mkdir(CONV_DIR.$path_prov, 0777, true);
        }
        if (!file_exists(CONV_DIR.$path_prov.$path_Prov_specifico)) {
            mkdir(CONV_DIR.$path_prov.$path_Prov_specifico, 0777, true);
        }
        if (!file_exists(CONV_DIR.$path_prov.$path_Prov_specifico.$path_comune)) {
            mkdir(CONV_DIR.$path_prov.$path_Prov_specifico.$path_comune, 0777, true);
        }
        if (!file_exists(CONV_DIR.$path_prov.$path_Prov_specifico.$path_comune.$path_comune_specifico)) {
            mkdir(CONV_DIR.$path_prov.$path_Prov_specifico.$path_comune.$path_comune_specifico, 0777, true);
        }

//        header('Content-Type: application/json');
        if (file_exists($file2write)) {
            if (!copy($file2write, $file2write.'old.json')) {
                $log->logError('Impossibile copiare il file: '. $file2write);
                //Logger::error("Impossibile copiare il file:", $specificheLog);
            }        
        }
        $dataJson = json_encode($jsonObject); 
        $bytes = file_put_contents($file2write, $dataJson);
        if (!$bytes) {
            $log->logFatal('Impossibile salvare il file: '. $file2write);
//            Logger::fatal("Impossibile salvare il file:", $specificheLog);
            return $bytes;
        }
        $log->logNotice('file salvato correttamente: '. $file2write);
//        Logger::notice("file salvato correttamente:", $specificheLog);
    }    
}
