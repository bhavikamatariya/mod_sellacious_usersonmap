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

class ModSellaciousUsersOnMap
{
	/**
	 * Method to list users addresses.
	 *
	 * @param  $params
	 *
	 * @return stdClass[]
	 * @throws Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getUsersMarkers($params)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$helper     = SellaciousHelper::getInstance();
		$categories = $params->get('categories', 0);

		if (empty($categories))
		{
			return null;
		}

		$query->select('u.id, u.name, u.email')
			->from('#__users u')
			->group('u.id')

			->select('a.mobile, a.website')
			->join('LEFT', '#__sellacious_profiles a ON a.user_id = u.id')

			->select('client.category_id as client_cat')
			->join('LEFT', '#__sellacious_clients client ON client.user_id = u.id')

			->select('mfr.category_id as mfr_cat')
			->join('LEFT', '#__sellacious_manufacturers mfr ON mfr.user_id = u.id')

			->select('staff.category_id as staff_cat')
			->join('LEFT', '#__sellacious_staffs staff ON staff.user_id = u.id')

			->select('seller.category_id as seller_cat')
			->join('LEFT', '#__sellacious_sellers seller ON seller.user_id = u.id')

			->select('ad.*')
			->join('LEFT', '#__sellacious_addresses ad ON ad.user_id = u.id')
			->where('ad.plot_on_map = 1');

		// Filter by category(ies)
		if (!empty($categories))
		{
			$categories = implode(',', $categories);
			$cond       = array(
				'client.category_id IN (' . $categories . ')',
				'seller.category_id IN (' . $categories . ')',
				'staff.category_id IN (' . $categories . ')',
				'mfr.category_id IN (' . $categories . ')'
			);

			$query->where('(' . implode(' OR ', $cond) . ')');
		}

		try
		{
			$users = $db->setQuery($query)->loadObjectList();

			$markers = array();
			foreach ($users as $i => $user)
			{
				$marker          = array();
				$marker['title'] = $user->name;

				$description = $user->company;
				$description .= '<br>' . $user->name;
				$address     = '';

				if (!empty($user->website))
				{
					$description .= '<br>' . '<a target="_blank" href="' . $user->website . '">' . $user->website . '</a>';
				}

				if (!empty($user->address))
				{
					$description .= '<br>' . $user->address;
					$address     .= $user->address;
				}

				if (!empty($user->landmark))
				{
					$description .= '<br>' . $user->landmark;
					$address     .= ', ' . $user->address;
				}

				if (!empty($user->city))
				{
					$city        = $helper->location->getFieldValue($user->city, 'title');
					$description .= ', ' . $city;
					$address     .= ', ' . $city;
				}

				if (!empty($user->state_loc))
				{
					$state       = $helper->location->getFieldValue($user->state_loc, 'title');
					$description .= ', ' . $state;
					$address     .= ', ' . $state;
				}

				if (!empty($user->district))
				{
					$dist        = $helper->location->getFieldValue($user->district, 'title');
					$description .= $dist;
					$address     .= ', ' . $dist;
				}

				if (!empty($user->country))
				{
					$country     = $helper->location->getFieldValue($user->country, 'title');
					$description .= ', ' . $helper->location->getFieldValue($user->country, 'title');
					$address     .= ', ' . $country;
				}

				if (!empty($user->zip))
				{
					$description .= '-' . $user->zip;
					$address     .= '-' . $user->zip;
				}

				if (!empty($user->mobile))
				{
					$description .= '<br>' . $user->mobile;
				}

				$marker['description'] = $description;
				$marker['address']     = $address;
				$marker['lat']         = '';
				$marker['lng']         = '';

				if (!empty($user->location))
				{
					$location = explode(',', $user->location);

					$marker['lat'] = $location[0];
					$marker['lng'] = $location[1];
				}

				$icon = '';
				if (!empty($user->client_cat))
				{
					$icon = ModSellaciousUsersOnMap::getMapIcon($user->user_id, $user->client_cat);
				}
				if (!empty($user->mfr_cat))
				{
					$icon = ModSellaciousUsersOnMap::getMapIcon($user->user_id, $user->mfr_cat);
				}
				if (!empty($user->staff_cat))
				{
					$icon = ModSellaciousUsersOnMap::getMapIcon($user->user_id, $user->staff_cat);
				}
				if (!empty($user->seller_cat))
				{
					$icon = ModSellaciousUsersOnMap::getMapIcon($user->user_id, $user->seller_cat);
				}

				$marker['icon'] = $icon;

				$markers[] = $marker;
			}
		}
		catch (Exception $e)
		{
			$markers = array();
			JLog::add('Error occurred while getting sellacious user data.', JLog::WARNING);
		}

		return $markers;
	}

	/**
	 * @param $userId
	 * @param $catId
	 *
	 * @return mixed|string|string[]
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getMapIcon($userId, $catId)
	{
		$helper = SellaciousHelper::getInstance();
		$icon   = '';

		$filter    = array('list.select' => 'a.map_marker, a.google_map_icon', 'id' => $catId);
		$catMarker = $helper->category->loadObject($filter);

		if ($catMarker->map_marker == 'sellacious_avatar')
		{
			$icon = $helper->media->getImages('user.avatar', (int) $userId, false);
			$icon = reset($icon);
		}
		elseif ($catMarker->map_marker == 'custom_icon')
		{
			$icon = $helper->media->getImages('categories.custom_map_icon', (int) $catId, false);
			$icon = reset($icon);
		}
		elseif ($catMarker->map_marker == 'google_icon')
		{
			$icon = 'http://maps.google.com/mapfiles/ms/micons/' . $catMarker->google_map_icon . '?s-map-google-icon';
		}

		if (!empty($icon) && ($catMarker->map_marker == 'custom_icon' || $catMarker->map_marker == 'sellacious_avatar'))
		{
			$icon .= '?s-map-icon';
		}

		return $icon;
	}
}
