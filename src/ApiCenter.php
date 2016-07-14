<?php

namespace Ufox;

class ApiCenter
{
    public function getCep($cep){
        return $this->curl( $this->getUrl( 'consulta_cep' ) . $cep );
    }

    public function getIp($ip){
        return $this->curl( $this->getUrl( 'consulta_ip' ) . $ip );
    }

    public function getOperadora($telefone){
        return $this->curl( $this->getUrl( 'consulta_operadora' ) . $telefone );
    }

    public function getVencimentoOperadora($operadora){
        return $this->curl( $this->getUrl( 'consulta_vencimento' ) . $operadora );
    }

    public function getInfoCPF($cpf){
        return $this->curl( $this->getUrl( 'consulta_cpf' ) . $cpf );
    }

    public function getValidaDados( $cpf, $nome, $mae, $dataNasc ){
        return $this->curl( $this->getUrl( 'valida_dados' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'cpf'=>$cpf, 'nome_completo'=>$nome, 'mae'=>$mae, 'data_nascimento'=>$dataNasc ] )
        ] );
    }

    public function getInfoCodRastreamento( $codigo ){
        return $this->curl( $this->getUrl( 'consulta_cod_rastreamento' ) . $codigo );
    }

    public function getPlanos( $ddd, $tipo = 'controle', $operadora = 'VIVO' ){
        return $this->curl( $this->getUrl( 'consulta_planos' ) . "?operadora=$operadora&ddd=$ddd&tipo=$tipo" );
    }

    public function getStoreMagento(){
        return $this->curl( $this->getUrl( 'get_store' ) );
    }

    public function getUrl($apiName){
        $retorno = $this->curl( config('app.url_api_center').$apiName.'/'.config('app.env') );
        return $retorno['url'];

    }

    private function parseReturn( $jsonString ){

        $json = json_decode( $jsonString, TRUE );
        $retorno = FALSE;
        if( !isset( $json['error'] ) ) {
            $retorno = $json;
        }
        return $retorno;

    }

    private function curl ($url, array $options = [])
    {
        $timeout           = 5;
        $connectionTimeout = 3;

        $ch = curl_init();

        if (count($options) > 0) {

            if (!empty($options['timeout']))
                $timeout = $options['timeout'];

            if (!empty($options['connectionTimeout']))
                $connectionTimeout = $options['connectionTimeout'];

            if (!empty($options['header'])) {
                if (is_array($options['header'])) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $options['header']);
                } else {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array($options['header']));
                }
            }

            if (!empty($options['method']))
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);

            if (!empty($options['post']))
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

            if (!empty($options['data']))
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);

            if (!empty($options['json'])) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($options['data']))
                );
            }
        }

        $refer = ( isset( $_SERVER['HTTP_X_ALT_REFERER'] ) ? $_SERVER['HTTP_X_ALT_REFERER'] : ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : \Request::server('HTTP_REFERER') ) );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectionTimeout);
        curl_setopt($ch, CURLOPT_REFERER, $refer );

        $result = curl_exec($ch);
        curl_close($ch);

        return $this->parseReturn( $result );
    }
}