<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

if (class_exists('SellaciousHelper'))
{
	require_once __DIR__ . '/helper.php';

	/** @var  Joomla\Registry\Registry $params */
	$helper   = SellaciousHelper::getInstance();
	$jInput   = JFactory::getApplication()->input;
	$classSfx = $params->get('class_sfx', '');
	$zoom     = (int) $params->get('zoom', '');
	$location = (string) $params->get('location', '');

	$markers = ModSellaciousUsersOnMap::getUsersMarkers($params);

	require JModuleHelper::getLayoutPath('mod_sellacious_usersonmap');
}

