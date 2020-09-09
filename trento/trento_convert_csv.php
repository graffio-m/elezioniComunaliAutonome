<?php
/**
 *
 * PHP version >= 5.0
 *
 * @author		Maurizio Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2020
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version		0.1
 * 
 * @abstract    Trento data conversion
 * 				csv --> json
 * 
 *    			Affluenza 19.00 del 20-09
 *    			Affluenza 23.00 del 20-09
 *    			Affluenza 15.00 finale del 21-09
 *    			Voti ai candidati Sindaco
 *    			Voti alle liste
 *    			Voti di preferenza
 */

include_once 'config.inc.php';
include_once '../Logger/Logger.php';
include_once '../utility.inc.php';

$desc_prov = 'TRENTO';
$cod_prov = 0;


/**
 * Inizializzazione file da scrivere
 */
$file2write_part = CONV_DIR;

/**
 * Lettura voti sindaco da file locale

$fileNameVotiSindaco = DOWN_DIR .'/'.'VotiSindaci.txt';
$dataVotiSindacoAr = FileManagement::csv_to_array($fileNameVotiSindaco,';');
var_dump($dataVotiSindacoAr);
 */

/**
 * Lettura voti sindaco da file remoto
 */
$fileDaRecuperare = REMOTE_SITE_TRENTO.'/'.'VotiSindaci.txt';
$dataVotiSindacoAr = FileManagement::getFileFromRemote($fileDaRecuperare);
$specificaLog[] = $fileDaRecuperare;
if (!$dataVotiSindacoAr) {
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}

/**
 * lettura Affluenza.
 * lettura da filesystem
 * Essendo iniziato lo scrutinio si può prendere l'ultimo aggiornamento dell'affluenza

$fileNameAffluenza = DOWN_DIR.'/'.'Affluenza15-del-21-09.txt';
$specificaLog[0] = $fileNameAffluenza;
$dataAffluenzaAr = FileManagement::csv_to_array($fileNameAffluenza,';');

if (!$dataAffluenzaAr) {
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}
 */

/**
 * lettura Affluenza da remoto.
 * Essendo iniziato lo scrutinio si può prendere l'ultimo aggiornamento dell'affluenza
 */

$fileNameAffluenza = REMOTE_SITE_TRENTO.'/'.'Affluenza15-del-21-09.txt';
$specificaLog[0] = $fileNameAffluenza;
$dataAffluenzaAr = FileManagement::getFileFromRemote($fileNameAffluenza,';');
if (!$dataAffluenzaAr) {
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}


/**
 * trasformazione in array associativo Affluenza.
 * si accede ai dati dell'affluenza del comune tramite indice Codice Istat 
 */
foreach ($dataAffluenzaAr as $comuneAffluenza) {
		$CodIstatComune = $comuneAffluenza['Istat Comune'];
		$comuneAffluenza['cod_prov'] = $cod_prov;
		$comuneAffluenza['desc_prov'] = $desc_prov;
		$dataAffluenzaHA[$CodIstatComune] = $comuneAffluenza;
}

/**
 * Lettura voti Liste
$fileNameVotiListe = DOWN_DIR.'/'.'VotiListe.txt';
$dataVotiListeAr = FileManagement::csv_to_array($fileNameVotiListe,';');
$specificaLog[] = $fileNameVotiListe;
if (!$dataVotiListeAr) {
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}
 */

/**
 * Lettura voti Liste
 * Lettura da remoto
 */
$fileNameVotiListe = REMOTE_SITE_TRENTO.'/'.'VotiListe.txt';
$dataVotiListeAr = FileManagement::getFileFromRemote($fileNameVotiListe,';');
$specificaLog[0] = $fileNameVotiListe;
if (!$dataVotiListeAr) {
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}


/**
 * trasformazione in array associativo VotiListe.
 * si accede ai dati dei vori delle liste tramite indice ID Sindaco 
 */
$SindacoId = 0;
foreach ($dataVotiListeAr as $dataVotiSingolaLista) {
	if ($SindacoId <> $dataVotiSingolaLista['Sindaco Id'] ) {
		$SindacoId = $dataVotiSingolaLista['Sindaco Id'];
	}
	$dataVotiListeHA[$SindacoId][] = $dataVotiSingolaLista;
}

/**
 * Creazione oggetto x json
 * modello Ministero dell'Interno
 * scrutinio_comunali_1t.json
 */

$comuneInCorso = '';

/**
 * Cicla Voti Sindaco
 * crea nuovo oggetto per ogni comune
 * Imposta dati generali (parte in new scrutinio, parte in setCandidato. Alcuni dati generali sono nel file dei voti del sindaco)
 * Imposta Voti lista per ogni sindaco in setVotiListeCandidato
 */

foreach ($dataVotiSindacoAr as $singleDataVotiSindacoAr) {
	if ($singleDataVotiSindacoAr['Istat Comune'] == $comuneInCorso) { //ricordarsi di controllare variabile più sicura
		$objectComune->numeroCandidato = $objectComune->numeroCandidato + 1;
		$objectComune->setCandidato($singleDataVotiSindacoAr);
		// Aggiunge voti di lista per ogni candidato
		$objectComune->setVotiListeCandidato($dataVotiListeHA);
	} else {
		if (isset($objectComune)) { //->jsonObject->desc_com)) {
			// scrive file
			$file2write = $file2write_part.'/'.$comuneInCorso.'.json';
			FileManagement::save_object_to_json($objectComune->jsonObject,$file2write); 
			// distrugge oggetto
			unset($objectComune);
		}
		$comuneInCorso = $singleDataVotiSindacoAr['Istat Comune'];
		// crea oggetto
		$objectComune = new scrutinio($dataAffluenzaHA[$comuneInCorso]);

		// Aggiungi candidato
		$objectComune->setCandidato($singleDataVotiSindacoAr);

		// Aggiunge voti di lista per ogni candidato
		$objectComune->setVotiListeCandidato($dataVotiListeHA);

	}
}
