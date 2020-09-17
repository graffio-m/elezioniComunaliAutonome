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

include_once '../Logger/KLogger02.php';

//$log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
$log = KLogger::instance(DIR_LOG, KLogger::DEBUG);

/**
 * Esempi d'uso del logger
 * 
$log->logInfo('Info Test');
$log->logNotice('Notice Test');
$log->logWarn('Warn Test');
$log->logError('Error Test');
$log->logFatal('Fatal Test');
$log->logAlert('Alert Test');
$log->logCrit('Crit test');
$log->logEmerg('Emerg Test');
*/


//include_once '../Logger/Logger53.php';
include_once '../utility.inc.php';
include_once '../oggetti.inc.php';

/**
 * Lettura Lista comuni
 */
$fileDaRecuperare = LISTA_COMUNI;
$dataListaComuniHA = FileManagement::json_to_array($fileDaRecuperare,$log);
if (!$dataListaComuniHA) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileDaRecuperare);
	die();
}
$desc_prov = 'TRENTO';
$cod_prov = COD_PROV;


/**
 * Inizializzazione file da scrivere
 */
//$file2write_part = CONV_DIR;
$file2write_part = FILE_PATH_CONVERTITO;

/**
 * Lettura voti sindaco da file locale

$fileNameVotiSindaco = DOWN_DIR .'/'.'VotiSindaci.txt';
$dataVotiSindacoAr = FileManagement::csv_to_array($fileNameVotiSindaco,$log,';');
var_dump($dataVotiSindacoAr);
 */

/**
 * Lettura voti sindaco da file remoto
 */
$fileDaRecuperare = REMOTE_SITE_TRENTO.'/'.'VotiSindaci.txt';
$dataVotiSindacoAr = FileManagement::getFileFromRemote($fileDaRecuperare,$log);
$dataVotiSindacoAr = FileManagement::csv_to_array($fileDaRecuperare,$log,';',false);
$specificaLog[] = $fileDaRecuperare;
if (!$dataVotiSindacoAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileDaRecuperare);
//	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}

/**
 * lettura Affluenza.
 * lettura da filesystem
 * Essendo iniziato lo scrutinio si può prendere l'ultimo aggiornamento dell'affluenza

$fileNameAffluenza = DOWN_DIR.'/'.'Affluenza15-del-21-09.txt';
$specificaLog[0] = $fileNameAffluenza;
$dataAffluenzaAr = FileManagement::csv_to_array($fileNameAffluenza,$log,';');

if (!$dataAffluenzaAr) {
	$log->logError('Impossibile proseguire. Impossibile recuperare il file'. $fileNameAffluenza);
	//Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}
 */

/**
 * lettura Affluenza da remoto.
 * Essendo iniziato lo scrutinio si può prendere l'ultimo aggiornamento dell'affluenza
 */

$fileNameAffluenza = REMOTE_SITE_TRENTO.'/'.'Affluenza15-del-21-09.txt';
$specificaLog[0] = $fileNameAffluenza;
$dataAffluenzaAr = FileManagement::getFileFromRemote($fileNameAffluenza,$log);
if (!$dataAffluenzaAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameAffluenza);
//	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}


/**
 * trasformazione in array associativo Affluenza.
 * si accede ai dati dell'affluenza del comune tramite indice Codice Istat 
 */
foreach ($dataAffluenzaAr as $comuneAffluenza) {
		$CodIstatComune = $comuneAffluenza['Istat Comune'];
		$comuneAffluenza['cod_prov'] = $cod_prov;
		$comuneAffluenza['cod_com'] = substr($dataListaComuniHA[$CodIstatComune]['CODICE ELETTORALE'],-4);
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
$dataVotiListeAr = FileManagement::getFileFromRemote($fileNameVotiListe,$log,';');
$specificaLog[0] = $fileNameVotiListe;
if (!$dataVotiListeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameVotiListe);
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

$objectEnte = new enti();
$tot_com = 0;

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
			$cod_com = $objectComune->jsonObject->int->cod_com;
			$file2write = $file2write_part.$cod_com.'/response.json';
//			$file2write = $file2write_part.$comuneInCorso.'response.json';
			FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

			//Upload file to dl
			if (MAKE_UPLOAD) {
				FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
			}
			echo $tot_com . ': '.$objectComune->jsonObject->int->cod_com.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . '<br>';

			//Aggiunge comune a Ente
			$objectEnte->setComune($objectComune->jsonObject);

			// distrugge oggetto
			unset($objectComune);
		}
		$comuneInCorso = $singleDataVotiSindacoAr['Istat Comune'];
		// crea oggetto
		$objectComune = new scrutinio($dataAffluenzaHA[$comuneInCorso]);
		$tot_com++;

		// Aggiungi candidato
		$objectComune->setCandidato($singleDataVotiSindacoAr);

		// Aggiunge voti di lista per ogni candidato
		$objectComune->setVotiListeCandidato($dataVotiListeHA);

	}
}
/* Scrive ultimo comune
*/
if (isset($objectComune)) { //->jsonObject->desc_com)) {
	// scrive file
	$cod_com = $objectComune->jsonObject->int->cod_com;
	$file2write = $file2write_part.$cod_com.'/response.json';
//			$file2write = $file2write_part.$comuneInCorso.'response.json';
	FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

	//Upload file to dl
	if (MAKE_UPLOAD) {
		FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
	}
	echo $tot_com . ': '.$objectComune->jsonObject->int->cod_com.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . '<br>';

	//Aggiunge comune a Ente
	$objectEnte->setComune($objectComune->jsonObject);

	// distrugge oggetto
	unset($objectComune);
}


/**
 * Scrive il file Enti
 */
if (AGGIORNA_ENTI) {
	$file2write = FILE_PATH_CONVERTITO.'responseTrento.json';
	FileManagement::save_object_to_json($objectEnte->jsonObject,$file2write,$log); 
	
	//Upload file to dl
	if (MAKE_UPLOAD) {
		FileManagement::upload_generic_to_dl($file2write, $log, $upload_path=DL_PATH_ENTI, $url=UPLOAD_URL);
	}
	
}

echo "<h2>Conversione della provincia di Trento terminata con successo</h2>";