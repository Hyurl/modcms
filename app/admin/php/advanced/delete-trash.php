<?php
$tables = array();
foreach(array_keys(database()) as $table){
	$tables[$table] = lang($table.'.label') ?: $table;
}
$tablesJSON = json_encode($tables);