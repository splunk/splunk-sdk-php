<?php
/**
 * Copyright 2012 Splunk, Inc.
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
     */
    public static function createUriForStream($stream)
    {
        $streamId = uniqid();
        Splunk_StreamStream::$registeredStreams[$streamId] = $stream;
        return 'splunkstream://' . $streamId;
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
    
    public function stream_close()
    {
        fclose($this->stream);
        unset(Splunk_StreamStream::$registeredStreams[$this->streamId]);
    }
    
    public function url_stat($path, $flags)
    {
        $url = parse_url($path);
        $streamId = $url['host'];
        
        if (array_key_exists($streamId, Splunk_StreamStream::$registeredStreams))
        {
            $stream = Splunk_StreamStream::$registeredStreams[$streamId];
            return fstat($stream);
        }
        else
        {
            return FALSE;
        }
    }
}

stream_wrapper_register('splunkstream', 'Splunk_StreamStream') or 
    die('Could not register protocol.');
