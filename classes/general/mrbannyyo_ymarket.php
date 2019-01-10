<?php
IncludeModuleLangFile(__FILE__);


class MRBANNYYOYMarket
{


	public $Errors = array();
	private $Params = array();
	private $Id = -1;
	private $Step = -1;
	private $MaxStep = -1;
	private $CurentDate;
	private $AdditionalStep = 2;
	private $NavParams = array();
	private $OFile;

	function __construct()
	{

		CModule::IncludeModule("iblock");
		CModule::IncludeModule('highloadblock');
		$this->CurentDate = strtotime(date("d.m.Y"));

		if (empty($this->Params) && 'isbetaversion' == 'isbetaversion') {
			$this->Params = Array
			(
				'Ib' => 2,
				'FileName' => '/yandex_928588.php',
				'ProfileId' => 1,
				'ProfileName' => "",
				'ServerName' => 'http://tehtfa',
				'Https' => 'http://',
				'Warehouses' => '',
				'PriceId' => 1,
				'SectionId' => Array(2, 3),
				'ActualAmount' => '',
				'Currency' => 'RUB',
				'MaxElm' => 10,
				'FileNameTmp' => '/yandex_928588.phptmp'
			);
		}

	}

	public function Run()
	{
		$MessagText = GetMessage("MRBANNYYO_YMCG_1");

		$this->Check();

		$this->Params["FileName"] = $_SERVER["DOCUMENT_ROOT"] . $this->Params["FileName"];
		$this->Params["FileNameTmp"] = $this->Params["FileName"] . "tmp";

		$Query = CIBlockSection::GetList(
			array("LEFT_MARGIN" => "ASC"),
			array("IBLOCK_ID" => $this->Params["Ib"], "ID" => $this->Params["SectionId"]),
			false,
			array("ID", "IBLOCK_SECTION_ID", "NAME")
		);


		$dom = new domDocument("1.0", "UTF-8");
		$root = $dom->createElement("yml_catalog");
		$root->setAttribute("date", date("Y-m-d H:i"));
		$dom->appendChild($root);
		$shop = $dom->createElement("shop");
		$root->appendChild($shop);
		$name = $dom->createElement("name", COption::GetOptionString("main", "site_name", ""));
		$shop->appendChild($name);
		$comp = $dom->createElement("company", COption::GetOptionString("main", "site_name", ""));
		$shop->appendChild($comp);
		$url = $dom->createElement("url", $this->Params["ServerName"]);
		$shop->appendChild($url);
		$currencies = $dom->createElement("currencies");
		$shop->appendChild($currencies);
		$currency = $dom->createElement("currency");
		$currency->setAttribute("id", $this->Params["Currency"]);
		$currency->setAttribute("rate", "1");
		$currency->setAttribute("plus", "");
		$currencies->appendChild($currency);
		$categories = $dom->createElement("categories");
		$shop->appendChild($categories);

		while ($Answer = $Query->Fetch()) {


			$category = $dom->createElement("category", $this->YandexText($Answer["NAME"], true));
			$category->setAttribute("id", $this->YandexText($Answer["ID"], true));
			$categories->appendChild($category);
		}

		$dom->save($this->Params["FileName"]);


		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($this->Params["Ib"])->fetch();
		if ($hlblock) {
			$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
		}

		$dom = new domDocument("1.0", "UTF-8");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->load($this->Params["FileName"]);
		$offers = $dom->getElementsByTagName('offers')->item(0);
		if ($offers == NULL) {
			$shop = $dom->getElementsByTagName('shop')->item(0);
			$offers = $dom->createElement("offers");
			$shop->appendChild($offers);
		}


		$filter = array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $this->Params["Ib"],
			"INCLUDE_SUBSECTIONS" => "Y",
			"SECTION_ID" => $this->Params["SectionId"],
			">CATALOG_PRICE_" . $this->Params["PriceId"],
		);
		$elemsQuery = CIBlockElement::GetList(
			array("SORT" => "ASC"),
			$filter,
			false,
			array(
				'iNumPage' => 0,
				'nPageSize' => $this->Params["MaxElm"],
			),
			array("*", 'CATALOG_GROUP_' . $this->Params["PriceId"])
		);

