<?php

declare(strict_types=1);

/*
 * 
 *  ____                          _       _                
 * / ___|   _   _   _ __    ___  | |__   (_)  _ __     ___ 
 * \___ \  | | | | | '_ \  / __| | '_ \  | | | '_ \   / _ \
 *  ___) | | |_| | | | | | \__ \ | | | | | | | | | | |  __/
 * |____/   \__,_| |_| |_| |___/ |_| |_| |_| |_| |_|  \___|
 *                                                               
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author MaoMaoPE Team
 * @link https://github.com/MaoMaoPE/Sunshine
 *
 * 
*/

namespace pocketmine\utils;

use pocketmine\level\generator\biome\Biome;

class ChunkNetworkConverter {

    /** @var float[][]|null */
    private static $GAUSSIAN_KERNEL = null;
    private const SMOOTH_SIZE = 2;

    private static function generateKernel() {
        if (self::$GAUSSIAN_KERNEL !== null) {
            return;
        }

        self::$GAUSSIAN_KERNEL = [];

        $bellSize = 1 / self::SMOOTH_SIZE;
        $bellHeight = 2 * self::SMOOTH_SIZE;

        for ($sx = -self::SMOOTH_SIZE; $sx <= self::SMOOTH_SIZE; ++$sx) {
            self::$GAUSSIAN_KERNEL[$sx + self::SMOOTH_SIZE] = [];

            for ($sz = -self::SMOOTH_SIZE; $sz <= self::SMOOTH_SIZE; ++$sz) {
                $bx = $bellSize * $sx;
                $bz = $bellSize * $sz;
                self::$GAUSSIAN_KERNEL[$sx + self::SMOOTH_SIZE][$sz + self::SMOOTH_SIZE] =
                    $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
            }
        }
    }

    public static function convertToP91(string $binary): string {
        self::generateKernel();

        $offset = 0;
        $subChunkCount = ord($binary[$offset++]);

        $subChunks = [];
        for ($y = 0; $y < $subChunkCount; ++$y) {
            $version = ord($binary[$offset++]);
            $ids = substr($binary, $offset, 4096);
            $offset += 4096;
            $data = substr($binary, $offset, 2048);
            $offset += 2048;
            $skyLight = substr($binary, $offset, 2048);
            $offset += 2048;
            $blockLight = substr($binary, $offset, 2048);
            $offset += 2048;
            $subChunks[$y] = [
                'ids' => $ids,
                'data' => $data,
                'skyLight' => $skyLight,
                'blockLight' => $blockLight,
            ];
        }

        $fullIds = '';
        $fullData = '';
        $fullSkyLight = '';
        $fullBlockLight = '';

        for ($x = 0; $x < 16; ++$x) {
            for ($z = 0; $z < 16; ++$z) {
                for ($y = 0; $y < 8; ++$y) {
                    if (isset($subChunks[$y])) {
                        $sub = $subChunks[$y];
                        $fullIds .= substr($sub['ids'], ($x << 8) | ($z << 4), 16);
                        $fullData .= substr($sub['data'], ($x << 7) | ($z << 3), 8);
                        $fullSkyLight .= substr($sub['skyLight'], ($x << 7) | ($z << 3), 8);
                        $fullBlockLight .= substr($sub['blockLight'], ($x << 7) | ($z << 3), 8);
                    } else {
                        $fullIds .= str_repeat("\x00", 16);
                        $fullData .= str_repeat("\x00", 8);
                        $fullSkyLight .= str_repeat("\xff", 8);
                        $fullBlockLight .= str_repeat("\x00", 8);
                    }
                }
            }
        }

        $heightMapStr = '';
        for ($i = 0; $i < 256; ++$i) {
            $val = unpack('v', substr($binary, $offset, 2))[1];
            $offset += 2;
            if ($val > 127) $val = 127;
            $heightMapStr .= chr($val);
        }

        $biomeIds = substr($binary, $offset, 256);
        $offset += 256;

        $biomes = [];
        for ($i = 0; $i < 256; ++$i) {
            $x = $i & 0x0f;
            $z = $i >> 4;
            $biomes[$x][$z] = Biome::getBiome(ord($biomeIds[$i]));
        }

        $biomeColorsStr = '';
        for ($i = 0; $i < 256; ++$i) {
            $x = $i & 0x0f;
            $z = $i >> 4;

            $colorR = 0.0;
            $colorG = 0.0;
            $colorB = 0.0;
            $weightSum = 0.0;

            for ($sx = -self::SMOOTH_SIZE; $sx <= self::SMOOTH_SIZE; ++$sx) {
                for ($sz = -self::SMOOTH_SIZE; $sz <= self::SMOOTH_SIZE; ++$sz) {
                    $nx = $x + $sx;
                    $nz = $z + $sz;
                    if ($nx < 0) $nx = 0;
                    if ($nx > 15) $nx = 15;
                    if ($nz < 0) $nz = 0;
                    if ($nz > 15) $nz = 15;

                    $weight = self::$GAUSSIAN_KERNEL[$sx + self::SMOOTH_SIZE][$sz + self::SMOOTH_SIZE];

                    $biome = $biomes[$nx][$nz];
                    $bColor = Biome::generateBiomeColor($biome->getTemperature(), $biome->getRainfall());

                    $r = ($bColor >> 16) & 0xFF;
                    $g = ($bColor >> 8) & 0xFF;
                    $b = $bColor & 0xFF;

                    $colorR += ($r * $r) * $weight;
                    $colorG += ($g * $g) * $weight;
                    $colorB += ($b * $b) * $weight;
                    $weightSum += $weight;
                }
            }

            $finalR = (int) sqrt($colorR / $weightSum);
            $finalG = (int) sqrt($colorG / $weightSum);
            $finalB = (int) sqrt($colorB / $weightSum);

            $finalColor = (0xFF << 24) | ($finalR << 16) | ($finalG << 8) | $finalB;
            $biomeColorsStr .= pack('N', $finalColor);
        }

        $borderBlockCount = ord($binary[$offset++]);

        $stream = new BinaryStream(substr($binary, $offset));
        $extraCount = $stream->getVarInt();

        $extraData = Binary::writeInt($extraCount);
        for ($i = 0; $i < $extraCount; ++$i) {
            $key = $stream->getVarInt();
            $value = $stream->getLShort();
            $extraData .= Binary::writeInt($key);
            $extraData .= Binary::writeShort($value);
        }

        $tilesData = $stream->get(true);

        return $fullIds .
               $fullData .
               $fullSkyLight .
               $fullBlockLight .
               $heightMapStr .
               $biomeColorsStr .
               $extraData .
               $tilesData;
    }
}