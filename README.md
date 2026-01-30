# NFSe API

Utilizando a NS API, este exemplo - criado em PHP - possui funcionalidades para consumir documento fiscal eletrônico do tipo NFS-e, deixando mais pratica e facil a integração com a NS API.

## Primeiros passos:

### Integrando ao sistema:

Para utilizar as funções de comunicação com a API, você precisa realizar os seguintes passos:

1. Extraia o conteúdo da pasta compactada que você baixou;
2. Copie para sua aplicação a pasta src, na qual contem todos as classes que serão utilizadas;
3. Abra o seu projeto e importe a pasta copiada.

Pronto! Agora, você já pode consumir a NS NFS-e API através do seu sistema. Todas as funcionalidades de comunicação foram implementadas na classe NFSeAPI.php.

------

## Emissão Sincrona:

### Realizando uma Emissão Sincrona:

Para realizar uma emissão completa de uma NFSe, você poderá utilizar a função emitirNFSeSincrono da classe NFSeAPI. Veja abaixo sobre os parâmetros necessários, e um exemplo de chamada do método.

##### Parâmetros:

**ATENÇÃO:** o **token** também é um parâmetro necessário e você deve, primeiramente, defini-lo na classe **NFSeAPI.php**, como pode ver abaixo:

Parametros     | Descrição
:-------------:|:-----------
conteudo       | Conteúdo de emissão do documento.
tpConteudo     | Tipo de conteúdo que está sendo enviado. Valores possíveis: json, xml
CNPJ           | CNPJ do emitente do documento.
tpDown         | Tipo de arquivos a serem baixados.Valores possíveis: <ul> <li>**X** - XML</li> <li>**P** - PDF</li> <li>**XP** - XML e PDF</li> </ul> 
tpAmb          | Ambiente onde foi autorizado o documento.Valores possíveis:<ul> <li>1 - produção</li> <li>2 - homologação</li> </ul>
caminho        | Caminho onde devem ser salvos os documentos baixados.
exibeNaTela    | Se for baixado, exibir o PDF na tela após a autorização.Valores possíveis: <ul> <li>**True** - será exibido</li> <li>**False** - não será exibido</li> </ul> 

##### Exemplo de chamada:

Após ter todos os parâmetros listados acima, você deverá fazer a chamada da função. Veja o código de exemplo abaixo:

      $retorno = $NFSeAPI->emitirNFSeSincrono($conteudo, $tpConteudo, $cnpjEmit, $tpDown, $tpAmb, $caminho, $exibeNaTela);
      $retorno = json_decode($retorno, true);
	  var_dump($retorno);

A função emitirNFSeSincrono fará o envio, a consulta e download do documento, utilizando as funções emitirDocumento, consultarStatusProcessamento e downloadDocumentoESalvar, presentes na classe NFSeAPI.php. Por isso, o retorno será um JSON com os principais campos retornados pelos métodos citados anteriormente. No exemplo abaixo, veja como tratar o retorno da função emitirNFSeSincrono:

##### Exemplo de tratamento de retorno:

O JSON retornado pelo método terá os seguintes campos: statusEnvio, statusConsulta, statusDownload, cStat, chNFSe, chDPS, motivo, nsNRec, erros. Veja o exemplo abaixo:

    {
        "statusEnvio": "200",
        "statusConsulta": "200",
        "statusDownload": "200",
        "cStat": "100",
        "chNFSe": "31062002213278005000122000000000002926016153125604",
        "chDPS": "310620021327800500012200001000000000000029",
        "motivo": "Autorizado o uso da NFS-e",
        "nsNRec": "313022",
        "erros": ""
    }
      
Confira um código para tratamento do retorno, no qual pegará as informações dispostas no JSON de Retorno disponibilizado:


    $resposta = $NFSeAPI->emitirNFSeSincrono($conteudo, $tpConteudo, $cnpjEmit, $tpDown, $tpAmb, $caminho, $exibeNaTela);

    $statusEnvio = $resposta['statusEnvio'];
    $statusConsulta = $resposta['statusConsulta'];
    $statusDownload = $resposta['statusDownload'];
    $cStat = $resposta['cStat'];
    $chNFSe = $resposta['chNFSe'];
    $chDPS = $resposta['chDPS'];
    $motivo = $resposta['motivo'];
    $nsNRec = $resposta['nsNRec'];
    $erros = $resposta['erros'];

    if ($statusEnvio == 200 || $statusEnvio == -6){
        if ($statusConsulta == 200){
            if ($cStat == 100){
                echo $motivo;
                if ($statusDownload != 200){
                    echo 'Erro Download';
                }
            }else{
                echo $motivo;
            }
        }else{
            echo $motivo . '<br>' . $erros;
        }
    }else{
        echo $motivo . '<br>' . $erros;
    }
-----

![Ns](https://nstecnologia.com.br/blog/wp-content/uploads/2018/11/ns%C2%B4tecnologia.png) | Obrigado pela atenção!
