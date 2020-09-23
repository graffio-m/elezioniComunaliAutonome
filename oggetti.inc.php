<?php

/**
 * @abstract Classe scrutinio. Gestisce l'oggetto relativo dello scrutinio di ogni comune
 */

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

                $nomeComune = $dataAffluenzaAR['DESCRIZIONEISTAT_I'].'/'.$dataAffluenzaAR['DESCRIZIONEISTAT_D'].'/'.$dataAffluenzaAR['DESCRIZIONEISTAT_L'];
                $this->jsonObject->int->desc_com = strtoupper($nomeComune);
                $this->jsonObject->int->cod_com = $dataAffluenzaAR['cod_com'];;
                $this->jsonObject->int->desc_prov = $dataAffluenzaAR['desc_prov'];
                $this->jsonObject->int->cod_prov = COD_PROV; 
                $this->jsonObject->int->cod_ISTAT = $dataAffluenzaAR['cod_ISTAT'];
                $this->jsonObject->int->cod_comune_originale = $dataAffluenzaAR['COMUNEISTAT'];

                $this->jsonObject->int->ele_m = $dataAffluenzaAR['ELETTORIMASCHI'];
                $this->jsonObject->int->ele_f = $dataAffluenzaAR['ELETTORIFEMMINE'];
                $this->jsonObject->int->ele_t = $dataAffluenzaAR['TOTALEELETTORI'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaAR['VOTANTIMASCHI'];
                $this->jsonObject->int->vot_f = $dataAffluenzaAR['VOTANTIFEMMINE'];
                $this->jsonObject->int->vot_t = $dataAffluenzaAR['TOTALEVOTANTI'];

                $percVoti = 0;
                if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
                    $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
                }
                $this->jsonObject->int->perc_vot = $percVoti;
    
                $this->jsonObject->int->dt_agg = date("YmdHis");
            break;

        }
        $this->jsonObject->cand = array();

    }

    public function __destruct() {
        unset($this->jsonObject);

        
    }    
    /**
     * @abstract Imposta i dati delle liste collegate al candidato sindaco. chiama le funzioni di impostazioni specifiche di ogni regione/provincia
     *
     * @param array $dataVotiListeHA
     * @return void
     */
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
    /**
     * @abstract Imposta i dati del candidato sindaco. chiama le funzioni di impostazioni specifiche di ogni regione/provincia
     *
     * @param array $candidatoAr
     * @return void
     */
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
     * Imposta i dati delle liste collegate al candidato Sindaco
     * Trento
     *
     * @param array $dataVotiListeHA
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
                $percVotiLista = round((($singolaLista['Voti']/$this->jsonObject->int->vot_t)*100),2);
            }
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->perc = $percVotiLista; 

            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->img_lis = '';                
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->seggi = 0; 
            $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->sort_lis = 0; 


            $this->numeroLista++;

        }

    } 

    /**
     * Imposta i dati delle liste collegate al candidato Sindaco
     * Bolzano
     *
     * @param array $dataVotiListeHA indice: codice comune (versione della provincia di Bolzano) + ordine candidatura ($idSindaco) 
     * @return void
     */
    public function setVotiListeCandidatoBolzano($dataVotiListeHA) {
        $idSindaco = $this->jsonObject->cand[$this->numeroCandidato]->id_sindaco;
        $codComTmp = $this->jsonObject->int->cod_comune_originale;
        /**
         * Ciclare $dataVotiListeHA[$idSindaco]
         */
//        if (array_key_exists())
        $nomeCandidato = $this->jsonObject->cand[$this->numeroCandidato]->cogn;
        foreach ($dataVotiListeHA[$codComTmp] as $singolaLista) {

            if ($nomeCandidato == $singolaLista['NOMINATIVO']) {
                if (!array_key_exists($this->numeroLista, $this->jsonObject->cand[$this->numeroCandidato]->liste)) {
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista] = new stdClass();
                }
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->descr_lista = $singolaLista['DESCRIZIONELISTA']; 
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->voti = $singolaLista['VOTILISTA']; 
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->pos = $singolaLista['ORDINELISTA']; 

                $percVotiLista = 0;
                if ($singolaLista['VOTILISTA'] > 0 && $singolaLista['VOTIVALIDIDILISTE'] > 0) {
                    $percVotiLista = round((($singolaLista['VOTILISTA']/$singolaLista['VOTIVALIDIDILISTE'])*100),2);
                }
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->perc = $percVotiLista; 

                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->img_lis = '';                
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->seggi = 0; 
                $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->sort_lis = 0; 
                $this->numeroLista++;
            }
        }

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
        $votiValidi = $this->jsonObject->int->vot_t - ($candidatoAr['DI_CUI_SCHEDEBIANCHE'] + $candidatoAr['VOTINONVALIDI']);
        if ($candidatoAr['VOTI_SINDACO'] > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = round((($candidatoAr['VOTI_SINDACO']/$votiValidi)*100),2);
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

        ;

        $percVoti = 0;
        $votiValidi = $this->jsonObject->int->vot_t - ($candidatoAr['Schede Bianche'] + $candidatoAr['Schede nulle o contenenti solo voti nulli'] + $candidatoAr['Schede contestate e non attribuite']);

        if ($candidatoAr['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = round((($candidatoAr['Voti']/$votiValidi)*100),2);
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
            $this->jsonObject->int->sz_p_sind = $candidatoAr['Sez.Pervenute'];
            $this->jsonObject->int->sz_p_cons = $candidatoAr['Sez.Pervenute'];
            $this->jsonObject->int->sk_bianche = $candidatoAr['Schede Bianche'];
            $this->jsonObject->int->sk_nulle = $candidatoAr['Schede nulle o contenenti solo voti nulli'];
            $this->jsonObject->int->sk_contestate = $candidatoAr['Schede contestate e non attribuite'];

            $percVoti = 0;
            if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
                $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
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

/**
 * @abstract Classe scrutinio. Gestisce l'oggetto relativo dello scrutinio di ogni comune
 */

class enti {

    public $jsonObject;
    public $numeroEnte = 0;

    public function __construct() {

        $this->jsonObject = new stdClass();
        $this->jsonObject->int = new stdClass();
        $this->jsonObject->int->area = 'I';
        $this->jsonObject->int->file = 'GEOPOLITICA COMUNALI';
        $this->jsonObject->int->t_ele = 'Comunali';
        $this->jsonObject->int->dt_ele = DATA_ELEZIONI;

        $this->jsonObject->enti = array();
//        $this->jsonObject->enti = new stdClass();


        switch (DESC_PROV) {
            case 'TRENTO':
                if (!array_key_exists(0, $this->jsonObject->enti)) {
                    $this->jsonObject->enti[0] = new stdClass();
                }
                    //if (!array_key_exists($this->numeroCandidato, $this->jsonObject->cand)) {
                    $this->jsonObject->enti[0]->desc = 'TRENTINO ALTO ADIGE';
                    $this->jsonObject->enti[0]->cod = '040000000';
                    $this->jsonObject->enti[0]->tipo = 'RE';
                    $this->jsonObject->enti[0]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[0]->tipo_comune = null;

                    $this->jsonObject->enti[1] = new stdClass();
                    $this->jsonObject->enti[1]->desc = 'PROVINCIA AUTONOMA TRENTO';
                    $this->jsonObject->enti[1]->cod = '040830000';
                    $this->jsonObject->enti[1]->tipo = 'PR';
                    $this->jsonObject->enti[1]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[1]->tipo_comune = null;

                    $this->numeroEnte = 2;
                break;    

            case 'BOLZANO':
                if (!array_key_exists(0, $this->jsonObject->enti)) {
                    $this->jsonObject->enti[0] = new stdClass();
                }
                    //if (!array_key_exists($this->numeroCandidato, $this->jsonObject->cand)) {
                    $this->jsonObject->enti[0]->desc = 'TRENTINO ALTO ADIGE';
                    $this->jsonObject->enti[0]->cod = '040000000';
                    $this->jsonObject->enti[0]->tipo = 'RE';
                    $this->jsonObject->enti[0]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[0]->tipo_comune = null;

                    $this->jsonObject->enti[1] = new stdClass();
                    $this->jsonObject->enti[1]->desc = 'PROVINCIA AUTONOMA BOLZANO';
                    $this->jsonObject->enti[1]->cod = '040140000';
                    $this->jsonObject->enti[1]->tipo = 'PR';
                    $this->jsonObject->enti[1]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[1]->tipo_comune = null;

                    $this->numeroEnte = 2;
                break;
            }
        }

    public function setComune($objectComune) {
            if (!array_key_exists($this->numeroEnte,$this->jsonObject->enti)) {
                $this->jsonObject->enti[$this->numeroEnte] = new stdClass();
                $this->jsonObject->enti[$this->numeroEnte]->desc = $objectComune->int->desc_com;
                $this->jsonObject->enti[$this->numeroEnte]->cod = '04'.$objectComune->int->cod_prov.$objectComune->int->cod_com;
                $this->jsonObject->enti[$this->numeroEnte]->tipo = 'CM';
                $this->jsonObject->enti[$this->numeroEnte]->dt_agg = date("YmdHis");
                $this->jsonObject->enti[$this->numeroEnte]->tipo_comune = 'N';
                $this->numeroEnte++;


            }
        }


}