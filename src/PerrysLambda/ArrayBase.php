<?php

namespace PerrysLambda;

/**
 * Base class for array-like types
 */
abstract class ArrayBase extends Property implements \ArrayAccess, \SeekableIterator
{

    /**
     * Iternator index
     * @var int
     */
    protected $__iteratorindex;

    /**
     * Caches data keys
     * @var array
     */
    protected $__keycache = null;

    /**
     * Class type of child items
     * @var string
     */
    protected $__fieldtype;

    /**
     * Autoconvert new fields to $this->__fieldtype
     * @var boolean
     */
    protected $__convertfield;

    /**
     * Fieldvalue converters
     * @var array
     */
    protected $__converters;

    /**
     * Fieldvalue validators
     * @var array
     */
    protected $__validators;


    /**
     * Constructor
     * @param \Iterator $iterator
     * @param string $fieldtype
     * @param boolean $convertfield
     * @throws InvalidTypeException
     */
    public function __construct($data=array(), $fieldtype=null, $convertfield=true)
    {
        if(is_string($fieldtype) && !class_exists($fieldtype))
        {
            throw new InvalidTypeException("Invalid fieldtype: ".$fieldtype);
        }
        elseif(!is_string($fieldtype))
        {
            $fieldtype=null;
        }

        $this->__iteratorindex = 0;
        $this->__convertfield = ($convertfield===true);
        $this->__fieldtype = $fieldtype;
        $this->__converters = array();
        $this->__validators = array();

        if(is_array($data))
        {
            parent::__construct($data);
        }
        else
        {
            parent::__construct(array());
        }
    }

    /**
     * Get current classname as string
     * @return string
     */
    protected function getClassName()
    {
        return get_called_class();
    }

    /**
     * Get child classname as string
     * Used for ->groupBy()
     * Default is \PerrysLambda\ObjectArray
     * @return string
     */
    protected function getItemClassName()
    {
        $type = null;
        if(!is_null($this->__fieldtype)/* && is_subclass_of($this->__fieldtype, __CLASS__)*/)
        {
            $type = $this->__fieldtype;
        }
        return $type;
    }

    /**
     * Creates new instance of current class type
     * Expect a subclass of \PerrysLambda\ArrayBase
     * @return \PerrysLambda\ArrayBase
     */
    protected function newInstance()
    {
        $class = $this->getClassName();
        $o = new $class(null, $this->getItemClassName(), $this->__convertfield);
        return $o;
    }

