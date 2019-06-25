<?php
/**
 * Magento Snowfall extension
 *
 * @category   Magecomp
 * @package    Magecomp_Snowfall
 * @author     Magecomp Snowfall
 */
class Magecomp_Snowfall_Block_Init extends Mage_Core_Block_Template
{
	public function getJsonOptions()
	{
		$options = new stdClass();
		$options->flakes = Mage::getStoreConfig('snowfall/snowfall_group/snowfall_number');
		$options->color = explode(',', Mage::getStoreConfig('snowfall/snowfall_group/snowfall_colors'));
		$options->text = Mage::getStoreConfig('snowfall/snowfall_group/snowfall_text');
		$options->speed = Mage::getStoreConfig('snowfall/snowfall_group/snowfall_speed');
		$options->size = (object) array(
			'min' => Mage::getStoreConfig('snowfall/snowfall_group/snowfall_minsize'),
			'max' => Mage::getStoreConfig('snowfall/snowfall_group/snowfall_maxsize')
		);
			
		return json_encode($options);
	}
}