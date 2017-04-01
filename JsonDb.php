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
     * @var string json file name
     */
    private $filename;
    /**
     * @var string store error if there are some exception catch
     */
    private $error = '';


    /**
     * JsonDb constructor.
     * @param $filename string the file name of json file, exclude extension
     */
    public function __construct($filename, $primary = 'id', $ext = '.json')
    {
        $this->filename = $filename . $ext;
        $this->primary = $primary;

        self::$data = $this->read();
    }

    /**
     * Insert one record to json file
     * @param $value array data inserted to json file
     * @return bool
     */
    public function insert($value)
    {
        $data = self::$data;

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
            self::$data = $data;
            unset($data);
            return $value[$this->primary];
        }

        return false;
    }

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

        $data = self::$data;
        if (!isset($data[$key])) {
            $this->error = 'Record ' . $this->primary . '="' . $key . '" does not exist.';
            return false;
        }
        
        $data[$key] = array_merge($data[$key], $value);

        if ($this->write($data)) {
            self::$data = $data;
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

        $data = self::$data;
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
            self::$data = $data;
            unset($data);
            return $success;
        }

        return false;
    }

    public function delete($key)
    {
        if (!$key) {
            $this->error = 'Key is required.';
            return false;
        }

        $data = self::$data;
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
            self::$data = $data;
            unset($data);
            return true;
        }
        return false;
    }

    public function select($key)
    {
        return $key ? self::$data[$key] : [];
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

        return array_diff_key(self::$data, $keys);
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

        $data = self::$data;
        uasort($data, function ($a, $b) use ($sortKey, $direct){
            $prev = isset($a[$sortKey]) ? (int)$a[$sortKey] : 0;
            $next = isset($b[$sortKey]) ? (int)$b[$sortKey] : 0;

            return strtolower($direct) === 'desc' ? strcmp($next, $prev) : strcmp($prev, $next);
        });

        return $data;
    }

    public function count()
    {
        return count(self::$data);
    }

    public function getError()
    {
        return $this->error;
    }

    private function read()
    {
        $content = file_get_contents($this->filename);
        return (array)json_decode($content, true);
    }

    private function write($data)
    {
        return file_put_contents($this->filename, json_encode($data));
    }
}