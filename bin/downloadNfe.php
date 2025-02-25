<?php

    require_once '../vendor/autoload.php';

    use App\Nfe\NfeDownloader;

    //Configurações e informações necessárias do cliente
    $pfxcontent = file_get_contents('../certificado/certificadoA1.pfx'); //Arquivo .pfx do certificado A1
    $password = file_get_contents('../certificado/senha.txt'); //Senha do certificado A1
    $config = [
        "atualizacao" => "2025-01-01 12:00:00", //Apenas uma referência. Não tem utilidade real
        "tpAmb" => 1, //1-Produção ou 2-Homologação
        "razaosocial" => "EMPRESA LTDA", //Nome completo da pessoa jurídica
        "siglaUF" => "SC", //Sigla da unidade da Federação da pessoa jurídica
        "cnpj" => "00000000000000", //CNPJ da pessoa jurídica
        "schemes" => "PL_009_V4", //Nome da pasta onde estão os schemas utilizados pelo sped-nfe
        "versao" => "4.00" //Numero de versão do layout. Consultar versão em (https://www.nfe.fazenda.gov.br/portal/webServices.aspx)
    ];

    $nfeDownloader = new NfeDownloader($pfxcontent, $password, $config);
    $utlNSU = $nfeDownloader->DownloadSefazNFE(0); //Último NSU + 1 como parâmetro caso ainda existam para consultar
    echo $utlNSU;
?>