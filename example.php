<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$iblockID = InternalModules\IBlock::getIblock("TEST_IBLOCK"); // 1

echo "<pre>ID инфоблока: ", var_dump($iblockID), "</pre>";