Elezioni comunali 2020: Conversione dati scrutini comunali nel formato del Ministero Interni 
==========

Script per la lettura dei dati degli scrutini provenienti dalle regioni autonome e conversione in formato json con tracciato unifoirmato a quello del ministero.


Struttura delle directory e dei file
--------------
**Nella directory principale sono presenti**:
1. utility.inc.php. Contiene:
   classe Filemagener per recuperare, convertire i dati e scrivere i file contenenti i dati convertiti 
   oggetto scrutinio che contiene i metodi per manipolare i dati e un oggetto dati che è quello che viene salvato nel file json 
2. la directory Logger. Contiene sia gli script contenenti i metodi per gestire il log che  la directory logs dove sono salvati i file log 
3. la directory relativa alla regione (o provincia autonoma). Al momento trento

**Ogni regione ha una propria directory** (al momento trento) in cui sono presenti:
1. config.php è il file di configurazione. contiene: 
   URL o path per recuperare i file degli scrutini
   il path dove salvare i file json convertiti
2. lo script specifico per il recupero e la conversione dei dati (es.: trento_convert_csv.php)
3. la directory dati_convertiti
4. la directory dati_scaricati (si intende scaricati dalla regione. Non usato nel caso di trento, che recupera i dati direttamente dal sito della provincia)   



Requisiti
--------------
- php ver. 5.6> php 7.2 preferibile
- json extension (default con php 7)
- php installato per essere eseguito da linea di comando (CLI)


Istruzioni per l'installazione 
----------
1. copiare i files e le directory 
2. dare i permessi in scrittura all'utente che manderà in esecuzione lo script alle seguenti directory:
   1.  Logger/logs
   2.  trento/dati_convertiti
   3.  trento/dati_scaricati


Esecuzione
--------------
Gli script possono essere eseguiti sia da linea di comando che da browser web. 

**Per eseguire da linea di comando**: 
1. posizionarsi nella directory della regione di cui si vogliono recuperare e convertire i files
   es.: **cd regioni_autonome/trento**
2. eseguire lo script relativo alla regione
   es.: **php trento_convert_csv.php**
3. i file convertiti si troveranno nella directory dati_convertiti
   es.: regioni_autonome/trento/dati_convertiti 
   Ogni comune avrà un proprio file identificato dal codice istat e l'estensione json
   es.: 022241.json
   La versione precedente del file verrà copiata in  022241.jsonold.json
4. controllare nella directory regioni_autonome/Logger/logs il risultato dell'elaborazione



Note
--------------
I log di sistema di php vengono ridirezionati nel file Logger/logs/php-error.php (configurato in config.php)
 
