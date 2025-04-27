<?php
declare(strict_types=1);

namespace OCA\RenameByExif\Command;

use OCP\Files\IRootFolder;
use OCP\IUserManager;
use OCP\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RenameMedia extends Command {
    private IRootFolder $rootFolder;
    private IUserManager $userManager;

    public function __construct(IRootFolder $rootFolder, IUserManager $userManager) {
        parent::__construct();
        $this->rootFolder   = $rootFolder;
        $this->userManager  = $userManager;
    }

    protected function configure(): void {
        $this
            ->setName('renamebyexif:rename-media')
            ->setDescription('Rename .MOV/.HEIC files based on EXIF DateTimeOriginal')
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'User ID to process (all users if omitted)',
                ''
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Relative path in user folder to process (e.g. "Photos/2025")',
                ''
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Show what would be renamed without actually renaming'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $dryRun = (bool)$input->getOption('dry-run');
        $userId = trim((string)$input->getOption('user'));
        $subpath = trim((string)$input->getOption('path'), '/');

        // 対象ユーザーを決定
        $users = $userId !== ''
            ? [ $this->userManager->get($userId) ]
            : $this->userManager->search('');

        foreach ($users as $user) {
            if (!$user) {
                $output->writeln("<error>Unknown user: {$userId}</error>");
                continue;
            }
            $output->writeln("→ Processing user: {$user->getUID()}");

            // ユーザーフォルダを取得
            $userFolder = $this->rootFolder->getUserFolder($user->getUID());

            // path 指定があればそのディレクトリのみ、なければルート
            $startNode = $subpath !== ''
                ? $userFolder->get($subpath)
                : $userFolder;

            if ($subpath !== '' && $startNode->getType() !== 'dir') {
                $output->writeln("<error>Not a directory: {$subpath}</error>");
                continue;
            }

            $this->processFolder($startNode, $output, $dryRun);
        }

        $output->writeln('✔ All done.');
        return Command::SUCCESS;
    }

    /**
     * @param \OCP\Files\Folder|\OCP\Files\File $node
     */
    private function processFolder($node, OutputInterface $output, bool $dryRun): void {
        // ディレクトリは再帰
        if ($node->getType() === 'dir') {
            /** @var \OCP\Files\Folder $folder */
            $folder = $node;
            foreach ($folder->getDirectoryListing() as $child) {
                $this->processFolder($child, $output, $dryRun);
            }
            return;
        }

        // ファイルだけ処理
        /** @var \OCP\Files\File $file */
        $file = $node;
        $ext = strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['mov', 'heic'], true)) {
            return;
        }

        // EXIF 取得
        $meta = @exif_read_data($file->getPath());
        if (empty($meta['DateTimeOriginal'])) {
            $output->writeln("  [skip] {$file->getName()} (no EXIF date)");
            return;
        }

        // 新ファイル名を組み立て
        $dt = str_replace([':', ' '], ['', '_'], $meta['DateTimeOriginal']);
        $newName = "{$dt}_{$file->getName()}";

        if ($dryRun) {
            $output->writeln("  [dry-run] {$file->getName()} → {$newName}");
        } else {
            $file->move($file->getParent()->getPath(), $newName);
            $output->writeln("  Renamed: {$file->getName()} → {$newName}");
        }
    }
}
