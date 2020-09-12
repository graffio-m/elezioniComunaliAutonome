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
 * Prova o esercizio
 */  
define ('STATO','= DATI DI PROVA');
//define ('STATO','= DATI DI ESERCIZIO');
 
/**
 * DATA elezioni. Scrive nel json
 */  

//define('DATA_ELEZIONI',20200920000000); //versione produzione
define ('DATA_ELEZIONI', 30200920); // versione test


define('UPLOAD_URL','http://10.99.36.78:40525');
//define('DL_PATH','dl/rainews/elezioni2020/PX/scrutiniG/DE/20200920/TE/08'); // versione in produzione
define('DL_PATH','/dl/rainews/elezioni2020/PX/scrutiniG/DE/30200920/TE/08'); //versione di test
define('UPLOAD_ACTION','/action/push');

/**
 * Struttura del path in cui scrivere i json dei dati
 */  
define('PATH_PROV','/PR');
define('PATH_PROV_TRENTO','/083');
define('PATH_COMUNI','/CM/');

define('FILE_PATH_CONVERTITO',CONV_DIR.PATH_PROV.PATH_PROV_TRENTO.PATH_COMUNI);

 /**
  *  Remote site Trento
  */
  define('REMOTE_SITE_TRENTO', 'http://media.2020.elezionicomunali.tn.it');

/**
 * Costanti Trento
 */

  define('DESC_PROV','TRENTO');
  define('COD_PROV','083');


define('DIR_LOG','../Logger/logs');

/**
 * configurazione LOG php
 */
error_reporting(E_ALL & ~E_NOTICE);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__."/Logger/logs/php-error.log");
