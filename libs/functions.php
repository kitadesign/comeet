<?php

/**
 * 外部インプットデータの場合のエスケープ
 */
function html ($str) {
	$str = htmlspecialchars( $str );
	return $str;
}

/**
 * テンプレートをJSに渡すためのJSGateway
 */
function jsGateway ( $key, $template ) {
	$tempString = file_get_contents( TEMPLATES_DIR . $template );
	$head = '<div id="js_gateway_'.$key.'" style="display:none;"><!--';
	$foot = '--></div>';
	$tempString = preg_replace( '[\n|\r|\nr|\t]', '', $tempString );
	$tempString = preg_replace( '/<!--[\s\S]*?-->/', '', $tempString );
	echo( $head . $tempString . $foot );
}

/**
 * Ajaxやり取りのためのシグネチャ生成
 */
function getSignature ( $name, $base ) {
	return base64_encode( sha1( $name . '_' . $base . Conf::SIGNATURE_SOLT ) );
}

/**
 * シグネチャのチェック
 */
function isMatchSignature ( $name, $base, $signature ) {
	if ( strlen( $name ) == 0 ) return false;
	if ( strlen( $base ) == 0 ) return false;
	if ( strlen( $signature ) == 0 ) return false;
	return ( getSignature( $name, $base ) == $signature );
}

/**
 * TELを数字のみからハイフン入りに変える
 */
function parseTel( $tel ) {
	$ex_list = array(
		'09969','09913','09912','09802','09496',
		'08636','08514','08512','08477','08396',
		'08388','08387','07468','05979','05974',
		'05769','04998','04996','04994','04992',
		'01658','01656','01655','01654','01648',
		'01635','01634','01632','01587','01586',
		'01564','01558','01547','01466','01457',
		'01456','01398','01397','01392','01377',
		'01374','01372','01267','0997','0996',
		'0995','0994','0993','0987','0986',
		'0985','0984','0983','0982','0980',
		'0979','0978','0977','0974','0973',
		'0972','0969','0968','0967','0966',
		'0965','0964','0959','0957','0956',
		'0955','0954','0952','0950','0949',
		'0948','0947','0946','0944','0943',
		'0942','0940','0930','0920','0898',
		'0897','0896','0895','0894','0893',
		'0892','0889','0887','0885','0884',
		'0883','0880','0879','0877','0875',
		'0869','0868','0867','0866','0865',
		'0863','0859','0858','0857','0856',
		'0855','0854','0853','0852','0848',
		'0847','0846','0845','0838','0837',
		'0836','0835','0834','0833','0829',
		'0827','0826','0824','0823','0820',
		'0799','0798','0797','0796','0795',
		'0794','0791','0790','0779','0778',
		'0776','0774','0773','0772','0771',
		'0770','0768','0767','0766','0765',
		'0763','0761','0749','0748','0747',
		'0746','0745','0744','0743','0742',
		'0740','0739','0738','0737','0736',
		'0735','0725','0721','0599','0598',
		'0597','0596','0595','0594','0587',
		'0586','0585','0584','0581','0578',
		'0577','0576','0575','0574','0573',
		'0572','0569','0568','0567','0566',
		'0565','0564','0563','0562','0561',
		'0558','0557','0556','0555','0554',
		'0553','0551','0550','0548','0547',
		'0545','0544','0539','0538','0537',
		'0536','0533','0532','0531','0495',
		'0494','0493','0480','0479','0478',
		'0476','0475','0470','0467','0466',
		'0465','0463','0460','0439','0438',
		'0436','0428','0422','0299','0297',
		'0296','0295','0294','0293','0291',
		'0289','0288','0287','0285','0284',
		'0283','0282','0280','0279','0278',
		'0277','0276','0274','0270','0269',
		'0268','0267','0266','0265','0264',
		'0263','0261','0260','0259','0258',
		'0257','0256','0255','0254','0250',
		'0248','0247','0246','0244','0243',
		'0242','0241','0240','0238','0237',
		'0235','0234','0233','0229','0228',
		'0226','0225','0224','0223','0220',
		'0198','0197','0195','0194','0193',
		'0192','0191','0187','0186','0185',
		'0184','0183','0182','0179','0178',
		'0176','0175','0174','0173','0172',
		'0167','0166','0165','0164','0163',
		'0162','0158','0157','0156','0155',
		'0154','0153','0152','0146','0145',
		'0144','0143','0142','0139','0138',
		'0137','0136','0135','0134','0133',
		'0126','0125','0124','0123','099',
		'098','097','096','095','093','092',
		'089','088','087','086','084','083',
		'082','079','078','077','076','075',
		'073','072','059','058','055','054',
		'053','052','049','048','047','046',
		'045','044','043','042','029','028',
		'027','026','025','024','023','022',
		'019','018','017','015','011',
		'06','04','03'
		,'070', '090', '080', '050', '0120'	
	);

	$last = substr($tel, -4);
	$tlen = strlen($tel);
	for ( $i = 5; $i > 1; $i-- ) {
		$t = substr( $tel, 0, $i );
		$idx = array_search( $t, $ex_list );
		if ( $idx !== FALSE ) {
			$first = $ex_list[$idx];
			$second = substr( $tel, $i, $tlen - 4 - $i );
			return $first . '-' . $second . '-' . $last;
		}
	}
	return $tel;
}
