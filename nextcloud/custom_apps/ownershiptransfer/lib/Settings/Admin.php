<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Settings;

use OCA\OwnershipTransfer\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IUserManager;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {

	public function __construct(
		private string $userId,
		private IUserManager $userManager,
		private IAppManager $appManager,
		private IInitialState $initialState,
	) {
	}

	/**
	 * @return TemplateResponse
	 * @throws \Exception
	 */
	public function getForm(): TemplateResponse {
		$user = $this->userManager->get($this->userId);
		if (!$user) {
			throw new \Exception('User not found');
		}

		$enabledApps = $this->appManager->getEnabledAppsForUser($user);
		$this->initialState->provideInitialState('enabledApps', $enabledApps);

		Util::addStyle(Application::APP_ID, 'ownershiptransfer-adminSettings');
		Util::addScript(Application::APP_ID, 'ownershiptransfer-adminSettings');
		return new TemplateResponse(Application::APP_ID, 'adminSettings');
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 10;
	}
}
