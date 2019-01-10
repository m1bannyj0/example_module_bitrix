<?

/** @global CUser $USER */
/** @global CMain $APPLICATION */
	
	set_time_limit(0);
	CModule::IncludeModule("mrbannyyo.ymarket");
	
	if(!isset($PROFILE_ID) && isset($profile_id) && $profile_id > 0){
		$PROFILE_ID = $profile_id;
	}
	
	$Byy = new MRBANNYYOYMarket();
	$Byy->SetParams(array(
		"Ib" => $IBLOCK_ID,
		"FileName" => $SETUP_FILE_NAME,
		"ProfileId" => $PROFILE_ID,
		"ProfileName" => $SETUP_PROFILE_NAME,
		"ServerName" =>  $SETUP_SERVER_NAME,
		"Https" => $USE_HTTPS,
		"Warehouses" => $WAREHOUSES,
		"PriceId" => $PRICE_ID,
		"SectionId" => $IBLOCK_SECTION_ID,
		"ActualAmount" => $ACTUAL_BALANCES,
		"Currency" => $CURRENCY,
		"MaxElm" => $MAX_ELM
	));
	
	$Byy->Run();
	
	if(!empty($Byy->Errors)){
		$strExportErrorMessage = implode(", ",$Byy->Errors);	
	}
?>