# rmodels

Repositories for the RedBeanPHP


```php
use JasterStary\RModels\RDatabase;
use JasterStary\RModels\RRepo;

```

```php
$this->db = new RDatabase();
$this->db->start(getenv('DB_CONN'),getenv('DB_USER'),getenv('DB_PASS'));
$this->db->log(getenv('DB_LOGS'));


$theRepo = new RRepo();


```


## Create:

```php

$id = $theRepo->Create([
  'title' => 'The title',
  'description' => 'Lorem ipsum...'
]);


```

Create, but dont allow the same title twice:

```php

$id = $theRepo->Create([
  'title' => 'The title',
  'description' => 'Lorem ipsum...'
], ['title']);


```

Update, if there is record with the same title already:

```php

$id = $theRepo->Create([
  'title' => 'The title',
  'description' => 'Lorem ipsum...'
], ['title'], true);


```

Following call returns bean (see RedBeanPHP) instead of id.

```php

$bean = $theRepo->Created([
  'title' => 'The title',
  'description' => 'Lorem ipsum...'
], ['title'], true);


```


## Retrieve:


```php

$theRepo->Retrieve($id);


```

Retrieve also records from shared tables

```php

$theRepo->Retrieve($id, ['features', 'reviews']);


```

## Update:


```php

$theRepo->Update(
$id,
[
  'title' => 'The title',
  'description' => 'Lorem ipsum...'
]);


```


## Delete:


```php

$theRepo->Delete($id);


```


## Count:

Count all records:

```php

$data = $theRepo->Count();

```

Count records by condition:

```php

$data = $theRepo->Count([
  'title IN' => ['debitis est id qui', 'aut aut ea cum', true]
  ]);

```

## Collection:

```php

$data = $theRepo->Collection()->toArray();


```

Pagination can be added automatically with RCollection method toPages:

```php

$data = $theRepo->Collection([
  'LIMIT' => 100, 'OFFSET' => 0
], ['title'])->toPages();


```

Results could be altered with closure function:

```php

$route = 'something';
$data = $theRepo->Collection([
  'LIMIT' => 100, 'OFFSET' => 0
], ['title'])->toPages(function(&$row) use ($route) {
  if (isset($row['id'])) unset($row['id']);
  $row['route'] = $route;
});

```

With RCollection method toModels you get array of models:

```php

$route = 'something';
$data = $theRepo->Collection([
  'LIMIT' => 100, 'OFFSET' => 0
], ['title'])->toModels();


```

## Listing:

Listing is just the same as Collection, but with array output.
It is maintained for backward compatibility only.

Listing using SQL IN:

```php

$data = $theRepo->Listing([
  'title IN' => ['debitis est id qui', 'aut aut ea cum', true]
  ],
  ['title', 'headline'],
  true
);


```

Listing using SQL functions:

```php

$data = $theRepo->Listing([
  'title|REVERSE LIKE' => 'd%'
  ],
  ['title', 'headline'],
  true
);


```

Querying NULL:

```php

$data = $theRepo->Listing([
  'page_id' => null
  ],
  ['title', 'headline'],
  true
);

$data = $theRepo->Listing([
  'page_id NOT' => null
  ],
  ['title', 'headline'],
  true
);


```


```php

$data = $theRepo->SimpleList();


```


```php

$data = $theRepo->Distinct('pages');


```


```php

$data = $theRepo->Options();


```



```php

$data = $theRepo->exportColumns(true);


```
