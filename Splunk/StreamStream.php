<?php
/**
 * Copyright 2013 Splunk, Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"): you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * A stream wrapper implementation that reads from another underlying stream.
 * 
 * This is useful to pass a stream as an input to a PHP API that only accepts
 * URIs for its inputs, such as XMLReader.
 * 
 * @package Splunk
 * @internal
 */
class Splunk_StreamStream
{
    // === Registration ===
    
    private static $registeredStreams = array();
    
    /**
     * Makes the specified stream accessible by URI.
     * 
     * @param resource $stream      A stream created by fopen().
     * @return string               A URI that the provided stream can be
     *                              opened with via another fopen() call.
     *                              For example "splunkstream://5016d2d5c9d90".
     */
    public static function createUriForStream($stream)
    {
        $streamId = uniqid();
        Splunk_StreamStream::$registeredStreams[$streamId] = $stream;
        return 'splunkstream://' . $streamId;
    }
    
    /**
     * @return integer              The number of streams that have been
     *                              registered with createUriForStream()
     *                              that haven't been closed.
     */
    public static function getOpenStreamCount()
    {
        return count(Splunk_StreamStream::$registeredStreams);
    }
    
    // === Stream ===
    
    private $stream;
    private $streamId;
    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $streamId = $url['host'];
        
        if (array_key_exists($streamId, Splunk_StreamStream::$registeredStreams))
        {
            $this->stream = Splunk_StreamStream::$registeredStreams[$streamId];
            $this->streamId = $streamId;
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function stream_read($count)
    {
        return fread($this->stream, $count);
    }
    
    public function stream_write($data)
    {
        return fwrite($this->stream, $data);
    }
    
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->stream, $offset, $whence);
    }
    
    public function stream_tell()
    {
        return ftell($this->stream);
    }
    
    public function stream_eof()
    {
        return feof($this->stream);
    }
    
    public function stream_stat()
    {
        return fstat($this->stream);
    }
    
    public function stream_flush()
    {
        return fflush($this->stream);
    }
    
    public function stream_close()
    {
        fclose($this->stream);
        
        // (When called from a shutdown hook, sometimes this variable no
        //  longer exists when this method is called.)
        if (isset(Splunk_StreamStream::$registeredStreams))
        {
            unset(Splunk_StreamStream::$registeredStreams[$this->streamId]);
        }
    }
    
    public function url_stat($path, $flags)
    {
        $url = parse_url($path);
        $streamId = $url['host'];
        
        if (array_key_exists($streamId, Splunk_StreamStream::$registeredStreams))
        {
            $stream = Splunk_StreamStream::$registeredStreams[$streamId];
            $statResult = fstat($stream);
            if ($statResult === FALSE)
            {
                /* 
                 * The API for url_stat() always requires a valid (non-FALSE),
                 * result, even though fstat() can return FALSE. The docs say
                 * to set unknown values to "a rational value (usually 0)".
                 *
                 * XMLReader::open() enforces this, printing out a cryptic
                 * "Unable to open source data" error if a FALSE result for
                 * url_stat() is returned.
                 */
                return array(
                    'dev' => 0,
                    'ino' => 0,
                    'mode' => 0,
                    'nlink' => 1,
                    'uid' => 0,
                    'gid' => 0,
                    'rdev' => -1,
                    'size' => 0,
                    'atime' => 0,
                    'mtime' => 0,
                    'ctime' => 0,
                    'blksize' => -1,
                    'blocks' => -1,
                );
            }
            else
            {
                return $statResult;
            }
        }
        else
        {
            return FALSE;
        }
    }
}

stream_wrapper_register('splunkstream', 'Splunk_StreamStream') or 
    die('Could not register protocol.');
