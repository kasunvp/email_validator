<?php
# Import Settings
require_once('settings.php');
$mailgunPubkey = $settings['mailgunPubkey'];
$csvFileName = 'input/' . $settings['csvFileName'];
$chunkSize = $settings['chunkSize'];

# Set error handlers
set_error_handler("warning_handler", E_WARNING);

function warning_handler($errno, $errstr)
{
//    switch ($errno) {
//        case 2:
//            echo "Error opening file. Are you sure the file exists?\n";
//            break;
//    }
    echo "$errno : $errstr";
}

# Include the Autoloader (see "Libraries" for install instructions in mailgun)
require 'vendor/autoload.php';
use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun($mailgunPubkey);
$domain = 'mailgun.org';

# Read the csv file to $addressList as a string
if (($handle = fopen($csvFileName, "r")) !== FALSE) {
    $addressList = '';
    while (($csvContentArr = fgetcsv($handle)) !== FALSE) {
        $imploded = implode(',', $csvContentArr);
        $addressList = $addressList . ($addressList !== '' ? ',' : '') . $imploded;
    }
    fclose($handle);

    if (trim($addressList) !== '') {
        # Issue the call to the client.
        $result = $mgClient->get("address/parse", array('addresses' => $addressList));
        $validatedList = array();

        foreach ($result->http_response_body->parsed as $email) {
            $validated = $mgClient->get('address/validate', array('address' => $email));

            if ($validated->http_response_body->is_valid) {
                array_push($validatedList, $validated->http_response_body->address);
            }
        }

        if (($validCount = count($validatedList)) > 0) {
            echo "The file " . $csvFileName . " contains " . $validCount . " valid email/s.\n";
            $fileNo = 0;
            $chunks = array_chunk($validatedList, $chunkSize);
            echo "\nCreating files with " . $chunkSize . " email"
                . ($chunkSize > 1 ? 's' : '') . " (max)...\n";
            if(!is_dir('output')) mkdir('output', 0775); // create directory output
            foreach ($chunks as $chunk) {
                $fileNo++;
                $fileName = 'file' . $fileNo . '_' . time() . '.csv';
                $fp = fopen('output/' . $fileName, 'w');
                echo "\t" . $fileName . "\n";
                fputcsv($fp, $chunk);
                fclose($fp);
                #echo "Done!\n";
            }
            echo "Completed!\n";
            echo $fileNo . " file" . ($fileNo > 1 ? 's' : '') . " created.\n";
        } else {
            echo "The file " . $csvFileName . " does not contain any valid email.\n";
        }
    } else {
        echo "There is no content in " . $csvFileName . " file.\n";
    }
} else {
    echo $csvFileName . " opening error.\n";
}
?>
