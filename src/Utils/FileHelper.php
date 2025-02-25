<?php

    namespace App\Utils;

    class FileHelper {

        public static function saveFile($filename, $content) {
            file_put_contents($filename, $content);
        }

        public static function getXmlFiles($path) {
            return glob($path . '*.xml');
        }

        public static function decodeAndSaveZip($file, $destination) {
            $dom = new \DOMDocument();
            $dom->load($file); 
            $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);

            if ($node) {
                $lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);
                
                if ($lote) {
                    $docs = $lote->getElementsByTagName('docZip');
                    if ($docs->length > 0) {
                        $content = gzdecode(base64_decode($docs->item(0)->nodeValue));
                        file_put_contents($destination . basename($file, '.xml'), $content);
                    } 
                } 
            }
        }
    }
?>