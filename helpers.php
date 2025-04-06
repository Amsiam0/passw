<?php

function init(){

    if(file_exists('database.json')){
        return;
    }
    
    try{
        //check if HOME_DIR/config folder exists
        if (!file_exists(HOME_DIR.'/config/public_key.asc')) { 
            createDatabase();
            echo "Database created successfully.\n";
        }else{
            setDatabase();
            echo "Database updated successfully.\n";
        }
    }
        
        catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit;
        }
}




function createDatabase(){
    // ask for username email and password
    $username = readline("Enter your username: ");
    $email = readline("Enter your email: ");
    $password = readline("Enter your password: ");
    // create an array with the data
    $data = array(
        'userid' => $username.'<'.$email.'>',
        'email' => $email
    );

    try{
        genarateGPGKey($username, $email, $password);
    }catch (Exception $e) {
        throw $e;
    }

    // encode the data to json
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    // create the database.json file
    file_put_contents('database.json', $json_data);
}

function setDatabase(){
    $homeDir = HOME_DIR."/config";
    $publicKeyFile = "$homeDir/public_key.asc";


    exec("gpg --list-packets $publicKeyFile | grep -i 'user id'", $output);
    if (empty($output)) {
        throw new Exception("Failed to list packets. GPG says: " . implode("\n", $output));
    }
    $userId = trim($output[0]);
    //find the email in the user id by regex
    $regex = '/"([^<]+?)\s+<([^>]+)>"/';

    if (preg_match($regex, $userId, $matches)) {
        $name = $matches[1];  // Group 1 is the name
        $email = $matches[2];
    } else {
        throw new Exception("Failed to extract email from user id. GPG says: " . implode("\n", $output));
    }
    
    //save to database.json
    $data = array(
        'userid' => $name.'<'.$email.'>',
        'email' => $email
    );

    // encode the data to json
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    // create the database.json file
    file_put_contents('database.json', $json_data);
    
}

function genarateGPGKey($username, $email, $password) {

    $homeDir = HOME_DIR."/config";
    // check if gpg is installed
    $output = shell_exec('gpg --version');
    if (strpos($output, 'gpg (GnuPG)') === false) {
        throw new Exception("GPG is not installed. Please install GPG and try again.");
    }
    // check if gpg key already exists
    $output = shell_exec("gpg --homedir $homedir --list-keys");
    if (strpos($output, $email) !== false) {
        throw new Exception("GPG key already exists for $email. Please use a different email.");
    } 

    $publicKeyFile = "$homeDir/public_key.asc";
    $privateKeyFile = "$homeDir/private_key.asc";

    // Make sure our key hideout exists
    if (!file_exists($homeDir)) {
        mkdir($homeDir, 0700, true);
    }

    $batchConfig = <<<EOT
    %echo Generating keys like a cryptographic wizard...
    Key-Type: RSA
    Key-Length: 2048
    Subkey-Type: RSA
    Subkey-Length: 2048
    Name-Real: $username
    Name-Email: $email
    Expire-Date: 0
    Passphrase: $password
    %commit
    %echo Done! Time to encrypt the universe!
    EOT;

    // Create the batch config file
    file_put_contents("$homeDir/genkey.conf", $batchConfig);

    // Generate the key—let’s summon some cryptographic magic!
    exec("gpg --homedir $homeDir --batch --generate-key $homeDir/genkey.conf 2>&1", $output, $returnVar);

    //remove the batch config file
    unlink("$homeDir/genkey.conf");

    // Check if the key generation was successful
    if ($returnVar !== 0) {
        throw new Exception("Key generation failed! GPG says: " . implode("\n", $output));
    }

    // Export the public key—share 
    exec("gpg --homedir $homeDir --armor --export $email > $publicKeyFile 2>&1", $pubOutput, $pubReturn);
    if ($pubReturn !== 0) {
        throw new Exception("Public key export failed! GPG says: " . implode("\n", $pubOutput));
    }

    // Export the private key—guard 
    exec("gpg --homedir $homeDir --armor --export-secret-keys $email > $privateKeyFile 2>&1", $privOutput, $privReturn);
    if ($privReturn !== 0) {
        throw new Exception("Private key export failed! GPG says: " . implode("\n", $privOutput));
    }


    echo "Passphrase: $password (Don’t lose it, or you’re toast!)\n";
}

function printHelp(){
    echo "Passw version: 1.0.0\n";
    echo "Usage: php main.php <key>\n";
    echo "Commands:\n";
    echo "  new, add, -a: Add a new password\n";
    echo "  show, view, -s: Show a password\n";
    echo "  delete, remove, -d: Delete a password\n";
    echo "  list, -l: List all Entries\n";
    echo "  help, -h: Show this help message\n";
}


