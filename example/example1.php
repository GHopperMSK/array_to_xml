<?php
use ghopper\arrayToXml;
use ghopper\arrayToXmlException;

require_once('../vendor/autoload.php');

$a = [
    [
        'books_book_id' => '1',
        'books_book_name' => 'Suffocationg',
        'books_book_author' => 'Chuck Palahniuk'
    ],
    [
        'books_book_id' => '2',
        'books_book_name' => 'Atlas Shrugged',
        'books_book_author' => 'Ayn Rand'
    ]
];

try {
    $atxml = new arrayToXML();

    foreach ($a as $row) {
        $atxml->parse($row);
    }

    unset($atxml);
} catch (arrayToXmlException $ex) {
    echo $ex->getMessage();
}

?>
