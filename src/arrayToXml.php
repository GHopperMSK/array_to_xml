<?php
namespace ghopper;
use ghopper\arrayToXmlException;

class arrayToXML 
{
    private $hFile;
    private $sFileName;
    private $aOpenTags;
    private $iCurTagLevel;
    private $bNewLine;

    const KEY_MARKER = '_key-value_';
    const XML_HEADER = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";

    function __construct($sFileName=NULL) {
        if (!$sFileName)
            throw new arrayToXmlException('Output filename doesn\'t specity');

        $this->sFileName = $sFileName;
        $this->bFirstRecord = TRUE;
        $this->iCurTagLevel = 0;

        $this->hFile = fopen("{$sFileName}.tmp", 'w');
        if ($this->hFile === FALSE)
            throw new arrayToXmlException('Can\'t open file');
        $this->_write(self::XML_HEADER.PHP_EOL);
    }

    private function _write($str) {
        if (fwrite($this->hFile, $str) === FALSE)
            throw new arrayToXmlException('Can\'t write to file');
    }

    function __destruct() {
        $s = '';
        for ($i=$this->iCurTagLevel; $i>=0; $i--) {
            if ($this->bNewLine)
                $s .= str_repeat("\t", $i);
            $s .= "</{$this->aOpenTags[$i]}>".PHP_EOL;
            $this->bNewLine = TRUE;
            unset($this->aOpenTags[$i]);
        }
        $this->_write($s);

        fclose($this->hFile);

        if (file_exists($this->sFileName)) 
            rename($this->sFileName, "{$this->sFileName}.old");
        
        rename("{$this->sFileName}.tmp", $this->sFileName);
    }
    
    function parse($aFields = array()) {
        $aKeys = array();
        foreach ($aFields as $key => $value) {
            $i = strpos($key, self::KEY_MARKER);
            if ($i !== FALSE) {
                $sPath = substr($key, 0, $i);
                $aKeys[$sPath] = substr($key, $i+strlen(self::KEY_MARKER))."=\"{$value}\"";
                unset($aFields[$key]);
            }
        }

        foreach ($aFields as $key => $value) {
            $s = '';
            
            $aPath = explode('_', $key);
            $iLevel = count($aPath) - 1;
            if ($iLevel > $this->iCurTagLevel) {
                for ($i=0; $i<=$iLevel; $i++) {
                    if (!isset($this->aOpenTags[$i]) OR ($this->aOpenTags[$i] != $aPath[$i])) {
                        if (isset($this->aOpenTags[$i])) {
                            if ($this->bNewLine)
                                $s .= str_repeat("\t", $i);
                            $s .= "</{$this->aOpenTags[$i]}>".PHP_EOL;
                        }
                        $this->aOpenTags[$i] = $aPath[$i];
                        $s .= str_repeat("\t", $i);
                        $sOpenTags = implode('_', $this->aOpenTags);
                        if (isset($aKeys[$sOpenTags]))
                            $sProp = ' '.$aKeys[$sOpenTags];
                        else
                            $sProp = NULL;
                        $s .= "<{$aPath[$i]}{$sProp}>";
                        if ($i < $iLevel) {
                            $this->bNewLine = TRUE;
                            $s .= PHP_EOL;
                        } else
                            $this->bNewLine = FALSE;
                    }
                }
            }
            else if ($iLevel < $this->iCurTagLevel) {
                for ($i=$this->iCurTagLevel; $i>=$iLevel; $i--) {
                    if (!isset($aPath[$i])) {
                        if ($this->bNewLine)
                            $s .= str_repeat("\t", $i);
                        $s .= "</{$this->aOpenTags[$i]}>".PHP_EOL;
                        $this->bNewLine = TRUE;
                        unset($this->aOpenTags[$i]);
                    }
                    else if ($this->aOpenTags[$i] != $aPath[$i]) {
                        if ($this->bNewLine)
                            $s .= str_repeat("\t", $i);
                        $s .= "</{$this->aOpenTags[$i]}>".PHP_EOL;
                        $s .= str_repeat("\t", $i);
                        $sOpenTags = implode('_', $this->aOpenTags);
                        if (isset($aKeys[$sOpenTags]))
                            $sProp = ' '.$aKeys[$sOpenTags];
                        else
                            $sProp = NULL;
                        $s .= "<{$aPath[$i]}{$sProp}>";
                        $this->bNewLine = FALSE;
                        $this->aOpenTags[$i] = $aPath[$i];
                    }
                }
            }
            else if ($iLevel == $this->iCurTagLevel) {
                if ($this->aOpenTags == $aPath) {
                    if ($this->bNewLine)
                        $s .= str_repeat("\t", $iLevel-1);
                    $s .= "</{$this->aOpenTags[$iLevel]}>".PHP_EOL;
                    $s .= str_repeat("\t", $iLevel);
                    $sOpenTags = implode('_', $this->aOpenTags);
                    if (isset($aKeys[$sOpenTags]))
                        $sProp = ' '.$aKeys[$sOpenTags];
                    else
                        $sProp = NULL;
                    $s .= "<{$this->aOpenTags[$iLevel]}{$sProp}>";
                } else {
                    for ($i=0;$i<=$iLevel;$i++) {
                        if ($this->aOpenTags[$i] != $aPath[$i])
                            break;
                    }
                    for ($ii=$iLevel; $ii>=$i; $ii--) {
                        if ($this->bNewLine)
                            $s .= str_repeat("\t", $ii);
                        $s .= "</{$this->aOpenTags[$ii]}>".PHP_EOL;
                        $this->bNewLine = TRUE;
                        $this->aOpenTags[$i] = $aPath[$i];
                    }
                    for ($ii=$i; $ii<=$iLevel; $ii++) {
                        $s .= str_repeat("\t", $ii);
                        $sOpenTags = implode('_', $this->aOpenTags);
                        if (isset($aKeys[$sOpenTags]))
                            $sProp = ' '.$aKeys[$sOpenTags];
                        else
                            $sProp = NULL;
                        $s .= "<{$this->aOpenTags[$ii]}{$sProp}>";
                        if ($ii != $iLevel) {
                            $s .= PHP_EOL;
                        }
                        else
                            $this->bNewLine = FALSE;
                    }
                }
            }
            $this->iCurTagLevel = count($this->aOpenTags) - 1;

            $this->_write($s);
            $this->_write(htmlspecialchars(strip_tags($value)));
        }

        $s = '';
        while ($this->iCurTagLevel>0) {
            if ($this->bNewLine)
                $s .= str_repeat("\t", $this->iCurTagLevel);
            $s .= "</{$this->aOpenTags[$this->iCurTagLevel]}>".PHP_EOL;
            $this->bNewLine = TRUE;
            unset($this->aOpenTags[$this->iCurTagLevel]);
            $this->iCurTagLevel--;
        }

        $this->_write($s);
    }

}

?>
