<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Command;

use App\Core\Command\AbstractCommand;
use App\Core\Session\CliSessionStartable;
use App\Repository\ClientSessionRepository;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:client-session:clean',
	description: 'Clean unused old client sessions',
)]
class ClientSessionCleanCommand extends AbstractCommand implements CliSessionStartable {
	
	public function __construct(
		private readonly ClientSessionRepository $clientSessionRepository
	) {
		parent::__construct();
	}
	
	protected function configure(): void {
		$this
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run mode');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		$dryRun = $input->getOption('dry-run');
		
		$before = new DateTime('midnight');
		
		$deleteIds = $this->clientSessionRepository->query()
			->select('session.id')
			->leftJoin('session.userAnswers', 'answer')
			->leftJoin('session.questions', 'question')
			->where('session.createDate < :before')
			->andWhere('answer IS NULL or question IS NULL')
			->setParameter('before', $before)
			->getQuery()->getResult();
		
		if( $dryRun ) {
			$count = count($deleteIds);
		} else {
			$count = $this->clientSessionRepository->query()
				->delete()
				->where('session.id IN (:ids)')
				->setParameter('ids', $deleteIds)
				->getQuery()->execute();
		}
		
		$io->success(sprintf('%sDeleted %d sessions', $dryRun ? '[DRY-RUN] ' : '', $count));
		
		return 0;
	}
	
}
