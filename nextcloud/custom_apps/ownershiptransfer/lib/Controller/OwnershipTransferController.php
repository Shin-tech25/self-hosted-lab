<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace OCA\OwnershipTransfer\Controller;

use OCA\Files\BackgroundJob\TransferOwnership;
use OCA\Files\Db\TransferOwnership as TransferOwnershipEntity;
use OCA\Files\Db\TransferOwnershipMapper;
use OCA\OwnershipTransfer\Service\CalendarService;
use OCA\OwnershipTransfer\Service\ContactsService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\BackgroundJob\IJobList;
use OCP\Files\IHomeStorage;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IUserManager;

/** @psalm-suppress UnusedClass */
/** @psalm-suppress MissingDependency */
class OwnershipTransferController extends OCSController {

	public const CALENDAR_URI = 'principals/users/';

	public function __construct(
		string $appName,
		IRequest $request,
		private IUserManager $userManager,
		private CalendarService $calendarService,
		private IRootFolder $rootFolder,
		private TransferOwnershipMapper $transferMapper,
		private IJobList $jobList,
		private ContactsService $contactsService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Launch a files transfer of the provided path from the source to the destination user
	 *
	 * @param string $sourceUserId the id of the source user
	 * @param string $destinationUserId the id of the destination user
	 * @param string $path the path of the folder to transfer, optional, defaults to the root folder (transfers all the files)
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_BAD_REQUEST|Http::STATUS_INTERNAL_SERVER_ERROR, array<empty>, array<string, mixed>>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array<string, mixed>>
	 *
	 * 200: files transfer launched
	 * 400: source or destination user doesn't exist or path doesn't exist
	 * 500: problem fetching the source user's root folder
	 *
	 * @IgnoreOpenAPI
	 */
	public function filesTransfer(string $sourceUserId, string $destinationUserId, string $path = '/'): DataResponse {
		$sourceUser = $this->userManager->get($sourceUserId);
		$destinationUser = $this->userManager->get($destinationUserId);

		if ($sourceUser == null || $destinationUser == null) {
			return new DataResponse(['message' => 'Error fetching the source or destination user'], Http::STATUS_BAD_REQUEST);
		}

		$userRoot = $this->rootFolder->getUserFolder($sourceUserId);

		try {
			$node = $userRoot->get($path);
		} catch (\Exception $e) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		if ($node->getOwner()?->getUID() !== $sourceUserId) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		if (!$node->getStorage()->instanceOfStorage(IHomeStorage::class)) {
			return new DataResponse([], Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		$transferOwnership = new TransferOwnershipEntity();
		$transferOwnership->setNodeName($node->getName());
		$transferOwnership->setFileId($node->getId());
		$transferOwnership->setSourceUser($sourceUserId);
		$transferOwnership->setTargetUser($destinationUserId);
		$this->transferMapper->insert($transferOwnership);

		$this->jobList->add(TransferOwnership::class, [
			'id' => $transferOwnership->getId(),
		]);

		return new DataResponse([], Http::STATUS_OK);
	}

	/**
	 * Transfers the calendar of the source user to the destination user.
	 * If no calendar uri is given, transfers every calendar of the source user.
	 *
	 * @param string $sourceUserId id of the source user
	 * @param string $destinationUserId id of the destination user
	 * @param string $calendarUri uri of the calendar to transfer, optional, if not provided transfers every calendar
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array<string, mixed>>|DataResponse<Http::STATUS_BAD_REQUEST|Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array<string, mixed>>
	 *
	 * 200: calendar transfer completed
	 * 400: source or destination user doesn't exist
	 * 500: calendar transfer failed
	 *
	 * @IgnoreOpenAPI
	 */
	public function calendarTransfer(string $sourceUserId, string $destinationUserId, string $calendarUri = ''): DataResponse {
		$sourceUser = $this->userManager->get($sourceUserId);
		$destinationUser = $this->userManager->get($destinationUserId);

		if ($sourceUser == null || $destinationUser == null) {
			return new DataResponse(['message' => 'Error fetching the source or destination user'], Http::STATUS_BAD_REQUEST);
		}

		try {
			if ($calendarUri === '') {
				$this->calendarService->transferAllCalendars($sourceUserId, $destinationUserId);
			} else {
				$this->calendarService->transferCalendar($sourceUserId, $destinationUserId, $calendarUri);
			}
		} catch (\Throwable $th) {
			return new DataResponse(['message' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse([], Http::STATUS_OK);
	}

	/**
	 * Transfers the address book of the source user to the destination user.
	 * If no address book uri is given, transfers every address book of the source user.
	 *
	 * @param string $sourceUserId id of the source user
	 * @param string $destinationUserId id of the destination user
	 * @param string $addressBookUri uri of the addressBook to transfer, optional, transfer every address book in not provided
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array<string, mixed>>|DataResponse<Http::STATUS_BAD_REQUEST|Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array<string, mixed>>
	 *
	 * 200: contacts transfer completed
	 * 400: source or destination user doesn't exist
	 * 500: contacts transfer failed
	 *
	 * @IgnoreOpenAPI
	 */
	public function contactsTransfer(string $sourceUserId, string $destinationUserId, string $addressBookUri = ''): DataResponse {
		$sourceUser = $this->userManager->get($sourceUserId);
		$destinationUser = $this->userManager->get($destinationUserId);

		if ($sourceUser == null || $destinationUser == null) {
			return new DataResponse(['message' => 'Error fetching the source or destination user'], Http::STATUS_BAD_REQUEST);
		}

		try {
			if ($addressBookUri === '') {
				$this->contactsService->transferAllAddressBooks($sourceUserId, $destinationUserId);
			} else {
				$this->contactsService->transferAddressBook($sourceUserId, $destinationUserId, $addressBookUri);
			}
		} catch (\Throwable $th) {
			return new DataResponse(['message' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse([], Http::STATUS_OK);
	}
}
