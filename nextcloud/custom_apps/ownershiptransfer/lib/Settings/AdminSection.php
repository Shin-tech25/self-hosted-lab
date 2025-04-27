<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Settings;

use OCA\OwnershipTransfer\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;

use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {

	private IURLGenerator $urlGenerator;
	private IL10N $l;

	public function __construct(
		IURLGenerator $urlGenerator,
		IL10N $l,
	) {
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
	}

	/**
	 * returns the ID of the section. It is supposed to be a lower case string
	 *
	 * @returns string
	 */
	public function getID(): string {
		return Application::APP_ID; //or a generic id if feasible
	}

	/**
	 * returns the translated name as it should be displayed, e.g. 'LDAP / AD
	 * integration'. Use the L10N service to translate it.
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->l->t('Ownership Transfer');
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 *             the settings navigation. The sections are arranged in ascending order of
	 *             the priority values. It is required to return a value between 0 and 99.
	 */
	public function getPriority(): int {
		return 80;
	}

	/**
	 * @return string The relative path to a an icon describing the section
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}
}
