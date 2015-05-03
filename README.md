# Open Data Lecce 2015

### 1	RICOSTRUZIONE DEI DATI
Uno dei principali obiettivi che si pone la raccolta dei dati, e la loro conseguente visualizzazione, è sicuramente la manutenibilità, la facilità nell’essere manipolati ma soprattutto la leggibilità, in modo che ogni cittadino sia in grado di interpretarli ed estrapolarne il significato. 
Il portale dedicati agli Open Data del Comune di Lecce pecca in alcuni di questi aspetti, più specificatamente nella scelta del formato di rappresentazione di alcuni dataset. Per la nostra applicazione avevamo bisogno di informazioni che potessero essere utilizzate dal cittadino per usufruire al meglio delle postazioni di Bike Sharing sparse per la città e delle piste ciclabili nella provincia di Lecce. Tali dati sono presenti sul portale tramite file in formato SHAPE, ottimi per rappresentazioni grafiche ma poco versatili nel caso si vogliano utilizzare gli stessi per manipolarli in ambiti applicativi diversi.
Descriveremo ora il processo di ricostruzione dei dataset, partendo da quelli ottenuti dal portale e da altri siti, per poi modellarli e avere infine nuovi dataset che rispecchiano le caratteristiche di manutenibilità e di riusabilità conformi all’ingegneria del software.

### 1.1	POSTAZIONI DI BIKE SHARING
I file in formato SHAPE, come accennato precedentemente, sono poco propensi all’essere riutilizzati in altri ambiti al di fuori della rappresentazione grafica, inoltre è davvero difficile estrarre le coordinate dalla lista di punti fornita dal dataset. Per questo l’unico rimedio disponibile è stata l’estrazione manuale di ogni coppia di punti (latitudine e longitudine della postazione di bike sharing) e del relativo indirizzo attraverso la rappresentazione su mappa dei punti stessi.
All’indirizzo http://umap.openstreetmap.fr/it/map/stazioni-bike-sharing-comune-di-lecce_24533#15/40.3549/18.1765 è possibile avere un’anteprima delle postazioni sparse per la città, i cui dati si possono estrarre manualmente attraverso questa procedura:
*	Click col tasto destro su una postazione, indicata da una icona con una bici;
*	Click sull’opzione “Indicazioni stradali da qui”;
*	Si aprirà una nuova finestra e l’indirizzo associato conterrà al suo interno, come parametri alla chiamata della pagina web, la locazione della postazione di Bike Sharing e il relativo indirizzo.

Ripetendo questa procedura per tutte le postazioni ci permetterà di ottenere le informazioni a noi necessarie, la cui riorganizzazione prevede la creazione di un file formato JSON, che rappresenta uno standard per quanto riguarda facilità di comprensione, interoperabilità e soprattutto riusabilità in diversi ambiti applicativi (creazione di software, rappresentazione dinamica di dati, ecc…).
Il JSON che creeremo ex novo sarà composto da 11 oggetti, ognuno dei quali sarà composto da 3 campi: address, latitude e longitude. Ogni oggetto della lista rappresenta l’insieme delle informazioni base di una postazione di bike sharing.
Es:
```sh
[
  {
    "address": "Viale Porta d'Europa, Lecce",
    "latitude": "40.36165334640747",
    "longitude": "18.169240951538086"
  },
  …
]
```
### 1.2	PISTE CICLABILI DI LECCE E PROVINCIA
Il dataset delle piste ciclabili offerto dal portale del Comune di Lecce affligge gli stessi problemi di quello relativo alle postazioni di Bike Sharing (in quanto anch’esso in formato SHAPE), quindi anche in questo caso si è presentato il vincolo di costruire un nuovo dataset che sfruttasse al meglio le peculiarità del formato JSON. In questo caso però le difficoltà si sono amplificate in quanto ogni pista ciclabile rappresenta un insieme di punti che, uniti ordinatamente dal primo all’ultimo, permettono di ottenere il tracciato della pista ciclabile. Da come si può intuire, ricostruire manualmente ogni dataset rappresenterebbe un lavoro troppo dispendioso e lungo, per questo si è virato verso nuovi dataset che fossero già semi-elaborati in partenza. La scelta finale è ricaduta sui dati forniti dal portale http://www.piste-ciclabili.com/comune-lecce che contiene tantissime informazioni sugli itinerari di Lecce e dintorni. Per ognuno dei 22 itinerari proposti dal portale, si è seguito il seguente iter:
*	Download del tracciato dell’itinerario in formato CSV, contenete le due colonne longitude e latitude. Es:
```sh
;Longitude,Latitude
18.096885681152,40.343469535337
18.097043931484,40.34555255154
…
```
*	Estrazione manuale delle informazioni dell’itinerario ed inserimento di quest’ultime all’interno di un file JSON apposito per l’itinerario, formato dai seguenti campi:
    * name:  nome dell’itinerario;
    * description: descrizione associata all’itinerario;
    * features: oggetto che riassume le informazioni contenute all’interno della scheda “Caratteristiche” associata alla pagina web di ogni itinerario. Questo oggetto contiene i campi:
        * type: tipo di pista ciclabile
        * distance: lunghezza del tracciato
        * road_surface: tipo di fondo strada dell’itinerario
        * is_suitable_for_children: valore booleano che indica se l’itinerario è adatto per i bambini
        * is_suitable_for_skaters: valore booleano che indica se l’itinerario è adatto per i pattinatori
    * details: oggetto che riassume le informazioni contenute all’interno della scheda “Dettagli” associata alla pagina web di ogni itinerario. Questo oggetto contiene i campi:
        * average_slope: pendenza media del tracciato
        * max_slope: pendenza massima del tracciato
        * track_density: densità del tracciato
        * difference: dislivello del tracciato (quota max-min)
        * ascent_difference: dislivello in salita del tracciato
        * descent:difference: dislivello in discesa del tracciato
        
