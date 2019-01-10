<?

/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global string $ACTION */
/** @global array $arOldSetupVars */
	IncludeModuleLangFile(__FILE__);
	
	global $APPLICATION;
	$DefPath = COption::GetOptionString("catalog", "export_default_path", "/bitrix/catalog_export/");

	$SetupErrors = array();
	
	if ($ACTION == "EXPORT_EDIT" || $ACTION == "EXPORT_COPY"){
		if($STEP == 1){
			if (isset($arOldSetupVars["IBLOCK_ID"]))
				$IBLOCK_ID = $arOldSetupVars["IBLOCK_ID"];
			if (isset($arOldSetupVars["SETUP_FILE_NAME"]))
				$SETUP_FILE_NAME = str_replace($DefPath, "", $arOldSetupVars["SETUP_FILE_NAME"]);
			if (isset($arOldSetupVars["SETUP_PROFILE_NAME"]))
				$SETUP_PROFILE_NAME = $arOldSetupVars["SETUP_PROFILE_NAME"];
			if (isset($arOldSetupVars["SETUP_SERVER_NAME"]))
				$SETUP_SERVER_NAME = $arOldSetupVars["SETUP_SERVER_NAME"];
			if (isset($arOldSetupVars["USE_HTTPS"]))
				$USE_HTTPS = $arOldSetupVars["USE_HTTPS"];
			if(isset($arOldSetupVars["WAREHOUSES"]))
				$WAREHOUSES = $arOldSetupVars["WAREHOUSES"];
			if(isset($arOldSetupVars["PRICE_ID"]))
				$PRICE_ID = $arOldSetupVars["PRICE_ID"];
			if(isset($arOldSetupVars["ACTUAL_BALANCES"]))
				$ACTUAL_BALANCES = $arOldSetupVars["ACTUAL_BALANCES"];
			if(isset($arOldSetupVars["CURRENCY"]))
				$CURRENCY = $arOldSetupVars["CURRENCY"];
			if(isset($arOldSetupVars["MAX_ELM"]))
				$MAX_ELM = $arOldSetupVars["MAX_ELM"];
		} elseif($STEP == 2){
			if(isset($arOldSetupVars["IBLOCK_SECTION_ID"]))
				$IBLOCK_SECTION_ID = $arOldSetupVars["IBLOCK_SECTION_ID"];
		}
	}

	if ($STEP == 2){
		if (!($IBLOCK_ID > 0)){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_1");
		}
		if(!(strlen(trim($CURRENCY)) > 0)){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_2");
		}
		if(!($MAX_ELM > 0)){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_3");
		}
		if(!($PRICE_ID > 0)){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_4");
		}
		//if(empty($WAREHOUSES)){
		//	$SetupErrors[] = GetMessage("MRBANNYYO_YMS_5");
		//}
		if (strlen($SETUP_FILE_NAME)<=0){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_6");
		}
		if (empty($SetupErrors)){
			$SETUP_FILE_NAME = str_replace("//","/",$DefPath.Rel2Abs("/", $SETUP_FILE_NAME));
			if (preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME)){
				$SetupErrors[] = GetMessage("MRBANNYYO_YMS_7");
			} elseif ($APPLICATION->GetFileAccessPermission($SETUP_FILE_NAME) < "W"){
				$SetupErrors[] = GetMessage("MRBANNYYO_YMS_8");
			}
		}

		if (!isset($USE_HTTPS) || $USE_HTTPS != "Y"){
			$USE_HTTPS = "N";
		}
		if (
			($ACTION=="EXPORT_SETUP" || $ACTION == "EXPORT_EDIT" || $ACTION == "EXPORT_COPY")
			&& 
			strlen($SETUP_PROFILE_NAME)<=0
		){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_9");
		}
		if (!empty($SetupErrors)){
			$STEP = 1;
		}
	} else if($STEP == 3){
		if(empty($IBLOCK_SECTION_ID)){
			$SetupErrors[] = GetMessage("MRBANNYYO_YMS_10");
		}
	}
	
	if(strlen($SETUP_FILE_NAME) == 0){$SETUP_FILE_NAME = "yandex_".mt_rand(0, 999999).".php";}
	if(strlen($SETUP_SERVER_NAME) == 0){$SETUP_SERVER_NAME = $_SERVER["HTTP_HOST"];}
	
	$ACMenu = new CAdminContextMenu(
		array(
			array(
				"TEXT" => GetMessage("MRBANNYYO_YMS_11"),
				"TITLE" => GetMessage("MRBANNYYO_YMS_12"),
				"LINK" => "/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID,
				"ICON" => "btn_list",
			)
		)
	);

	$ACMenu->Show();
	
	$ATControl = new CAdminTabControl(
		"tabControl",
		array(
			array("DIV" => "edit1", "TAB" => GetMessage("MRBANNYYO_YMS_13"), "ICON" => "store", "TITLE" => GetMessage("MRBANNYYO_YMS_13")),
			array("DIV" => "edit2", "TAB" => GetMessage("MRBANNYYO_YMS_14"), "ICON" => "store", "TITLE" => GetMessage("MRBANNYYO_YMS_14")),
			array("DIV" => "edit3", "TAB" => GetMessage("MRBANNYYO_YMS_15"), "ICON" => "store", "TITLE" => GetMessage("MRBANNYYO_YMS_15"))
		),
		false,
		true
	);
	
	if (!empty($SetupErrors)){ShowError(implode("<br>", $SetupErrors));}
