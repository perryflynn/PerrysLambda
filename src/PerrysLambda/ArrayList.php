<?php

namespace PerrysLambda;

class ArrayList extends Property implements \ArrayAccess, \SeekableIterator
{

    protected $__iterator = 0;
    protected $__keycache = null;
    protected $__fieldtype;
    protected $__convertfield;


    /**
     * New ArrayList with fixed item type
     * @param string $type
     * @param array $data
     * @return \PerrysLambda\ArrayList
     */
    public static function asType($type, array $data)
    {
        return new static($data, $type);
    }

    /**
     * New ArrayList with ObjectArrays as items
     * @param array $data
     * @return \PerrysLambda\ArrayList
     */
    public static function asObjectArray(array $data)
    {
        return new static($data, '\PerrysLambda\ObjectArray');
    }

    public function __construct(array $data = null, $fieldtype=null, $convertfield=true)
    {
        if(is_string($fieldtype))
        {
            if(!class_exists($fieldtype))
            {
                throw new InvalidException("Invalid Itemtype: ".$fieldtype);
            }
        }

        if(!is_string($fieldtype) && !is_null($fieldtype))
        {
            $fieldtype=null;
        }

        $this->__convertfield = ($convertfield===true);
        $this->__fieldtype = $fieldtype;
        parent::__construct($data);
    }

    /**
     * Set data
     * @param array $data
     * @throws InvalidDataException
     * @throws InvalidKeyException
     * @throws InvalidValueException
     */
    public function setData($data)
    {
        if(!$this->getIsValidData($data))
        {
            throw new InvalidDataException();
        }

        $data = $this->convertData($data);

        if(!$this->getIsAllKeysValid($data))
        {
            throw new InvalidKeyException();
        }

        if(!$this->getIsAllValuesValid($data))
        {
            throw new InvalidValueException();
        }

        $this->__data = $data;
        $this->regenerateKeyCache();
    }

    /**
     * Validate data
     * @param array $val
     * @return bool
     */
    public function getIsValidData($val)
    {
        return is_array($val);
    }

    /**
     * Regenerate array key cache
     */
    protected function regenerateKeyCache()
    {
        $this->__keycache = array_keys($this->__data);
    }

    /**
     * Get all field names
     * @return array
     */
    public function getNames()
    {
        if(is_null($this->__keycache) && is_array($this->__data))
        {
            $this->regenerateKeyCache();
        }
        return $this->__keycache;
    }

    /**
     * Get field name by index
     * @param int $i
     * @return mixed
     */
    public function getNameAt($i)
    {
        $fields = $this->getNames();
        if($i<$this->length())
        {
            return $fields[$i];
        }
        return null;
    }

    /**
     * Convert data
     * @param array $data
     * @return array
     */
    protected function convertData($data)
    {
        foreach($data as &$field)
        {
            $field = $this->convertDataField($field);
        }
        unset($field);
        return $data;
    }

    /**
     * Convert single data field
     * @param mixed $field
     * @return mixed
     */
    protected function convertDataField($field)
    {
        if($this->__convertfield===true && !is_null($this->__fieldtype) &&
            !is_a($field, $this->__fieldtype))
        {
            $class = $this->__fieldtype;
            return new $class($field);
        }
        return $field;
    }

    /**
     * Get data
     * @return array
     */
    public function toArray()
    {
        return $this->__data;
    }

    /**
     * Get count of fields
     * @return int
     */
    public function length()
    {
        return count($this->__data);
    }

    /**
     * Check for field by its name
     * @param mixed $field
     * @return bool
     */
    public function exists($field)
    {
        return is_array($this->__data) && isset($this->__data[$field]);
    }

    /**
     * Get field name by index
     * @param int $i
     * @param mixed $default
     * @return mixed
     */
    public function getAt($i, $default=null)
    {
        $field = $this->getNameAt($i);
        if($this->exists($field))
        {
            return $this->get($field);
        }
        return $default;
    }

    /**
     * Get field by its name
     * @param mixed $field
     * @param mixed $default
     * @param bool $autoset
     * @return mixed
     */
    public function get($field, $default=null, $autoset=false)
    {
        if(isset($this->__data[$field]))
        {
            return $this->__data[$field];
        }
        if($autoset===true)
        {
            $this->set($field, $default);
            return $this->get($field);
        }
        return $default;
    }

    /**
     * Get field value as scalar by index
     * @param int $i
     * @param mixed $default
     * @return \PerrysLambda\ScalarProperty
     */
    public function getScalarAt($i, $default=null)
    {
        $name = $this->getNameAt($i);
        if(!is_null($name))
        {
            return $this->getScalar($name, $default);
        }
        return $default;
    }

    /**
     * Get field value as scalar by field name
     * @param mixed $field
     * @param mixed $default
     * @param bool $autoset
     * @return \PerrysLambda\ScalarProperty
     */
    public function getScalar($field, $default=null, $autoset=false)
    {
        $value = $this->get($field, $default, $autoset);
        return new ScalarProperty($value);
    }

    /**
     * Set field
     * @param mixed $field
     * @param mixed $value
     * @return \PerrysLambda\ArrayList
     * @throws InvalidKeyException
     * @throws InvalidValueException
     */
    public function set($field, $value)
    {
        if(!$this->getIsValidKey($field))
        {
            throw new InvalidKeyException();
        }
        if(!$this->getIsValidValue($value))
        {
            throw new InvalidValueException();
        }
        $this->__data[$field] = $value;
        $this->regenerateKeyCache();
        return $this;
    }

    /**
     * Add field
     * @param mixed $value
     * @return \PerrysLambda\ArrayList
     */
    public function add($value)
    {
        $this->__data[] = $value;
        $this->regenerateKeyCache();
        return $this;
    }

