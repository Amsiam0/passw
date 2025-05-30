<?php

function init(){

    if(file_exists('database.json')){
        return;
    }
    
    try{
        //check if HOME_DIR/config folder exists
        if (!file_exists(HOME_DIR.'/config/public_key.asc')) { 
            echo "Config folder does not exist. Creating...\n";
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


    //import the public key & private key
    
    exec("gpg --homedir $homeDir --import $publicKeyFile 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        throw new Exception("Key import failed! GPG says: " . implode("\n", $output));
    }

    //import the private key
    $privateKeyFile = "$homeDir/private_key.asc";
    exec("gpg --homedir $homeDir --import $privateKeyFile 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        throw new Exception("Key import failed! GPG says: " . implode("\n", $output));
    }


    exec("gpg --homedir $homeDir --list-packets $publicKeyFile | grep -i 'user id'", $output);
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
    $output = shell_exec("gpg --homedir $homeDir --list-keys");
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

function addNewService(){
    //get the data from arguments
    $service = readline("Enter the service name: ");

    $service = ucwords(strtolower($service));

    if (!serviceNameValidation($service,true)) {
        return;
    }

    $username = readline("Enter the username: ");
    $password = readline("Enter the password: ");

    //create an array with the data
    $data = array(
        'username' => $username,
        'password' => $password,
    );
    

    //create a folder with the service name
    if (!file_exists(HOME_DIR.'/'.$service)) {
        mkdir(HOME_DIR.'/'.$service, 0700, true);
    }

    //encrypt the data using gpg
    $json_data = json_encode($data, JSON_PRETTY_PRINT);

    
    $encryptedData = encryptFile($json_data);
    


    if($encryptedData === false){
        return;
    }

    

    //save the encrypted data to a file
    file_put_contents(HOME_DIR.'/'.$service.'/data.gpg', $encryptedData);
    echo "Service added successfully.\n";
}

function showService(){

    //get the data from arguments
    $service = readline("Enter the service name: ");


    //title case the service name
    $service = ucwords(strtolower($service));

    if (!serviceNameValidation($service)) {
        return;
    }

    //read from database.json
    $json_data = file_get_contents('database.json');
    $data = json_decode($json_data, true);


    putenv('GNUPGHOME='.HOME_DIR.'/config');
    
    // Clear gpg-agent cache before starting
    exec('gpgconf --kill gpg-agent'); // Stop the agent
    exec('gpg-agent --daemon --verbose > /dev/null 2>&1 &'); // Restart it

    $gpg = new gnupg();

    // throw exception if error occurs
    $gpg->seterrormode(gnupg::ERROR_EXCEPTION); 

    $decrypted_data = '';
    $ciphertext = file_get_contents(HOME_DIR.'/'.$service.'/data.gpg');


    $max_attempts = 3;
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        try {
            $passphrase = readline("Enter your passphrase (attempt $attempt/$max_attempts): ");
            $gpg->adddecryptkey($data['email'], $passphrase);
            $decrypted_data = $gpg->decrypt($ciphertext);
            $gpg->cleardecryptkeys();
            break; // Exit loop if successful
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            if ($attempt == $max_attempts) {
                die("Max attempts reached. Decryption failed.\n");
            }
            continue;
        }
    }


    //check if the decryption was successful
    if (empty($decrypted_data)) {
        echo "Decryption failed! GPG says: " . implode("\n", $output);
        return;
    }
    
    //decode the data from json
    $data = json_decode($decrypted_data, true);
    //check if the data is valid
    if (empty($data)) {
        echo "Decryption failed! GPG says: " . json_encode($decrypted_data);
        return;
    }
    
    echo "Service: $service\n";
    echo "Username: ".$data['username']."\n";
    echo "Password: ".$data['password']."\n";
    
}

function listServices(){
    //get the data from arguments
    $services = scandir(HOME_DIR);
    $services = array_diff($services, array('.', '..', 'config', 'database.json'));
    if (empty($services)) {
        echo "No services found.\n";
        return;
    }
    echo "Services:\n";
    foreach ($services as $service) {
        echo "- $service\n";
    }
}

function deleteService(){
    //get the data from arguments
    $service = readline("Enter the service name: ");

    //title case the service name
    $service = ucwords(strtolower($service));

    if (!serviceNameValidation($service)) {
        return;
    }

    //delete the folder with the service name
    unlink(HOME_DIR.'/'.$service.'/data.gpg');
    rmdir(HOME_DIR.'/'.$service);
    echo "Service deleted successfully.\n";
}

function updateService(){
    //get the data from arguments
    $service = readline("Enter the service name: ");

    $service = ucwords(strtolower($service));

    //check if the service isvalid
    if (!serviceNameValidation($service)) {
        return;
    }
    
    $username = readline("Enter the username: ");
    $password = readline("Enter the password: ");

    //create an array with the data
    $data = array(
        'username' => $username,
        'password' => $password,
    );

    
    
    

    //encrypt the data using gpg
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    
    $encrypted_data = encryptFile($json_data);

    if($encrypted_data === false){
        return;
    }


    //save the encrypted data to a file
    file_put_contents(HOME_DIR.'/'.$service.'/data.gpg', $encrypted_data);
    echo "Service updated successfully.\n";
}

function serviceNameValidation($service, $isNewService = false) {
    //check if the service name is empty
    if (empty($service)) {
        echo "Service name cannot be empty.\n";
        return false;
    }

    //title case the service name
    

    //check if the service name is valid
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $service)) {
        echo "Service name can only contain letters, numbers, and underscores.\n";
        return false;
    }

    //check if the service name exists
    if($isNewService){
        if (file_exists(HOME_DIR.'/'.$service.'/data.gpg')) {
            echo "Service already exist.\n";
            return false;
        }
    }else{
        if (!file_exists(HOME_DIR.'/'.$service.'/data.gpg')) {
            echo "Service name does not exist.\n";
            return false;
        }
    }

    return true;
}

function encryptFile($data) {

    $homeDir = HOME_DIR."/config";
    $publicKeyFile = "$homeDir/public_key.asc";

    
    $publicKey = file_get_contents($publicKeyFile);


    putenv('GNUPGHOME='.HOME_DIR.'/config');

    $gpg = new gnupg();
    $gpg->seterrormode(gnupg::ERROR_EXCEPTION);
    $info = $gpg->import($publicKey);
    $gpg->addencryptkey($info['fingerprint']);
    $encryptedData = $gpg->encrypt($data);
    $gpg->clearencryptkeys();
    

    //check if the encryption was successful
    if (empty($encryptedData)) {
        echo "Encryption failed! GPG says: " . implode("\n", $output);
        return false;
    }

    //check if the encrypted data is valid
    if (!preg_match('/^-----BEGIN PGP MESSAGE-----/', $encryptedData)) {
        echo "Encryption failed! GPG says: " . json_encode($encryptedData);
        return false;
    }

    return $encryptedData;

}