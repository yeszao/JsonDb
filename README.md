# Profile
**JsonDb** is a PHP class used to CRUD (`INSERT`, `UPDATE`, `SELECT`, `DELETE`) json file like SQL.

It's easy and lightweight.

# Demo
[Click Demo](http://www.awaimai.com/demo/JsonDb/example/)


# Usage
1. Create a directory `files` and a demo file name `demo.json` located the directory.
2. Include JsonDb.php in your project, `new JsonDb` and identify json files directory:
    ```
      include 'JsonDb.php';
      $json = new JsonDb('./files');
    ```
3. Then you can insert/select a record to `demo.json`:
    ```
      $data = ['name' => 'Gary', 'title' => 'PHP', 'website' => 'http://www.awaimai.com/'];

      echo $json->demo->insert($data);        //return the inserted id
      print_r($json->demo->selectAll());      // return all record

      $json->demo->delete('*');
    ```
Here, property `demo` of `$json` is same as the name of json file `demo.json` (exclude extension `.json`).

# User guide

### 1. Setup

Create JsonDB with `$directory='./files'`, `$primary='page_id'`, and file `$extension='.js'`:
```
$json = new JsonDb('./files', 'page_id', '.js');
```
**Note:** **directory** is required, primary key default is `id`, file extension default is `'.json'`.

### 2. Select
```
    $json->demo->select(2);                       // Select a record which's primary key is 2
    $json->demo->selectAll();                     // Select all records
    $json->demo->selectAll(['sort' => 'DESC');    // Select all records ordered desc by sort
    $json->demo->selectIn([2, 3, 5]);             // Select some records
    $json->demo->count();                         // Number of records
```

### 3. Insert
```
    $json->demo->insert(['name' => 'Gary', 'title' => 'PHP']);
```

### 4.Update
```
    $json->demo->update(['id' => 1, 'name' => 'Galley']);
```
This will change the name of a record which's id is `1`, other field will not be affected.

### 5. Update multiples
```
    $data = [
        ['id' => 1, 'name' => 'Jack'],
        ['id' => 3, 'title' => 'Javascript'],
    ];

    $json->demo->updates($data);
```
You must identify primary key value in the array.

### 6. Delete
```
    $json->demo->delete(2);                       // Delete one record
    $json->demo->delete([2, 3, 5]);               // Delete some records
    $json->demo->delete('*');                     // Delete *all* records
```