?>
	<form method="POST" action="<?=$APPLICATION->GetCurPage(); ?>" enctype="multipart/form-data" name="dataload">
	<?$ATControl->Begin();$ATControl->BeginNextTab();?>
		<?if($STEP == 1 || $STEP == 2):?>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_16")?></td>
			<td>
				<select style="width: 100%;" name="IBLOCK_ID" onchange="MrBannyyo.ChangeSections(this);">
					<?
					$Catalogs = array("List" => array(),"Id" => array("Offers" => array()));
					$Query = CCatalog::GetList();
					while($Answer = $Query->Fetch()) {
						$Catalogs["List"][] = $Answer;
						if($Answer["OFFERS_IBLOCK_ID"] > 0){
							$Catalogs["Id"]["Offers"][] = $Answer["OFFERS_IBLOCK_ID"];
						}
						$Catalogs["Id"]["Catalog"][] = $Answer["ID"];
					}
					?>
					<?foreach($Catalogs["List"] as $key => $value):?>
						<?if(in_array($value["IBLOCK_ID"],$Catalogs["Id"]["Offers"])){continue;}?>
						<option  
							<?=($value["IBLOCK_ID"] == $IBLOCK_ID ? " selected=\"selected\" " : "")?>
							value="<?=$value["IBLOCK_ID"]?>">
							<?=$value["NAME"]?> (<?=$value["IBLOCK_ID"]?>)
						</option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_17")?></td>
			<td>
				<select style="width: 100%;" name="WAREHOUSES[]" multiple="" size="10">
					<?$Query = CCatalogStore::GetList();?>
					<?while($Answer = $Query->Fetch()):?>
						<option 
							<?=(in_array($Answer["ID"],$WAREHOUSES) ? " selected=\"selected\" " : "")?>
							value="<?=$Answer["ID"]?>">
							<?=$Answer["TITLE"]?>
						</option>
					<?endwhile;?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_18")?></td>
			<td>
				<select style="width: 100%;" name="PRICE_ID">
					<?$Query = CCatalogGroup::GetList();?>
					<?while($Answer = $Query->Fetch()):?>
						<option 
							<?=($Answer["ID"] ==$PRICE_ID ? " selected=\"selected\" " : "")?>
							value="<?=$Answer["ID"]?>">
							<?=$Answer["NAME"]?>
						</option>
					<?endwhile;?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_19")?></td>
			<td>
				<select style="width: 100%;" name="CURRENCY">
				<?$Query = CCurrency::GetList(($by="name"), ($order="asc"), LANGUAGE_ID);?>
				<?while($Answer = $Query->Fetch()):?>
					<option 
						<?=($Answer["CURRENCY"] == $CURRENCY ? " selected=\"selected\" " : "")?>
						value="<?=$Answer["CURRENCY"]?>">
						<?=$Answer["FULL_NAME"]?> (<?=$Answer["CURRENCY"]?>)
					</option>
				<?endwhile;?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_20")?></td>
			<td>
				<input 
					type="checkbox" 
					name="USE_HTTPS" 
					value="Y"
					<?=($USE_HTTPS == "Y" ? " checked=\"checked\" " : ""); ?> />
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_21")?></td>
			<td>
				<input 
					type="checkbox" 
					name="ACTUAL_BALANCES"
					value="Y"
					<?=($ACTUAL_BALANCES == "Y" ? " checked=\"checked\" " : ""); ?> />
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_22")?></td>
			<td>
				<input 
					type="text" 
					name="MAX_ELM" 
					value="<?=$MAX_ELM?>" />
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_23")?></td>
			<td>
				<input 
					type="text" 
					name="SETUP_SERVER_NAME" 
					value="<?=$SETUP_SERVER_NAME?>" />
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_24")?></td>
			<td><b>/bitrix/catalog_export/</b>
				<input 
					type="text" 
					name="SETUP_FILE_NAME" 
					value="<?=$SETUP_FILE_NAME?>" 
					size="50">
			</td>
		</tr>
		<?if ($ACTION=="EXPORT_SETUP" || $ACTION == "EXPORT_EDIT" || $ACTION == "EXPORT_COPY"):?>
		<tr>
			<td><?=GetMessage("MRBANNYYO_YMS_25")?></td>
			<td>
				<input type="text" name="SETUP_PROFILE_NAME" value="<?=$SETUP_PROFILE_NAME?>" size="30">
			</td>
		</tr>
		<?endif;?>
	<?endif;?>
	<?$ATControl->EndTab();$ATControl->BeginNextTab();?>
		<?if($STEP == 1 || $STEP == 2):?>
		<tr>
			<td colspan="2">
				<select style="width: 100%;" name="IBLOCK_SECTION_ID[]" multiple="" size="20">
				<?$Query = CIBlockSection::GetList(
					array("LEFT_MARGIN"=>"ASC"),
					array("IBLOCK_ID"=>$IBLOCK_ID),
					false,
					array("ID","NAME","DEPTH_LEVEL")
				);?>
				<?$i = 100;$j = 0;?>
				<?while($Answer = $Query->Fetch()):?>
					<?for($i = 0; $i < $Answer["DEPTH_LEVEL"];$i++){
						$Answer["NAME"] = "  .  ".$Answer["NAME"];
					}?>
					<option 
						<?=(in_array($Answer["ID"],$IBLOCK_SECTION_ID) ? " selected=\"selected\" " : "")?>
						value="<?=$Answer["ID"]?>">
						<?=$Answer["NAME"]?>
					</option>
				<?endwhile;?>
				</select>
			</td>
		</tr>
		<?endif;?>
	<?$ATControl->EndTab();$ATControl->BeginNextTab();?>
		<?if($STEP == 3){$FINITE = true;}?>
	<?$ATControl->EndTab();$ATControl->Buttons();?>
		<?=bitrix_sessid_post();?>
		<?if ($ACTION == "EXPORT_EDIT" || $ACTION == "EXPORT_COPY"):?>
			<input type="hidden" name="PROFILE_ID" value="<?=intval($PROFILE_ID); ?>">
		<?endif;?>
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
		<input type="hidden" name="ACT_FILE" value="<?=htmlspecialcharsbx($_REQUEST["ACT_FILE"])?>">
		<input type="hidden" name="ACTION" value="<?=htmlspecialcharsbx($ACTION) ?>">
		<input type="hidden" name="STEP" value="<?=intval($STEP) + 1 ?>">
		<input type="hidden" name="SETUP_FIELDS_LIST" value="MAX_ELM,CURRENCY,ACTUAL_BALANCES,IBLOCK_SECTION_ID,PRICE_ID,IBLOCK_ID,SETUP_SERVER_NAME,SETUP_FILE_NAME,USE_HTTPS,WAREHOUSES">
		<input type="submit" value="<?=GetMessage("MRBANNYYO_YMS_26")?>">
	<?$ATControl->End();?>
</form>
<script type="text/javascript">
<?if ($STEP == 1):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3")
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
<?elseif ($STEP == 3):?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit1");
<?endif;?>
</script>