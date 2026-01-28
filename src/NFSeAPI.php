<?php

require(__DIR__ . '/Compartilhados/Endpoints.php');
require(__DIR__ . '/Compartilhados/Parametros.php');
require(__DIR__ . '/Compartilhados/Genericos.php');

foreach (glob(__DIR__ . '/Requisicoes/_Genericos/*.php') as $filename) {
    include_once($filename);
}

require_once(__DIR__ . '/Requisicoes/NFSe/ConsStatusProcessamentoReqNFSe.php');
require(__DIR__ . '/Requisicoes/NFSe/DownloadReqNFSe.php');

require(__DIR__ . '/Retornos/NFSe/EmitirSincronoRetNFSe.php');

class NFSeAPI {


    private $token;
    private $parametros;
    private $endpoints;
    private $genericos;
   
    public function __construct() {
        $this->parametros = new Parametros(1);
        $this->endpoints = new Endpoints;
        $this->genericos = new Genericos;
        $this->token = 'SEU_TOKEN_AQUI';
    }


    // Esta funcao envia um conteudo para uma URL, em requisicoes do tipo POST
    private function enviaConteudoParaAPI($conteudoAEnviar, $url, $tpConteudo){
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $conteudoAEnviar);

        if ($tpConteudo == 'json')
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-AUTH-TOKEN: ' . $this->token));
        else if ($tpConteudo == 'xml')
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'X-AUTH-TOKEN: ' . $this->token));
        else
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', 'X-AUTH-TOKEN: ' . $this->token));
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $erroCurl = curl_error($ch);
            return [
                'status' => -1,
                'motivo' => 'Erro de Conexão cURL: ' . $erroCurl,
                'erros' => []
            ];
        }


        $json_response = json_decode($result, true);

        if ($json_response === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => $httpCode,
                'motivo' => 'Erro ao decodificar JSON ou resposta vazia. Raw: ' . $result,
                'erros' => []
            ];
        }

        return $json_response;
    }


    // Metodos especificos de NFSe
    public function emitirNFSeSincrono($conteudo, $tpConteudo, $CNPJ, $tpDown, $tpAmb, $caminho, $exibeNaTela) {

        $statusEnvio = null;
        $statusConsulta = null;
        $statusDownload = null;
        $motivo = null;
        $nsNRec = null;
        $chNFSe = null;
        $chDPS = null;
        $cStat = null;
        $nProt = null;
        $erros = null; 

        $this->genericos->gravarLinhaLog("NFSe: ", '[EMISSAO_SINCRONA_INICIO]');

        $resposta = $this->emitirDocumento($conteudo, $tpConteudo);
        
        if (!is_array($resposta) || !isset($resposta['status'])) {
             $statusEnvio = -1;
             $motivo = "A API não retornou uma resposta válida.";
             if (isset($resposta['motivo'])) $motivo = $resposta['motivo'];
        } else {
             $statusEnvio = $resposta['status'];
        }

        if ($statusEnvio == 200 || $statusEnvio == -6){

            $nsNRec = $resposta['nsNRec'];

            sleep($this->parametros->TEMPO_ESPERA);

            $consStatusProcessamentoReqNFSe = new ConsStatusProcessamentoReqNFSe();
            $consStatusProcessamentoReqNFSe->CNPJ = $CNPJ;
            $consStatusProcessamentoReqNFSe->nsNRec = $nsNRec;
            $consStatusProcessamentoReqNFSe->tpAmb = $tpAmb;

            $resposta = $this->consultarStatusProcessamento($consStatusProcessamentoReqNFSe);
            $statusConsulta = $resposta['status'];

            if ($statusConsulta == 200){

                $cStat = $resposta['cStat'];

                if ($cStat == 100 || $cStat == 150){

                    $chNFSe = $resposta['chNFSe'];
                    $chDPS = $resposta['chDPS'];
                    $motivo = $resposta['xMotivo'];

                    $downloadReqNFSe = new DownloadReqNFSe();
                    $downloadReqNFSe->chNFSe = $chNFSe;
                    $downloadReqNFSe->chDPS = $chDPS;
                    $downloadReqNFSe->CNPJ = $CNPJ;
                    $downloadReqNFSe->tpAmb = $tpAmb;
                    $downloadReqNFSe->tpDown = $tpDown;

                    $resposta = $this->downloadDocumentoESalvar( $downloadReqNFSe, $caminho, $chNFSe . '-NFSe', $exibeNaTela);
                    $statusDownload = $resposta['status'];

                    if ($statusDownload != 200) $motivo = $resposta['motivo'];
                }else{
                    $motivo = $resposta['xMotivo'];
                }
            }else if ($statusConsulta == -2) {

                $cStat = $resposta['cStat'];
                $motivo = $resposta['erro']['xMotivo'];

            }else{
                $motivo = $resposta['motivo'];
            }
        }
        else if ($statusEnvio == -7){

            $motivo = $resposta['motivo'];
            $nsNRec = $resposta['nsNRec'];

        }
        else if ($statusEnvio == -4 || $statusEnvio == -2) {

            $motivo = $resposta['motivo'];
            $erros = $resposta['erros'];

        }
        else if ($statusEnvio == -999 || $statusEnvio == -5) {
            
            if (isset($resposta['erro']['xMotivo'])) {
                $motivo = $resposta['erro']['xMotivo'];
            } else {
                $motivo = $resposta['motivo'] ?? 'Erro desconhecido';
            }

        }
        else {
            try {
                $motivo = $resposta['motivo'];
            }catch (Exception $ex){
                $motivo = $resposta;
            }
        }

        $emitirSincronoRetNFSe = new EmitirSincronoRetNFSe();
        $emitirSincronoRetNFSe->statusEnvio = $statusEnvio;
        $emitirSincronoRetNFSe->statusConsulta = $statusConsulta;
        $emitirSincronoRetNFSe->statusDownload = $statusDownload;
        $emitirSincronoRetNFSe->cStat = $cStat;
        $emitirSincronoRetNFSe->chNFSe = $chNFSe;
        $emitirSincronoRetNFSe->chDPS = $chDPS;
        $emitirSincronoRetNFSe->nProt = $nProt;
        $emitirSincronoRetNFSe->motivo = $motivo;
        $emitirSincronoRetNFSe->nsNRec = $nsNRec;
        $emitirSincronoRetNFSe->erros = $erros;

        $emitirSincronoRetNFSe = array_filter((array) $emitirSincronoRetNFSe);

        $retorno = json_encode($emitirSincronoRetNFSe, JSON_UNESCAPED_UNICODE);

        $this->genericos->gravarLinhaLog("NFSe: ", '[JSON_RETORNO]');
        $this->genericos->gravarLinhaLog("NFSe: ", $retorno);
        $this->genericos->gravarLinhaLog("NFSe: ", '[EMISSAO_SINCRONA_FIM]');

        return $retorno;
    }


    // Métodos genéricos, compartilhados entre diversas funções
    public function emitirDocumento($conteudo, $tpConteudo){
       

        $urlEnvio = $this->endpoints->NFSeEnvio;

        $this->genericos->gravarLinhaLog("NFSe", '[ENVIA_DADOS]');
        $this->genericos->gravarLinhaLog("NFSe", $conteudo);


        $resposta = $this->enviaConteudoParaAPI($conteudo, $urlEnvio, $tpConteudo);


        $this->genericos->gravarLinhaLog("NFSe", '[ENVIA_RESPOSTA]');
        $this->genericos->gravarLinhaLog("NFSe", json_encode($resposta));


        return $resposta;
    }

    public function consultarStatusProcessamento($consStatusProcessamentoReq){

        $urlConsulta = $this->endpoints->NFSeConsStatusProcessamento;

        $json = json_encode((array) $consStatusProcessamentoReq, JSON_UNESCAPED_UNICODE);


        $this->genericos->gravarLinhaLog("NFSe: ", '[CONSULTA_DADOS]');
        $this->genericos->gravarLinhaLog("NFSe: ", $json);
       
        $resposta = $this->enviaConteudoParaAPI($json, $urlConsulta, 'json');


        $this->genericos->gravarLinhaLog("NFSe: ", '[CONSULTA_RESPOSTA]');
        $this->genericos->gravarLinhaLog("NFSe: ", json_encode($resposta));


        return $resposta;
    }

    public function downloadDocumento($downloadReq){

        $urlDownload = $this->endpoints->NFSeDownload;
       
        $json = json_encode((array) $downloadReq, JSON_UNESCAPED_UNICODE);


        $this->genericos->gravarLinhaLog("NFSe: ", '[DOWNLOAD_DADOS]');
        $this->genericos->gravarLinhaLog("NFSe: ", $json);


        $resposta = $this->enviaConteudoParaAPI($json, $urlDownload, 'json');
        $status = $resposta['status'];


        if(($status != 200) || ($status != 100)){
            $this->genericos->gravarLinhaLog("NFSe: ", '[DOWNLOAD_RESPOSTA]');
            $this->genericos->gravarLinhaLog("NFSe: ", json_encode($resposta));
        }else{
            $this->genericos->gravarLinhaLog("NFSe: ", '[DOWNLOAD_STATUS]');
            $this->genericos->gravarLinhaLog("NFSe: ", $status);
        }
        return $resposta;
    }

    public function downloadDocumentoESalvar($downloadReq, $caminho, $nome, $exibeNaTela){
       
        $resposta = $this->downloadDocumento($downloadReq);
        $status = $resposta['status'];
        if (($status == 200) || ($status == 100)) {
            try{
                if (strlen($caminho) > 0) if (!file_exists($caminho)) mkdir($caminho, 0777, true);
                if(substr($caminho, -1) != '/') $caminho= $caminho . '/';
            }catch(Exception $e){
                $this->genericos->gravarLinhaLog("NFSe: ", '[CRIA_DIRETORIO] '+ $caminho);
                $this->genericos->gravarLinhaLog("NFSe: ", $e->getMessage());
                throw new Exception('Exceção capturada: ' + $e->getMessage());
            }

               
                if (strpos(strtoupper($downloadReq->tpDown), 'X') >= 0) {
                    $xml = $resposta['xml'];
                    $this->genericos->salvaXML($xml, $caminho, $nome);
                }
                if (strpos(strtoupper($downloadReq->tpDown), 'P') >= 0) {
                    $pdf = $resposta['pdfDocumento'];
                    $this->genericos->salvaPDF($pdf, $caminho, $nome);


                    if ($exibeNaTela) {
                        $this->genericos->exibirNaTela($caminho, $nome);
                    }  
                }
        }
        return $resposta;
    }
}
?>