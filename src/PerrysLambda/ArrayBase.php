<?php

namespace PerrysLambda;

use PerrysLambda\Exception\InvalidDataException;
use PerrysLambda\Exception\InvalidKeyException;
use PerrysLambda\Exception\InvalidValueException;
use PerrysLambda\IArrayable;
use PerrysLambda\IBaseConverter;
use PerrysLambda\IListConverter;
use PerrysLambda\IItemConverter;
use PerrysLambda\Exception\PerrysLambdaException;


/**
 * Base class for array-like types
 */
abstract class ArrayBase extends Property
        implements \ArrayAccess, \SeekableIterator, IArrayable, ICloneable, \Countable
{

    /**
     * Iternator index
     * @var int
     */
    protected $__iteratorindex;

    /**
     * Is key cache invalidated
     * @var bool
     */
    protected $__keycacheinvalid = false;

    /**
     * Caches data keys
     * @var array
     */
    protected $__keycache = null;

    /**
     * Caches data keys by index
     * @var array
     */
    protected $__keycacheindex = null;

    /**
     * Record and field converter
     * @var \PerrysLambda\Converter\IBaseConverter
     */
    protected $__converter;


    /**
     * Constructor
     * @param array/IBaseConverter $data
     */
    public function __construct($data=array())
    {
        $this->__iteratorindex = 0;
        $this->__converter = null;

        if($data instanceof IListConverter)
        {
            parent::__construct(array());
            $this->__converter = $data;
            $this->__converter->importInto($this);
        }
        elseif(is_array($data))
        {
            parent::__construct($data);
        }
        else
        {
            throw new InvalidValueException("Parameter 1 must be a IListConverter or array");
        }
    }

    /**
     * Create a new converter instance
     * @return \PerrysLambda\IBaseConverter
     */
    protected function newConverterInstance()
    {
        if($this->__converter instanceof IBaseConverter)
        {
            return $this->__converter->newInstance();
        }
        return null;
    }

    /**
     * Creates new instance of current class type
     * Expect a subclass of \PerrysLambda\ArrayBase
     * @return \PerrysLambda\ArrayBase
     */
    public function newInstance($data=null)
    {
        $class = $this->getClassName();
        $conv = $this->newConverterInstance();

        if(is_null($data))
        {
            $data = array();
        }

        if($conv instanceof IListConverter)
        {
            $data = $conv;
        }

        $o = new $class($data);
        if($conv instanceof IBaseConverter)
        {
            $o->setConverter($conv);
        }

        return $o;
    }

    /**
     * Clone this object
     * @return \PerrysLambda\ArrayBase
     */
    public function copy()
    {
        $temp = $this->newInstance();
        $temp->setData($this->getData());
        return $temp;
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

        if(!$this->getIsAllKeysValid($data))
        {
            throw new InvalidKeyException();
        }

        if(!$this->getIsAllValuesValid($data))
        {
            throw new InvalidValueException();
        }

        $this->__data = $data;
        $this->invalidateKeycache();
    }

    /**
     * Set a converter
     * @param \PerrysLambda\IBaseConverter $conv
     */
    public function setConverter(IBaseConverter $conv)
    {
        $this->__converter = $conv;
    }

    /**
     * Applies given fields to this object if field does not exist
     * @param array $defaults
     */
    public function applyDefaults(array $defaults)
    {
        foreach($defaults as $dkey => $dvalue)
        {
            if(!$this->exists($dkey))
            {
                $this[$dkey] = $dvalue;
            }
        }
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
     * Is keycache invalidated
     * @return bool
     */
    protected function isKeycacheInvalid()
    {
        return is_null($this->__keycache) || $this->__keycacheinvalid === true;
    }

    /**
     * Invalidate keycache
     */
    protected function invalidateKeycache()
    {
        $this->__keycacheinvalid = true;
        $this->__keycache = null;
        $this->__keycacheindex = null;
    }

    /**
     * Regenerate array key cache
     */
    protected function regenerateKeyCache()
    {
        if(is_array($this->__data))
        {
            $i=0;
            $this->__keycache = array();
            $this->__keycacheindex = array();
            foreach($this->__data as $key => $value)
            {
                $this->__keycache[$key] = $i;
                $this->__keycacheindex[$i] = $key;
                $i++;
            }
        }
        else
        {
            $this->__keycache = null;
            $this->__keycacheindex = null;
        }

        $this->__keycacheinvalid = false;
    }

    /**
     * Get all field names
     * @return array
     */
    public function getNames()
    {
        if($this->isKeycacheInvalid())
        {
            $this->regenerateKeyCache();
        }
        if(is_array($this->__keycacheindex))
        {
            return $this->__keycacheindex;
        }
        return array();
    }

    /**
     * Get all field names
     * @return array
     */
    public function getKeys()
    {
        return $this->getNames();
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
     * Get field name by its value
     * NULL if not exist
     * @param mixed $value
     * @return mixed
     */
    public function getNameByValue($value)
    {
        $i = array_search($value, $this->__data, true);
        if($i===false)
        {
            return null;
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
        if($this->isKeycacheInvalid())
        {
            $this->regenerateKeyCache();
        }

        if(isset($this->__keycache[$key]))
        {
            return $this->__keycache[$key];
        }

        return -1;
    }

    /**
     * Index of element
     * -1 = element not found
     * @param mixed $value
     * @return int
     */
    public function indexOfValue($value)
    {
        $name = $this->getNameByValue($value);
        if(!is_null($name))
        {
            return $this->indexOfKey($name);
        }
        return -1;
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
     * Serialize object data into array
     */
    abstract public function serialize();

    /**
     * Serialize object data into generator
     * @throws PerrysLambdaException
     */
    public function serializeGenerator()
    {
        throw new PerrysLambdaException("Not implemented");
    }

    /**
     * Get count of fields currently loaded from data source
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
        return $this->indexOfKey($field)>=0;
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
        if($this->exists($field))
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
        return new ScalarProperty($default);
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
     * @param boolean $skipconverter
     * @return \PerrysLambda\ArrayList
     * @throws InvalidKeyException
     * @throws InvalidValueException
     */
    public function set($field, $value, $skipconverter=false)
    {
        $tempfield = $field;
        $tempvalue = $value;

        $insert = true;
        if($skipconverter===false && $this->__converter !== null)
        {
            if($this->__converter instanceof IListConverter && $this->__converter->isItemConverterExist())
            {
                $insert = $this->__converter->getItemConverter()->deserializeAll($tempvalue, $tempfield);
            }
            else if($this->__converter instanceof IItemConverter)
            {
                $insert = $this->__converter->deserializeAll($tempvalue, $tempfield);
            }
        }

        if(!$this->getIsValidKey($tempfield))
        {
            throw new InvalidKeyException();
        }

        if(!$this->getIsValidValue($tempvalue))
        {
            throw new InvalidValueException();
        }

        if($insert===true)
        {
            $this->__data[$tempfield] = $tempvalue;
            $this->invalidateKeycache();
        }

        return $this;
    }

    /**
     * Add field
     * @param mixed $value
     * @param boolean $skipconverter
     * @return \PerrysLambda\ArrayList
     */
    public function add($value, $skipconverter=false)
    {
        $foo = null;
        $tempvalue = $value;

        $insert = true;
        if($skipconverter===false && $this->__converter !== null)
        {
            if($this->__converter instanceof IListConverter && $this->__converter->isItemConverterExist())
            {
                $insert = $this->__converter->getItemConverter()->deserializeAll($tempvalue, $foo);
            }
            else if($this->__converter instanceof IItemConverter)
            {
                $insert = $this->__converter->deserializeAll($tempvalue, $foo);
            }
        }

        if($insert===true)
        {
            $this->__data[] = $tempvalue;
            $this->invalidateKeycache();
        }

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
     * Remove field by its value
     * @param mixed $value
     * @return \PerrysLambda\ArrayList
     */
    public function removeValue($value)
    {
        $i = $this->getNameByValue($value);
        if($i>=0)
        {
            $this->removeKey($i);
        }
        return $this;
    }

    /**
     * Remove field by name
     * @param mixed $field
     * @return \PerrysLambda\ArrayList
     */
    public function removeKey($field)
    {
        if($this->exists($field))
        {
            unset($this->__data[$field]);
            $this->invalidateKeycache();
        }
        return $this;
    }

    /**
     * Validate all field keys
     * @param array $data
     * @return boolean
     */
    public function getIsAllKeysValid(array $data)
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
    public function getIsAllValuesValid(array $data)
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
     * @param callable|array $where
     * @return \PerrysLambda\ArrayList
     */
    public function where($where)
    {
        $where = LambdaUtils::toConditionCallable($where);
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
     * @param callable|array $where
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function whereFirst($where)
    {
        $where = LambdaUtils::toConditionCallable($where);
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
     * @param callable|array $where
     * @param mixed $default
     * @return mixed
     */
    public function whereFirstOrDefault($where, $default=null)
    {
        $where = LambdaUtils::toConditionCallable($where);
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
     * @param callable|string|null $group
     * @return \PerrysLambda\ArrayBase
     */
    public function groupBy($group=null)
    {
        $group = LambdaUtils::toSelectCallable($group);

        $result = new ObjectArray(array());
        if($this instanceof ObjectArray)
        {
            $result = $this->newInstance();
        }

        foreach($this as $record)
        {
            $key = call_user_func($group, $record);

            $data = array();
            if($this->__converter instanceof IListConverter)
            {
                $data = $this->newConverterInstance();
            }
            $newitemtype = new ArrayList($data);

            $result->get($key, $newitemtype, true)->add($record);
        }

        return $result;
    }

    /**
     * filter duplicate field by condition
     * @param callable|string|null $distinct
     * @return \PerrysLambda\ArrayBase
     */
    public function distinct($distinct=null)
    {
        $distinct = LambdaUtils::toSelectCallable($distinct);

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
     * Intersection to another object
     * @param \PerrysLambda\ArrayBase $comparedata
     * @return \PerrysLambda\ArrayBase
     */
    public function intersect(ArrayBase $comparedata)
    {
        $cmpvalue = function($v1, $v2) { return $v1==$v2 ? 0 : ($v1>$v2 ? 1 : -1); };
        $collection = $this->newInstance();

        $temp = array_uintersect($this->getData(), $comparedata->getData(), $cmpvalue);

        $collection->setData($temp);
        unset($temp);
        return $collection;
    }

    /**
     * Difference to another object
     * @param \PerrysLambda\ArrayBase $comparedata
     * @return \PerrysLambda\ArrayBase
     */
    public function except(ArrayBase $comparedata)
    {
        $cmpvalue = function($v1, $v2) { return $v1==$v2 ? 0 : ($v1>$v2 ? 1 : -1); };
        $collection = $this->newInstance();

        $temp1 = array_udiff($this->getData(), $comparedata->getData(), $cmpvalue);
        $temp2 = array_udiff($comparedata->getData(), $this->getData(), $cmpvalue);
        $temp = array_merge($temp1, $temp2);
        unset($temp1, $temp2);

        $collection->setData($temp);
        unset($temp);
        return $collection;
    }

    /**
     * Check for any field by condition
     * @param callable|array $where
     * @return bool
     */
    public function any($where)
    {
        $where = LambdaUtils::toConditionCallable($where);
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
     * @param callable|array $where
     * @return bool
     */
    public function all($where)
    {
        $where = LambdaUtils::toConditionCallable($where);
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
     * @param callable|string|null $select
     * @return \PerrysLambda\ArrayList
     */
    public function select($select=null)
    {
        $select = LambdaUtils::toSelectCallable($select);

        $result = array();
        foreach($this as $key => $record)
        {
            $result[] = call_user_func($select, $record, $key);
        }
        return new ArrayList($result);
    }

    /**
     * Select a field and merge all arrays into one
     * @param callable|string|null $select
     * @return \PerrysLambda\ArrayList
     */
    public function selectMany($select=null)
    {
        $select = LambdaUtils::toSelectCallable($select);

        $result = array();
        foreach($this as $key => $record)
        {
            $temp = call_user_func($select, $record, $key);
            if(is_array($temp) || $temp instanceof \Iterator)
            {
                foreach($temp as $tempitem)
                {
                    $result[] = $tempitem;
                }
            }
            else
            {
                $result[] = $temp;
            }
        }
        return new ArrayList($result);
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
     * @param callable|string|null $sum
     * @return numeric
     */
    public function sum($sum=null)
    {
        $sum = LambdaUtils::toSelectCallable($sum);
        $temp = $this->select($sum)->toArray();
        return array_sum($temp);
    }

    /**
     * Find the lowest value from given expression
     * @param callable|string|null $min
     * @return numeric
     */
    public function min($min=null)
    {
        $min = LambdaUtils::toSelectCallable($min);
        $temp = $this->select($min)->toArray();
        return min($temp);
    }

    /**
     * Find the biggest value from given expression
     * @param callable|string|null $max
     * @return numeric
     */
    public function max($max=null)
    {
        $max = LambdaUtils::toSelectCallable($max);
        $temp = $this->select($max)->toArray();
        return max($temp);
    }

    /**
     * Find the average of the values from given expression
     * @param callable|string|null $avg
     * @return numeric
     */
    public function avg($avg=null)
    {
        $avg = LambdaUtils::toSelectCallable($avg);
        return ($this->sum($avg)/$this->length());
    }

    /**
     * Join values from expression to one string
     * @param callable|string|null $join
     * @param string $glue
     * @return string
     */
    public function joinString($join=null, $glue=", ")
    {
        $join = LambdaUtils::toSelectCallable($join);
        $temp = $this->select($join)->toArray();
        return implode($glue, $temp);
    }

    /**
     * Get the first row
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function first()
    {
        if($this->length()>0)
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
        if($this->length()>0)
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
        if($this->length()>0)
        {
            return $this->getAt(($this->length()-1));
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
        if($this->length()>0)
        {
            return $this->getAt(($this->length()-1));
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
        if($this->length()==1)
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
        if(!is_int($length))
        {
            throw new \InvalidArgumentException();
        }

        if($length>=0 && $length>$this->length())
        {
            $length = $this->length();
        }
        elseif($length<0 && $length<(0-$this->length()))
        {
            $length = 0-$this->length();
        }

        $temp = $this->newInstance();

        if($length>=0)
        {
            $temp->setData(array_slice($this->getData(), 0, $length));
        }
        else
        {
            $temp->setData(array_slice($this->getData(), $length));
        }

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

        if($offset<0 || $offset>=$this->length())
        {
            throw new \OutOfBoundsException();
        }

        $temp = $this->newInstance();
        $temp->setData(array_slice($this->getData(), $offset));
        return $temp;
    }

    /**
     * Begin order asc
     * @param callable|string|null $order
     * @return \PerrysLambda\Sortable
     */
    public function order($order)
    {
        return Sortable::startOrder($this, $order);
    }

    /**
     * Begin order desc
     * @param callable|string|null $order
     * @return \PerrysLambda\Sortable
     */
    public function orderDesc($order)
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
        $this->removeKey($offset);
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


    // Countable ---------------------------------------------------------------


    /**
     * \Countable implementation
     */
    public function count()
    {
        return $this->length();
    }


    // String ------------------------------------------------------------------


    /**
     * Convert object content to string (php serialize)
     * @return string
     */
    public function toString()
    {
        return serialize($this->getData());
    }


    /**
     * Convert object content to string
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }


}
