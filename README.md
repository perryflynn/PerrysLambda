Wer schonmal mit Microsofts C# .NET gearbeitet hat wird sich jedes mal ärgern,
wenn man in einer anderen Sprache feststellt, dass die Lambda Expressions fehlen.

Dieses Projekt ist ein Versuch, genau diese Expressions in PHP umzusetzen.

![Travis-CI](https://travis-ci.org/perryflynn/PerrysLambda.svg)

## Status

Das Projekt befindet sich **noch in der Entwicklung**.

Es kann jederzeit eine Änderung der API geben.

## Composer

[Packagegist](https://packagist.org/packages/perryflynn/perrys-lambda)

```
composer require perryflynn/perrys-lambda:dev-master
```

## Klassen

- `Property` ist die Basisklasse welche genau einen Wert speichert und diesen
  validieren kann.
- `ArrayList` ermöglicht das Speichern von Arrays und stellt die
  Lambda Funktionen bereit.
- `ObjectArray` besitzt die gleichen Funktionen wie `ArrayList`. Zusätzlich kann
  auf die einzelnen Felder mit Objektzeigern zugegriffen werden.
- `ScalarProperty` stellt für Scalare Datentypen verschiedene Funktionen bereit.
  Zum Beispiel substring, length, replace, u.v.m.
- `Sortable` ermöglicht das Sortieren von Daten nach mehreren Bedingungen.

## Beispiel

```php
// Alle Zahlen größer 5
$test = new \PerrysLambda\ArrayList(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
var_dump($test->where(function($v) { return $v>5; })->toArray());
```

## Komplexeres Beispiel

```php
use PerrysLambda\ObjectArray as OA;
use PerrysLambda\ArrayList as AL;

$data = json_decode(file_get_contents(__DIR__."/testdata.json"), true);
$collection = AL::asObjectArray($data);

$skiptake = $collection
    // alle welche im useragent den Begriff "Android" enthalten
    ->where(function(OA $r) { return $r->agentScalar->contains('Android'); })
    // doppelte Einträge anhand des useragent entfernen
    ->distinct(function(OA $r) { return $r->agent; })
    // die ersten drei Einträge in der Liste löschen
    ->skip(3)
    // die ersten 5 Einträge nehmen, den rest löschen
    ->take(5)
    // sortieren nach "enthält den Begriff Linux"
    // agentScalar ist ein shortcut für `->getScalar('agent')`
    ->order(function(OA $r) { return $r->agentScalar->contains('Linux') ? 1 : 0; })
    // anschließend nach dem useragent sortieren
    ->thenBy(function(OA $r) { return $r->agent; })
    // sortieren ausführen und ArrayList zurück geben
    ->toList();

$skiptake->each(function(OA $r) { var_dump($r->agent); });
```

## Validieren und Konvertieren

Werte in Datenfeldern können konvertiert und validiert werden:

```php
use PerrysLambda\ArrayList as AL;
use PerrysLambda\ObjectArray as OA;
use PerrysLambda\Converter\CallableConverter as CC;
use PerrysLambda\Validator\PresenceValidator as PV;
use PerrysLambda\Validator\CallableValidator as CV;

// Angepasster Typ für die Unterelemente
class AccessLog extends OA
{
    public function __construct(array $data = null, $fieldtype = null, $convertfield = true)
    {
        parent::__construct($data, $fieldtype, $convertfield);

        // Timestamp string in ein DateTime Objekt konvertieren
        $this->setFieldConverter('timestamp', new CC(function($in, OA $r)
        {
            // 01/Feb/2016:07:06:16 +0100
            return \DateTime::createFromFormat('d/M/Y:H:i:s O', $in);
        }));

        // HTTP Methode aus der URI in separates Feld extrahieren
        $this->setFieldConverter('method', new CC(function($in, OA $r)
        {
            // GET /ajax/unseen-notices-count/?_=1454313293675 HTTP/1.1
            return substr($r->uri, 0, strpos($r->uri, ' '));
        }));

        // timestamp darf nicht leer sein
        $this->addFieldValidator('timestamp', new PV('Feld ist leer'));

        // Nur bestimmte HTTP Methoden erlauben
        $this->addFieldValidator('method', new CV("Ist keine erwartete http method", function($n, $v, OA $r)
        {
            return $v=="POST" || $v=="GET" || $v=="PUT";
        }));
    }
}

// Parse JSON
$data = json_decode(file_get_contents(__DIR__."/testdata.json"), true);
$collection = AL::asType('AccessLog', $data);

// Alle Datensätze wo Timestamp Sekunde = 3
// Anschließend die Datensätze dumpen

$collection
    ->where(function(OA $r) { return $r->timestamp->format('s')==3; })
    ->each(function(OA $r) { var_dump([ $r->timestamp->format('Y-m-d H:i:s'), $r->method, $r->version ]); });

// Ausgabe ob der erste Datensatz gültig ist
// Leeres Array = Alles korrekt
// Ansonsten Liste mit Fehlermeldungen
var_dump($collection[0]->isValid());
```

## Selbst in der php-cli ausprobieren

Einfach dieses Repository mit `git clone` auf den lokalen PC laden
und die Beispiel Dateien mit `php -f example/usage.php` ausführen.

- [examples/usage.php](examples/usage.php) Grundfunktionen
- [examples/converter.php](examples/converter.php) Datenfelder konvertieren
- [examples/validator.php](examples/validator.php) Feldinhalte validieren

## Versuch der Typensicherheit

Die `ArrayList` ermöglicht das Sicherstellen der Typensicherheit der einzelnen
Items in der Liste. Im Konstruktor kann der erwartete Datentyp mitgegeben werden.
Auch die **automatische Umwandlung** in den erwarteten Datentyp ist möglich.

Siehe dafür folgende Methoden:

- `ArrayList::__construct`
- `ArrayList::convertData`
- `ArrayList::convertDataField`

Sofern der Zieldatentyp den Wert des Datenfeldes einfach **als ersten Parameter
des Konstuktors** erwartet, müssen diese Methoden nicht überschrieben werden.

Findet keine automatische Umwandlung statt, wird bei jedem unerwarteten
Datentyp eine Exception ausgelöst.

Für alles andere ist eine eigene Implementierung notwendig.

## Feedback

Speziell bei der Performance und dem Speicherverbrauch kann sicherlich noch
optimiert werden. Hierzu bitte ich um Codebeiträge und Feedback. :-)

