<?php 

require_once 'config.php';
require_once 'helpers.php';

init();


//get the data from arguments


//check if the key is passed
if ($argc < 2) {
    echo "Passw version: 1.0.0\n";
    echo "Usage: php main.php <key>\n";
    echo "For Help: php main.php -h or php main.php help\n";
    die;
}

$key = $argv[1];



switch ($key) {
    case 'help':
    case '-h':
        printHelp();
        break;
    case 'new':
    case 'add':
    case '-a':
        addNewService();
        break;
    case 'show':
    case 'view':
    case '-s':
        showService();
        break;
    case 'update':
    case 'edit':
    case '-u':
        updateService();
        break;
    case 'delete':
    case 'remove':
    case '-d':
        deleteService();
        break;
    case 'list':
    case 'ls':
    case '-l':
        listServices();
        break;
    default:
        echo "Invalid command: $key\n";
        printHelp();
        break;
}