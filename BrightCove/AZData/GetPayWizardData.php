<?php



/*
$c = curl_init("sftp://azubu:LZvuhB0vQYXI@azubu.sftp.paywizard.com/outgoing/archive/Azubu%20payments%20report%2020160209.csv");
curl_setopt($c, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
$data = curl_exec($c);
curl_close($c);
var_dump($data);
*/
/*
$host = 'azubu.sftp.paywizard.com';
$pass = 'LZvuhB0vQYXI';

$remote = "sftp://root:$pass@$host/var/www/test.txt";
$local = 'C:\wamp\www\test.txt';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $remote);
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
curl_setopt($curl, CURLOPT_USERPWD, "azubu:$pass");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//file_put_contents($local, curl_exec($curl));
$data = curl_exec($curl);
var_dump($data);

curl_close($curl);


$c = curl_init("sftp://azubu:LZvuhB0vQYXI@azubu.sftp.paywizard.com/outgoing/archive/Azubu%20payments%20report%2020160209.csv");
//$fh = fopen("Azubu%20payments%20report%2020160209.csv", 'r') or die("ERROR");
curl_setopt($c, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
curl_setopt($c, CURLOPT_FILE, $fh);
curl_exec($c);
curl_close($c);
*/

$host = 'azubu.sftp.paywizard.com';
$port = 22;
$username = 'azubu';
$password = 'LZvuhB0vQYXI';
$remoteDir = '/outgoing/archive/';
$localDir = 'paywizardfiles/';
if (!file_exists($localDir)) {
    mkdir($localDir, 0777, true);
}


if (!function_exists("ssh2_connect"))
    die('Function ssh2_connect not found, you cannot use ssh2 here');

if (!$connection = ssh2_connect($host, $port))
    die('Unable to connect');

if (!ssh2_auth_password($connection, $username, $password))
    die('Unable to authenticate.');

if (!$stream = ssh2_sftp($connection))
    die('Unable to create a stream.');

if (!$dir = opendir("ssh2.sftp://{$stream}{$remoteDir}"))
    die('Could not open the directory');

$files = array();
while (false !== ($file = readdir($dir)))
{
    if ($file == "." || $file == "..")
        continue;
    $files[] = $file;
}



foreach ($files as $file)
{
    echo "Copying file: $file\n";
    if (!$remote = @fopen("ssh2.sftp://{$stream}/{$remoteDir}{$file}", 'r'))
    {
        echo "Unable to open remote file: $file\n";
        continue;
    }

    if (!$local = @fopen($localDir . $file, 'w'))
    {
        echo "Unable to create local file: $file\n";
        continue;
    }

    $read = 0;
    $filesize = filesize("ssh2.sftp://{$stream}/{$remoteDir}{$file}");
    while ($read < $filesize && ($buffer = fread($remote, $filesize - $read)))
    {
        $read += strlen($buffer);
        if (fwrite($local, $buffer) === FALSE)
        {
            echo "Unable to write to local file: $file\n";
            break;
        }
    }
    fclose($local);
    fclose($remote);
}

$lastMod = "";
$lastModFile = '';
$dir=$localDir;
foreach (scandir($dir) as $entry) {
    if (is_file($dir.$entry) && ($dir.$entry) > $lastModFile) {
        $lastMod = ($dir.$entry);
        $lastModFile = $entry;
    }
}
var_dump($lastModFile);

include './BrightCove/credentials/BrightCoveCredentials.php';
$outputfile=$S3sourceDir . "/paywizarddata.csv";

#file_put_contents($outputfile, $lastModFile);
copy( $localDir."/".$lastModFile,$outputfile);
include './BrightCove/bcs3.php';
include './BrightCove/AZData/LoadPayWizardData.php';


?> 
