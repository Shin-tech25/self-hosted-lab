<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

return [
	'routes' => [
	],
	'ocs' => [
		['name' => 'ownershipTransfer#filesTransfer', 'url' => '/files', 'verb' => 'PUT'],
		['name' => 'ownershipTransfer#calendarTransfer', 'url' => '/calendar', 'verb' => 'PUT'],
		['name' => 'ownershipTransfer#contactsTransfer', 'url' => '/contacts', 'verb' => 'PUT'],

		['name' => 'calendar#getUserCalendars', 'url' => '/calendarlist', 'verb' => 'GET'],
		['name' => 'contacts#getUserAddressBooks', 'url' => '/addressbookslist', 'verb' => 'GET'],

		['name' => 'file#index', 'url' => '/nodelist', 'verb' => 'GET'],
		['name' => 'file#content', 'url' => '/nodelist/{dir}', 'verb' => 'GET', 'requirements' => ['dir' => '.+']],
	]
];
