<?php
namespace KazSudo\Google\Language;

use KazSudo\Google\Language\ServiceWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('analyze')
      ->setDescription('Analyze syntax, sentiment and entities.')
      ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.
<info>php %command.full_name% text.txt</info>
<info>php %command.full_name% gs://bucket/text.txt</info>
EOF
      )
      ->addArgument('file', InputArgument::REQUIRED, 'Path to Cloud Storage file or local file.')
      ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Set key name.')
      ->addOption('config', null, InputOption::VALUE_OPTIONAL, 'Set config.php path.', './app/config.php')
      ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $language = new ServiceWrapper($input->getOption('config'));

    $info = null;
    $file = $input->getArgument('file');
    if(preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $file)){
      $info = $language->annotateTextFromCloudStorage($file);
    }
    else if(is_file($file)){
      $content = file_get_contents($file);
      $info = $language->annotateTextFromFile($file);
    }
    else{
      $output->writeln('<error>Invalid input file.</error>');
      return false;
    }

    if($info){
      if($input->getOption('name')){
        $info['_name'] = $input->getOption('name');
      }
      $ret = $language->saveToDataStore($info);

      if($ret){
        $output->writeln('Saved as <info>name:'.$info['_name'].'</info>');
      }
      else{
        $output->writeln('<error>Invalid input file.</error>');
      }
    }
    else{
      $output->writeln('<error>Annotation failed.</error>');
    }
  }
}
