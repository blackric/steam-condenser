<?php
/** 
 * This code is free software; you can redistribute it and/or modify it under
 * the terms of the new BSD License.
 * 
 * @author Sebastian Staudt
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package Source Condenser (PHP)
 * @subpackage MasterServerQueryResponsePacket
 * @version $Id$
 */

/**
 * Represents a response of a master server.
 * @package Source Condenser (PHP)
 * @subpackage MasterServerQueryRequestPacket
 */
class MasterServerQueryResponsePacket extends SteamPacket
{
	private $serverArray;
	
	public function __construct($data)
	{
		parent::__construct(SteamPacket::MASTER_SERVER_QUERY_REQUEST_HEADER, $data);
		
		if($this->contentData->getByte() != 10)
		{
			throw new PacketFormatException("Master query response is missing additional 0x0A byte.");
		}
		
		do
		{
			$firstOctet = $this->contentData->getByte();
			$secondOctet = $this->contentData->getByte();
			$thirdOctet = $this->contentData->getByte();
			$fourthOctet = $this->contentData->getByte();
			$portNumber = $this->contentData->getShort();
      $portNumber = (($portNumber & 0xFF) << 8) + ($portNumber >> 8);
			
			$this->serverArray[] = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctet:$portNumber";
		}
		while($this->contentData->remaining() > 0);
	}
	
	public function getServers()
	{
		return $this->serverArray;
	}
}
?>