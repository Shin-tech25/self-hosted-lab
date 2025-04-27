<?php
declare(strict_types=1);

namespace OCA\RenameByExif\AppInfo;

use OCP\AppFramework\App;
use OCP\IUserManager;
use OCP\Files\IRootFolder;
use OCA\RenameByExif\Command\RenameMedia;

class Application extends App {
    public function __construct(array $urlParams = []) {
        parent::__construct('renamebyexif', $urlParams);

        // DI コンテナから必要なサービスを取得
        /** @var IRootFolder $rootFolder */
        $rootFolder  = $this->getContainer()->get(IRootFolder::class);
        /** @var IUserManager $userManager */
        $userManager = $this->getContainer()->get(IUserManager::class);

        // コマンドインスタンスを生成し、Console アプリケーションに登録
        $command = new RenameMedia($rootFolder, $userManager);
        $this->getContainer()
             ->getServer()
             ->getConsole()
             ->addCommand($command);
    }
}
