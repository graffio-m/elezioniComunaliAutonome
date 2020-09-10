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
     public static function csv_to_array($filename='', $log, $delimiter=',')
    {
        $specificheLog[0] = $filename;
        
        if(!file_exists($filename) || !is_readable($filename)) {
            $log->logFatal('Impossibile recuperare il file: '. $filename);
//            Logger::fatal("Impossibile recuperare il file:", $specificheLog);
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
        return $data;
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

    public static function save_object_to_json($jsonObject,$file2write,$log) {

        //encode and output jsonObject
        $specificheLog[0] = $file2write;
        $specificheLog[1] = 'Comune ' . $jsonObject->int->desc_com;

        $path_prov = PATH_PROV;
        $path_Prov_specifico = $jsonObject->int->cod_prov;
        $path_comune = PATH_COMUNI;
        $path_comune_specifico = $jsonObject->int->cod_ISTAT;

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
                $this->jsonObject->int->desc_com = $nomeComune;
                $this->jsonObject->int->cod_com = $nomeComune;
                $this->jsonObject->int->desc_prov = $dataAffluenzaAR['desc_prov'];
                $this->jsonObject->int->cod_prov = PATH_PROV_TRENTO;
                $this->jsonObject->int->cod_ISTAT = $dataAffluenzaAR['Istat Comune'];
                $this->jsonObject->int->ele_m = $dataAffluenzaAR['ElettoriM'];
                $this->jsonObject->int->ele_f = $dataAffluenzaAR['ElettoriF'];
                $this->jsonObject->int->ele_t = $dataAffluenzaAR['ElettoriT'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaAR['VotantiF'];
                $this->jsonObject->int->vot_f = $dataAffluenzaAR['VotantiF'];
                $this->jsonObject->int->vot_t = $dataAffluenzaAR['VotantiT'];

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
                    $this->numeroLista++;
    
                }


                /**
                 * 
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->seggi = 0; 
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->sort_lis = 0; 
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->img_lis = '';                
                 */


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
                /**
                 * 
                $this->jsonObject->cand[$this->numeroCandidato]->d_nasc = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->l_nasc = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->eletto = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->tot_vot_lis = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->perc_lis = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->sg_ass = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->sort_coal = ''; 
                $this->jsonObject->cand[$this->numeroCandidato]->sg_sort_coal = ''; 
                 */


                /**
                 *  dati generali
                 *  A Trento sono ripetuti nel recordo di ogni candidato sindaco
                 */

                if (!isset($this->jsonObject->sz_tot)) {
                    $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
                    $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
                    $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
                    $this->jsonObject->int->sz_p_sind = $candidatoAr['Sez.Totali'];
                    $this->jsonObject->int->sz_p_cons = $candidatoAr['Sez.Pervenute'];
                    $this->jsonObject->int->sk_bianche = $candidatoAr['Schede Bianche'];
                    $this->jsonObject->int->sk_nulle = $candidatoAr['Schede nulle o contenenti solo voti nulli'];
                    $this->jsonObject->int->sk_contestate = $candidatoAr['Schede contestate e non attribuite'];

                }
            break;

        }

    }

}

