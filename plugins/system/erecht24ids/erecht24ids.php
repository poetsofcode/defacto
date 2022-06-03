<?php
/**
 * @package     eRecht24 Plugin
 * @subpackage  System.Erecht24ids
 *
 * @copyright   (C) eRecht24 - Karsten Dietrich
 * @link 		https://www.e-recht24.de Official website
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgSystemErecht24ids extends JPlugin{

	public function onBeforeCompileHead() {
	    $app = JFactory::getApplication();
		$document  = JFactory::getDocument();
		
		$gauid = $this->params->get('gauid');
		
		if (!$app->isClient('administrator'))
		{
    		if(!empty($gauid)){
    			$privacycode = $this->params->get('privacycode');
    			$code =	"
					var gaProperty = '".$gauid."';
					var disableStr = 'ga-disable-' + gaProperty;
    				if (document.cookie.indexOf(disableStr + '=true') > -1) {
    					window[disableStr] = true;
    				}
    				function gaOptout() {
    					document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
    					window[disableStr] = true;
    					alert('Das Tracking durch Google Analytics wurde in Ihrem Browser für diese Website deaktiviert.');
    				}
					</script>
					<script async src=\"https://www.googletagmanager.com/gtag/js?id=".$gauid."\"></script>
					<script>
						window.dataLayer = window.dataLayer || [];
						function gtag(){dataLayer.push(arguments);}
						gtag('js', new Date());
						gtag('config', '".$gauid."', { 'anonymize_ip': true });
					</script><script type=\"text/javascript\">";
    				
    			$document->addScriptDeclaration($code);
    		}
		}
	}
	
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		
		$imprintcode = $this->params->get('imprintcode');
		$privacycode = $this->params->get('privacycode');
		$regex_hl = '/<h1>[^<]+<\/h1>/iUs';
		
		if(preg_match($regex_hl,$imprintcode,$match)){
			$imprintcode = preg_replace($regex_hl,'',$imprintcode);
		}
		$regex = '/^.*{erecht24_impressum}.*$/i';
		
		$row->text = preg_replace($regex, $imprintcode, $row->text);
		
		if(preg_match($regex_hl,$privacycode,$match)){
			$privacycode= preg_replace($regex_hl,'',$privacycode);
		}
		$regex = '/^.*{erecht24_datenschutz}.*$/i';
		$row->text = preg_replace($regex, $privacycode, $row->text);
	
	}

}
?>