		while ($arElems = $elemsQuery->GetNextElement()) {

			$fields = $arElems->GetFields();
			$props = $arElems->GetProperties();
			$available = 'false';

			$offer = $dom->createElement("offer");
			$offer->setAttribute("id", $fields['ID']);
			$offer->setAttribute("available", $available);
			$offers->appendChild($offer);
			if ($hlblock) {
				$rsData = $entity_data_class::getList(array(
					"select" => array("*"),
					"order" => array("ID" => "ASC"),
				));
				if ($arData = $rsData->Fetch()) {
					if ((int)$arData["UF_COND"] > 0) {
						$available = 'true';
					}
				}
			}
			$categoryId = $dom->createElement("categoryId", $fields['IBLOCK_SECTION_ID']);
			$offer->appendChild($categoryId);
			$price = $dom->createElement("price", (int)$fields['CATALOG_PRICE_' . $this->Params["PriceId"]]);
			$offer->appendChild($price);
			if (!empty($props['SPECIALOFFER']['VALUE'])) {
				$oldprice = $dom->createElement("oldprice", (int)$fields['CATALOG_PRICE_' . $this->Params["PriceId"]] + 100);
				$offer->appendChild($oldprice);
			}
			$currencyId = $dom->createElement("currencyId", $this->Params["Currency"]);
			$offer->appendChild($currencyId);

			$vendor = $props['maker']['VALUE'];
			if (!empty($vendor)) {
				$vendor = $dom->createElement("vendor", $vendor);
				$offer->appendChild($vendor);
			}
			$name = $dom->createElement("name", $fields['NAME']);
			$offer->appendChild($name);

		}
		$dom->save($this->Params["FileName"]);

	}

	private function Check()
	{
		if (!($this->Params["ProfileId"] > 0)) {
			$this->Errors[] = GetMessage("MRBANNYYO_YMCG_3");
		}
		if (!($this->Params["MaxElm"] > 0)) {
			$this->Errors[] = GetMessage("MRBANNYYO_YMCG_4");
		}
		if (!($this->Params["Ib"] > 0)) {
			$this->Errors[] = GetMessage("MRBANNYYO_YMCG_5");
		}

	}

	private function YandexText($text, $bHSC = false, $bDblQuote = false)
	{
		global $APPLICATION;

		$bHSC = (true == $bHSC ? true : false);
		$bDblQuote = (true == $bDblQuote ? true : false);

		if ($bHSC) {
			$text = htmlspecialcharsbx($text);
			if ($bDblQuote)
				$text = str_replace('&quot;', '"', $text);
		}
		$text = preg_replace('/[\x01-\x08\x0B-\x0C\x0E-\x1F]/', "", $text);
		$text = str_replace("'", "&apos;", $text);
		return $text;
	}

	private function ConvertFileWin1251()
	{
		$this->CloseFile();
		if ($this->OpenFile("r")) {
			if ($NFile = fopen($this->Params["FileName"], "w")) {
				while (!feof($this->OFile)) {
					$TmpData = fread($this->OFile, 8192);
					fwrite($NFile, mb_convert_encoding($TmpData, "windows-1251", LANG_CHARSET));
				}
				fclose($NFile);
			}
		}
	}

	private function NewExport()
	{
		global $DB;
		$Result = $DB->Query("INSERT INTO 
			b_MRBANNYYO_yandex_market 
				(TIMESTAMP_UNIX,PROFILE_ID,STEP,MAX_STEP,PARAMS,FINAL) 
			VALUES
				(
					'" . $DB->ForSQL($this->CurentDate) . "',
					'" . $DB->ForSQL($this->Params["ProfileId"]) . "',
					'1',
					'-1',
					'" . $DB->ForSQL($this->GetParams()) . "',
					'N'
				) 
		;");
		return $Result;
	}

	public function GetParams()
	{
		return serialize($this->Params);
	}

	public function SetParams($Arg0 = array())
	{
		foreach ($Arg0 as $key => $value) {
			if ($key == "Https") {
				$value = "http" . ($value == "Y" ? "s" : "") . "://";
			}
			$this->Params[$key] = $value;
		}
	}
}


?>