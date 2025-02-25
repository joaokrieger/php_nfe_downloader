<?php

    namespace App\Nfe;

    use NFePHP\NFe\Tools;
    use NFePHP\Common\Certificate;
    use App\Utils\FileHelper; 
    use DOMDocument;
    use Exception;

    class NfeDownloader {

        // Variáveis referentes ao certificado A1
        private $pfxcontent;
        private $pfxsenha;

        // Variáveis referentes às informações do cliente
        private $config;

        // Variáveis referentes à biblioteca de Download de NFE
        private $tools;

        // Variáveis para controlar execução de requisições
        private $ultNSU = 0;
        private $maxNSU = 0;
        private $loopLimit = 12; //Mantenha o número de consultas abaixo de 20, cada consulta retorna até 50 documentos por vez
        private $iCount = 0;

        function __construct($pfxContent, $pfxSenha, $config = []) {
            $this->pfxcontent = $pfxContent;
            $this->pfxsenha = $pfxSenha;
            $this->config = $config;

            $configJson = json_encode($this->config);
            $this->tools = new Tools($configJson, Certificate::readPfx($this->pfxcontent, $this->pfxsenha));
            $this->tools->model('55');
            $this->tools->setEnvironment(1); //1 - Produção, 2 - Homologação
            $this->iCount = 0;
        }

        public function DownloadSefazNFE($ultNSU) {

            $this->ultNSU = $ultNSU;
            $this->maxNSU = $ultNSU;

            while ($this->ultNSU <= $this->maxNSU) {
                $this->iCount++;

                if ($this->iCount >= $this->loopLimit) {
                    break;
                }

                try {
                    $resp = $this->getDistDFe();
                    FileHelper::saveFile('../../storage/nfe/raw/xml_zip_nsu' . $this->ultNSU . '.xml', $resp);
                    $this->processNode($resp);
                } 
                catch (Exception $e) {
                    return $e->getMessage();
                    break;
                }
            }

            $this->processZip();

            return $this->ultNSU;
        }

        private function getDistDFe() {
            return $this->tools->sefazDistDFe($this->ultNSU);
        }

        private function processNode($resp) {

            $dom = new \DOMDocument();
            $dom->loadXML($resp);
            $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);

            if ($node) {
                $cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
                $xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
                $this->ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
                $this->maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;        
                if (($this->ultNSU == $this->maxNSU) || ($cStat == 137 || $cStat == 656)) {
                    throw new \Exception('Erro ao realizar requisição de NFE: ' . $xMotivo . ' => Status (' . $cStat . ').');
                }
            }
            else {
                throw new \Exception('Resposta de requisição inválida.');
            }
        }

        private function processZip() {
            $files = FileHelper::getXmlFiles('files/nfe/raw/');
            foreach ($files as $file) {

                $dom = new \DOMDocument();
                $dom->load($file); 
                $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
                
                if ($node) {
                    $lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);
                    
                    if ($lote) {
                        $docs = $lote->getElementsByTagName('docZip');
                        if ($docs->length > 0) {
                            FileHelper::decodeAndSaveZip($file, '../../storage/nfe/processed/');
                        } 
                    } 
                }
            }
        }
    }
?>