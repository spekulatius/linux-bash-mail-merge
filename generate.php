<?php

message("Preparing emails for sending");

// Load template and prepare the content
$template = file_get_contents('template.txt');
list($subject, $body) = explode("\n", $template, 2);
$subject = str_replace('Subject: ', '', $subject);

// Load data and iterate through to send emails
$items = loadAndMatchCSV('targets.csv');
message(count($items) . " emails to send.");

// Prepare the emails
foreach ($items as $item) {
    // Message
    message(sprintf("Email to %s", $item['EMAIL']));

    // open firefox to go to the email program
    exec(sprintf(
        // command
        "sleep 3; x-www-browser 'mailto:%s?subject=%s&body=%s'",

        // to
        $item['EMAIL'],

        // generate subject line
        replacePlaceholders($subject, $item),

        // generate body
        replacePlaceholders($body, $item)
    ));

    // Is there an URL with this? If so, lets open it to make it easier.
    if (array_key_exists('URL', $item)) {
        exec(sprintf("sleep 1; x-www-browser '%s'", $item['URL']));
    }
}




/**
 * prints a message with timestamps
 **/
function message($note)
{
    echo sprintf(
        "[%s] %s\n",
        date('Y-m-d H:i:s'),
        $note
    );
}

/**
 * @see https://www.php.net/manual/en/function.fgetcsv.php
 **/
function loadAndMatchCSV($file)
{
    // get the csv
    $csv = array_map('str_getcsv', file($file));

    // Capitalise the headers and remove spaces.
    foreach ($csv[0] as $idx => $key) {
        $csv[0][$idx] = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '', $key));
    }

    // Match the columns
    array_walk($csv, function (&$a) use ($csv) {
        $a = array_map('trim', $a);
        $a = array_combine($csv[0], $a);
    });

    // Remove column headers
    array_shift($csv);

    return $csv;
}

function replacePlaceholders($template, $placeholders)
{
    // replace placeholders in the template.
    foreach ($placeholders as $key => $value) {
        $template = str_replace('%' . $key . '%', trim($value), $template);
    }

    // clean up to make sure it's actually working.
    $template = trim($template);
    $template = preg_replace('/[\n\r]/', '%0A', $template);
    $template = str_replace("'", '%27', $template);
    $template = str_replace('(', '%28', $template);
    $template = str_replace(')', '%29', $template);

    return trim($template);
}
