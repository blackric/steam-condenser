<?php
/**
 * This code is free software; you can redistribute it and/or modify it under
 * the terms of the new BSD License.
 *
 * Copyright (c) 2008-2011, Sebastian Staudt
 *
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD Lic$
 */

require_once STEAM_CONDENSER_PATH . 'exceptions/BufferUnderflowException.php';

/**
 * This class represents a byte buffer which helps reading byte-wise data from
 * a string which acts as a raw byte array.
 *
 * @author  Sebastian Staudt
 * @package steam-condenser
 */
class ByteBuffer
{
	/**
	 * @var string
	 */
	private $byteArray;

	/**
	 * @var int
	 */
	private $capacity;

	/**
	 * @var int
	 */
	private $limit;

	/**
	 * @var int
	 */
	private $position;

    /**
     * Allocates a string with the specified amount of bytes wrapped into a
     * byte buffer object
     *
     * @param  $length The size of the byte buffer
     * @return ByteBuffer The new byte buffer object
     */
	public static function allocate($length)
	{
		return new ByteBuffer(str_repeat("\0", $length));
	}

    /**
     * Wraps an existing string into a byte buffer object
     *
     * @param  $byteArray The string to encapsulate into the
     *         byte buffer
     * @return ByteBuffer The new ByteBuffer object
     */
	public static function wrap($byteArray)
	{
		return new ByteBuffer($byteArray);
	}

	/**
     * Creates a new byte buffer instance
     *
	 * @param string $byteArray The string to encapsulate into the
     *        byte buffer
	 */
	public function __construct($byteArray)
	{
		$this->byteArray = $byteArray;
		$this->capacity = strlen($byteArray);
		$this->limit = $this->capacity;
		$this->position = 0;
	}

    /**
     * Returns the string wrapped into this byte buffer object
     *
     * @return string The string encapsulated in this byte buffer
     */
	public function _array()
	{
		return $this->byteArray;
	}

    /**
     * Clears the state of this byte buffer object
     *
     * Sets the <var>limit</var> to the <var>capacity</var> of the buffer and
     * resets the <var>position</var>.
     */
	public function clear()
	{
		$this->limit = $this->capacity;
		$this->position = 0;
	}

    /**
     * Sets the <var>limit</var> to the current <var>position</var> before
     * resetting the <var>position</var>.
     *
     * @return ByteBuffer This byte buffer
     */
    public function flip()
    {
        $this->limit = $this->position;
        $this->position = 0;

        return $this;
    }

	/**
     * Reads the specified amount of bytes from the current <var>position</var>
     * of the byte buffer
     *
     * @param int $length The amount of bytes to read from the buffer or
     *        <var>null</var> if everything up to <var>limit</var> should be
     *        read
     * @return string The data read from the buffer
	 */
	public function get($length = null)
	{
		if($length === null)
		{
			$length = $this->limit - $this->position;
		}
		elseif($length > $this->remaining())
		{
			throw new BufferUnderFlowException();
		}

		$data = substr($this->byteArray, $this->position, $length);
		$this->position += $length;

		return $data;
	}

	/**
     * Reads a single byte from the buffer
     *
	 * @return int The byte at the current position
	 */
	public function getByte()
	{
		return ord($this->get(1));
	}

	/**
     * Reads a floating point number from the buffer
     *
     * @return float The floating point number, i.e. four bytes converted to a
     *         <var>float</var> read at the current position
	 */
	public function getFloat()
	{
		$data = unpack("f", $this->get(4));
		return $data[1];
	}

	/**
     * Reads a long integer from the buffer
     *
     * @return long The long integer, i.e. four bytes converted to a
     *         <var>long</var> read at the current position
	 */
	public function getLong()
	{
		$data = unpack("l", $this->get(4));
		return $data[1];
	}

	/**
     * Reads a short integer from the buffer
     *
     * @return short The short integer, i.e. two bytes converted to a
     *         <var>short</var> read at the current position
	 */
	public function getShort()
	{
		$data = unpack("v", $this->get(2));
		return $data[1];
	}

	/**
	 * @return String
	 */
	public function getString()
	{
		$zeroByteIndex = strpos($this->byteArray, "\0", $this->position);
		if($zeroByteIndex === false)
		{
			return "";
		}
		else
		{
			$dataString = $this->get($zeroByteIndex - $this->position);
			$this->position ++;
			return $dataString;
		}
	}

	/**
     * Reads an unsigned long integer from the buffer
     *
     * @return long The long integer, i.e. four bytes converted to an
     *         unsigned <var>float</var> read at the current position
	 */
	public function getUnsignedLong()
	{
		$data = unpack("V", $this->get(4));
		return $data[1];
	}

    /**
     * Sets or returns the <var>limit</var> of the buffer
     *
     * @param int $newLimit Sets the buffer's <var>limit</var> to this value
     * @return int If no new <var>limit</var> value is given, the current value
     */
	public function limit($newLimit = null)
	{
		if($newLimit == null)
		{
			return $this->limit;
		}
		else
		{
			$this->limit = $newLimit;
		}
	}

    /**
     * Returns the current <var>position</var> of the buffer
     *
     * @return int The current <var>position</var> of the buffer
     */
	public function position()
	{
		return $this->position;
	}

    /**
     * Replaces the contents of the byte buffer with the bytes from the source
     * string beginning at the current <var>position</var>
     *
     * @param string $sourceByteArray The string to take bytes from
     * @return ByteBuffer This byte buffer
     */
	public function put($sourceByteArray)
	{
		$newPosition = min($this->remaining(), strlen($sourceByteArray));
		$this->byteArray = substr_replace($this->byteArray, $sourceByteArray, $this->position, $newPosition);
		$this->position = $newPosition;

		return $this;
	}

    /**
     * Returns the remaining number of byte from the current
     * <var>position</var> to the <var>limit</var> of the buffer
     *
     * @return int The number of bytes remaining in the buffer
     */
	public function remaining()
	{
		return $this->limit - $this->position;
	}

    /**
     * Resets the <var>position</var> of this buffer
     *
     * @return ByteBuffer This byte buffer
     */
	public function rewind()
	{
		$this->position = 0;

		return $this;
	}
}
?>
