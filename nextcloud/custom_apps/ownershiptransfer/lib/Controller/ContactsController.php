<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Controller;

use OCA\OwnershipTransfer\AppInfo\Application;
use OCA\OwnershipTransfer\Service\ContactsService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\IUserManager;

/** @psalm-suppress UnusedClass */
class ContactsController extends OCSController {

	public const PRINCIPALS_URI = 'principals/users/';

	public function __construct(
		IRequest $request,
		private IUserManager $userManager,
		private ContactsService $contactsService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * Get a list of an user's address books
	 *
	 * @param string $userId the id of the user
	 * @return DataResponse<Http::STATUS_OK, array, array<string, mixed>>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array<string, mixed>>
	 *
	 * 200: returns the list of address books
	 * 400: user doesn't exists
	 *
	 * @IgnoreOpenAPI
	 */
	public function getUserAddressBooks(string $userId): DataResponse {
		$user = $this->userManager->get($userId);

		if ($user == null) {
			return new DataResponse(['message' => "Couldn't fetch the user"], Http::STATUS_BAD_REQUEST);
		}

		$addressBooks = $this->contactsService->getAddressBooks($userId);

		return new DataResponse($addressBooks);
	}
}
