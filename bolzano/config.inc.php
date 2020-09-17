<?php
/**
 *
 * PHP version >= 5.0
 *
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2020,  Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.2
 * @version		0.1
 */

 /**
  *  Root dir relative path
  */
  define('ROOT_DIR', __DIR__);

 /**
  *  Dati scaricati
  */
  define('DOWN_DIR', __DIR__ . '/dati_scaricati');
 
/**
  *  Dati convertiti
*/
  define('CONV_DIR', __DIR__ . '/dati_convertiti');

/**
 *  File lista comuni
 */
define('LISTA_COMUNI','../lista_comuni.json');

/**
 *  File lista comuni
 */
define('LISTA_CANDIDATURE',DOWN_DIR.'/candidature_bolzano.csv');

  /**
 * Prova o esercizio
 */  
define ('STATO','= DATI DI PROVA');
//define ('STATO','= DATI DI ESERCIZIO');
 
/**
 * DATA elezioni. Scrive nel json
 */  

//define('DATA_ELEZIONI',20200920000000); //versione produzione
define ('DATA_ELEZIONI', 30200920); // versione test

/**
 * Costanti per l'upload su dl
 */
define('MAKE_UPLOAD',TRUE);
define('AGGIORNA_ENTI',TRUE);

 define('UPLOAD_URL','http://10.99.36.78:40525');

 //define('DL_PATH','dl/rainews/elezioni2020/PX/scrutiniG/DE/20200920/TE/08'); // versione in produzione
define('DL_PATH','/dl/rainews/elezioni2020/PX/scrutiniG/DE/30200920/TE/08'); //versione di test

define('UPLOAD_ACTION','/action/push');
define('POST_NAME','response.json');

//define('DL_PATH_ENTI','dl/rainews/elezioni2020/PX/getentiG/DE/20200920/TE/08/'); // versione in produzione
define('DL_PATH_ENTI','/dl/rainews/elezioni2020/PX/getentiG/DE/30200920/TE/08'); //versione di test

/**
 * Struttura del path in cui scrivere i json dei dati
 */  
define('PATH_PROV','/PR');
define('PATH_PROV_BOLZANO','/014');
define('PATH_COMUNI','/CM/');

define('FILE_PATH_CONVERTITO',CONV_DIR.PATH_PROV.PATH_PROV_BOLZANO.PATH_COMUNI);

 /**
  *  Remote site Bolzano
  */
//  define('REMOTE_SITE_BOLZANO', 'https://press.elezionicomunali.bz.it/'); // versione in produzione
define('REMOTE_SITE_BOLZANO', 'https://test-press.elezionicomunali.bz.it/'); // versione test

/**
 * Costanti Bolzano
 */

  define('DESC_PROV','BOLZANO');
  define('COD_PROV','014');
  define('REG_STO',4);
define('PROV_ISTAT','021');
define('DIR_LOG','../Logger/logs');

/**
 * configurazione LOG php
 */
error_reporting(E_ALL & ~E_NOTICE);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__."/Logger/logs/php-error.log");
