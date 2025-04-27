<?php

/**
 * SPDX-FileCopyrightText: 2022 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Romain Lebrun Thauront <romain@framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Controller;

use OCA\OwnershipTransfer\AppInfo\Application;
use OCA\OwnershipTransfer\Helper;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

/** @psalm-suppress UnusedClass */
class FileController extends OCSController {

	public function __construct(
		IRequest $request,
		private Helper $helper,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * Get files from the user's root directory
	 *
	 * @param string $userId the id of the user
	 * @param string $sortAttribute the attribute to sort on, optional
	 * @param bool $sortDescending whether files should be sorted descending, optional
	 * @return DataResponse<Http::STATUS_OK, array, array<string, mixed>>|DataResponse<Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array<string, mixed>>
	 *
	 * 200: returns the file list
	 * 500: returns an error
	 *
	 * @IgnoreOpenAPI
	 */
	public function index(string $userId, string $sortAttribute = 'name', bool $sortDescending = false): DataResponse {
		try {
			$fileList = $this->helper->getFiles('', $userId, $sortAttribute, $sortDescending);
		} catch (\Throwable $th) {
			return new DataResponse(['message' => $th->getMessage()], HTTP::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse($fileList);
	}

	/**
	 * Get files from user's given directory
	 *
	 * @param string $userId the id of the user
	 * @param string $sortAttribute the attribute to sort on, optional
	 * @param bool $sortDescending whether files should be sorted descending, optional
	 * @return DataResponse<Http::STATUS_OK, array, array<string, mixed>>|DataResponse<Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array<string, mixed>>
	 *
	 * 200: returns the file list
	 * 500: returns an error
	 *
	 * @IgnoreOpenAPI
	 */
	public function content(string $userId, string $dir, string $sortAttribute = 'name', bool $sortDescending = false): DataResponse {
		try {
			$fileList = $this->helper->getFiles(ltrim($dir, '/'), $userId, $sortAttribute, $sortDescending);
		} catch (\Throwable $th) {
			return new DataResponse(['message' => $th->getMessage()], HTTP::STATUS_INTERNAL_SERVER_ERROR);
		}
		return new DataResponse($fileList);
	}
}
