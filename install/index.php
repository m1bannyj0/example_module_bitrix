<?
IncludeModuleLangFile(__FILE__);

class mrbannyyo_ymarket extends CModule
{
	var $MODULE_ID = "mrbannyyo.ymarket";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $PARTNER_NAME;
	var $PARTNER_URI;
	var $MODULE_CSS;

	function __construct()
	{
		include($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/' . $this->MODULE_ID . '/install/version.php');

		$this->MODULE_VERSION = $arModuleVersion[VERSION];
		$this->MODULE_VERSION_DATE = $arModuleVersion[VERSION_DATE];
		$this->MODULE_NAME = GetMessage(MRBANNYYO_IYM_1);
		$this->MODULE_DESCRIPTION = GetMessage(MRBANNYYO_IYM_2);
		$this->PARTNER_NAME = GetMessage(MRBANNYYO_IYM_3);
		$this->PARTNER_URI = GetMessage(MRBANNYYO_IYM_4);
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		if ($this->InstallFiles() && $this->InstallDB()) {
			RegisterModule($this->MODULE_ID);
		}
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/load', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export');
		return true;
	}

	function InstallDB()
	{


		global $DB, $DBType, $APPLICATION;
		$step1 = $DB->RunSqlBatch($_SERVER["DOCUMENT_ROOT"] .
			"/bitrix/modules/" . $this->MODULE_ID . "/install/db/mysql/install.sql");

		return true;
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		if ($this->UnInstallFiles() && $this->UnInstallDB()) {
			UnRegisterModule($this->MODULE_ID);
		}
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/load', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export');
		return true;
	}

	function UnInstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$step1 = $DB->RunSqlBatch($_SERVER["DOCUMENT_ROOT"] .
			"/bitrix/modules/" . $this->MODULE_ID . "/install/db/mysql/uninstall.sql");
		$step2 = $DB->Query("DELETE FROM b_agent WHERE MODULE_ID='mrbannyyo.ymarket'", false, $err_mess . __LINE__);


		return true;
	}
}

?>