    /**
     * Create new instance of current child class type
     * Used for ->groupBy()
     * Expect a subclass of \PerrysLambda\ArrayBase
     * @return \PerrysLambda\ArrayBase
     */
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
        if($i<$this->lengthCached())
        {
            return $fields[$i];
        }
        return null;
    }

    /**
     * Index of element
     * -1 = element not found
     * @param mixed $value
     * @return int
     */
    public function indexOfValue($value)
    {
        $i = array_search($value, $this->__data, true);
        if($i===false)
        {
            return -1;
        }
        return $i;
    }

    /**
     * Index of key
     * @param mixed $key
     * @return int
     */
    public function indexOfKey($key)
    {
        $i = array_search($key, $this->getNames(), true);
        if($i===false)
        {
            return -1;
        }
        return $i;
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
        if($this->__convertfield===true && !is_null($this->getItemClassName()) &&
            !is_a($field, $this->getItemClassName()))
        {
            $class = $this->getItemClassName();
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
     * Get count of fields currently loaded from data source
     * @return int
     */
    public function lengthCached()
    {
        return count($this->__data);
    }

    /**
     * Read datasource to end and get count of fields
     * @return int
     */
    public function length()
    {
        return $this->lengthCached();
    }

    /**
     * Check for field by its name
     * @param mixed $field
     * @return bool
     */
    public function exists($field)
    {
        return is_array($this->__data) && array_key_exists($field, $this->__data);
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

    /**
     * Validate all fields
     * @return array
     */
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
    public function &getAt($i, $default=null)
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
    public function &get($field, $default=null, $autoset=false)
    {
        if(isset($this->__converters[$field]))
        {
            $var = array_key_exists($field, $this->__data) ? $this->__data[$field] : null;
            $temp = $this->__converters[$field]->convert($var, $this);
            return $temp;
        }
        elseif(array_key_exists($field, $this->__data))
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
        $this->removeKey($field);
        return $this;
    }

    /**
     * Remove field by name
     * @param mixed $field
     * @return \PerrysLambda\ArrayList
     */
    public function removeKey($field)
    {
        if(array_key_exists($field, $this->__data))
        {
            unset($this->__data[$field]);
        }
        return $this;
    }

    /**
     * Remove field by its value
     * @param mixed $value
     * @return \PerrysLambda\ArrayList
     */
    public function removeValue($value)
    {
        $i = $this->indexOfValue($value);
        if($i>=0)
        {
            $this->removeKey($i);
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
     * Get first item matching to callable
     * @param callable $where
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function whereFirst(callable $where)
    {
        foreach($this as $record)
        {
            if(call_user_func($where, $record))
            {
                return $record;
            }
        }
        throw new \OutOfBoundsException();
    }

    /**
     * Get first item matching to callable or default
     * @param callable $where
     * @param mixed $default
     * @return mixed
     */
    public function whereFirstOrDefault(callable $where, $default=null)
    {
        foreach($this as $record)
        {
            if(call_user_func($where, $record))
            {
                return $record;
            }
        }
        return $default;
    }

    /**
     * Group fields by condition
     * @param callable $group
     * @return \PerrysLambda\ArrayList
     */
    public function groupBy(callable $group)
    {
        $c = $this->getItemClassName();
        $result = new $c(null, $c, true);
        foreach($this as $record)
        {
            $key = call_user_func($group, $record);
            $result->get($key, $this->newItemInstance(), true)->add($record);
        }
        return $result;
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
     * Check for all fields by condition
     * @param callable $where
     * @return bool
     */
    public function all(callable $where)
    {
        foreach($this as $record)
        {
            if(!call_user_func($where, $record))
            {
                return false;
            }
        }
        return true;
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
        foreach($this->__data as $key => &$record)
        {
            call_user_func_array($each, array(&$record, $key));
        }
        unset($record);
        return $this;
    }

    /**
     * Calculates the sum of values from given expression
     * @param callable $sum
     * @return numeric
     */
    public function sum(callable $sum)
    {
        $temp = $this->select($sum);
        return array_sum($temp);
    }

    /**
     * Find the lowest value from given expression
     * @param callable $min
     * @return numeric
     */
    public function min(callable $min)
    {
        $temp = $this->select($min);
        return min($temp);
    }

    /**
     * Find the biggest value from given expression
     * @param callable $max
     * @return numeric
     */
    public function max(callable $max)
    {
        $temp = $this->select($max);
        return max($temp);
    }

    /**
     * Find the average of the values from given expression
     * @param callable $avg
     * @return numeric
     */
    public function avg(callable $avg)
    {
        return ($this->sum($avg)/$this->lengthCached());
    }

    /**
     * Join values from expression to one string
     * @param callable $join
     * @param string $glue
     * @return string
     */
    public function joinString(callable $join, $glue=", ")
    {
       $temp = $this->select($join);
       return implode($glue, $temp);
    }

    /**
     * Get the first row
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function first()
    {
        if($this->lengthCached()>0)
        {
            return $this->getAt(0);
        }
        throw new \OutOfBoundsException();
    }

    /**
     * Get the first row or the default value
     * @param mixed $default
     * @return mixed
     */
    public function firstOrDefault($default=null)
    {
        if($this->lengthCached()>0)
        {
            return $this->getAt(0);
        }
        return $default;
    }

    /**
     * Get the last row
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function last()
    {
        if($this->lengthCached()>0)
        {
            return $this->getAt(($this->lengthCached()-1));
        }
        throw new \OutOfBoundsException();
    }

    /**
     * Get the last row or the default value
     * @param mixed $default
     * @return mixed
     */
    public function lastOrDefault($default=null)
    {
        if($this->lengthCached()>0)
        {
            return $this->getAt(($this->lengthCached()-1));
        }
        return $default;
    }

    /**
     * Get the single row if row count=1
     * If row count != 1 the method will throw a exception
     * @return mixed
     * @throws \LengthException
     */
    public function single()
    {
        if($this->lengthCached()==1)
        {
            return $this->getAt(0);
        }
        throw new \LengthException();
    }

    /**
     * Get the single row if row count=1
     * If row count != 1 the method will return the default value
     * @param mixed $default
     * @return mixed
     */
    public function singleOrDefault($default=null)
    {
        if($this->lengthCached()==1)
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
        if(!is_int($length))
        {
            throw new \InvalidArgumentException();
        }

        if($length>$this->lengthCached())
        {
            $length = $this->lengthCached();
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
        if(!is_int($offset))
        {
            throw new \InvalidArgumentException();
        }

        if($offset>=$this->lengthCached())
        {
            throw new \OutOfBoundsException();
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


    /**
     * \ArrayAccess implementation
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     * \ArrayAccess implementation
     */
    public function &offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * \ArrayAccess implementation
     */
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

    /**
     * \ArrayAccess implementation
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }


    // Generator ---------------------------------------------------------------


    public function &generator()
    {
        foreach($this->__data as $key => &$record)
        {
            yield $key => $record;
        }
        unset($record);
    }


    // SeekableIterator --------------------------------------------------------


    /**
     * \SeekableIterator implementation
     */
    public function current()
    {
        return $this->getAt($this->__iteratorindex);
    }

    /**
     * \SeekableIterator implementation
     */
    public function key()
    {
        return $this->getNameAt($this->__iteratorindex);
    }

    /**
     * \SeekableIterator implementation
     */
    public function next()
    {
        $this->__iteratorindex++;
    }

    /**
     * \SeekableIterator implementation
     */
    public function rewind()
    {
        $this->__iteratorindex = 0;
    }

    /**
     * \SeekableIterator implementation
     */
    public function valid()
    {
        return $this->key()!==null && $this->exists($this->key());
    }

    /**
     * \SeekableIterator implementation
     */
    public function seek($position)
    {
        $this->__iteratorindex = $position;
    }


}
