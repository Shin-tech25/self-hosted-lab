<?php

/**
 * Based on nextcloud/apps/files/lib/Helper.php by multiples authors and
 * somewhat stripped and modified
 *
 * SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-FileContributor: Romain Lebrun Thauront <romain@framasoft.org>
 * SPDX-FileContributor: Val Jossic <val@framasoft.org>
 *
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\OwnershipTransfer;

use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\StorageInvalidException;
use OCP\ITagManager;
use OCP\Share\IManager as IShareManager;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\Util;

/**
 * Helper class for manipulating file information
 */
class Helper {
	public function __construct(
		private IRootFolder $storage,
		private ITagManager $tagManager,
		private ISystemTagObjectMapper $tagObjectMapper,
		private ISystemTagManager $systemTagManager,
		private IShareManager $shareManager,
	) {
	}

	public static function concatenate_callback(string $carry, string $item): string {
		$carry .= $item;
		return $carry;
	}

	/**
	 * Comparator function to sort files alphabetically and have
	 * the directories appear first
	 *
	 * @param string[] $a file
	 * @param string[] $b file
	 * @return int -1 if $a must come before $b, 1 otherwise
	 */
	public function compareFileOrFolder(array $a, array $b): int {
		$aType = $a['type'];
		$bType = $b['type'];
		if ($aType === 'dir' and $bType !== 'dir') {
			return -1;
		} elseif ($aType !== 'dir' and $bType === 'dir') {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * Comparator function to sort files alphabetically and have
	 * the directories appear first
	 *
	 * @param string[] $a file
	 * @param string[] $b file
	 * @return int -1 if $a must come before $b, 1 otherwise
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function compareFileNames(array $a, array $b): int {
		$aName = $a['nodeName'];
		$bName = $b['nodeName'];
		return Util::naturalSortCompare($aName, $bName);
	}

	/**
	 * Comparator function to sort files by date
	 *
	 * @param string[] $a file
	 * @param string[] $b file
	 * @return int -1 if $a must come before $b, 1 otherwise
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function compareTimestamp(array $a, array $b): int {
		$aTime = $a['mtime'];
		$bTime = $b['mtime'];
		return ($aTime < $bTime) ? -1 : 1;
	}

	/**
	 * Comparator function to sort files by size
	 *
	 * @param string[] $a file
	 * @param string[] $b file
	 * @return int -1 if $a must come before $b, 1 otherwise
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function compareSize(array $a, array $b): int {
		$aSize = $a['size'];
		$bSize = $b['size'];
		return ($aSize < $bSize) ? -1 : 1;
	}

	/**
	 * Comparator function to sort files by tags
	 *
	 * @param string[] $a file
	 * @param string[] $b file
	 * @return int -1 if $a must come before $b, 1 otherwise
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function compareTags(array $a, array $b): int {
		$aTags = array_reduce($a['tags'], [Helper::class, 'concatenate_callback']);
		$bTags = array_reduce($b['tags'], [Helper::class, 'concatenate_callback']);
		return Util::naturalSortCompare($aTags, $bTags);
	}

	/**
	 * Comparator function to sort files by tags
	 *
	 * @param string[] $a file
	 * @param string[] $b file
	 * @return int -1 if $a must come before $b, 1 otherwise
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function compareSystemTags(array $a, array $b): int {
		$aTags = array_reduce($a['systemTags'], [Helper::class, 'concatenate_callback']);
		$bTags = array_reduce($b['systemTags'], [Helper::class, 'concatenate_callback']);
		return Util::naturalSortCompare($aTags, $bTags);
	}

	/**
	 *
	 * Retrieves the contents of the given directory and returns it as a sorted
	 * array of json ready fileList, populate with Tags and SystemTags
	 *
	 * @param string $dir path to the directory
	 * @param string $userId current user's id
	 * @param string $sortAttribute attribute to sort on
	 * @param bool $sortDescending true for descending sort, false otherwise
	 * @param bool $folderFirst true for having folders on top of files, false otherwise
	 * @return array fileList
	 * @throws InvalidPathException
	 * @throws StorageInvalidException
	 * @throws NotPermittedException
	 */
	public function getFiles(string $dir, string $userId, string $sortAttribute = 'name', bool $sortDescending = false, bool $folderFirst = true): array {
		$userFolder = $this->storage->getUserFolder($userId);
		try {
			$folder = $userFolder->get($dir);
			if ($folder instanceof Folder) {
				$nodes = $folder->getDirectoryListing();
				$content = $this->nodesToArray($nodes);
				return $this->sortFiles($content, $sortAttribute, $sortDescending, $folderFirst);
			} else {
				throw new StorageInvalidException('Can not read from folder');
			}
		} catch (NotFoundException $e) {
			throw new InvalidPathException('Folder does not exist');
		}
	}

	/**
	 * Populate the result set with file tags
	 *
	 * @param array $fileList
	 * @param string $fileIdentifier identifier attribute name for values in $fileList
	 * @return array file list populated with tags
	 */
	public function populateTags(array $fileList, string $fileIdentifier): array {
		$ids = [];
		foreach ($fileList as $fileData) {
			$ids[] = $fileData[$fileIdentifier];
		}
		$tagger = $this->tagManager->load('files');
		$tags = $tagger->getTagsForObjects($ids);

		if (!is_array($tags)) {
			throw new \UnexpectedValueException('$tags must be an array');
		}

		// Set empty tag array
		foreach ($fileList as $key => $fileData) {
			$fileList[$key]['tags'] = [];
		}

		if (!empty($tags)) {
			foreach ($tags as $fileId => $fileTags) {
				foreach ($fileList as $key => $fileData) {
					if ($fileId !== $fileData[$fileIdentifier]) {
						continue;
					}

					$fileList[$key]['tags'] = $fileTags;
				}
			}
		}

		return $fileList;
	}

	/**
	 * Populate the result set with file systems tags
	 *
	 * @param array $fileList
	 * @param string $fileIdentifier identifier attribute name for values in $fileList
	 * @return array file list populated with tags
	 */
	public function populateSystemTags(array $fileList, string $fileIdentifier): array {
		$ids = [];
		foreach ($fileList as $fileData) {
			$ids[] = $fileData[$fileIdentifier];
		}
		$systemTags = $this->tagObjectMapper->getTagIdsForObjects($ids, 'files');

		if (!is_array($systemTags)) {
			throw new \UnexpectedValueException('$systemTags must be an array');
		}

		// Set empty tag array
		foreach ($fileList as $key => $fileData) {
			$fileList[$key]['systemTags'] = [];
		}

		if (!empty($systemTags)) {
			foreach ($systemTags as $fileId => $fileTags) {
				foreach ($fileList as $key => $fileData) {
					if ($fileId !== $fileData[$fileIdentifier]) {
						continue;
					}
					$tagName = [];
					foreach ($this->systemTagManager->getTagsByIds($fileTags) as $tagObject) {
						$tagName[] = $tagObject->getName();
					}
					$fileList[$key]['systemTags'] = $tagName;
				}
			}
		}

		return $fileList;
	}

	/**
	 * Sort the given file info array
	 *
	 * @param array $fileList files to sort
	 * @param string $sortAttribute attribute to sort on
	 * @param bool $sortDescending true for descending sort, false otherwise
	 * @return array Sorted files
	 */
	public function sortFiles(array $fileList, string $sortAttribute = 'name', bool $sortDescending = false, $folderFirst = true): array {
		switch ($sortAttribute) {
			case 'mtime':
				$sortFunc = 'compareTimestamp';
				break;
			case 'size':
				$sortFunc = 'compareSize';
				break;
			case 'tags':
			case 'favorite':
				if (!array_key_exists('tags', $fileList)) {
					$fileList = $this->populateTags($fileList, 'id');
				}
				$sortFunc = 'compareTags';
				break;
			case 'systemtags':
				if (!array_key_exists('systemTags', $fileList)) {
					$fileList = $this->populateTags($fileList, 'id');
				}
				$sortFunc = 'compareSystemTags';
				break;
			case 'name':
			default:
				$sortFunc = 'compareFileNames';
				break;
		}
		usort($fileList, [Helper::class, $sortFunc]);
		if ($folderFirst) {
			usort($fileList, [Helper::class, 'compareFileOrFolder']);
		}
		if ($sortDescending) {
			$fileList = array_reverse($fileList);
		}
		return $fileList;
	}

	/**
	 * Json serialize FileInfos
	 *
	 * @param FileInfo[] $files FileInfos to serialize
	 *
	 * @return array Files metadata
	 */
	public function nodesToArray(array $files): array {
		$nodes = [];
		foreach ($files as $node_info) {
			$accessList = $this->shareManager->getAccessList($node_info);
			$nodes[] = [
				'id' => $node_info->getId(),
				'nodeName' => $node_info->getName(),
				'path' => preg_replace('/^(\/[^\/]*){2}/', '', $node_info->getPath()),
				'size' => $node_info->getSize(),
				'mtime' => $node_info->getMTime(),
				'type' => $node_info->getType(),
				'mimetype' => $node_info->getMimetype(),
				'sharedUsers' => $accessList['users'],
				'publicShared' => $accessList['public'],
				'owner' => $node_info->getOwner()?->getUID(),
			];
		}

		$nodes = $this->populateTags($nodes, 'id');
		return $this->populateSystemTags($nodes, 'id');
	}
}
