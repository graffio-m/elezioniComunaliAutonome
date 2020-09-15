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

        header('Content-Type: application/json');
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


class scrutinio {

    public $jsonObject;
    public $numeroCandidato = 0;
    public $numeroLista = 0;

    public function __construct($dataAffluenzaAR) {

        $this->jsonObject = new stdClass();
        $this->jsonObject->int = new stdClass();
        $this->jsonObject->int->st = STATO;
        $this->jsonObject->int->t_ele = 'Comunali';
        $this->jsonObject->int->f_ele = 'SCRUTINI';
        $this->jsonObject->int->dt_ele = DATA_ELEZIONI;
        $this->jsonObject->int->l_terr = 'COMUNE';
        $this->jsonObject->int->area = 'I';

        switch ($dataAffluenzaAR['desc_prov']) {

            case 'TRENTO':

                $nomeComune = $dataAffluenzaAR['Nome Comune'];
                $this->jsonObject->int->desc_com = strtoupper($nomeComune);
                $this->jsonObject->int->cod_com = $dataAffluenzaAR['cod_com'];;
                $this->jsonObject->int->desc_prov = $dataAffluenzaAR['desc_prov'];
                $this->jsonObject->int->cod_prov = COD_PROV; 
                $this->jsonObject->int->cod_ISTAT = $dataAffluenzaAR['Istat Comune'];
                $this->jsonObject->int->ele_m = $dataAffluenzaAR['ElettoriM'];
                $this->jsonObject->int->ele_f = $dataAffluenzaAR['ElettoriF'];
                $this->jsonObject->int->ele_t = $dataAffluenzaAR['ElettoriT'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaAR['VotantiF'];
                $this->jsonObject->int->vot_f = $dataAffluenzaAR['VotantiF'];
                $this->jsonObject->int->vot_t = $dataAffluenzaAR['VotantiT'];

                $this->jsonObject->int->dt_agg = date("YmdHis");
            break;
            case 'BOLZANO':

                $nomeComune = $dataAffluenzaAR['DESCRIZIONEISTAT_I'].'/'.$dataAffluenzaAR['DESCRIZIONEISTAT_D'];
                $this->jsonObject->int->desc_com = strtoupper($nomeComune);
                $this->jsonObject->int->cod_com = $dataAffluenzaAR['cod_com'];;
                $this->jsonObject->int->desc_prov = $dataAffluenzaAR['desc_prov'];
                $this->jsonObject->int->cod_prov = COD_PROV; 
                $this->jsonObject->int->cod_ISTAT = $dataAffluenzaAR['cod_ISTAT'];
                $this->jsonObject->int->ele_m = $dataAffluenzaAR['ELETTORIMASCHI'];
                $this->jsonObject->int->ele_f = $dataAffluenzaAR['ELETTORIFEMMINE'];
                $this->jsonObject->int->ele_t = $dataAffluenzaAR['TOTALEELETTORI'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaAR['VOTANTIMASCHI'];
                $this->jsonObject->int->vot_f = $dataAffluenzaAR['VOTANTIFEMMINE'];
                $this->jsonObject->int->vot_t = $dataAffluenzaAR['TOTALEVOTANTI'];

                $this->jsonObject->int->dt_agg = date("YmdHis");
            break;

        }
        $this->jsonObject->cand = array();

    }

    public function __destruct() {
        unset($this->jsonObject);

        
    }    

    public function setVotiListeCandidato($dataVotiListeHA) {
        $idSindaco = $this->jsonObject->cand[$this->numeroCandidato]->id_sindaco;
        if (!isset($this->jsonObject->cand[$this->numeroCandidato]->liste)) {
            $this->jsonObject->cand[$this->numeroCandidato]->liste = array();
        }

        switch ($this->jsonObject->int->desc_prov) {
            case 'TRENTO':
                $this->setVotiListeCandidatoTrento($dataVotiListeHA);
                break;
            case 'BOLZANO':
                $this->setVotiListeCandidatoBolzano($dataVotiListeHA);
                break;
        }

    }

    public function setCandidato($candidatoAr) {
        if (!array_key_exists($this->numeroCandidato, $this->jsonObject->cand)) {
            $this->jsonObject->cand[$this->numeroCandidato] = new stdClass();
            $this->numeroLista = 0;
        }
        switch ($this->jsonObject->int->desc_prov) {
            case 'TRENTO':
                $this->setCandidatoTrento($candidatoAr);
                break;
            case 'BOLZANO':
                $this->setCandidatoBolzano($candidatoAr);
                break;

        }

    }
    /**
     * Imposta i dati del candidato Sindaco
     * Trento
     *
     * @param array $candidatoAr
     * @return void
     */

    public function setVotiListeCandidatoTrento($dataVotiListeHA) {
        $idSindaco = $this->jsonObject->cand[$this->numeroCandidato]->id_sindaco;
        /**
         * Ciclare $dataVotiListeHA[$idSindaco]
         */
        foreach ($dataVotiListeHA[$idSindaco] as $singolaLista) {
            if (!array_key_exists($this->numeroLista, $this->jsonObject->cand[$this->numeroCandidato]->liste)) {
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista] = new stdClass();
            }
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->descr_lista = $singolaLista['Nome Lista']; 
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->voti = $singolaLista['Voti']; 
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->pos = $singolaLista['Progressivo Lista']; 

            $percVotiLista = 0;
            if ($singolaLista['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
                $percVotiLista = ($singolaLista['Voti']/$this->jsonObject->int->vot_t)*100;
            }
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->perc = $percVotiLista; 

            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->img_lis = '';                
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->seggi = 0; 
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->sort_lis = 0; 


            $this->numeroLista++;

        }

    } 

    public function setVotiListeCandidatoBolzano($dataVotiListeHA) {

    } 

    /**
     * Imposta i dati del candidato Sindaco
     * Bolzano
     *
     * @param array $candidatoAr
     * @return void
     */
    public function setCandidatoBolzano($candidatoAr) {

        $this->jsonObject->cand[$this->numeroCandidato]->cogn = $candidatoAr['NOMINATIVO']; 
        $this->jsonObject->cand[$this->numeroCandidato]->nome = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->a_nome = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->pos = $candidatoAr['ORDINECANDIDATURA']; 
        $this->jsonObject->cand[$this->numeroCandidato]->voti = $candidatoAr['VOTI_SINDACO']; 
        $this->jsonObject->cand[$this->numeroCandidato]->id_sindaco = $candidatoAr['ORDINECANDIDATURA']; 

        $percVoti = 0;
        if ($candidatoAr['VOTI_SINDACO'] > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = ($candidatoAr['VOTI_SINDACO']/$this->jsonObject->int->vot_t)*100;
        }
        $this->jsonObject->cand[$this->numeroCandidato]->perc = $percVoti; 
        $this->jsonObject->cand[$this->numeroCandidato]->d_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->l_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->eletto = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_ass = 0; 
        $this->jsonObject->cand[$this->numeroCandidato]->sort_coal = null; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_sort_coal = null; 

        /** Duplicato di voti e perc
         */
        $this->jsonObject->cand[$this->numeroCandidato]->tot_vot_lis = $candidatoAr['VOTI_SINDACO']; 
        $this->jsonObject->cand[$this->numeroCandidato]->perc_lis = $percVoti; 


        /**
         *  dati generali
         *  A Trento sono ripetuti nel recordo di ogni candidato sindaco
         */

        if (!isset($this->jsonObject->int->sz_tot)) {
            $this->jsonObject->int->sz_tot = $candidatoAr['NUMTOTALESEZIONI'];
            $this->jsonObject->int->sz_p_sind = $candidatoAr['NUMTOTALESEZIONI'];
            $this->jsonObject->int->sz_p_cons = $candidatoAr['NUMSEZPERVENUTE'];
            $this->jsonObject->int->sk_bianche = $candidatoAr['DI_CUI_SCHEDEBIANCHE'];
            $this->jsonObject->int->sk_nulle = $candidatoAr['VOTINONVALIDI'];
            $this->jsonObject->int->sk_contestate = 0;

            $percVoti = 0;
            if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
                $percVoti = ($this->jsonObject->int->ele_t/$this->jsonObject->int->vot_t)*100;
            }
            $this->jsonObject->int->perc_vot = $percVoti;
            $this->jsonObject->int->fine_rip = '';
            $this->jsonObject->int->sg_spett = 0;
            $this->jsonObject->int->sg_ass = 0;
            $this->jsonObject->int->tot_vot_cand = 0;
            $this->jsonObject->int->tot_vot_lis = 0;
            $this->jsonObject->int->non_valid = '';
            $this->jsonObject->int->data_prec_elez = '';
            $this->jsonObject->int->reg_sto = REG_STO;
            $this->jsonObject->int->prov_sto = $this->jsonObject->int->cod_prov;
            $this->jsonObject->int->comu_sto = $this->jsonObject->int->cod_com;

        }
    }

    public function setCandidatoTrento($candidatoAr) {
        $this->jsonObject->cand[$this->numeroCandidato]->cogn = $candidatoAr['Cognome']; 
        $this->jsonObject->cand[$this->numeroCandidato]->nome = $candidatoAr['Nome']; 
        $this->jsonObject->cand[$this->numeroCandidato]->a_nome = $candidatoAr['Nome Detto']; 
        $this->jsonObject->cand[$this->numeroCandidato]->pos = $candidatoAr['Progressivo Sindaco']; 
        $this->jsonObject->cand[$this->numeroCandidato]->voti = $candidatoAr['Voti']; 
        $this->jsonObject->cand[$this->numeroCandidato]->id_sindaco = $candidatoAr['Sindaco Id']; 

        $percVoti = 0;
        if ($candidatoAr['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = ($candidatoAr['Voti']/$this->jsonObject->int->vot_t)*100;
        }
        $this->jsonObject->cand[$this->numeroCandidato]->perc = $percVoti; 
        $this->jsonObject->cand[$this->numeroCandidato]->d_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->l_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->eletto = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_ass = 0; 
        $this->jsonObject->cand[$this->numeroCandidato]->sort_coal = null; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_sort_coal = null; 

        /** Duplicato di voti e perc
         */
        $this->jsonObject->cand[$this->numeroCandidato]->tot_vot_lis = $candidatoAr['Voti']; 
        $this->jsonObject->cand[$this->numeroCandidato]->perc_lis = $percVoti; 


        /**
         *  dati generali
         *  A Trento sono ripetuti nel recordo di ogni candidato sindaco
         */

        if (!isset($this->jsonObject->int->sz_tot)) {
            $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_p_sind = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_p_cons = $candidatoAr['Sez.Pervenute'];
            $this->jsonObject->int->sk_bianche = $candidatoAr['Schede Bianche'];
            $this->jsonObject->int->sk_nulle = $candidatoAr['Schede nulle o contenenti solo voti nulli'];
            $this->jsonObject->int->sk_contestate = $candidatoAr['Schede contestate e non attribuite'];

            $percVoti = 0;
            if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
                $percVoti = ($this->jsonObject->int->ele_t/$this->jsonObject->int->vot_t)*100;
            }
            $this->jsonObject->int->perc_vot = $percVoti;
            $this->jsonObject->int->fine_rip = '';
            $this->jsonObject->int->sg_spett = 0;
            $this->jsonObject->int->sg_ass = 0;
            $this->jsonObject->int->tot_vot_cand = 0;
            $this->jsonObject->int->tot_vot_lis = 0;
            $this->jsonObject->int->non_valid = '';
            $this->jsonObject->int->data_prec_elez = '';
            $this->jsonObject->int->reg_sto = REG_STO;
            $this->jsonObject->int->prov_sto = $this->jsonObject->int->cod_prov;
            $this->jsonObject->int->comu_sto = $this->jsonObject->int->cod_com;

        }

    }

}

