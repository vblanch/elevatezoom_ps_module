<?php
 
/*  Copyright 2014  Victor Blanch  (email : victor@vblanch.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('_PS_VERSION_'))
  exit;

class ElevateZoom extends Module
{

	public $cursors;
	public $zoomType;
	public $lensShape;
		
	public function __construct()
	{
		$this->name = 'elevatezoom';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Victor Blanch';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.5.9.9');

		parent::__construct();	//needed for translations

		$this->displayName = $this->l('elevateZoom for Prestashop');
		$this->description = $this->l('Enables zooming on images and magnifier effects.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		
		/* some constants from css and elevateZoom plugin */
		$this->cursors = array("auto", "default", "crosshair", "pointer", "-moz-zoom-in");		
		$this->zoomType = array("inner", "lens", "window");
		$this->zoomTypeTr = array($this->l('inner'), $this->l('lens'), $this->l('window'));
		$this->lensShape = array("square","round"); 
		$this->lensShapeTr = array($this->l('square'), $this->l('round'));
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
		/* set default values when installing */

		return (parent::install() &&
			Configuration::updateValue('ELEVATEZOOM_PRODUCT', 1) &&
			
			Configuration::updateValue('ELEVATEZOOM_ZOOM_TYPE', 'inner') &&
			Configuration::updateValue('ELEVATEZOOM_FADE_IN', 400) &&
			Configuration::updateValue('ELEVATEZOOM_FADE_OUT', 550) &&
			Configuration::updateValue('ELEVATEZOOM_WINDOW_POS', 1) &&
			
			Configuration::updateValue('ELEVATEZOOM_SCROLL', 'false') &&
			Configuration::updateValue('ELEVATEZOOM_EASING', 'true') &&
			Configuration::updateValue('ELEVATEZOOM_CURSOR_TYPE', 'crosshair') &&
			
			Configuration::updateValue('ELEVATEZOOM_TINT', 'false') &&
			Configuration::updateValue('ELEVATEZOOM_TINT_COLOR', '#333') &&
			Configuration::updateValue('ELEVATEZOOM_TINT_OPACITY', 0.4) &&
			
			Configuration::updateValue('ELEVATEZOOM_LENS_SHAPE', 'square') &&
			Configuration::updateValue('ELEVATEZOOM_LENS_SIZE', 200) &&
				
			Configuration::updateValue('ELEVATEZOOM_EXTRA_PARAMS', '') &&
			
			Configuration::updateValue('ELEVATEZOOM_OTHER', 0) &&
			Configuration::updateValue('ELEVATEZOOM_OTHER_CODE', '$("a.product_image img").elevateZoom({zoomType : "lens", lensShape : "round", lensSize : 200});') &&
											
			$this->registerHook('header'));	//install and register the module on header hook
	}
	
	public function uninstall()
	{
		if (!parent::uninstall() ||
			!Configuration::deleteByName('ELEVATEZOOM_PRODUCT') ||
			!Configuration::deleteByName('ELEVATEZOOM_ZOOM_TYPE') ||
			!Configuration::deleteByName('ELEVATEZOOM_FADE_IN') ||
			!Configuration::deleteByName('ELEVATEZOOM_FADE_OUT') ||
			!Configuration::deleteByName('ELEVATEZOOM_WINDOW_POS') ||
			!Configuration::deleteByName('ELEVATEZOOM_SCROLL') ||
			!Configuration::deleteByName('ELEVATEZOOM_EASING') ||
			!Configuration::deleteByName('ELEVATEZOOM_CURSOR_TYPE') ||
			!Configuration::deleteByName('ELEVATEZOOM_TINT') ||
			!Configuration::deleteByName('ELEVATEZOOM_TINT_COLOR') ||
			!Configuration::deleteByName('ELEVATEZOOM_TINT_OPACITY') ||
			!Configuration::deleteByName('ELEVATEZOOM_LENS_SHAPE') ||
			!Configuration::deleteByName('ELEVATEZOOM_LENS_SIZE') ||
			!Configuration::deleteByName('ELEVATEZOOM_EXTRA_PARAMS') ||
			!Configuration::deleteByName('ELEVATEZOOM_OTHER') ||
			!Configuration::deleteByName('ELEVATEZOOM_OTHER_CODE'))
			return false;
		return true;
	}	
	
	//show configure button in backend and process the form/s
	public function getContent()
	{
		$output = null;
		$output .= '<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'modules/elevatezoom/css/styles.css"/>';										
		$output .= '<h2>'.$this->displayName.'</h2>';		
	
		if (Tools::isSubmit('submitElevatezoom'))
		{
			$product = (int)(Tools::getValue('zoom_product'));
			$fadein  = (int)(Tools::getValue('zoom_fade_in'));
			$fadeout = (int)(Tools::getValue('zoom_fade_out'));
			$windowpos = (int)(Tools::getValue('zoom_window_pos'));
			$scroll = Tools::getValue('zoom_scroll');
			$easing = Tools::getValue('zoom_easing');
			$cursortype = Tools::getValue('cursor_type');
			$zoomtype = Tools::getValue('zoom_type');
			$tint = Tools::getValue('zoom_tint');
			$tintcolor = Tools::getValue('zoom_tint_color');
			$tintopacity = Tools::getValue('zoom_tint_opacity');
			$lensshape = Tools::getValue('lens_shape');
			$lenssize = Tools::getValue('lens_size');
			$extraparams = Tools::getValue('zoom_extra_params'); 
			
			/* zoom for product page param */
			Configuration::updateValue('ELEVATEZOOM_PRODUCT', $product);		
			
			/* zoom type param */
			Configuration::updateValue('ELEVATEZOOM_ZOOM_TYPE', $zoomtype); 
			
			if ($fadein < 0)
				$output .= '<div class="alert error">'.$this->l('Invalid zoom fade in value').'</div>';
			else
				Configuration::updateValue('ELEVATEZOOM_FADE_IN', $fadein);					

			/* fading params */			
			if ($fadeout < 0)
				$output .= '<div class="alert error">'.$this->l('Invalid zoom fade out value').'</div>';
			else
				Configuration::updateValue('ELEVATEZOOM_FADE_OUT', $fadeout);			
				
			if ($windowpos < 1 || $windowpos > 16)
				$output .= '<div class="alert error">'.$this->l('Invalid window position value').'</div>';
			else
				Configuration::updateValue('ELEVATEZOOM_WINDOW_POS', $windowpos);										
				
			/* scroll, easing, cursor params */			
			Configuration::updateValue('ELEVATEZOOM_SCROLL', $scroll);		
			Configuration::updateValue('ELEVATEZOOM_EASING', $easing);	
			Configuration::updateValue('ELEVATEZOOM_CURSOR_TYPE', $cursortype);
			
			/* tint params */
			Configuration::updateValue('ELEVATEZOOM_TINT', $tint);			
			Configuration::updateValue('ELEVATEZOOM_TINT_COLOR', $tintcolor);
			
			if ($tintopacity < 0.0)
				$output .= '<div class="alert error">'.$this->l('Invalid tint opacity value').'</div>';	
			else
				Configuration::updateValue('ELEVATEZOOM_TINT_OPACITY', $tintopacity); 			
			
			/* lens params */
			Configuration::updateValue('ELEVATEZOOM_LENS_SHAPE', $lensshape); 
			
			if ($lenssize < 0)
				$output .= '<div class="alert error">'.$this->l('Invalid lens size value').'</div>';
			else
				Configuration::updateValue('ELEVATEZOOM_LENS_SIZE', (int)($lenssize));				
			
			/* extra params */
			Configuration::updateValue('ELEVATEZOOM_EXTRA_PARAMS', $extraparams); 			
						
			$output .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';			
		}
		
		/* other code for extra calls */
		if (Tools::isSubmit('submitElevatezoomExtra'))
		{		
			//other params for extra eleavateZoom calls
			$other = (int)(Tools::getValue('zoom_other'));
			$other_code = Tools::getValue('zoom_other_code');
			
			Configuration::updateValue('ELEVATEZOOM_OTHER', $other);
			Configuration::updateValue('ELEVATEZOOM_OTHER_CODE', $other_code);
		}
		
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$html = '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Product page zoom settings').'</legend>
				
				<p class="">'.$this->l('Note: Please remember that this module requires to turn off JqZoom in Preferences > Products to work properly.').'</p>
				<br class="clear"/>
				
				<label for="zoom_product">'.$this->l('Activate elevateZoom for product page').'</label>
				<div class="margin-form">
					<input type="radio" name="zoom_product" id="zoom_product_on" value="1" '.(Tools::getValue('zoom_product', Configuration::get('ELEVATEZOOM_PRODUCT')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_product_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="zoom_product" id="zoom_product_off" value="0" '.(!Tools::getValue('zoom_product', Configuration::get('ELEVATEZOOM_PRODUCT')) ? 'checked="checked" ' : '').'/>					
					<label class="t" for="zoom_product_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="preference_description">'.$this->l('Set if elevateZoom is active in product page with the parameters shown below.').'</p>
				</div>
				<br class="clear"/>
				
				<label for="zoom_type">'.$this->l('Zoom type').'</label>
				<div class="margin-form">
					<select id="zoom_type" name="zoom_type">';	 
					for($i=0; $i<count($this->zoomType); $i++){
						$zoom = $this->zoomType[$i];
						$zoom_tr = $this->zoomTypeTr[$i];  
						$html .= '<option value="'.$zoom.'"'.(Configuration::get('ELEVATEZOOM_ZOOM_TYPE')==$zoom ? ' selected="selected"' : '').'>'.$zoom_tr.'</option>';
					}
					$html .= '</select>
					<p class="preference_description">'.$this->l('Select the zoom type: inner (on the same image), lens (magnfier effect) or window (in a separate window).').'</p>
				</div>
				<br class="clear"/>

				<label for="zoom_fade_in">'.$this->l('Fade in delay').'</label>
				<div class="margin-form">
					<input id="zoom_fade_in" type="text" name="zoom_fade_in" value="'.(int)Configuration::get('ELEVATEZOOM_FADE_IN').'" style="width:250px" />
					<p class="preference_description">'.$this->l('Set the fade in delay in milliseconds.').'</p>
				</div>
				<br class="clear"/>			
				
				<label for="zoom_fade_out">'.$this->l('Fade out delay').'</label>
				<div class="margin-form">
					<input id="zoom_fade_out" type="text" name="zoom_fade_out" value="'.(int)Configuration::get('ELEVATEZOOM_FADE_OUT').'" style="width:250px" />					
					<p class="preference_description">'.$this->l('Set the fade out delay in milliseconds.').'</p>
				</div>
				<br class="clear"/>				
				
				<label for="zoom_window_pos">'.$this->l('Window position').'</label>
				<div class="margin-form">
					<!--<input id="zoom_window_pos" type="text" name="zoom_window_pos" value="'.(int)Configuration::get('ELEVATEZOOM_WINDOW_POS').'" style="width:250px" />-->					
					<select id="zoom_window_pos" name="zoom_window_pos">';	
					for($i=1; $i<=16; $i++){
						$html .= '<option value="'.$i.'"'.(Configuration::get('ELEVATEZOOM_WINDOW_POS')==$i ? ' selected="selected"' : '').'>'.$i.'</option>';
					}					
					$html .= '</select>					
					<p class="preference_description">'.$this->l('Set the window position. Must be an integer between 1 and 16. See the positions').
					' <a target="_blank" href="'.$this->_path.'/img/window-positions.png" alt="Window positions" title="Window positions">'.$this->l('here')
					.'.</a></p>
				</div> 
				<br class="clear"/>			

				<label for="zoom_scroll">'.$this->l('Scroll with mousewheel').'</label>
				<div class="margin-form">
					<input type="radio" name="zoom_scroll" id="zoom_scroll_on" value="true" '.(Configuration::get('ELEVATEZOOM_SCROLL')=='true' ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_scroll_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="zoom_scroll" id="zoom_scroll_off" value="false" '.(Configuration::get('ELEVATEZOOM_SCROLL')=='false' ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_scroll_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="preference_description">'.$this->l('Set if scrolling in and out with the mousewheel is allowed.').'</p>
				</div>
				<br class="clear"/>				

				<label for="zoom_easing">'.$this->l('Use easing').'</label>
				<div class="margin-form">
					<input type="radio" name="zoom_easing" id="zoom_easing_on" value="true" '.(Configuration::get('ELEVATEZOOM_EASING')=='true' ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_easing_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="zoom_easing" id="zoom_easing_off" value="false" '.(Configuration::get('ELEVATEZOOM_EASING')=='false' ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_easing_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="preference_description">'.$this->l('Set if easing is used or not.').'</p>					
				</div>
				<br class="clear"/>							

				<label for="cursor_type">'.$this->l('Cursor type').'</label>
				<div class="margin-form">
					<select id="cursor_type" name="cursor_type">';	
					foreach ($this->cursors as $cursor)				
						$html .= '<option value="'.$cursor.'"'.(Configuration::get('ELEVATEZOOM_CURSOR_TYPE')==$cursor ? ' selected="selected"' : '').'>'.$cursor.'</option>';
					$html .= '</select>
					<p class="preference_description">'.$this->l('Choose the cursor icon that will be displayed over the image.').'</p>
				</div>
				<br class="clear"/>		
								
				<label for="zoom_tint">'.$this->l('Use tint').'</label>
				<div class="margin-form">
					<input type="radio" name="zoom_tint" id="zoom_tint_on" value="true" '.(Configuration::get('ELEVATEZOOM_TINT')=='true' ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_tint_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="zoom_tint" id="zoom_tint_off" value="false" '.(Configuration::get('ELEVATEZOOM_TINT')=='false' ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_tint_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="preference_description">'.$this->l('Set if the image will be tinted when zooming.').'</p>					
				</div>
				<br class="clear"/>		
				
				<label for="zoom_tint_color">'.$this->l('Tint color').'</label>
				<div class="margin-form">
					<input id="zoom_tint_color" type="text" name="zoom_tint_color" value="'.Configuration::get('ELEVATEZOOM_TINT_COLOR').'" style="width:250px" />
					<p class="preference_description">'.$this->l('Set the color of the tint in case tinting is active. Can be any valid css color: red, #ccc, rgb(0,0,0), etc.').'</p>
				</div>
				<br class="clear"/>
				
				<label for="zoom_tint_opacity">'.$this->l('Tint opacity').'</label>
				<div class="margin-form">
					<input id="zoom_tint_opacity" type="text" name="zoom_tint_opacity" value="'.(float)Configuration::get('ELEVATEZOOM_TINT_OPACITY').'" style="width:250px" />
					<p class="preference_description">'.$this->l('Set the tint opacity percentage in case tinting is active. Must be float between 0.0 and 1.0.').'</p>
				</div>
				<br class="clear"/>							
								
				<label for="lens_shape">'.$this->l('Lens shape').'</label>
				<div class="margin-form">
					<select id="lens_shape" name="lens_shape">';						
					for($i=0; $i<count($this->lensShape); $i++){
						$shape = $this->lensShape[$i];
						$shape_tr = $this->lensShapeTr[$i];
						$html .= '<option value="'.$shape.'"'.(Configuration::get('ELEVATEZOOM_LENS_SHAPE')==$shape ? ' selected="selected"' : '').'>'.$shape_tr.'</option>';
					}
					$html .= '</select>
					<p class="preference_description">'.$this->l('Choose a lens shape in case the zoom mode is \'lens\'.').'</p>
				</div>
				<br class="clear"/>				 

				<label for="lens_size">'.$this->l('Lens size').'</label>
				<div class="margin-form">
					<input id="lens_size" type="text" name="lens_size" value="'.(int)Configuration::get('ELEVATEZOOM_LENS_SIZE').'" style="width:250px" />
					<p class="preference_description">'.$this->l('Set the lens size in pixels in case the zoom mode is \'lens\'.').'</p>
				</div>
				<br class="clear"/>	

				<label for="zoom_extra_params">'.$this->l('Extra parameters').'</label>
				<div class="margin-form">
					<input id="zoom_extra_params" type="text" name="zoom_extra_params" value="'.Configuration::get('ELEVATEZOOM_EXTRA_PARAMS').'" style="width:250px" />
					<p class="preference_description">'.$this->l('Put any extra option parameters that you want for the elevateZoom jQuery plugin, comma-separated.').'</p>					
					<p class="preference_description">'.$this->l('Check the elevateZoom homepage (see Credits) for details about other parameters or look at the jquery.elevatezoom.js file.').'</p>
					<p class="preference_description">'.$this->l('Example: zoomWindowWidth:300, zoomWindowHeight:100').'</p>
				</div>
				<br class="clear"/>
								
				<p class="center"><input type="submit" name="submitElevatezoom" value="'.$this->l('Save').'" class="button" /></p>
			</fieldset>
		</form>'; 
		
		$html .='
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Additional zoom settings').'</legend>
			
				<label for="zoom_other">'.$this->l('Activate elevateZoom additional code').'</label>
				<div class="margin-form">
					<input type="radio" name="zoom_other" id="zoom_other_on" value="1" '.(Tools::getValue('zoom_other', Configuration::get('ELEVATEZOOM_OTHER')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="zoom_other_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="zoom_other" id="zoom_other_off" value="0" '.(!Tools::getValue('zoom_other', Configuration::get('ELEVATEZOOM_OTHER')) ? 'checked="checked" ' : '').'/>					
					<label class="t" for="zoom_other_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="preference_description">'.$this->l('Set if additional code to apply elevateZoom will be executed.').'</p>
				</div>
				<br class="clear"/>
				
				<label for="zoom_other_code">'.$this->l('Additional code').'</label>
				<div class="margin-form">
					<textarea name="zoom_other_code" rows="10" cols="70">'.Tools::safeOutput(Configuration::get('ELEVATEZOOM_OTHER_CODE')).'</textarea>
					<p class="preference_description">'.$this->l('Put here any additional JavaScript or jQuery code to apply elevateZoom jQuery plugin.').'</p>
					<p class="preference_description">'.$this->l('Example to apply to product lists').': $("a.product_image img").elevateZoom({zoomType : "lens", lensShape : "round", lensSize : 200});</p>
				</div>
				<br class="clear"/>				

				<p class="center"><input type="submit" name="submitElevatezoomExtra" value="'.$this->l('Save').'" class="button" /></p>				
			</fieldset>
		</form>
		';
		
		
		$html .= '	
			<fieldset class="fieldset-credits">
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Credits').'</legend>
				<div>
					<p>'.$this->l('This module is based on:').'</p>
					<p>'.$this->l('jQuery elevateZoom 3.0.8').'</p>
					<p>Copyright (c) 2012 Andrew Eades</p>
					<p><a target="_blank" href="http://www.elevateweb.co.uk">http://www.elevateweb.co.uk</a></p>
					<p>'.$this->l('Dual licensed under the GPL and MIT licenses.').'</p>	
					<p>'.$this->l('Adapted to Prestashop and turned into a module by Victor Blanch.').'</p>	
					<p><a target="_blank" href="http://vblanch.com">http://vblanch.com</a></p>
				</div>					
			</fieldset>
		';				
		
		$html .= '	
			<fieldset class="fieldset-donate">
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Donate').'</legend>
				<div>'.$this->l('If you like this module, please consider making a donation to support the author using Paypal, Bitcoin, Litecoin or Dogecoin').':</div> 
				<div class="margin-form">	</div>	
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
					<input type="hidden" name="cmd" value="_donations">
					<input type="hidden" name="business" value="victor@vblanch.com">
					<input type="hidden" name="lc" value="US">
					<input type="hidden" name="item_name" value="Hookmanager PS Module">
					<input type="hidden" name="no_note" value="0">
					<input type="hidden" name="currency_code" value="EUR">
					<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
					<input type="image" src="https://www.paypalobjects.com/es_XC/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea.">
					<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
					</form>
					<div class="margin-form">	</div>	
					<div class="pay-method">
						Bitcoin : 12NZncFaCSv5xE8GCVDFBMaCAoLDDmqhL4
					</div>					
					<div class="margin-form">	</div>	
					<div class="pay-method">
						Litecoin: LYqdGQ9Eu2XCva6kSHWJ3uSTxBivFyfDNM
					</div>					
					<div class="margin-form">	</div>	
					<div class="pay-method">
						Dogecoin: DShCGaE7c9Ur9N29kd7Wfs7xqTCQTKoLAg
					</div>														
			</fieldset>
		';		
		
		return $html;
	}

	//hooks module to header
	public function hookHeader($params)
	{	
		$this->smarty->assign(array(		
			'zoom_product' =>       (int)Configuration::get('ELEVATEZOOM_PRODUCT'),
			'zoom_type' =>			Configuration::get('ELEVATEZOOM_ZOOM_TYPE'),
			'zoom_fade_in' =>     	(int)(Configuration::get('ELEVATEZOOM_FADE_IN')),
			'zoom_fade_out' =>    	(int)(Configuration::get('ELEVATEZOOM_FADE_OUT')),
			'zoom_window_pos' =>    (int)(Configuration::get('ELEVATEZOOM_WINDOW_POS')),
			'zoom_scroll' =>    	Configuration::get('ELEVATEZOOM_SCROLL'),
			'zoom_easing' =>    	Configuration::get('ELEVATEZOOM_EASING'),
			'zoom_cursor_type' => 	Configuration::get('ELEVATEZOOM_CURSOR_TYPE'),
			'zoom_tint' => 			Configuration::get('ELEVATEZOOM_TINT'),
			'zoom_tint_color' => 	Configuration::get('ELEVATEZOOM_TINT_COLOR'),
			'zoom_tint_opacity' => 	Configuration::get('ELEVATEZOOM_TINT_OPACITY'),
			'zoom_lens_shape' => 	Configuration::get('ELEVATEZOOM_LENS_SHAPE'),
			'zoom_lens_size' =>     (int)(Configuration::get('ELEVATEZOOM_LENS_SIZE')),
			'zoom_extra_params'=>   Configuration::get('ELEVATEZOOM_EXTRA_PARAMS'),
			'zoom_other' =>       	(int)Configuration::get('ELEVATEZOOM_OTHER'),
			'zoom_other_code' =>	Configuration::get('ELEVATEZOOM_OTHER_CODE')		
		));						 
		
		return $this->display(__FILE__, 'elevatezoom.tpl');			
	}
}