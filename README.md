# Profile
**JsonDb** is a PHP class used to CRUD (`INSERT`, `UPDATE`, `SELECT`, `DELETE`) json file like SQL.

It's easy and lightweight.

# Demo
[Click Demo](http://www.awaimai.com/demo/JsonDb/example/)


# Usage
1. Create an json file. For example `demo.json`.
2. Include JsonDb.php in project, create an instance using `new JsonDb` and identify json file:
```
  include 'JsonDb.php';
  $json = new JsonDb('demo');
```
File extension is not needed, because it has a default value `.json`.

3. Then you can insert/select a record:
```
  $data = ['name' => 'Gary', 'title' => 'PHP', 'website' => 'http://www.awaimai.com/'];

  echo $json->insert($data);        //return the inserted id
  print_r($json->selectAll());      // return all record

  $json->delete('*');
```
# User guide

### 1. Setup
```
// create JsonDB with filename='demo', primary key='page_id', and file extension='.js'
$json = new JsonDb('demo', 'page_id', '.js');
```
**Note:** **filename** is required, primary key default is `id`, file extension default is '.json'

### 2. Select
```
    $json->select(2);                       // Select a record which's primary key is 2
    $json->selectAll();                     // Select all records
    $json->selectAll(['sort' => 'DESC');    // Select all records ordered desc by sort
    $json->selectIn([2, 3, 5]);             // Select some records
    $json->getCount();                      // Number of records
```

### 3. Insert
```
    $json->insert(['name' => 'Gary', 'title' => 'PHP']);
```

### 4.Update
```
    $json->update(['id' => 1, 'name' => 'Galley']);
```
This will change the name of a record which's id is `1`, other field will not be affected.

### 5. Update multiples
```
    $data = [
        ['id' => 1, 'name' => 'Jack'],
        ['id' => 3, 'title' => 'Javascript'],
    ];

    $json->updates($data);
```
You must identify primary key value in the array.

### 6. Delete
```
    $json->delete(2);                       // Delete one record
    $json->delete([2, 3, 5]);               // Delete some records
    $json->delete('*');                     // Delete *all* records
```




