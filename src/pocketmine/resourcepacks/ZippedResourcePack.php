<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 *  _____            _               _____           
 * / ____|          (_)             |  __ \          
 *| |  __  ___ _ __  _ ___ _   _ ___| |__) | __ ___  
 *| | |_ |/ _ \ '_ \| / __| | | / __|  ___/ '__/ _ \ 
 *| |__| |  __/ | | | \__ \ |_| \__ \ |   | | | (_) |
 * \_____|\___|_| |_|_|___/\__, |___/_|   |_|  \___/ 
 *                         __/ |                    
 *                        |___/                     
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

/*
 *Zip材质包加载接口
 *
*/

namespace pocketmine\resourcepacks;


class ZippedResourcePack implements ResourcePack{

	public static function verifyManifest(\stdClass $manifest){
		if(!isset($manifest->format_version) or !isset($manifest->header) or !isset($manifest->modules)){
			return false;
		}
		return
			isset($manifest->header->description) and
			isset($manifest->header->name) and
			isset($manifest->header->uuid) and
			isset($manifest->header->version) and
			count($manifest->header->version) === 3;
	}

	public static function verifyManifestOld(\stdClass $manifest) {
		if (!isset($manifest->header) || !is_object($manifest->header)) {
        	return false;
    	}
		$h = $manifest->header;

		// 跟着AI改的，不知道有没有用 QAQ
		if (!isset($h->pack_id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $h->pack_id)) {
        	return false;
    	}
		if (empty($h->name) || !is_string($h->name)) {
        	return false;
    	}
		if (empty($h->packs_version) || !is_string($h->packs_version) || !preg_match('/^\d+\.\d+\.\d+$/', $h->packs_version)) {
        	return false;
    	}
		if (!isset($h->modules) || !is_array($h->modules) || count($h->modules) === 0) {
        	return false;
    	}
		foreach ($h->modules as $module) {
        	if (!is_object($module)) return false;
        	if (empty($module->uuid) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $module->uuid)) {
            	return false;
        	}
        	if (empty($module->type) || !in_array($module->type, ['resources', 'data'], true)) {
            	return false;
        	}
        	if (isset($module->version) && !is_string($module->version)) {
        	    return false;
        	}
    	}
    	return true;

	}

	/** @var string */
	protected $path;

	/** @var \stdClass */
	protected $manifest;

	/** @var string */
	protected $sha256 = null;

	/** @var resource */
	protected $fileResource;

	public function __construct(string $zipPath){
		$this->path = $zipPath;

		if(!file_exists($zipPath)){
			throw new \InvalidArgumentException("无法打开材质包 $zipPath: 文件夹无法打开");
		}

		$archive = new \ZipArchive();
		if(($openResult = $archive->open($zipPath)) !== true){
			throw new \InvalidStateException("打开 $zipPath 时遇到ZipArchive错误 $openResult");
		}

		if(($manifestData = $archive->getFromName("manifest.json")) === false && ($manifestData = $archive->getFromName("pack_manifest.json")) === false){
			throw new \InvalidStateException("无法加载材质包 $zipPath: 找不到主类");
		}

		$archive->close();

		$manifest = json_decode($manifestData);
		if($manifest == null || (!self::verifyManifest($manifest) && !self::verifyManifestOld($manifest))){
			throw new \InvalidStateException("无法加载材质包 $zipPath: 主类错误或不完整，如果主类没有错误，请检查是否有注释并删除注释");
		}

		$this->manifest = $manifest;

		$this->fileResource = fopen($zipPath, "rb");
	}

	public function getPackName() : string{
		return $this->manifest->header->name;
	}

	public function getPackVersion() : string{
		return implode(".", $this->manifest->header->version);
	}

	public function getPackId() : string{
		return $this->manifest->header->uuid;
	}

	public function getPackSize() : int{
		return filesize($this->path);
	}

	public function getSha256(bool $cached = true) : string{
		if($this->sha256 === null or !$cached){
			$this->sha256 = hash_file("sha256", $this->path, true);
		}
		return $this->sha256;
	}

	public function getPackChunk(int $start, int $length) : string{
		fseek($this->fileResource, $start);
		if(feof($this->fileResource)){
			throw new \RuntimeException("Requested a resource pack chunk with invalid start offset");
		}
		return fread($this->fileResource, $length);
	}
}