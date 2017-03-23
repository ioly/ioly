<?php
$recipes = unserialize(file_get_contents('.recipes.db'));
foreach ($recipes as $recipe) {
    echo $recipe['packageString'];
    echo " : ";
    echo basename($recipe['_cookbook'])."\n";
}
