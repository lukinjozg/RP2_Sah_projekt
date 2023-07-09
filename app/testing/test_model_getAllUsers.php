<?php 

require_once __DIR__ . '/../../model/libraryservice.class.php';
require_once __DIR__ . '/../../model/user.class.php';

$cs = new ChessService();
$users = $cs->getAllUsers();

echo '<pre>';
print_r( $users );
echo '</pre>';

?>
