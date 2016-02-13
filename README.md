Wer schonmal mit Microsofts C# .NET gearbeitet hat wird sich jedes mal ärgern,
wenn man in einer anderen Sprache feststellt, dass die Lambda Expressions fehlen.

Dieses Projekt ist ein Versuch, genau diese Expressions in PHP umzusetzen.

## Status

Das Projekt befindet sich **noch in der Entwicklung**.

Es kann jederzeit eine Änderung der API geben.

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

Weitere Beispiele in [examples/usage.php](examples/usage.php)

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

