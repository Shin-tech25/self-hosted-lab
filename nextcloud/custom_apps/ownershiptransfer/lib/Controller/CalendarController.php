<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Controller;

use OCA\OwnershipTransfer\AppInfo\Application;
use OCA\OwnershipTransfer\Service\CalendarService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\IUserManager;

/** @psalm-suppress UnusedClass */
class CalendarController extends OCSController {

	public const PRINCIPALS_URI = 'principals/users/';

	public function __construct(
		IRequest $request,
		private IUserManager $userManager,
		private CalendarService $calendarService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * Get a list of the specified user's calendars
	 *
	 * @param string $userId the id of the user
	 * @return DataResponse<Http::STATUS_OK, array, array<string, mixed>>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array<string, mixed>>
	 *
	 * 200: returns the calendar list
	 * 400: the user doesn't exists
	 *
	 * @IgnoreOpenAPI
	 */
	public function getUserCalendars(string $userId): DataResponse {
		$user = $this->userManager->get($userId);

		if ($user == null) {
			return new DataResponse(['message' => "Couldn't fetch the user"], Http::STATUS_BAD_REQUEST);
		}

		$calendars = $this->calendarService->getCalendars($userId);

		return new DataResponse($calendars);
	}
}
