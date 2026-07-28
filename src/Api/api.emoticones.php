<?php

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

/**
 * Controlador AJAX
 *
 * @name    ajax.emoticones.php
 * @author  PHPost Team
*/
/**********************************\

*	(VARIABLES POR DEFAULT)		*

\*********************************/

	// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCIÓN
	$files = [
		'emoticonos' => ['n' => 2, 'p' => ''],
	];

/**********************************\

* (VARIABLES LOCALES ESTE ARCHIVO)	*

\*********************************/

	// REDEFINIR VARIABLES
	$tsPage = 'p.emoticonos.'.$files[$action]['p'];
	$tsLevel = $files[$action]['n'];
	$tsAjax = empty($files[$action]['p']) ? 1 : 0;

/**********************************\

*	(INSTRUCCIONES DE CODIGO)		*

\*********************************/
	
	// DEPENDE EL NIVEL
	$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
	if($tsLevelMsg != 1) { echo '0: '.$tsLevelMsg['mensaje']; die();}
	// HTML
    $emoticones = [
        ["(A)","014.png"],
        [":[","043.png"],
        [":-#","048.png"],
        [":-*","052.png"],
        ["+o(","053.png"],
        ["(brb)","066.gif"],
        [":^)","072.gif"],
        ["*-)","073.gif"],
        ["<o)","075.gif"],
        ["8-)","076.gif"],
        ["|-)","078.gif"],
        [";-/","082.png"],
        ["(jk)","084.png"],
        ["(j)","086.png"],
        ["(V)","087.png"],
        ["(lol)","089.gif"],
        ["(xD)","090.png"],
        [":8)","088.png"],
        ["(ff)","091.gif"],
        ["(fm)","092.gif"],
        [":'|","093.gif"],
        [":]","094.gif"],
        [":}","095.png"],
        ["(BOO)","096.png"],
        ["*|","097.gif"],
        ["*\\","098.png"],
        ["(wm)","100.png"],
        ["(xo)","101.gif"],
        // OBJETOS
        ["(l)","015.png"],
        ["(u)","016.png"],
        ["(@)","018.png"],
        ["(&)","019.png"],
        ["(S)","020.png"],
        ["(*)","021.png"],
        ["(~)","022.png"],
        ["(8)","023.png"],
        ["(E)","024.png"],
        ["(F)","025.png"],
        ["(W)","026.png"],
        ["(O)","027.gif"],
        ["(K)","028.png"],
        ["(G)","029.png"],
        ["(^)","030.png"],
        ["(P)","031.png"],
        ["(I)","032.png"],
        ["(C)","033.png"],
        ["(T)","034.png"],
        ["({)","035.png"],
        ["(})","036.png"],
        ["(B)","037.png"],
        ["(D)","038.png"],
        ["(Z)","039.png"],
        ["(X)","040.png"],
        ["(Y)","041.png"],
        ["(N)","042.png"],
        ["(nnh)","044.png"],
        ["( #)","046.png"],
        ["(R)","047.png"],
        ["(sn)","054.png"],
        ["(tu)","055.png"],
        ["(pl)","056.png"],
        ["(||)","057.png"],
        ["(pi)","058.png"],
        ["(so)","059.png"],
        ["(au)","060.png"],
        ["(ap)","061.png"],
        ["(um)","062.png"],
        ["(ip)","063.png"],
        ["(co)","064.png"],
        ["(mp)","065.png"],
        ["(st)","067.png"],
        ["(pu)","102.png"],
        ["(yn)","068.png"],
        ["(h5)","069.gif"],
        ["(mo)","070.png"],
        ["(bah)","071.png"],
        ["(li)","074.gif"],
        ["(wo)","077.png"],
        ["'.'","080.png"],
        ["(bus)","045.png"],
        ["*p*","079.png"],
        ["*s*","085.png"],
        ["(M)","017.png"],
        ["(xx)","103.png"],
];
    // 
    foreach($emoticones as $key => $emo){
        echo '<a smile="'.$emo[0].'" href="#"><img src="'.$tsCore->settings['default'].'/images/smiles/'.$emo[1].'" style="margin:auto 2px;"/></a>';
    }
?>