    /**
     * Remove field by index
     * @param int $i
     * @return \PerrysLambda\ArrayList
     */
    public function removeAt($i)
    {
        $field = $this->getNameAt($i);
        $this->remove($field);
        return $this;
    }

    /**
     * Remove field by name
     * @param mixed $field
     * @return \PerrysLambda\ArrayList
     */
    public function remove($field)
    {
        if(isset($this->__data[$field]))
        {
            unset($this->__data[$field]);
        }
        return $this;
    }

    /**
     * Validate all field keys
     * @param array $data
     * @return boolean
     */
    public function getIsAllKeysValid($data)
    {
        $keys = array_keys($data);
        foreach($keys as $key)
        {
            if(!$this->getIsValidKey($key))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate all field values
     * @param array $data
     * @return boolean
     */
    public function getIsAllValuesValid($data)
    {
        foreach($data as $value)
        {
            if(!$this->getIsValidValue($value))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Is field key valid
     * @param mixed $name
     * @return bool
     */
    public function getIsValidKey($name)
    {
        //return is_int($name);
        return is_string($name) || is_numeric($name) || is_null($name);
    }

    /**
     * Is field value valid
     * @param mixed $value
     * @return boolean
     */
    public function getIsValidValue($value)
    {
        if(is_null($this->__fieldtype))
        {
            return true;
        }
        return is_a($value, $this->__fieldtype);
    }


    // Lambda methods ----------------------------------------------------------


    /**
     * filter by condition
     * @param callable $where
     * @return \PerrysLambda\ArrayList
     */
    public function where(callable $where)
    {
        $collection = $this->newInstance();
        foreach($this as $record)
        {
            if(call_user_func($where, $record))
            {
                $collection->add($record);
            }
        }
        return $collection;
    }

    /**
     * filter duplicate field by condition
     * @param callable $distinct
     * @return \PerrysLambda\ArrayList
     */
    public function distinct(callable $distinct)
    {
        $keys = array();
        $collection = $this->newInstance();
        foreach($this as $record)
        {
            $value = call_user_func($distinct, $record);
            $hash = md5(json_encode($value));
            if(!isset($keys[$hash]))
            {
                $keys[$hash]=true;
                $collection->add($record);
            }
        }
        return $collection;
    }

    /**
     * Check for any field by condition
     * @param callable $where
     * @return bool
     */
    public function any(callable $where)
    {
        foreach($this as $record)
        {
            if(call_user_func($where, $record))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Select field
     * @param callable $select
     * @return array
     */
    public function select(callable $select)
    {
        $result = array();
        foreach($this as $key => $record)
        {
            $result[] = call_user_func($select, $record, $key);
        }
        return $result;
    }

    /**
     * Iterate all fields
     * @param callable $each
     * @return \PerrysLambda\ArrayList
     */
    public function each(callable $each)
    {
        foreach($this as $key => $record)
        {
            call_user_func($each, $record, $key);
        }
        return $this;
    }

    /**
     * Group fields by condition
     * @param callable $group
     * @return \PerrysLambda\ArrayList
     */
    public function groupBy(callable $group)
    {
        $result = $this->newInstance();
        foreach($this as $record)
        {
            $key = call_user_func($group, $record);
            $result->get($key, $this->newInstance(), true)->add($record);
        }
        return $result;
    }

    public function sum(callable $sum)
    {
        $temp = $this->select($sum);
        return array_sum($temp);
    }

    public function min(callable $min)
    {
        $temp = $this->select($min);
        return min($temp);
    }

    public function max(callable $max)
    {
        $temp = $this->select($max);
        return max($temp);
    }

    public function avg(callable $avg)
    {
        return ($this->sum($avg)/$this->length());
    }

    public function join(callable $join, $glue=", ")
    {
       $temp = $this->select($join);
       return implode($glue, $temp);
    }

    /**
     * Take first x fields
     * @param int $length
     * @return \PerrysLambda\ArrayList
     */
    public function take($length)
    {
        if(!is_int($length) || $length>$this->length())
        {
            $length = $this->length();
        }

        $temp = $this->newInstance();
        $temp->setData(array_slice($this->getData(), 0, $length));
        return $temp;
    }

    /**
     * Skip x fields
     * @param type $offset
     * @return \PerrysLambda\ArrayList
     */
    public function skip($offset)
    {
        if(!is_int($offset) || $offset>=$this->length())
        {
            return null;
        }

        $temp = $this->newInstance();
        $temp->setData(array_slice($this->getData(), $offset));
        return $temp;
    }

    /**
     * Begin order asc
     * @param callable $order
     * @return \PerrysLambda\Sortable
     */
    public function order(callable $order)
    {
        return Sortable::startOrder($this, $order);
    }

    /**
     * Begin order desc
     * @param \PerrysLambda\callable $order
     * @return \PerrysLambda\Sortable
     */
    public function orderDesc(callable $order)
    {
        return Sortable::startOrderDesc($this, $order);
    }


    // ArrayAccess -------------------------------------------------------------


    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        if(is_null($offset))
        {
            $this->add($value);
        }
        else
        {
            $this->set($offset, $value);
        }
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }


    // SeekableIterator --------------------------------------------------------


    public function current()
    {
        return $this->getAt($this->__iterator);
    }

    public function key()
    {
        return $this->getNameAt($this->__iterator);
    }

    public function next()
    {
        $this->__iterator++;
    }

    public function rewind()
    {
        $this->__iterator=0;
    }

    public function valid()
    {
        return $this->key()!==null && $this->exists($this->key());
    }

    public function seek($position)
    {
        $this->__iterator = $position;
    }

}
