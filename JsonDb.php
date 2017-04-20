<?php

/**
 * Class JsonDb
 */
class JsonDb
{
    /**
     * @var string primary key of the table,
     * it will be use add a column to record automation
     */
    private $primary;
    /**
     * @var string store latest data of the json file
     */
    private static $data  = [];
    /**
     * @var string json files locate directory
     */
    private $directory;
    /**
     * @var string current filename
     */
    private $fileName = '';
    /**
     * @var string current file path
     */
    private $filePath = '';
    /**
     * @var string JSON file extension, can be identify on constructing
     */
    private $extension;
    /**
     * @var array all JSON files name, do not include extension
     */
    private static $fileNames = [];
    /**
     * @var string store error if there are some exception catch
     */
    private $error = '';

    /**
     * JsonDb constructor.
     * @param $directory string json files locate directory, exclude extension
     */
    public function __construct($directory, $primary = 'id', $extension = '.json')
    {
        $this->directory = rtrim($directory, '/') . '/';
        if (is_dir($this->directory) === false) {
            throw new Exception('There is not such directory.');
        }
        $this->primary = $primary;
        $this->extension = $extension;
        self::$fileNames = $this->loadFileNames();
    }

    /**
     * Save current file name and full file path,
     * and load data from file system if OBJECT data is not set.
     * @param $name string file name
     * @return $this current OBJECT
     * @throws Exception if there is not such file
     */
    public function __get($name)
    {
        if (in_array($name, self::$fileNames)) {
            $this->fileName = $name;
            $this->filePath = $this->directory . $name . $this->extension;
            if (isset(self::$data[$this->fileName]) === false) {
                self::$data[$this->fileName] = $this->read();
            }
            return $this;
        }

        $this->error = 'There is not json file named ' . $name . $this->extension;
        throw new Exception($this->error);
    }

    /**
     * load all file names from file system
     * @return array
     */
    private function loadFileNames()
    {
        $files = scandir($this->directory);
        $files = array_diff($files, ['.', '..']);
        $files =  array_map(function($value) {
            return strpos($value, $this->extension) ? substr($value, 0, -strlen($this->extension)) : '';
        }, $files);

        return array_filter($files);
    }

    /**
     * Insert one record to json file
     * @param $value array data inserted to json file
     * @return bool
     */
    public function insert($value)
    {
        $data = self::$data[$this->fileName];

        if (empty($data)) {
            $value[$this->primary] = 1;
            $data[1] = $value;
        } else {
            $keys = array_keys($data);
            $primaryValue = isset($value[$this->primary]) ? (int)$value[$this->primary] : false;

            if ($primaryValue && in_array($primaryValue, $keys)) {
                $this->error = 'Primary key conflict.';
                return false;
            } elseif ($primaryValue) {
                $data[$primaryValue] = $value;
            } else {
                end($data);
                $value[$this->primary] = key($data) + 1;
                array_push($data, $value);
            }
        }

        ksort($data);
        if ($this->write($data)) {
            self::$data[$this->fileName] = $data;
            unset($data);
            return $value[$this->primary];
        }

        return false;
    }

    /**
     * @param $value array insert data
     * @return bool
     */
    public function update($value)
    {
        if (!is_array($value)) {
            return false;
        }

        $key = isset($value[$this->primary]) ? (int)$value[$this->primary] : 0;
        if (!$key) {
            $this->error = 'Field "' . $this->primary . '" is required.';
            return false;
        }

        $data = self::$data[$this->fileName];
        if (!isset($data[$key])) {
            $this->error = 'Record ' . $this->primary . '="' . $key . '" does not exist.';
            return false;
        }
        
        $data[$key] = array_merge($data[$key], $value);

        if ($this->write($data)) {
            self::$data[$this->fileName] = $data;
            unset($data);
            return true;
        }

        return false;
    }

    /**
     * Update data in array
     * @param $values array data to update
     */
    public function updates($values)
    {
        if (!is_array($values) || empty($values)) {
            $this->error = 'Params should be array.';
            return false;
        }

        $success = 0;
        $lackField = 0;
        $notExist = [];

        $data = self::$data[$this->fileName];
        foreach ($values as $value) {
            $key = isset($value[$this->primary]) ? (int)$value[$this->primary] : 0;
            if (!$key) {
                $lackField++;
                continue;
            }
            if (!isset($data[$key])) {
                $notExist[] = $key;
                continue;
            }
            $success++;
            $data[$key] = array_merge($data[$key], $value);
        }

        if ($lackField) {
            $this->error = $lackField . ' field(s)  is lack of ' . $this->primary;
        }

        if ($notExist) {
            $this->error .= ' Field(s) with ' . $this->primary . ' = ' . implode(', ', $notExist) . 'do not exist';
        }

        if ($this->write($data)) {
            self::$data[$this->fileName] = $data;
            unset($data);
            return $success;
        }

        return false;
    }

    /**
     * @param $key mixed key or keys to delete
     * @return bool
     */
    public function delete($key)
    {
        if (!$key) {
            $this->error = 'Key is required.';
            return false;
        }

        $data = self::$data[$this->fileName];
        if ($key === '*') {
            $data = [];
        } elseif (is_array($key)) {
            array_diff_key($data, array_flip($key));
        } elseif (isset($data[$key])) {
            unset($data[$key]);
        } else {
            return false;
        }

        if ($this->write($data)) {
            self::$data[$this->fileName] = $data;
            unset($data);
            return true;
        }
        return false;
    }

    /**
     * @param $key string re
     * @return array
     */
    public function select($key)
    {
        return $key ? self::$data[$this->fileName][$key] : [];
    }

    /**
     * select some records
     * @param $keys array primary keys that select
     */
    public function selectIn($keys)
    {
        if (!is_array($keys)) {
            return [];
        }
        $keys = array_flip($keys);

        return array_diff_key(self::$data[$this->fileName], $keys);
    }

    /**
     * Select all records from json and order by $sortBy, default is order by primary key asc
     * @param $sortBy mixed order condition
     * @return array
     */
    public function selectAll($sortBy = '')
    {
        if (is_array($sortBy)) {
            $sortKey = $sortBy ? key($sortBy) : $this->primary;
            $direct = current($sortBy);
        } else {
            $sortKey = $sortBy ?: $this->primary;
            $direct = 'asc';
        }

        $data = self::$data[$this->fileName];
        uasort($data, function ($a, $b) use ($sortKey, $direct){
            $prev = isset($a[$sortKey]) ? (int)$a[$sortKey] : 0;
            $next = isset($b[$sortKey]) ? (int)$b[$sortKey] : 0;

            return strtolower($direct) === 'desc' ? strcmp($next, $prev) : strcmp($prev, $next);
        });

        return $data;
    }

    public function count()
    {
        return count(self::$data[$this->fileName]);
    }

    public function getError()
    {
        return $this->error;
    }

    private function read()
    {
        $content = file_get_contents($this->filePath);
        return (array)json_decode($content, true);
    }

    private function write($data)
    {
        return file_put_contents($this->filePath, json_encode($data));
    }
}