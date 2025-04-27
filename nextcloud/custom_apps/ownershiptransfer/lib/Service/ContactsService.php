<?php

/**
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer\Service;

use OCA\DAV\CardDAV\AddressBook;
use OCA\DAV\CardDAV\CardDavBackend;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\Share\IManager as IShareManager;

class ContactsService {

	public const PRINCIPALS_URI = 'principals/users/';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct(
		private CardDavBackend $cardDav,
		private IDBConnection $db,
		private IShareManager $shareManager,
		private IGroupManager $groupManager,
		private IL10N $l10n,
	) {
	}

	/**
	 * Transfers an address book from the source to the destination user. Uses OCA\DAV\CardDAV\CardDavBackend
	 *
	 * @param string $sourceUserId id of the source user
	 * @param string $destinationUserId id of the destination user
	 * @param string $addressBookUri uri of the address book to transfer
	 */
	public function transferAddressBook(string $sourceUserId, string $destinationUserId, string $addressBookUri): void {
		$addressBook = $this->cardDav->getAddressBooksByUri(self::PRINCIPALS_URI . $sourceUserId, $addressBookUri);

		if ($addressBook === null) {
			throw new \Exception('AddressBook not found');
		}

		if ($addressBook['principaluri'] !== 'principals/users/' . $sourceUserId) {
			throw new \Exception('Adressbook not owned by the source user');
		}

		$addressBookName = (string)$addressBook['{DAV:}displayname'];
		$date = date('YmdHis');
		$newAddressBookName = $addressBookName . '-' . $sourceUserId . '-' . $date;
		$newAddressBookUri = $addressBookUri . '-' . $sourceUserId . '-' . $date;

		$this->handleShares($addressBook, $destinationUserId);

		$this->moveAddressBook($addressBookUri, self::PRINCIPALS_URI . $sourceUserId, self::PRINCIPALS_URI . $destinationUserId, $newAddressBookUri, $newAddressBookName);

	}

	/**
	 * Transfers all the address books from the source to the destination user
	 *
	 * @param string $sourceUserId the id of the source user
	 * @param string $destinationUserId the id of the destination user
	 */
	public function transferAllAddressBooks(string $sourceUserId, string $destinationUserId): void {
		/** @var array[] */
		$sourceAddressBooks = $this->cardDav->getAddressBooksForUser(self::PRINCIPALS_URI . $sourceUserId);

		foreach ($sourceAddressBooks as $addressBook) {
			$uri = (string)$addressBook['uri'];
			$this->transferAddressBook($sourceUserId, $destinationUserId, $uri);
		}
	}

	/**
	 * Handle sharing issues when transferring an addressbook
	 *
	 * @param array $addressBook an array describing the addressbook, as returned by OCA\DAV\CardDAV\CardDavBackend
	 * @param string $userDestination the id of the destination user
	 */
	private function handleShares(array $addressBook, string $userDestination): void {
		$shares = $this->cardDav->getShares((int)$addressBook['id']);
		foreach ($shares as $share) {
			/** @psalm-suppress PossiblyUndefinedArrayOffset */
			[, $prefix, $userOrGroup] = explode('/', $share['href'], 3);

			/**
			 * Check that user destination is member of the groups which whom the addressBook was shared
			 */
			if ($this->shareManager->shareWithGroupMembersOnly() === true && $prefix === 'groups' && !$this->groupManager->isInGroup($userDestination, $userOrGroup)) {
				$this->cardDav->updateShares(new AddressBook($this->cardDav, $addressBook, $this->l10n), [], ['principal:principals/groups/' . $userOrGroup]);
			}

			/**
			 * Check that addressBook isn't already shared with user destination
			 */
			if ($userOrGroup === $userDestination) {
				$this->cardDav->updateShares(new AddressBook($this->cardDav, $addressBook, $this->l10n), [], ['principal:principals/users/' . $userOrGroup]);
			}
		}
	}

	/**
	 * Move an address book from one user to another
	 *
	 * @param string $uriName the uri of the address book to move
	 * @param string $uriOrigin the principal uri of the address book to move
	 * @param string $uriDestination the principal uri of the destination
	 * @param string $newUriName the new uri of the address book
	 * @param string $newDisplayName the new display name of the address book
	 */
	private function moveAddressBook(string $uriName, string $uriOrigin, string $uriDestination, string $newUriName, string $newDisplayName): void {
		$query = $this->db->getQueryBuilder();
		$query->update('addressbooks')
			->set('principaluri', $query->createNamedParameter($uriDestination))
			->set('uri', $query->createNamedParameter($newUriName))
			->set('displayname', $query->createNamedParameter($newDisplayName))
			->where($query->expr()->eq('principaluri', $query->createNamedParameter($uriOrigin)))
			->andWhere($query->expr()->eq('uri', $query->createNamedParameter($uriName)))
			->executeStatement();
	}

	/**
	 * Returns a list of the user's address books, with some associated details:
	 * * uri: the address book's uri
	 * * displayName: the address book's canonical name
	 * * cardCount: the number of cards (contacts) in the address book
	 * * shared: true if the address book is shared, false otherwise
	 *
	 * @param string $userId the id of the user
	 * @return list<array{uri: string, displayName: string, cardsCount: int, shared: bool, receivedShare: bool}>
	 */
	public function getAddressBooks(string $userId): array {
		$result = [];

		/** @var array[] $addressBooks */
		$addressBooks = $this->cardDav->getAddressBooksForUser(self::PRINCIPALS_URI . $userId);

		foreach ($addressBooks as $addB) {
			// ignore read-only address books
			if ((bool)$addB['{http://owncloud.org/ns}read-only']) {
				continue;
			}

			/** @var int $addBId */
			$addBId = $addB['id'];
			$addBShares = $this->cardDav->getShares($addBId);

			$query = $this->db->getQueryBuilder();
			$query->select($query->func()->count('*'))
				->from('cards')
				->where($query->expr()->eq('addressbookid', $query->createNamedParameter($addBId, IQueryBuilder::PARAM_INT)));
			$queryResult = $query->executeQuery();
			$cardsCount = (int)$queryResult->fetchOne();
			$queryResult->closeCursor();

			$result[] = [
				'uri' => (string)$addB['uri'],
				'displayName' => (string)$addB['{DAV:}displayname'],
				'cardsCount' => $cardsCount,
				'shared' => sizeof($addBShares) > 0,
				'receivedShare' => str_contains((string)$addB['uri'], '_shared_by_'),
			];
		}

		return $result;
	}
}
