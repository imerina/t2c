# t2c
Outil générant une classe métier à partir d'une table SQL. 
Fonctionne en ligne de commande via PHP.

## Paramètres

* -b la base de données MySQL
* -t la table (* si toutes les tables de la base doivent être générées)
* Les user/password sont écrits en dur dans le script PHP de l'outil

## Utilisation

php.exe -f t2c.php -- -b MaBase

php.exe -f t2c.php -- -b MaBase -t MaTable

## Fonctionnalités
* Chaque table devient une classe métier
* Chaque champ devient une propriété privée
* On accède aux propriétés via des méthodes (getter/setter)
* Une méthode "populate" permet d'hydrater la classe (transforme un array en objet)
* Une méthode "display" affiche la liste des propriétés au format HTML
* Les commentaires dans la classe contiennent le type MySQL et éventuellement le contenu du commentaire MySQL

## Sortie
Crée un sous-dossier 'output' (si nécessaire) et y place les classes générées.
Il y a un fichier .php par classe. 
