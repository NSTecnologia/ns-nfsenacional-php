<?php
    // Ajuste o caminho conforme sua estrutura de pastas
    include('src/Layout\NFSe\DPSJSON.php'); 
    include('src/NFSeAPI.php'); 

    $NFSeApi = new NFSeAPI;

    $DPSJSON = new DPSJSON;
    $dps = new DPS;
    $infDPS = new InfDPS;
    $prest = new Prest;
    $regTrib = new RegTrib;
    $serv = new Serv;
    $locPrest = new LocPrest;
    $cServ = new CServ;
    $valores = new Valores;
    $vServPrest = new VServPrest;
    $trib = new Trib;
    $tribMun = new TribMun;
    $tribFed = new TribFed;
    $piscofins = new Piscofins;
    $totTrib = new TotTrib;

    $DPSJSON->DPS = $dps;
    $dps->infDPS = $infDPS;
    
    $dps->versao = "1.00";

    $infDPS->tpAmb = "2";
    $infDPS->dhEmi = "2026-01-28T09:52:00-03:00";
    $infDPS->verAplic = "NS1.0.0";
    $infDPS->serie = "1";
    $infDPS->nDPS = "29";
    $infDPS->dCompet = "2025-01-28";
    $infDPS->tpEmit = "1";
    $infDPS->cLocEmi = "3106200";

    $infDPS->prest = $prest;
    $prest->CNPJ = "13278005000122";
    $prest->fone = "3192106582";
    $prest->email = "FERNANDOAVRA@GMAIL.COM";

    $prest->regTrib = $regTrib;
    $regTrib->opSimpNac = "3";
    $regTrib->regApTribSN = "1";
    $regTrib->regEspTrib = "0";

    $infDPS->serv = $serv;
    
    $serv->locPrest = $locPrest;
    $locPrest->cLocPrestacao = "3106200";

    $serv->cServ = $cServ;
    $cServ->cTribNac = "171102";
    $cServ->cTribMun = "002";
    $cServ->xDescServ = "Mensalidade Sistema Food Plus";
    $cServ->cNBS = "103012200";

    $infDPS->valores = $valores;

    $valores->vServPrest = $vServPrest;
    $vServPrest->vServ = "1000.00";

    $valores->trib = $trib;

    $trib->tribMun = $tribMun;
    $tribMun->tribISSQN = "1";
    $tribMun->tpRetISSQN = "1";

    $trib->tribFed = $tribFed;
    $tribFed->piscofins = $piscofins;
    $piscofins->CST = "08";

    $trib->totTrib = $totTrib;
    $totTrib->pTotTribSN = "12.00";

    $conteudo = json_encode($DPSJSON, JSON_UNESCAPED_UNICODE);

    $tpConteudo  = "json";
    $cnpjEmit    = "13278005000122"; 
    $tpDown      = "XP"; 
    $tpAmb       = "2";
    $caminho     = "./Notas/";
    $exibeNaTela = true;


    $retorno = $NFSeApi->emitirNFSeSincrono($conteudo, $tpConteudo, $cnpjEmit, $tpDown, $tpAmb, $caminho, $exibeNaTela);
    $retorno = json_decode($retorno, true);
    var_dump($retorno);
?>