Es:
    
```sh
{
  "name": "Arnesano - Lecce",
  "description": "Passeggiata tra le campagne, medio livello, con un punto in salita verso l'arrivo a Lecce.",
  "features": {
    "type": "strada",
    "distance": "5.9 km",
    "road_surface": "asfalto",
    "is_suitable_for_children": "no",
    "is_suitable_for_skaters": "no"
  },
  "details": {
    "average_slope": "0.5 %",
    "max_slope": "4 %",
    "track_density": "5.7 punti/km",
    "difference": "31 m",
    "ascent_difference": "44 m",
    "descent_difference": "18 m"
  }
}
```

### 2	INTEGRAZIONE DEL DATASET CON DATI DINAMICI
Nella fase precedente abbiamo analizzato i passi che hanno permesso la ricostruzione dei dati e la serializzazione degli stessi. La scelta di non scaricarli e di non farne il parsing in tempo reale è dettata dalla natura dei dati stessi, in quanto non tendono a cambiare spesso nel tempo.
Dati che invece tendono a cambiare e ad essere aggiornati sono più propensi ad essere scaricati e analizzati a runtime, nel momento in cui se ne intrevede la necessità di utilizzarli. In questa sezione spiegheremo quali sono i dati che abbiamo bisogno di interrogare a runtime e come pensiamo di utilizzarli.

### 2.1	POSTAZIONI DI BIKE SHARING
I dati statici che riguardano le postazioni di bike sharing sono decisamente scarni, in quanto di ogni postazione conosciamo solamente l’indirizzo e le coordinate. Il portale http://bicincitta.tobike.it/frmLeStazioni.aspx?ID=159 fornisce un servizio di monitoraggio in tempo reale delle postazioni di bike sharing, visualizzando informazioni molto utili come il nome, le bici libere, i posti disponibili e l’effettiva operatibilità per ogni postazione.

Per estrarre queste informazioni dalla pagina web c’è bisogno di uno strumento che possa facilitare l’interpretazione del codice HTML e Javascript, e qui ci viene incontro l’applicazione Import.io, che permette il parsing di pagine web e di richiamare il risultato con una semplice query. Dopo diverse prove e alcune problematiche affrontate, il miglior approccio è stato quello di estrarre i dati e gestirli in una tabella formata da due colonne:
* name: campo contenente il nome della postazione ed eventualmente una stringa che indica la sua effettiva operabilità
* value: campo contenente il numero di bici libere e il numero di posti disponibili per postazione

La query effettuata da Import.io permette di ottenere la tabella appena descritta e di restituirla al chiamante attraverso un JSON che ha le seguenti fattezze: 
```sh
{
    "offset": 0,
    "results": [
        {
            "name": "Foro Boario",
            "value": "0 bici libere 11 posti disponibili"
        },
        …
    ],
    "title": "Stazioni",
    "pageUrl": "http://bicincitta.tobike.it/frmLeStazioni.aspx?ID=159",
    "connectorGuid": "74ca0f38-5e47-4242-a52e-19d971952b15"
}
```
### 3	SVILUPPO DEGLI SCRIPT PER UNIRE E RAFFINARE I DATI
Nei punti precedenti abbiamo ricostruito i dataset di base, sia statici che dinamici, che contengono le informazioni che dovranno essere trattate prima di restituirle all’utente finale. In questo punto spiegheremo come realizzare gli script che permetteranno di unire, raffinare e ridefinire nuovi dataset a partire da quelli di partenza ed ottenere, infine, i dataset nella loro forma definitiva.
Gli script sono realizzati in linguaggio PHP, linguaggio orientato ad oggetti che permette di realizzare script conformi agli standard dell’ingegneria del software, e i dataset restituiti saranno sempre in formato JSON, formato di interscambio leggibile e leggero, compatibile con qualunque dispositivo mobile e non presente sul mercato.



