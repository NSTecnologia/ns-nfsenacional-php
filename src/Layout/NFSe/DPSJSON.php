<?php
	class RegTrib {
		public $opSimpNac; //String
		public $regApTribSN; //String
		public $regEspTrib; //String
	}

	class Prest {
		public $CNPJ; //String
		public $fone; //String
		public $email; //String
		public $regTrib; //RegTrib
	}

	class LocPrest {
		public $cLocPrestacao; //String
	}

	class CServ {
		public $cTribNac; //String
		public $cTribMun; //String
		public $xDescServ; //String
		public $cNBS; //String
	}

	class Serv {
		public $locPrest; //LocPrest
		public $cServ; //CServ
	}

	class VServPrest {
		public $vServ; //String
	}

	class TribMun {
		public $tribISSQN; //String
		public $tpRetISSQN; //String
	}

	class Piscofins {
		public $CST; //String
	}

	class TribFed {
		public $piscofins; //Piscofins
	}

	class TotTrib {
		public $pTotTribSN; //String
	}

	class Trib {
		public $tribMun; //TribMun
		public $tribFed; //TribFed
		public $totTrib; //TotTrib
	}

	class Valores {
		public $vServPrest; //VServPrest
		public $trib; //Trib
	}

	class InfDPS {
		public $tpAmb; //String
		public $dhEmi; //String
		public $verAplic; //String
		public $serie; //String
		public $nDPS; //String
		public $dCompet; //String
		public $tpEmit; //String
		public $cLocEmi; //String
		public $prest; //Prest
		public $serv; //Serv
		public $valores; //Valores
	}

	class DPS {
		public $versao; //String
		public $infDPS; //InfDPS
	}

	class DPSJSON {
		public $DPS; //DPS
	}
?>