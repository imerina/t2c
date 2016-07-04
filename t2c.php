<?php

/**
 * Génère une classe métier à partir du contenu d'une table MySQL
 * Sous Windows, la ligne commande ressemble à ça
 * php.exe -f t2c.php -- -b base -ttable 
 */
/**
 * Vérification des paramètres 
 * -b base de données
 * -t table
 */
$options = getopt("b:t:");  // Récupère les paramètres dans la ligne de commande
//var_dump($options) ; //test
$base = isset($options['b']) ? trim($options['b']) : '';
$table = isset($options['t']) ? trim($options['t']) : '*';
// @todo remplacer ces valeurs en dur
$host = 'localhost';
$user = "root";
$password = "";


/* echo $base.PHP_EOL; //test
  echo $table.PHP_EOL; //test
  var_dump($isCamelCase); //test
 */

/**
 * Connexion à la base de données
 */
try {
  $dsn = 'mysql:host=' . $host . ';dbname=' . $base;
  $dbh = new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  e("Impossible de se connecter à la base '" . $base . "'" . PHP_EOL . $e->getMessage());
  exit(99);
}
/**
 * Liste des tables 
 */
if ($table == '*') {
  try {
    // Récupère les tables de la base
    $sql = "show tables";
    $sth = $dbh->query($sql);
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    e("Impossible de lire le contenu de la table '" . $table . "'" . PHP_EOL . $e->getMessage());
    exit(99);
  }
  foreach ($rows as $row) {
    $tables[] = $row['Tables_in_wikipizza'];
  }
} else {
  $tables[] = $table;
}


foreach ($tables as $table) {
  /**
   * Lecture du contenu de la table MySQL demandée
   */
  try {
    // Récupère les colonnes de la table
    $sql = "show full columns from $table";
    $sth = $dbh->query($sql);
    $colonnes = $sth->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    e("Impossible de lire le contenu de la table '" . $table . "'" . PHP_EOL . $e->getMessage());
    exit(99);
  }
  /**
   * Génération de la classe métier
   */
  $date = new DateTime();
  $horodatage = $date->format("d/m/Y à H:i:s");
// Entête de classe
  $data = '<?php' . PHP_EOL;
  $data .= '/**' . PHP_EOL;
  $data .= ' * Classe ' . ucfirst($table) . PHP_EOL;
  $data .= ' *' . PHP_EOL;
  $data .= ' *   Base      : ' . $base . PHP_EOL;
  $data .= ' *   Table     : ' . $table . PHP_EOL;
  $data .= ' *   Généré le : ' . $horodatage . PHP_EOL;
  $data .= ' */' . PHP_EOL;
  $data .= 'class ' . ucfirst($table) . ' {' . PHP_EOL;
  $data .= PHP_EOL;
// Propriétés
  foreach ($colonnes as $colonne) {
    // Nom de la variable
    $nom = trim($colonne['Comment'] != '') ? trim($colonne['Comment']) : str_replace('_', ' ', $colonne['Field']);
    $data.= ' /**' . PHP_EOL;
    $data.= '  * ' . $nom . PHP_EOL;
    $data.= '  * @var ' . $colonne['Type'] . PHP_EOL;
    $data.= '  */' . PHP_EOL;
    $data.= '  private $' . $colonne['Field'] . ';' . PHP_EOL;
  }
// Constructeur
  $data.= PHP_EOL;
  $data.= ' /**' . PHP_EOL;
  $data.= '  * Constructeur' . PHP_EOL;
  $data.= '  */' . PHP_EOL;
  $data.= '  function __construct($tableau=array()) {' . PHP_EOL;
  $data.= '    $this->populate($tableau);' . PHP_EOL;
  $data.= '  }' . PHP_EOL;
// Getter/setter
  $data.= PHP_EOL;
  $data.= ' /**' . PHP_EOL;
  $data.= '  * Getter/Setter' . PHP_EOL;
  $data.= '  */' . PHP_EOL;
  foreach ($colonnes as $colonne) {
    $data.= '  function get' . ucfirst($colonne['Field']) . '() {' . PHP_EOL;
    $data.= '    return $this->' . $colonne['Field'] . ';' . PHP_EOL;
    $data.= '  }' . PHP_EOL;
    $data.= '  function set' . ucfirst($colonne['Field']) . '($' . $colonne['Field'] . ') {' . PHP_EOL;
    $data.= '    $this->' . $colonne['Field'] . '=$' . $colonne['Field'] . ';' . PHP_EOL;
    $data.= '  }' . PHP_EOL;
  }
// Hydrateur
  $data.= PHP_EOL;
  $data.= ' /**' . PHP_EOL;
  $data.= '  * Hydrateur' . PHP_EOL;
  $data.= '  */' . PHP_EOL;
  $data.= '  function populate(array $tableau) {' . PHP_EOL;
  $data.= '    foreach ($tableau as $cle => $valeur) {' . PHP_EOL;
  $data.= '      $methode = \'set\'.ucfirst($cle);' . PHP_EOL;
  $data.= '      if (method_exists($this, $methode))  {' . PHP_EOL;
  $data.= '        $this->$methode($valeur);' . PHP_EOL;
  $data.= '      }' . PHP_EOL;
  $data.= '    }' . PHP_EOL;
  $data.= '  }' . PHP_EOL;

// Afficheur
  $data .= PHP_EOL;
  $data .= '  // Liste des propriétés' . PHP_EOL;
  $data .= '  function display() {' . PHP_EOL;
  $data .= '    echo "<ul>".PHP_EOL;' . PHP_EOL;
  $data .= '    foreach ($this as $cle=>$valeur) {' . PHP_EOL;
  $data .= '      echo \'<li>\'.$cle.\'=\'.$valeur."</li>".PHP_EOL;' . PHP_EOL;
  $data .= '    }' . PHP_EOL;
  $data .= '    echo "</ul>".PHP_EOL;' . PHP_EOL;
  $data .= '  }' . PHP_EOL;
// Bas de classe
  $data .= PHP_EOL;
  $data .= "} // Classe" . PHP_EOL;
// Vérifie que le dossier de destination existe
  $dirname = 'output';
  if (!file_exists($dirname)) {
    mkdir($dirname, 0777);
  }
// Génère le fichier
  $filename = $table . ".class.php";
  file_put_contents($dirname . DIRECTORY_SEPARATOR . $filename, $data);
}
/**
 * Fonctions communes
 */

/**
 * Affiche un message dans la console Windows
 * @param string $message
 */
function e($message) {
  echo iconv("UTF-8", "CP437//IGNORE", $message);
  //echo iconv("UTF-8", "CP1252//IGNORE", $message);
}
