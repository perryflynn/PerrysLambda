<?php

namespace PerrysLambda;

abstract class ArrayBase extends Property implements \ArrayAccess, \SeekableIterator
{

    protected $__iterator = 0;
    protected $__keycache = null;
    protected $__fieldtype;
    protected $__convertfield;
    protected $__converters;
    protected $__validators;


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
        $this->__converters = array();
        $this->__validators = array();
        parent::__construct($data);
    }

    protected function getClassName()
    {
        return get_called_class();
    }

    protected function newInstance()
    {
        $class = $this->getClassName();
        return new $class(array(), $this->__fieldtype, $this->__convertfield);
    }

    protected function getItemClassName()
    {
        $type = '\PerrysLambda\ObjectArray';
        if(!is_null($this->__fieldtype) && is_subclass_of($this->__fieldtype, __CLASS__))
        {
            $type = $this->__fieldtype;
        }
        return $type;
    }

    protected function newItemInstance()
    {
        $c = $this->getItemClassName();
        return new $c();
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
     * Set converter for a field
     * @param mixed $field
     * @param \PerrysLambda\Converter\BasicConverter $converter
     */
    public function setFieldConverter($field, \PerrysLambda\Converter\BasicConverter $converter)
    {
        $this->__converters[$field] = $converter;
    }

    /**
     * Add validator for a field
     * @param mixed $field
     * @param \PerrysLambda\Validator\BasicValidator $validator
     */
    public function addFieldValidator($field, \PerrysLambda\Validator\BasicValidator $validator)
    {
        if(!isset($this->__validators[$field]))
        {
            $this->__validators[$field] = array();
        }
        $this->__validators[$field][] = $validator;
    }

    /**
     * Validate field
     * @param mixed $field
     * @return array
     */
    public function isFieldValid($field)
    {
        $result = array();
        if(isset($this->__validators[$field]))
        {
            foreach($this->__validators[$field] as $v)
            {
                if(!$v->validate($field, $this->get($field), $this))
                {
                    $result[] = $v->getMessage();
                }
            }
        }
        return $result;
    }

    public function isValid()
    {
        $result = array();
        $keys = array_keys($this->__validators);
        foreach($keys as $key)
        {
            $result[$key] = $this->isFieldValid($key);
        }
        foreach($this->getNames() as $key)
        {
            if(!isset($result[$key]))
            {
                $result[$key] = array();
            }
        }
        return $result;
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
        if(isset($this->__converters[$field]))
        {
            $var = isset($this->__data[$field]) ? $this->__data[$field] : null;
            return $this->__converters[$field]->convert($var, $this);
        }
        elseif(isset($this->__data[$field]))
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
    abstract public function getIsValidKey($name);

    /**
     * Is field value valid
     * @param mixed $value
     * @return boolean
     */
    abstract public function getIsValidValue($value);


    // Lambda ------------------------------------------------------------------


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

    public function first()
    {
        if($this->length()>0)
        {
            return $this->getAt(0);
        }
        throw new \OutOfBoundsException();
    }

    public function firstOrDefault($default=null)
    {
        if($this->length()>0)
        {
            return $this->getAt(0);
        }
        return $default;
    }

    public function last()
    {
        if($this->length()>0)
        {
            return $this->getAt(($this->length()-1));
        }
        throw new \OutOfBoundsException();
    }

    public function lastOrDefault($default=null)
    {
        if($this->length()>0)
        {
            return $this->getAt(($this->length()-1));
        }
        return $default;
    }

    public function single()
    {
        if($this->length()==1)
        {
            return $this->getAt(0);
        }
        throw new \LengthException();
    }

    public function singleOrDefault($default=null)
    {
        if($this->length()==1)
        {
            return $this->getAt(0);
        }
        return $default;
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

    /**
     * Group fields by condition
     * @param callable $group
     * @return \PerrysLambda\ArrayList
     */
    public function groupBy(callable $group)
    {
        $c = $this->getItemClassName();
        $result = new $c(array(), $c, true);
        foreach($this as $record)
        {
            $key = call_user_func($group, $record);
            $result->get($key, $this->newItemInstance(), true)->add($record);
        }
        return $result;
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
