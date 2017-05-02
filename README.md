# arrayToXml
Convenient way for xml-feed generation. You can get an multi-depth feed right from a SQL-query result!

## Configuring
All you need is an array with specific structure.
Array
```
[
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
]
```
Will be transformed into
```
<?xml version="1.0" encoding="UTF-8" ?>
<books>
        <book>
                <id>1</id>
                <name>Suffocationg</name>
                <author>Chuck Palahniuk</author>
        </book>
        <book>
                <id>2</id>
                <name>Atlas Shrugged</name>
                <author>Ayn Rand</author>
        </book>
</books>
```
Also, you can specify tag attributes by adding "_key-value_" marker in a column name.
Array
```
[
    [
        'books_book_key-value_id' => '1',
        'books_book_name' => 'Suffocationg',
        'books_book_author' => 'Chuck Palahniuk'
    ],
    [
        'books_book_key-value_id' => '2',
        'books_book_name' => 'Atlas Shrugged',
        'books_book_author' => 'Ayn Rand'
    ]
];

```
Will be transfored into
```
<?xml version="1.0" encoding="UTF-8" ?>
<books>
        <book id="1">
                <name>Suffocationg</name>
                <author>Chuck Palahniuk</author>
        </book>
        <book id="2">
                <name>Atlas Shrugged</name>
                <author>Ayn Rand</author>
        </book>
</books>
```

Look at the *example* folder for more information.

