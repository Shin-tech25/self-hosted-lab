<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Service;

use OCA\DAV\CalDAV\CalDavBackend;
use OCA\DAV\CalDAV\Calendar;
use OCP\Calendar\IManager as ICalendarManager;
use OCP\Constants;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IUserManager;
use OCP\Share\IManager as IShareManager;
use Psr\Log\LoggerInterface;
use Sabre\DAV\PropPatch;

class CalendarService {

	public const PRINCIPALS_URI = 'principals/users/';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct(
		private CalDavBackend $calDav,
		private IUserManager $userManager,
		private ICalendarManager $calendarManager,
		private IDBConnection $db,
		private IShareManager $shareManager,
		private IGroupManager $groupManager,
		private IL10N $l10n,
		private IConfig $config,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * Transfers a calendar from the source to the destination user. Uses OCA\DAV\CalDAV\CalDavBackend
	 *
	 * @param string $sourceUserId id of the source user
	 * @param string $destinationUserId id of the destination user
	 * @param string $calendarUri uri of the calendar to transfer
	 */
	public function transferCalendar(string $sourceUserId, string $destinationUserId, string $calendarUri): void {
		$calendar = $this->calDav->getCalendarByUri(self::PRINCIPALS_URI . $sourceUserId, $calendarUri);

		if ($calendar === null) {
			throw new \Exception('Calendar not found');
		}

		if ($calendar['principaluri'] !== 'principals/users/' . $sourceUserId) {
			throw new \Exception('Calendar not owned by the source user');
		}

		$this->handleShares($calendar, $destinationUserId);

		$date = date('YmdHis');
		$newCalendarName = $calendarUri . '-' . $sourceUserId . '-' . $date;

		$this->calDav->moveCalendar($calendarUri, self::PRINCIPALS_URI . $sourceUserId, self::PRINCIPALS_URI . $destinationUserId, $newCalendarName);

		$newCalendar = $this->calDav->getCalendarByUri(self::PRINCIPALS_URI . $destinationUserId, $newCalendarName);

		if ($newCalendar !== null) {
			/** @var string */
			$oldDisplayName = $newCalendar['{DAV:}displayname'];
			/** @psalm-suppress PossiblyNullReference */
			$calendarPatch = new PropPatch(['{DAV:}displayname' => $oldDisplayName . '-' . $this->userManager->get($sourceUserId)->getDisplayName() . '-' . $date]);

			$this->calDav->updateCalendar($newCalendar['id'], $calendarPatch);
			$calendarPatch->commit();
		}
	}

	/**
	 * Tranfsers all the calendars from the source user to the destination user.
	 *
	 * @param string $sourceUserId id of the source user
	 * @param string $destinationUserId id of the destination user
	 */
	public function transferAllCalendars(string $sourceUserId, string $destinationUserId): void {
		$sourceCalendars = $this->calendarManager->getCalendarsForPrincipal(self::PRINCIPALS_URI . $sourceUserId);

		foreach ($sourceCalendars as $calendar) {
			$this->transferCalendar($sourceUserId, $destinationUserId, $calendar->getUri());
		}
	}

	/**
	 * Handles sharing conflicts when transferring a calendar (e.g. the calendar is shared with the destination user already)
	 *
	 * @param array $calendar the calendar to transfer, as returned from OCA\DAV\CalDAV\CalDavBackend
	 * @param string $userDestination the if of the destination user
	 */
	private function handleShares(array $calendar, string $userDestination): void {
		$shares = $this->calDav->getShares((int)$calendar['id']);
		foreach ($shares as $share) {
			/** @psalm-suppress PossiblyUndefinedArrayOffset */
			[, $prefix, $userOrGroup] = explode('/', $share['href'], 3);

			/**
			 * Check that user destination is member of the groups which whom the calendar was shared
			 */
			if ($this->shareManager->shareWithGroupMembersOnly() === true && $prefix === 'groups' && !$this->groupManager->isInGroup($userDestination, $userOrGroup)) {
				$this->calDav->updateShares(new Calendar($this->calDav, $calendar, $this->l10n, $this->config, $this->logger), [], ['principal:principals/groups/' . $userOrGroup]);
			}

			/**
			 * Check that calendar isn't already shared with user destination
			 */
			if ($userOrGroup === $userDestination) {
				$this->calDav->updateShares(new Calendar($this->calDav, $calendar, $this->l10n, $this->config, $this->logger), [], ['principal:principals/users/' . $userOrGroup]);
			}
		}
	}

	/**
	 * Returns a list of calendars for a user, with some associated details:
	 * * uri: the uri of the calendar
	 * * displayName: the canonical name of the calendar
	 * * eventsCount: the number of events in the calendar
	 * * shared: true if the calendar is shared, false otherwise
	 *
	 * @param string $userId the id of the user
	 * @return list<array{uri: string, displayName: string|null, eventsCount: int, shared: bool, receivedShare: bool}>
	 */
	public function getCalendars(string $userId): array {
		$result = [];

		$calendars = $this->calendarManager->getCalendarsForPrincipal(self::PRINCIPALS_URI . $userId);

		foreach ($calendars as $cal) {
			$calInfo = $this->calDav->getCalendarByUri(self::PRINCIPALS_URI . $userId, $cal->getUri());
			if ($calInfo == null || $cal->getPermissions() === Constants::PERMISSION_READ) {
				continue;
			}

			/** @var int $calId */
			$calId = $calInfo['id'];
			$calShares = $this->calDav->getShares($calId);

			$query = $this->db->getQueryBuilder();
			$query->select($query->func()->count('*'))
				->from('calendarobjects')
				->where($query->expr()->eq('calendarid', $query->createNamedParameter($calId, IQueryBuilder::PARAM_INT)));
			$queryResult = $query->executeQuery();
			$eventsCount = (int)$queryResult->fetchOne();
			$queryResult->closeCursor();

			$result[] = [
				'uri' => $cal->getUri(),
				'displayName' => $cal->getDisplayName(),
				'eventsCount' => $eventsCount,
				'shared' => sizeof($calShares) > 0,
				'receivedShare' => str_contains((string)$calInfo['uri'], '_shared_by_'),
			];
		}

		return $result;
	}
}
