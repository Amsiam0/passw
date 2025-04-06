<?php


putenv('GNUPGHOME=database/config');

// Clear gpg-agent cache before starting
exec('gpgconf --kill gpg-agent'); // Stop the agent
exec('gpg-agent --daemon --verbose > /dev/null 2>&1 &'); // Restart it

$gpg = new gnupg();
$gpg->seterrormode(gnupg::ERROR_EXCEPTION);

$publicKeyFile = file_get_contents('database/config/public_key.asc');
$info = $gpg->import($publicKeyFile);
$gpg->addencryptkey($info['fingerprint']);

$enc = $gpg->encrypt('test');
$gpg->clearencryptkeys();


echo $enc;

$gpg->adddecryptkey("amsiam990@gmail.com","1233");

echo $gpg->decrypt($enc);