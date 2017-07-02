<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

$Sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$ShopID = CSalePaySystemAction::GetParamValue("SHOP_ID");
$scid = CSalePaySystemAction::GetParamValue("SCID");
$customerNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$orderDate = CSalePaySystemAction::GetParamValue("ORDER_DATE");
$orderNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$paymentType = CSalePaySystemAction::GetParamValue("PAYMENT_VALUE");


//stsrt overt
CModule::IncludeModule("sale");
$ORDER_ID = $orderNumber;
//===


$contact = "--";

$dbOrderProps = CSaleOrderPropsValue::GetList(
   array("SORT" => "ASC"),
   array(
      "ORDER_ID" => $ORDER_ID, 
      "CODE" => array("EMAIL")
   )
);
if ($arOrderProps = $dbOrderProps->Fetch())
{
   $contact = $arOrderProps['VALUE'];          
}else{

	$dbOrderProps = CSaleOrderPropsValue::GetList(
	   array("SORT" => "ASC"),
	   array(
		  "ORDER_ID" => $ORDER_ID, 
		  "CODE" => array("PHONE")
	   )
	);
	if ($arOrderProps = $dbOrderProps->Fetch())
	{
	   $contact = $arOrderProps['VALUE'];          
	}
}



 $receipt = array(
                'customerContact' => $contact,
                'items' => array(),
            );


$arOrder = CSaleOrder::GetByID($ORDER_ID);

$dbBasket = CSaleBasket::GetList(
	array("NAME" => "ASC"),
	array("ORDER_ID" => $ORDER_ID)
);
while ($arBasket = $dbBasket->Fetch())
{
$receipt['items'][] = array(
                        'quantity' => $arBasket[QUANTITY],
                        'text' => substr($arBasket[NAME], 0, 128),
                        'tax' => 1,
                        'price' => array(
                            'amount' => number_format($arBasket[PRICE], 2, '.', ''),
                            'currency' => 'RUB'
                        ),
                    );

}

if ($arOrder[PRICE_DELIVERY]>0) {
	$receipt['items'][] = array(
		'quantity' => 1,
		'text' => substr('Äîñòàâêà', 0, 128),
		'tax' => 1,
		'price' => array(
			'amount' => number_format($arOrder[PRICE_DELIVERY], 2, '.', ''),
			'currency' => 'RUB'
		),
	);
}

$eValue = \Bitrix\Main\Web\Json::encode($receipt, $options = null);

//end overt
$Sum = number_format($Sum, 2, ',', '');
?>
<font class="tablebodytext">
Услугу предоставляет сервис онлайн-платежей <b>&laquo;Яндекс.Касса&raquo;</b>.<br /><br />
Сумма к оплате по счету: <b><?=$Sum?> р.</b><br />
<br />
</font>
<?if(strlen(CSalePaySystemAction::GetParamValue("IS_TEST")) > 0):
	?>
	<form name="ShopForm" action="https://demomoney.yandex.ru/eshop.xml" method="post" target="_blank">
<?else:
	?>
	<form name="ShopForm" action="https://money.yandex.ru/eshop.xml" method="post">
<?endif;?>
<font class="tablebodytext">
<input name="ShopID" value="<?=$ShopID?>" type="hidden">
<input name="scid" value="<?=$scid?>" type="hidden">
<input name="customerNumber" value="<?=$customerNumber?>" type="hidden">
<input name="orderNumber" value="<?=$orderNumber?>" type="hidden">
<input name="Sum" value="<?=$Sum?>" type="hidden">
<input name="paymentType" value="<?=$paymentType?>" type="hidden">
<input name="cms_name" value="1C-Bitrix" type="hidden">
<input name="ym_merchant_receipt" value='<?=$eValue?>' type="hidden"><?//overt?>

<!-- <br /> -->
<!-- Детали заказа:<br /> -->
<!-- <input name="OrderDetails" value="заказ №<?=$orderNumber?> (<?=$orderDate?>)" type="hidden"> -->
<br />
<input name="BuyButton" value="Оплатить" type="submit">

</font><p><font class="tablebodytext"><b>Обратите внимание:</b> если вы откажетесь от покупки, для возврата денег вам придется обратиться в магазин.</font></p>
</form>
